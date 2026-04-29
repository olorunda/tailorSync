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
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('client_data')->nullable()->after('notes');
            $table->json('items')->nullable()->after('client_data');
            $table->text('terms')->nullable()->after('items');
            $table->text('description')->nullable()->after('terms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['client_data', 'items', 'terms', 'description']);
        });
    }
};
