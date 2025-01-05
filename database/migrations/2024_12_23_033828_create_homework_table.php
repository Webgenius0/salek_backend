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
        Schema::create('homework', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained('courses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onUpdate('cascade')->onDelete('cascade');
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->text('instruction');
            $table->string('file', 255)->nullable();
            $table->string('link', 255)->nullable();
            $table->datetime('deadline')->nullable();
            $table->enum('type', ['single', 'multiple'])->default('single');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework');
    }
};
