<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->boolean('payment_enabled')->default(false)->comment('Whether payment processing is enabled');
            $table->string('default_payment_gateway')->default('none')->comment('Default payment gateway: none, paystack, flutterwave, stripe');
            $table->json('payment_settings')->nullable()->comment('Payment gateway settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->dropColumn('payment_enabled');
            $table->dropColumn('default_payment_gateway');
            $table->dropColumn('payment_settings');
        });
    }
};
