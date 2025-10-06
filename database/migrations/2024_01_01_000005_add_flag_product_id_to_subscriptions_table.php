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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add flag_product_id column
            if (!Schema::hasColumn('subscriptions', 'flag_product_id')) {
                $table->unsignedBigInteger('flag_product_id')->nullable()->after('user_id');
                $table->foreign('flag_product_id')->references('id')->on('flag_products')->onDelete('set null');
            }

            // Add billing_frequency column if it doesn't exist
            if (!Schema::hasColumn('subscriptions', 'billing_frequency')) {
                $table->string('billing_frequency')->nullable()->after('type');
            }

            // Add next_billing_date column if it doesn't exist
            if (!Schema::hasColumn('subscriptions', 'next_billing_date')) {
                $table->date('next_billing_date')->nullable()->after('end_date');
            }

            // Add placement_instructions column if it doesn't exist
            if (!Schema::hasColumn('subscriptions', 'placement_instructions')) {
                $table->text('placement_instructions')->nullable()->after('special_instructions');
            }

            // Add notes column if it doesn't exist
            if (!Schema::hasColumn('subscriptions', 'notes')) {
                $table->text('notes')->nullable()->after('placement_instructions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('subscriptions', 'flag_product_id')) {
                $table->dropForeign(['flag_product_id']);
                $table->dropColumn('flag_product_id');
            }

            if (Schema::hasColumn('subscriptions', 'billing_frequency')) {
                $table->dropColumn('billing_frequency');
            }

            if (Schema::hasColumn('subscriptions', 'next_billing_date')) {
                $table->dropColumn('next_billing_date');
            }

            if (Schema::hasColumn('subscriptions', 'placement_instructions')) {
                $table->dropColumn('placement_instructions');
            }

            if (Schema::hasColumn('subscriptions', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
