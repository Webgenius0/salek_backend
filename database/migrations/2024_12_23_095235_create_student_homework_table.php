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
        Schema::create('student_homework', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('homework_id')->nullable()->constrained('homework')->onUpdate('cascade')->onDelete('cascade');
            $table->string('answer_script', 250)->nullable();
            $table->decimal('score', 10, 2)->nullable();
            $table->text('comment')->nullable();
            $table->datetime('submission_at')->nullable();
            $table->enum('status', ['in_time', 'late'])->default('in_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_homework');
    }
};
