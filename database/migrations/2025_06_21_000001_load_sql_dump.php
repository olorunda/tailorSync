<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * WARNING: This migration will replace the entire database schema with the one defined in the SQL dump.
 * It will drop all existing tables and recreate them according to the SQL dump.
 * This is a destructive operation and will remove all existing data.
 * Make sure to backup your database before running this migration.
 *
 * This migration is intended to be run on a fresh database or in a development environment.
 * Running this migration in production without proper preparation can result in data loss.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        return;
        // Get the SQL dump file content
        $sqlDump = file_get_contents('tailorsync.sql');

        // Execute the entire SQL dump
        try {
            DB::unprepared($sqlDump);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error executing SQL dump: ' . $e->getMessage());
            throw $e; // Re-throw the exception to halt the migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all tables created by the SQL dump
        // This is a destructive operation and will remove all data
        $tables = [
            'appointments',
            'business_details',
            'cache',
            'cache_locks',
            'clients',
            'design_tags',
            'designs',
            'expenses',
            'failed_jobs',
            'inventory_items',
            'inventory_order',
            'invoices',
            'job_batches',
            'jobs',
            'measurements',
            'messages',
            'migrations',
            'notifications',
            'orders',
            'password_reset_tokens',
            'payments',
            'permissions',
            'role_permission',
            'roles',
            'sessions',
            'tasks',
            'team_members',
            'users'
        ];

        // Disable foreign key checks to allow dropping tables with foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
