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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('type', ['mastercard', 'paypal', 'stripe', 'visa', 'amex'])->default('mastercard');
            $table->string('cardholder_name', 250);
            $table->string('card_number')->nullable();
            $table->string('token')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->string('cvv')->nullable();
            $table->string('billing_address')->nullable();
            $table->enum('status', ['active', 'expired', 'disabled'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
