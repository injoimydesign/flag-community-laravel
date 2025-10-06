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
        Schema::table('flag_placements', function (Blueprint $table) {
            // Add placement address fields if they don't exist
            if (!Schema::hasColumn('flag_placements', 'placement_address')) {
                $table->string('placement_address')->nullable()->after('flag_product_id');
            }
            if (!Schema::hasColumn('flag_placements', 'placement_city')) {
                $table->string('placement_city')->nullable()->after('placement_address');
            }
            if (!Schema::hasColumn('flag_placements', 'placement_state')) {
                $table->string('placement_state')->nullable()->after('placement_city');
            }
            if (!Schema::hasColumn('flag_placements', 'placement_zip_code')) {
                $table->string('placement_zip_code')->nullable()->after('placement_state');
            }
            if (!Schema::hasColumn('flag_placements', 'placement_latitude')) {
                $table->decimal('placement_latitude', 10, 8)->nullable()->after('placement_zip_code');
            }
            if (!Schema::hasColumn('flag_placements', 'placement_longitude')) {
                $table->decimal('placement_longitude', 11, 8)->nullable()->after('placement_latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flag_placements', function (Blueprint $table) {
            $table->dropColumn([
                'placement_address',
                'placement_city',
                'placement_state',
                'placement_zip_code',
                'placement_latitude',
                'placement_longitude'
            ]);
        });
    }
};
