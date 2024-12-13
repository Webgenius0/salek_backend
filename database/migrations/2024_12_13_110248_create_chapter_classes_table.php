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
        Schema::create('chapter_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('courses_chapter_id')->constrained('course_chapters')->onUpdate('cascade')->onDelete('cascade');
            $table->string('title', 250)->nullable();
            $table->string('image_url', 250)->nullable();
            $table->string('video_url', 250)->nullable();
            $table->string('duration', 250)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_classes');
    }
};
