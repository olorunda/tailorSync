<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This migration requires the doctrine/dbal package to modify column types.
 * Please install it using: composer require doctrine/dbal
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert inventory_items.type enum to string
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('type', 20)->change();
        });

        // Convert messages table enums to string
        Schema::table('messages', function (Blueprint $table) {
            $table->string('direction', 20)->change();
            $table->string('status', 20)->change();
           // $table->string('channel', 20)->change();
        });

        // Convert appointments table enums to string
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->string('status', 20)->change();
        });

        // Convert task enums to string (from team_collaboration_tables)
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->string('priority', 20)->change();
            $table->string('status', 20)->change();
        });

        // Convert invoices.status enum to string (from finance_tables)
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });

        // Convert payments.payment_method enum to string (from finance_tables)
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->change();
            $table->string('status', 20)->change(); // Added later in 2025_06_17_080605_add_status_to_payments_table
        });

        // Convert orders.status enum to string
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });

        // Convert users.currency enum to string
        Schema::table('users', function (Blueprint $table) {
            $table->string('currency', 10)->default('USD')->change();
        });

        // Convert expenses.payment_method enum to string
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('payment_method', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert inventory_items.type string back to enum
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->enum('type', ['fabric', 'accessory', 'tool', 'other'])->default('fabric')->change();
        });

        // Convert messages table strings back to enums
        Schema::table('messages', function (Blueprint $table) {
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing')->change();
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent')->change();
            $table->enum('channel', ['internal', 'sms', 'whatsapp', 'email'])->default('internal')->change();
        });

        // Convert appointments table strings back to enums
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('type', ['fitting', 'consultation', 'delivery', 'other'])->default('fitting')->change();
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled'])->default('scheduled')->change();
        });

        // Convert task strings back to enums
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('type', ['cutting', 'sewing', 'fitting', 'delivery', 'other'])->default('sewing')->change();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->change();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending')->change();
        });

        // Convert invoices.status string back to enum
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft')->change();
        });

        // Convert payments.payment_method string back to enum
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_card', 'mobile_money', 'other'])->default('cash')->change();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed')->change();
        });

        // Convert orders.status string back to enum
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'fitting', 'delivered', 'paid'])->default('pending')->change();
        });

        // Convert users.currency string back to enum
        Schema::table('users', function (Blueprint $table) {
            $table->enum('currency', ['USD', 'EUR', 'GBP', 'NGN'])->default('USD')->change();
        });

        // Convert expenses.payment_method string back to enum
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_card', 'mobile_money', 'other'])->default('cash')->change();
        });
    }
};
