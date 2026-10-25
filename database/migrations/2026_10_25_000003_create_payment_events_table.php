<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24);
            $table->string('transaction_reference')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'created_at']);
        });

        DB::table('orders')->orderBy('id')->chunkById(500, function ($orders): void {
            $paymentIds = DB::table('payments')
                ->whereIn('order_id', $orders->pluck('id'))
                ->pluck('id', 'order_id');
            $events = [];

            foreach ($orders as $order) {
                $status = match ($order->status) {
                    'paid' => 'paid',
                    'failed', 'cancelled' => 'rejected',
                    'expired' => 'expired',
                    default => 'pending',
                };

                $events[] = [
                    'payment_id' => $paymentIds[$order->id],
                    'admin_id' => null,
                    'from_status' => null,
                    'to_status' => $status,
                    'transaction_reference' => null,
                    'reason' => 'Status diimpor dari pesanan lama; bukti dan admin historis tidak tersedia.',
                    'created_at' => $order->paid_at ?? $order->updated_at ?? $order->created_at,
                    'updated_at' => $order->updated_at ?? $order->created_at,
                ];
            }

            DB::table('payment_events')->insert($events);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
