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
        Schema::table('book_events', function (Blueprint $table) {
            $table->decimal('amount')->default(0.00)->after('seats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_events', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
