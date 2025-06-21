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
            $table->string('facebook_handle')->nullable()->after('logo_path');
            $table->string('instagram_handle')->nullable()->after('facebook_handle');
            $table->string('tiktok_handle')->nullable()->after('instagram_handle');
            $table->string('whatsapp_handle')->nullable()->after('tiktok_handle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->dropColumn('facebook_handle');
            $table->dropColumn('instagram_handle');
            $table->dropColumn('tiktok_handle');
            $table->dropColumn('whatsapp_handle');
        });
    }
};
