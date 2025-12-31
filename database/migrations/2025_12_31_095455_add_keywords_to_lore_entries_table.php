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
        Schema::table('lore_entries', function (Blueprint $table) {
            $table->json('keywords')->nullable()->after('type')->comment('Alternative names/aliases for cross-linking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lore_entries', function (Blueprint $table) {
            $table->dropColumn('keywords');
        });
    }
};
