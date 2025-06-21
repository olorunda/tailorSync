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
        // For MySQL, we need to modify the ENUM values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'in_progress', 'fitting', 'delivered', 'paid', 'ready', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original ENUM values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'in_progress', 'fitting', 'delivered', 'paid') DEFAULT 'pending'");
    }
};
