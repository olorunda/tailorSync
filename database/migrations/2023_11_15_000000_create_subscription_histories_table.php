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
        Schema::create('subscription_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_detail_id')->constrained()->onDelete('cascade');
            $table->string('subscription_plan')->nullable();
            $table->dateTime('subscription_start_date')->nullable();
            $table->dateTime('subscription_end_date')->nullable();
            $table->boolean('subscription_active')->default(false);
            $table->string('subscription_payment_method')->nullable();
            $table->string('subscription_payment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_histories');
    }
};
