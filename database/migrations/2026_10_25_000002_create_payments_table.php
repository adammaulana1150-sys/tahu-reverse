<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->string('method', 32);
            $table->string('reference')->unique();
            $table->char('lookup_token_hash', 64)->nullable()->unique();
            $table->string('status', 24)->index();
            $table->unsignedInteger('amount');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('verification_reference')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        DB::table('orders')->orderBy('id')->chunkById(500, function ($orders): void {
            foreach ($orders as $order) {
                $paymentStatus = match ($order->status) {
                    'paid' => 'paid',
                    'failed', 'cancelled' => 'rejected',
                    'expired' => 'expired',
                    default => 'pending',
                };

                DB::table('payments')->insert([
                    'order_id' => $order->id,
                    'method' => 'legacy',
                    'reference' => $order->order_code,
                    'status' => $paymentStatus,
                    'amount' => $order->total,
                    'reviewed_at' => $paymentStatus === 'paid' ? $order->paid_at : null,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
