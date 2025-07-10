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
            $table->time('business_hours_start')->nullable()->after('whatsapp_handle');
            $table->time('business_hours_end')->nullable()->after('business_hours_start');
            $table->json('available_days')->nullable()->after('business_hours_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->dropColumn('business_hours_start');
            $table->dropColumn('business_hours_end');
            $table->dropColumn('available_days');
        });
    }
};
