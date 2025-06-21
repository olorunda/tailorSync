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
        Schema::table('designs', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
            $table->json('materials')->nullable()->after('description');
            $table->json('tags')->nullable()->after('materials');
            $table->json('images')->nullable()->after('tags');
            $table->string('primary_image')->nullable()->after('images');
            // Rename image_path to avoid confusion with the new images field
            $table->renameColumn('image_path', 'old_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->renameColumn('old_image_path', 'image_path');
            $table->dropColumn('primary_image');
            $table->dropColumn('images');
            $table->dropColumn('tags');
            $table->dropColumn('materials');
            $table->dropColumn('category');
        });
    }
};
