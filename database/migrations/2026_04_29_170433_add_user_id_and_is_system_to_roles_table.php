<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->boolean('is_system')->default(false)->after('description');
            
            // Remove the global unique constraint if it exists
            $table->dropUnique(['name']);
            // Add a new constraint: unique name per user (including system roles with user_id = null)
            $table->unique(['name', 'user_id']);
        });

        // Set existing roles as system roles
        DB::table('roles')->update(['is_system' => true]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name', 'user_id']);
            $table->unique(['name']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_system']);
        });
    }
};
