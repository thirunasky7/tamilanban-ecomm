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
            $table->string('razorpay_order_id')->nullable()->after('payment_status');
            $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
        });

        DB::statement("ALTER TABLE orders MODIFY payment_method VARCHAR(20) NOT NULL DEFAULT 'cod'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['razorpay_order_id', 'razorpay_payment_id']);
        });

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cod','card','upi','gateway') NOT NULL DEFAULT 'cod'");
    }
};
