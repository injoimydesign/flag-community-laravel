<?php
// database/migrations/2024_01_01_000003_create_flag_sizes_table.php

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
        Schema::create('flag_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 3'x5', 4'x6', etc.
            $table->string('dimensions');
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flag_sizes');
    }
};