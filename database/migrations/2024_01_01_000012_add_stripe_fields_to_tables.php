<?php
// database/migrations/2024_01_01_000012_add_stripe_fields_to_tables.php

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
        // Add Stripe customer ID to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('role');
        });

        // Add Stripe product ID to flag products table
        Schema::table('flag_products', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('active');
        });

        // Add payment intent ID to subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_customer_id');
        });

        Schema::table('flag_products', function (Blueprint $table) {
            $table->dropColumn('stripe_product_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('stripe_payment_intent_id');
        });
    }
};