<?php
// database/migrations/YYYY_MM_DD_create_flag_placement_holiday_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flag_placement_holiday', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flag_placement_id');
            $table->unsignedBigInteger('holiday_id');
            $table->timestamps();

            $table->foreign('flag_placement_id')
                  ->references('id')
                  ->on('flag_placements')
                  ->onDelete('cascade');

            $table->foreign('holiday_id')
                  ->references('id')
                  ->on('holidays')
                  ->onDelete('cascade');

            // Prevent duplicates
            $table->unique(['flag_placement_id', 'holiday_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('flag_placement_holiday');
    }
};
