<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ManualPaymentVerificationService
{
    public function verify(Order $order, User $admin, string $transactionReference): Payment
    {
        return DB::transaction(function () use ($order, $admin, $transactionReference): Payment {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $payment = Payment::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();

            if ($payment->status === 'paid') {
                return $payment;
            }

            if ($payment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran ini tidak lagi menunggu verifikasi.',
                ]);
            }

            $product = Product::query()->lockForUpdate()->findOrFail($lockedOrder->product_id);
            if ($product->stock < $lockedOrder->quantity) {
                Log::critical('Verified payment exceeds available product stock.', [
                    'order_code' => $lockedOrder->order_code,
                    'product_id' => $product->id,
                    'stock' => $product->stock,
                    'quantity' => $lockedOrder->quantity,
                ]);

                throw ValidationException::withMessages([
                    'payment' => 'Stok tidak mencukupi untuk memproses pesanan yang telah dibayar.',
                ]);
            }

            $product->decrement('stock', $lockedOrder->quantity);
            $payment->update([
                'status' => 'paid',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'verification_reference' => $transactionReference,
            ]);
            $lockedOrder->update([
                'status' => 'paid',
                'fulfillment_status' => 'processing',
                'paid_at' => now(),
            ]);

            PaymentEvent::create([
                'payment_id' => $payment->id,
                'admin_id' => $admin->id,
                'from_status' => 'pending',
                'to_status' => 'paid',
                'transaction_reference' => $transactionReference,
            ]);

            return $payment->refresh();
        });
    }

    public function reject(Order $order, User $admin, string $reason): Payment
    {
        return DB::transaction(function () use ($order, $admin, $reason): Payment {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $payment = Payment::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();

            if ($payment->status === 'rejected') {
                return $payment;
            }

            if ($payment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran ini tidak lagi menunggu verifikasi.',
                ]);
            }

            $payment->update([
                'status' => 'rejected',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);
            $lockedOrder->update([
                'status' => 'failed',
                'fulfillment_status' => 'cancelled',
            ]);

            PaymentEvent::create([
                'payment_id' => $payment->id,
                'admin_id' => $admin->id,
                'from_status' => 'pending',
                'to_status' => 'rejected',
                'reason' => $reason,
            ]);

            return $payment->refresh();
        });
    }
}
