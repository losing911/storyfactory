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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('baslik');
            $table->longText('metin');
            $table->string('gorsel_url')->nullable();
            $table->dateTime('yayin_tarihi');
            $table->string('durum')->default('draft');
            $table->string('konu');
            $table->string('meta')->nullable();
            $table->json('etiketler')->nullable();
            $table->string('sosyal_ozet')->nullable();
            $table->text('gorsel_prompt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
