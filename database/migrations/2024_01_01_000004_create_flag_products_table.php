<?php
// database/migrations/2024_01_01_000004_create_flag_products_table.php

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
        Schema::create('flag_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flag_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('flag_size_id')->constrained()->onDelete('cascade');
            $table->decimal('one_time_price', 8, 2); // Single purchase price
            $table->decimal('annual_subscription_price', 8, 2); // Annual subscription price
            $table->string('stripe_price_id_onetime')->nullable();
            $table->string('stripe_price_id_annual')->nullable();
            $table->integer('inventory_count')->default(0);
            $table->integer('min_inventory_alert')->default(5);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->unique(['flag_type_id', 'flag_size_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flag_products');
    }
};