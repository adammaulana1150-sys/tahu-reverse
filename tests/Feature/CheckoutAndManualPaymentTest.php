<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutAndManualPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_creates_a_pending_qris_payment_using_the_server_product_price(): void
    {
        $product = $this->createProduct();

        $response = $this->postJson(route('checkout.store'), [
            'name' => 'Ayu',
            'phone' => '081234567890',
            'address' => 'Jalan Melati 10',
            'quantity' => 2,
            'price' => 1,
        ]);

        $response->assertOk()->assertJsonMissingPath('snap_token');
        $token = basename(parse_url($response->json('payment_url'), PHP_URL_PATH));
        $this->assertSame(64, strlen($token));

        $order = Order::query()->firstOrFail();
        $payment = Payment::query()->where('order_id', $order->id)->firstOrFail();

        $this->assertMatchesRegularExpression('/^TR-\d{8}-[A-Z0-9]{8}$/', $order->order_code);
        $this->assertSame('TR-'.now()->format('Ymd').'-'.substr($order->order_code, -8), $payment->reference);
        $this->assertSame(hash('sha256', $token), $payment->lookup_token_hash);
        $this->assertSame('qris_static', $payment->method);
        $this->assertSame('pending', $payment->status);
        $this->assertSame(20000, $payment->amount);
        $this->assertSame('new', $order->fulfillment_status);
        $this->assertSame(10000, $order->unit_price);
        $this->assertSame(20000, $order->total);
        $this->assertSame(1, PaymentEvent::count());
        $this->assertDatabaseHas('customers', [
            'name' => 'Ayu',
            'phone' => '6281234567890',
        ]);
        $this->assertSame($product->id, $order->product_id);
    }

    public function test_pending_qris_orders_reserve_stock_and_order_reference_is_unique(): void
    {
        $this->createProduct(3);

        foreach ([1, 1] as $quantity) {
            $response = $this->postJson(route('checkout.store'), [
                'name' => 'Ayu',
                'phone' => '081234567890',
                'address' => 'Jalan Melati 10',
                'quantity' => $quantity,
            ]);
            $response->assertOk();
        }

        $this->assertDatabaseCount('orders', 2);
        $this->assertSame(2, Order::query()->distinct()->count('order_code'));

        $this->postJson(route('checkout.store'), [
            'name' => 'Bima',
            'phone' => '081234567891',
            'address' => 'Jalan Mawar 5',
            'quantity' => 2,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('orders', 2);
    }

    public function test_payment_page_uses_a_private_token_and_warns_when_qris_is_not_configured(): void
    {
        [$order, , $token] = $this->createOrderAndPayment();

        $this->get(route('payments.show', $token))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee('Rp 10.000')
            ->assertSee('pending')
            ->assertSee('QRIS merchant resmi belum dikonfigurasi.')
            ->assertDontSee('Ayu')
            ->assertDontSee('6281234567890')
            ->assertDontSee('Jalan Melati 10');

        $this->get(route('payments.show', str_repeat('z', 64)))->assertNotFound();
    }

    public function test_payment_page_displays_the_uploaded_official_qris_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qris/official.png', 'image-content');
        Setting::create(['key' => 'qris_image_path', 'value' => 'qris/official.png']);
        [, , $token] = $this->createOrderAndPayment();

        $this->get(route('payments.show', $token))
            ->assertOk()
            ->assertSee('qris/official.png')
            ->assertDontSee('belum dikonfigurasi');
    }

    public function test_only_an_admin_can_verify_a_pending_payment_and_replay_is_idempotent(): void
    {
        [$order] = $this->createOrderAndPayment();

        $payload = [
            'verification_reference' => 'MERCHANT-TRX-123',
            'confirm' => '1',
        ];

        $this->post(route('admin.orders.payment.verify', $order), $payload)
            ->assertRedirect(route('admin.login'));

        $admin = $this->createAdmin();
        $route = route('admin.orders.payment.verify', $order);

        $this->actingAs($admin)->post($route, $payload)->assertRedirect();
        $this->actingAs($admin)->post($route, $payload)->assertRedirect();

        $order->refresh();
        $payment = $order->payment()->firstOrFail();

        $this->assertSame('paid', $payment->status);
        $this->assertSame('paid', $order->status);
        $this->assertSame('processing', $order->fulfillment_status);
        $this->assertSame($admin->id, $payment->reviewed_by);
        $this->assertNotNull($payment->reviewed_at);
        $this->assertSame('MERCHANT-TRX-123', $payment->verification_reference);
        $this->assertSame(9, $order->product->fresh()->stock);
        $this->assertSame(1, PaymentEvent::where('to_status', 'paid')->count());
        $this->assertSame(2, PaymentEvent::count());
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Rp 10.000');
    }

    public function test_authenticated_non_admin_cannot_verify_payment(): void
    {
        [$order] = $this->createOrderAndPayment();
        $user = User::create([
            'name' => 'Guest',
            'email' => 'guest@example.test',
            'password' => 'password',
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->post(route('admin.orders.payment.verify', $order), [
                'verification_reference' => 'MERCHANT-TRX-123',
                'confirm' => '1',
            ])
            ->assertForbidden();

        $this->assertSame('pending', $order->payment()->value('status'));
    }

    public function test_rejected_payment_is_audited_and_never_counted_as_revenue(): void
    {
        [$order] = $this->createOrderAndPayment();
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.orders.payment.reject', $order), [
                'reason' => 'Transaksi tidak ditemukan di aplikasi merchant.',
                'confirm' => '1',
            ])
            ->assertRedirect();

        $payment = $order->payment()->firstOrFail();
        $this->assertSame('rejected', $payment->status);
        $this->assertSame('cancelled', $order->fresh()->fulfillment_status);
        $this->assertSame($admin->id, $payment->reviewed_by);
        $this->assertDatabaseHas('payment_events', [
            'payment_id' => $payment->id,
            'admin_id' => $admin->id,
            'to_status' => 'rejected',
            'reason' => 'Transaksi tidak ditemukan di aplikasi merchant.',
        ]);
        $this->assertSame(0, Payment::where('status', 'paid')->sum('amount'));
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Rp 0');
    }

    public function test_failed_checkout_does_not_create_partial_records(): void
    {
        $this->createProduct(1);

        $this->postJson(route('checkout.store'), [
            'name' => 'Ayu',
            'phone' => '081234567890',
            'address' => 'Jalan Melati 10',
            'quantity' => 2,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_pending_order_expires_after_expires_at_and_releases_stock(): void
    {
        $product = $this->createProduct(stock: 5);

        $this->postJson(route('checkout.store'), [
            'name' => 'Ayu',
            'phone' => '081234567890',
            'address' => 'Jalan Melati 10',
            'quantity' => 2,
        ])->assertOk();

        $order = Order::query()->firstOrFail();
        $this->assertNotNull($order->expires_at);
        $this->assertSame(3, $product->fresh()->stock); // 5 - 2 reserved

        // Force the order into the past.
        $order->update(['expires_at' => now()->subMinute()]);

        $this->artisan('orders:expire-pending')->assertExitCode(0);

        $order->refresh();
        $this->assertSame('expired', $order->status);
        $this->assertSame('cancelled', $order->fulfillment_status);
        $this->assertSame('expired', $order->payment()->value('status'));

        // Stock should be released back to the product.
        $this->assertSame(5, $product->fresh()->stock);

        // An audit event should record the expiration.
        $this->assertDatabaseHas('payment_events', [
            'to_status' => 'expired',
            'reason' => 'Order expired automatically; pending payment window elapsed. Stock released.',
        ]);
    }

    public function test_expire_pending_orders_command_only_expires_past_orders(): void
    {
        $this->createProduct(stock: 10);

        // Create an order that is not yet expired.
        $this->postJson(route('checkout.store'), [
            'name' => 'Ayu',
            'phone' => '081234567890',
            'address' => 'Jalan Melati 10',
            'quantity' => 1,
        ])->assertOk();

        $activeOrder = Order::query()->firstOrFail();
        $activeOrder->update(['expires_at' => now()->addHour()]);

        // Create an order that is already expired.
        $this->postJson(route('checkout.store'), [
            'name' => 'Bima',
            'phone' => '081234567891',
            'address' => 'Jalan Mawar 5',
            'quantity' => 1,
        ])->assertOk();

        $expiredOrder = Order::query()->latest('id')->firstOrFail();
        $expiredOrder->update(['expires_at' => now()->subHour()]);

        $this->artisan('orders:expire-pending')->assertExitCode(0);

        $activeOrder->refresh();
        $expiredOrder->refresh();

        $this->assertSame('pending', $activeOrder->status);
        $this->assertSame('expired', $expiredOrder->status);
    }

    public function test_admin_can_upload_and_remove_the_future_official_qris_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'site_title' => 'Tahu Reverse',
                'whatsapp_number' => '',
                'instagram_url' => '',
                'qris_image' => UploadedFile::fake()->createWithContent(
                    'merchant-qris.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect();

        $imagePath = Setting::whereKey('qris_image_path')->value('value');
        $this->assertNotEmpty($imagePath);
        Storage::disk('public')->assertExists($imagePath);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'site_title' => 'Tahu Reverse',
                'whatsapp_number' => '',
                'instagram_url' => '',
                'remove_qris_image' => '1',
            ])
            ->assertRedirect();

        $this->assertNull(Setting::whereKey('qris_image_path')->value('value'));
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_order_price_snapshot_is_unchanged_after_product_price_changes(): void
    {
        [$order, ,] = $this->createOrderAndPayment();
        $product = $order->product;
        $product->update(['price' => 25000]);

        $this->assertSame(10000, $order->fresh()->unit_price);
        $this->assertSame(10000, $order->fresh()->total);
        $this->assertSame(10000, $order->payment()->value('amount'));
    }

    private function createProduct(int $stock = 10): Product
    {
        return Product::create([
            'name' => 'Tahu Reverse',
            'slug' => 'tahu-reverse',
            'description' => 'Tahu walik.',
            'price' => 10000,
            'stock' => $stock,
            'image' => 'images/product.jpg',
            'is_active' => true,
        ]);
    }

    private function createOrderAndPayment(): array
    {
        $order = $this->createOrderForProduct($this->createProduct());
        $token = str_repeat('a', 64);
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'qris_static',
            'reference' => $order->order_code,
            'lookup_token_hash' => hash('sha256', $token),
            'status' => 'pending',
            'amount' => $order->total,
        ]);
        PaymentEvent::create([
            'payment_id' => $payment->id,
            'from_status' => null,
            'to_status' => 'pending',
            'reason' => 'Pesanan QRIS dibuat.',
        ]);

        return [$order, $payment, $token];
    }

    private function createOrderForProduct(Product $product): Order
    {
        $customer = Customer::create([
            'name' => 'Ayu',
            'phone' => '6281234567890',
            'address' => 'Jalan Melati 10',
        ]);

        return Order::create([
            'order_code' => 'TR-20261025-TEST0001',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 10000,
            'total' => 10000,
            'status' => 'pending',
            'fulfillment_status' => 'new',
        ]);
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);
    }
}
