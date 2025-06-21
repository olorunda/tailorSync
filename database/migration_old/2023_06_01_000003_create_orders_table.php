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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->string('design_name')->nullable();
            $table->foreignId('design_id')->nullable(); // Will be created later
            $table->string('fabric_type')->nullable();
            $table->date('due_date');
            $table->enum('status', ['pending', 'in_progress', 'fitting', 'delivered', 'paid'])->default('pending');
            $table->decimal('cost', 10, 2);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
