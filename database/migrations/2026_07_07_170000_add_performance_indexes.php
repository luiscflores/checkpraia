<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beach_current_statuses', function (Blueprint $table) {
            $table->index('flag', 'idx_current_status_flag');
        });

        Schema::table('official_alerts', function (Blueprint $table) {
            $table->index(['beach_id', 'started_at', 'ended_at'], 'idx_official_alerts_beach_dates');
        });

        Schema::table('score_transactions', function (Blueprint $table) {
            $table->index('user_id', 'idx_score_transactions_user');
            $table->index('status', 'idx_score_transactions_status');
        });

        Schema::table('flag_reports', function (Blueprint $table) {
            $table->index('reported_at', 'idx_flag_reports_reported_at');
        });
    }

    public function down(): void
    {
        Schema::table('beach_current_statuses', function (Blueprint $table) {
            $table->dropIndex('idx_current_status_flag');
        });

        Schema::table('official_alerts', function (Blueprint $table) {
            $table->dropIndex('idx_official_alerts_beach_dates');
        });

        Schema::table('score_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_score_transactions_user');
            $table->dropIndex('idx_score_transactions_status');
        });

        Schema::table('flag_reports', function (Blueprint $table) {
            $table->dropIndex('idx_flag_reports_reported_at');
        });
    }
};
