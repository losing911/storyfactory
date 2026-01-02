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
        // Raw Logs
        Schema::create('analytics_logs', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id')->index(); // UUID from Cookie
            $table->string('ip_address')->nullable(); // Anonymized
            $table->string('url');
            $table->string('referrer')->nullable();
            $table->string('device_type')->default('desktop'); // mobile, desktop, tablet
            $table->text('user_agent')->nullable();
            $table->string('country_code')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // AI Insights Store
        Schema::create('analytics_insights', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique();
            $table->text('summary_text'); // Markdown from AI
            $table->json('ad_strategy')->nullable(); // JSON suggestions like { "target": "gamers", "platform": "twitter" }
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_insights');
        Schema::dropIfExists('analytics_logs');
    }
};
