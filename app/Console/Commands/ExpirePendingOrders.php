<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PaymentEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Expire pending orders and payments whose expires_at has passed,
 * and release the stock they reserved.
 *
 * Assumption: pending orders/payments expire 30 minutes after creation
 * (set in CheckoutController). Run this command periodically via cron/scheduler.
 *
 * Usage: php artisan orders:expire-pending
 */
class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire-pending';

    protected $description = 'Expire pending orders/payments past their expires_at and release reserved stock.';

    public function handle(): int
    {
        $now = now();

        $expiredOrders = Order::query()
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->lockForUpdate()
            ->get();

        $count = 0;

        foreach ($expiredOrders as $order) {
            $count += $this->expireOrder($order, $now);
        }

        $this->info("Expired {$count} pending order(s) and released reserved stock.");

        return Command::SUCCESS;
    }

    protected function expireOrder(Order $order, \Carbon\CarbonInterface $now): int
    {
        return DB::transaction(function () use ($order, $now): int {
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return 0;
            }

            if ($payment->status !== 'pending') {
                return 0;
            }

            // Release reserved stock back to the product.
            $product = Product::query()->lockForUpdate()->find($order->product_id);
            if ($product) {
                $product->increment('stock', $order->quantity);
            }

            // Mark the payment as expired.
            $payment->update([
                'status' => 'expired',
            ]);

            // Mark the order as expired / cancelled.
            $order->update([
                'status' => 'expired',
                'fulfillment_status' => 'cancelled',
            ]);

            // Record the audit event.
            PaymentEvent::create([
                'payment_id' => $payment->id,
                'admin_id' => null,
                'from_status' => 'pending',
                'to_status' => 'expired',
                'transaction_reference' => null,
                'reason' => 'Order expired automatically; pending payment window elapsed. Stock released.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return 1;
        });
    }
}