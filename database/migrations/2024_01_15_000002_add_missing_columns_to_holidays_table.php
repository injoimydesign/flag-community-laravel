<?php
// database/migrations/2024_01_15_000002_add_missing_columns_to_holidays_table.php

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
        Schema::table('holidays', function (Blueprint $table) {
            // Add date column if it doesn't exist
            if (!Schema::hasColumn('holidays', 'date')) {
                $table->date('date')->after('description');
            }

            // Add active column if it doesn't exist
            if (!Schema::hasColumn('holidays', 'active')) {
                $table->boolean('active')->default(true)->after('removal_days_after');
            }

            // Add sort_order column if it doesn't exist
            if (!Schema::hasColumn('holidays', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('active');
            }

            // Add recurring column if it doesn't exist
            if (!Schema::hasColumn('holidays', 'recurring')) {
                $table->boolean('recurring')->default(true)->after('date');
            }

            // Add placement/removal timing columns if they don't exist
            if (!Schema::hasColumn('holidays', 'placement_days_before')) {
                $table->integer('placement_days_before')->default(1)->after('recurring');
            }

            if (!Schema::hasColumn('holidays', 'removal_days_after')) {
                $table->integer('removal_days_after')->default(1)->after('placement_days_before');
            }

            // Add timestamps if they don't exist
            if (!Schema::hasColumn('holidays', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn([
                'date',
                'active',
                'sort_order',
                'recurring',
                'placement_days_before',
                'removal_days_after'
            ]);
        });
    }
};
