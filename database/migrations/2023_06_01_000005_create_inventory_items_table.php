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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['fabric', 'accessory', 'tool', 'other'])->default('fabric');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('meters'); // meters, yards, pieces, etc.
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('reorder_level', 10, 2)->nullable(); // Quantity at which to reorder
            $table->string('supplier')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->timestamps();
        });

        // Create a pivot table for inventory items used in orders
        Schema::create('inventory_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_used', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_order');
        Schema::dropIfExists('inventory_items');
    }
};
