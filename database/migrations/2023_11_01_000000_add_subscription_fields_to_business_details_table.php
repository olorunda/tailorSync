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
            $table->string('subscription_plan')->default('free')->after('payment_settings');
            $table->timestamp('subscription_start_date')->nullable()->after('subscription_plan');
            $table->timestamp('subscription_end_date')->nullable()->after('subscription_start_date');
            $table->boolean('subscription_active')->default(false)->after('subscription_end_date');
            $table->string('subscription_payment_method')->nullable()->after('subscription_active');
            $table->string('subscription_payment_id')->nullable()->after('subscription_payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->dropColumn('subscription_plan');
            $table->dropColumn('subscription_start_date');
            $table->dropColumn('subscription_end_date');
            $table->dropColumn('subscription_active');
            $table->dropColumn('subscription_payment_method');
            $table->dropColumn('subscription_payment_id');
        });
    }
};
