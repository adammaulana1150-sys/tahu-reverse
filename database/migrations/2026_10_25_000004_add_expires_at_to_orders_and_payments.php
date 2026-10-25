<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add expires_at to orders and payments so pending orders/payments
     * can be automatically expired and their reserved stock released.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->after('paid_at');
            $table->index('expires_at');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->after('amount');
            $table->index('expires_at');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['expires_at']);
            $table->dropColumn('expires_at');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex(['expires_at']);
            $table->dropColumn('expires_at');
        });
    }
};