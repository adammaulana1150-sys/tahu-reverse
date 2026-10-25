<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderFulfillmentService
{
    public function update(Order $order, User $admin, string $status): Order
    {
        return DB::transaction(function () use ($order, $admin, $status): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $payment = Payment::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();
            $current = $lockedOrder->fulfillment_status;

            if ($current === $status) {
                return $lockedOrder;
            }

            $allowed = match ($current) {
                'new' => ['cancelled'],
                'processing' => ['ready'],
                'ready' => ['completed'],
                default => [],
            };

            if (! in_array($status, $allowed, true)) {
                throw ValidationException::withMessages([
                    'fulfillment_status' => 'Perubahan status pesanan ini tidak diizinkan.',
                ]);
            }

            if ($status === 'cancelled') {
                if ($payment->status === 'paid') {
                    throw ValidationException::withMessages([
                        'fulfillment_status' => 'Pesanan yang sudah dibayar tidak dapat dibatalkan tanpa proses pengembalian dana.',
                    ]);
                }

                if ($payment->status === 'pending') {
                    $payment->update([
                        'status' => 'rejected',
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                        'rejection_reason' => 'Pesanan dibatalkan oleh admin.',
                    ]);

                    PaymentEvent::create([
                        'payment_id' => $payment->id,
                        'admin_id' => $admin->id,
                        'from_status' => 'pending',
                        'to_status' => 'rejected',
                        'reason' => 'Pesanan dibatalkan oleh admin.',
                    ]);
                }

                $lockedOrder->status = 'cancelled';
            }

            $lockedOrder->fulfillment_status = $status;
            $lockedOrder->save();

            return $lockedOrder;
        });
    }
}
