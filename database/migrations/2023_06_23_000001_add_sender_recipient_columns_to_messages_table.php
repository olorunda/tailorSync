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
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('sender_id')->nullable()->after('user_id');
            $table->foreignId('recipient_id')->nullable()->after('client_id');
            $table->string('subject')->nullable()->after('content');
            $table->text('message')->nullable()->after('subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['sender_id', 'recipient_id', 'subject', 'message']);
        });
    }
};
