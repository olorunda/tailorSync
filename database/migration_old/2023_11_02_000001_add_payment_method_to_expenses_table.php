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
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_card', 'mobile_money', 'other'])->nullable()->after('amount');
            $table->renameColumn('expense_date', 'date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->renameColumn('date', 'expense_date');
        });
    }
};
