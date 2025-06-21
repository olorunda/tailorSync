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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing');
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
            $table->enum('channel', ['internal', 'sms', 'whatsapp', 'email'])->default('internal');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('type', ['fitting', 'consultation', 'delivery', 'other'])->default('fitting');
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled'])->default('scheduled');
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('messages');
    }
};
