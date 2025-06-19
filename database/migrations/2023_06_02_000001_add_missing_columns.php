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
        // Add role column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->string('position')->nullable()->after('phone');
            $table->text('notes')->nullable()->after('position');
        });

        // Add total_amount column to orders table
        Schema::table('orders', function (Blueprint $table) {
//            $table->decimal('total_amount', 10, 2)->after('status');
        });

        // Add subject column to messages table
        Schema::table('messages', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('client_id');
            $table->renameColumn('content', 'body');
            $table->renameColumn('channel', 'message_type');
            $table->boolean('is_read')->default(false)->after('status');
        });

        // Add assigned_to column to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('team_member_id')->references('id')->on('users')->onDelete('set null');
        });

        // Add date column to appointments table
        Schema::table('appointments', function (Blueprint $table) {
            $table->date('date')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove date column from appointments table
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('date');
        });

        // Remove assigned_to column from tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });

        // Remove subject column from messages table
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('subject');
            $table->renameColumn('body', 'content');
            $table->renameColumn('message_type', 'channel');
            $table->dropColumn('is_read');
        });

        // Remove total_amount column from orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });

        // Remove role column from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->dropColumn('phone');
            $table->dropColumn('position');
            $table->dropColumn('notes');
        });
    }
};
