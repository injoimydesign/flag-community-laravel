<?php
// database/migrations/2024_01_15_000000_add_inventory_columns_to_flag_products_table.php

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
        Schema::table('flag_products', function (Blueprint $table) {
            // Add inventory management columns
            $table->integer('current_inventory')->default(0)->after('annual_subscription_price');
            $table->integer('low_inventory_threshold')->default(10)->after('current_inventory');
            $table->decimal('cost_per_unit', 8, 2)->default(0)->after('low_inventory_threshold');

            // Add active status column
            $table->boolean('active')->default(true)->after('cost_per_unit');

            // Add timestamps if they don't exist
            if (!Schema::hasColumn('flag_products', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flag_products', function (Blueprint $table) {
            $table->dropColumn([
                'current_inventory',
                'low_inventory_threshold',
                'cost_per_unit',
                'active'
            ]);
        });
    }
};
