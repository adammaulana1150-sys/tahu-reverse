<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_status', 24)->default('new')->index();
        });

        DB::table('orders')
            ->where('status', 'paid')
            ->update(['fulfillment_status' => 'processing']);

        DB::table('orders')
            ->whereIn('status', ['failed', 'expired', 'cancelled'])
            ->update(['fulfillment_status' => 'cancelled']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['fulfillment_status']);
            $table->dropColumn('fulfillment_status');
        });
    }
};
