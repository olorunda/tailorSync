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
            $table->string('tax_country')->default('none')->comment('Selected tax country: none, canada, us, uk');
            $table->json('tax_settings')->nullable()->comment('Tax settings for the selected country');
            $table->boolean('tax_enabled')->default(false)->comment('Whether tax calculation is enabled');
            $table->string('tax_number')->nullable()->comment('Business tax identification number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->dropColumn('tax_country');
            $table->dropColumn('tax_settings');
            $table->dropColumn('tax_enabled');
            $table->dropColumn('tax_number');
        });
    }
};
