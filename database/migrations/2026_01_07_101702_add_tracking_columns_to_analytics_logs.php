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
        Schema::table('analytics_logs', function (Blueprint $table) {
            $table->boolean('is_new_visitor')->default(true)->after('visitor_id');
            $table->string('utm_source')->nullable()->after('user_agent');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytics_logs', function (Blueprint $table) {
            $table->dropColumn(['is_new_visitor', 'utm_source', 'utm_medium', 'utm_campaign']);
        });
    }
};
