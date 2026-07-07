<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Beaches: listing queries with sorting and filtering
        Schema::table('beaches', function (Blueprint $table) {
            $table->index(['is_active', 'name'], 'idx_beaches_active_name');
            $table->index(['is_active', 'region', 'name'], 'idx_beaches_active_region_name');
            $table->index(['is_active', 'municipality'], 'idx_beaches_active_municipality');
        });

        // Flag reports: check existing user vote + history + purge
        Schema::table('flag_reports', function (Blueprint $table) {
            $table->index(['user_id', 'beach_id', 'status', 'reported_at'], 'idx_flag_reports_user_beach_status_time');
            $table->index(['user_id', 'reported_at'], 'idx_flag_reports_user_reported');
            $table->index('created_at', 'idx_flag_reports_created_at');
        });

        // Score transactions: rankings aggregation + referral bonus check
        Schema::table('score_transactions', function (Blueprint $table) {
            $table->index(['type', 'status', 'created_at'], 'idx_score_tx_type_status_created');
            $table->index(['user_id', 'type'], 'idx_score_tx_user_type');
        });

        // Referrals: counting qualified referrals per referrer
        Schema::table('referrals', function (Blueprint $table) {
            $table->index(['referrer_user_id', 'status'], 'idx_referrals_referrer_status');
            $table->index(['invited_user_id', 'status'], 'idx_referrals_invited_status');
        });

        // Users: ranking queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['is_suspended', 'score'], 'idx_users_suspended_score');
        });

        // Official alerts: global count without beach_id filter
        Schema::table('official_alerts', function (Blueprint $table) {
            $table->index(['started_at', 'ended_at'], 'idx_official_alerts_dates');
        });

        // Push subscriptions: user lookup
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->index('user_id', 'idx_push_subs_user');
        });

        // Tide forecasts: future tide lookup (station_id + tide_time is already unique)
        // Already covered by UNIQUE(tide_station_id, tide_time) -- good enough
    }

    public function down(): void
    {
        Schema::table('beaches', function (Blueprint $table) {
            $table->dropIndex('idx_beaches_active_name');
            $table->dropIndex('idx_beaches_active_region_name');
            $table->dropIndex('idx_beaches_active_municipality');
        });

        Schema::table('flag_reports', function (Blueprint $table) {
            $table->dropIndex('idx_flag_reports_user_beach_status_time');
            $table->dropIndex('idx_flag_reports_user_reported');
            $table->dropIndex('idx_flag_reports_created_at');
        });

        Schema::table('score_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_score_tx_type_status_created');
            $table->dropIndex('idx_score_tx_user_type');
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex('idx_referrals_referrer_status');
            $table->dropIndex('idx_referrals_invited_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_suspended_score');
        });

        Schema::table('official_alerts', function (Blueprint $table) {
            $table->dropIndex('idx_official_alerts_dates');
        });

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_push_subs_user');
        });
    }
};
