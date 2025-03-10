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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name', 250)->nullable();
            $table->string('slug', 250)->nullable();
            $table->string('description', 1000)->nullable();
            $table->integer('total_class');
            $table->integer('price');
            $table->integer('total_month')->nullable();
            $table->integer('additional_charge')->nullable();

            $table->string('introduction_title', 250)->nullable();
            $table->string('cover_photo', 250)->nullable();
            $table->string('class_video', 250)->nullable();
            $table->date('start_date')->nullable();
            $table->integer('total_levels')->default(1);
            $table->enum('status', ['publish', 'unpublish']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
