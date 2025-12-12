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
        Schema::create('topic_polls', function (Blueprint $table) {
            $table->id();
            $table->date('target_date')->unique(); // The date this story is for
            $table->json('options'); // [{id: 1, text: '...', votes: 0}, ...]
            $table->string('winning_topic')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_polls');
    }
};
