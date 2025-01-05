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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->string('title', 250)->nullable();
            $table->string('slug', 250)->nullable();
            $table->string('description', 500)->nullable();
            $table->timestamp('event_date')->nullable();
            $table->string('event_location', 500)->nullable();
            $table->integer('price')->nullable();
            $table->integer('total_seat')->nullable();
            $table->string('thumbnail', 250)->nullable();
            $table->foreignId('created_by')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('status', ['on_going', 'upcoming', 'complete'])->default('upcoming');
            $table->integer('flag')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
