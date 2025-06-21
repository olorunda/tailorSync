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
        // Modify the status enum to include 'pending'
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled', 'pending') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum without 'pending'
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft'");
    }
};
