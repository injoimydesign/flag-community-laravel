<?php
// database/migrations/2024_01_15_000003_create_inventory_adjustments_table.php

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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flag_product_id')->constrained()->onDelete('cascade');
            $table->enum('adjustment_type', ['initial', 'restock', 'sale', 'damage', 'loss', 'return', 'correction'])->default('correction');
            $table->integer('quantity'); // Positive for additions, negative for subtractions
            $table->integer('previous_quantity'); // Quantity before adjustment
            $table->integer('new_quantity'); // Quantity after adjustment
            $table->string('reason')->nullable();
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};