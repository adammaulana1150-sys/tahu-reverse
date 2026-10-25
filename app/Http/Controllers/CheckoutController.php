<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function store(CheckoutRequest $request): JsonResponse
    {
        $result = DB::transaction(function () use ($request): array {
            $product = Product::query()
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();
            $quantity = (int) $request->validated('quantity');
            $pendingQuantity = Order::query()
                ->where('product_id', $product->id)
                ->whereHas('payment', fn ($query) => $query->where('status', 'pending'))
                ->sum('quantity');

            if ($quantity > max(0, $product->stock - $pendingQuantity)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah melebihi stok yang tersedia.',
                ]);
            }
            if ($product->price > intdiv(4294967295, $quantity)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Total pesanan melebihi batas transaksi yang didukung.',
                ]);
            }

            $phone = preg_replace('/\D+/', '', $request->validated('phone'));
            if (str_starts_with($phone, '0')) {
                $phone = '62'.substr($phone, 1);
            }

            $customer = Customer::updateOrCreate(['phone' => $phone], [
                'name' => $request->validated('name'),
                'address' => $request->validated('address'),
            ]);

            do {
                $orderCode = 'TR-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            } while (Order::where('order_code', $orderCode)->exists());

            do {
                $token = Str::random(64);
                $tokenHash = hash('sha256', $token);
            } while (Payment::where('lookup_token_hash', $tokenHash)->exists());

            $expiresAt = now()->addMinutes(30);

            $order = Order::create([
                'order_code' => $orderCode,
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total' => $product->price * $quantity,
                'note' => $request->validated('note'),
                'status' => 'pending',
                'fulfillment_status' => 'new',
                'expires_at' => $expiresAt,
            ]);

            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => 'qris_static',
                'reference' => $orderCode,
                'lookup_token_hash' => $tokenHash,
                'status' => 'pending',
                'amount' => $order->total,
                'expires_at' => $expiresAt,
            ]);

            PaymentEvent::create([
                'payment_id' => $payment->id,
                'from_status' => null,
                'to_status' => 'pending',
                'reason' => 'Pesanan QRIS dibuat.',
            ]);

            return [
                'payment_url' => route('payments.show', ['token' => $token]),
            ];
        });

        return response()->json($result);
    }
}
