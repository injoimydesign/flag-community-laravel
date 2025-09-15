<?php
// database/migrations/2024_01_01_000006_create_subscriptions_table.php

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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('stripe_subscription_id')->unique()->nullable();
            $table->enum('type', ['annual', 'onetime']);
            $table->enum('status', ['active', 'canceled', 'expired', 'pending'])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('canceled_at')->nullable();
            $table->json('selected_holidays')->nullable(); // Array of holiday IDs
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};