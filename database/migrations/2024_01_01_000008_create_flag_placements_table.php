<?php
// database/migrations/2024_01_01_000008_create_flag_placements_table.php

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
        Schema::create('flag_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('holiday_id')->constrained()->onDelete('cascade');
            $table->foreignId('flag_product_id')->constrained()->onDelete('cascade');
            $table->date('placement_date');
            $table->date('removal_date');
            $table->enum('status', ['scheduled', 'placed', 'removed', 'skipped'])->default('scheduled');
            $table->foreignId('placed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('removed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flag_placements');
    }
};