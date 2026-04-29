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
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('remindable');
            $table->string('reminder_type'); // e.g., 'upcoming', 'overdue'
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['remindable_id', 'remindable_type', 'reminder_type'], 'remindable_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
