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
        Schema::table('payments', function (Blueprint $table) {
            // Add new columns for payment gateway integration
            $table->string('currency', 3)->default('NGN')->after('amount');
            $table->string('reference')->nullable()->unique()->after('reference_number');
            $table->json('metadata')->nullable()->after('notes');
            $table->text('gateway_response')->nullable()->after('metadata');

            // Modify existing columns if needed
            // Change payment_date from date to timestamp to store more precise time
            $table->timestamp('payment_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('currency');
            $table->dropColumn('reference');
            $table->dropColumn('metadata');
            $table->dropColumn('gateway_response');

            // Revert payment_date back to date
            $table->date('payment_date')->change();
        });
    }
};
