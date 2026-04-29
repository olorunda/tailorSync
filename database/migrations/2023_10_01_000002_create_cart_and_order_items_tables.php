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
        // Create shopping cart table
        Schema::create('shopping_carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });

        // Create cart items table
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->json('options')->nullable(); // For size, color, etc.
            $table->json('custom_design_data')->nullable(); // For custom order specifications
            $table->timestamps();
        });

        // Create order items table to link orders with products
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->json('options')->nullable(); // For size, color, etc.
            $table->json('custom_design_data')->nullable(); // For custom order specifications
            $table->timestamps();
        });

        // Add store_order flag to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_store_order')->default(false);
            $table->string('payment_status')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->string('tracking_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns from orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'is_store_order',
                'payment_status',
                'shipping_address',
                'billing_address',
                'shipping_method',
                'shipping_cost',
                'tax',
                'tracking_number',
            ]);
        });

        // Drop tables in reverse order
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('shopping_carts');
    }
};
