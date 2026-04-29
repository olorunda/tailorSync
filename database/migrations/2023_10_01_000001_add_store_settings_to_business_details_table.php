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
            $table->boolean('store_enabled')->default(false);
            $table->string('store_slug')->nullable()->unique();
            $table->string('store_theme_color')->default('#3b82f6'); // Default blue color
            $table->string('store_secondary_color')->default('#1e40af'); // Default darker blue
            $table->string('store_accent_color')->default('#f97316'); // Default orange
            $table->text('store_description')->nullable();
            $table->string('store_banner_image')->nullable();
            $table->json('store_featured_categories')->nullable();
            $table->json('store_social_links')->nullable();
            $table->text('store_announcement')->nullable();
            $table->boolean('store_show_featured_products')->default(true);
            $table->boolean('store_show_new_arrivals')->default(true);
            $table->boolean('store_show_custom_designs')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->dropColumn([
                'store_enabled',
                'store_slug',
                'store_theme_color',
                'store_secondary_color',
                'store_accent_color',
                'store_description',
                'store_banner_image',
                'store_featured_categories',
                'store_social_links',
                'store_announcement',
                'store_show_featured_products',
                'store_show_new_arrivals',
                'store_show_custom_designs',
            ]);
        });
    }
};
