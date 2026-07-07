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
            $table->index(['started_at', 'ended_at'], 'idx_official_alerts_dates');
        });

        Schema::table('score_transactions', function (Blueprint $table) {
            $table->index('user_id', 'idx_score_transactions_user');
            $table->index('status', 'idx_score_transactions_status');
            $table->index(['type', 'status', 'created_at'], 'idx_score_tx_type_status_created');
            $table->index(['user_id', 'type'], 'idx_score_tx_user_type');
            $table->index(['user_id', 'status', 'created_at'], 'idx_score_tx_user_status_created');
        });

        Schema::table('flag_reports', function (Blueprint $table) {
            $table->index('reported_at', 'idx_flag_reports_reported_at');
            $table->index(['user_id', 'beach_id', 'status', 'reported_at'], 'idx_flag_reports_user_beach_status_time');
            $table->index(['user_id', 'reported_at'], 'idx_flag_reports_user_reported');
            $table->index('created_at', 'idx_flag_reports_created_at');
        });

        Schema::table('beaches', function (Blueprint $table) {
            $table->index(['is_active', 'name'], 'idx_beaches_active_name');
            $table->index(['is_active', 'region', 'name'], 'idx_beaches_active_region_name');
            $table->index(['is_active', 'municipality'], 'idx_beaches_active_municipality');
            $table->index(['is_active', 'district'], 'idx_beaches_active_district');
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->index(['referrer_user_id', 'status'], 'idx_referrals_referrer_status');
            $table->index(['invited_user_id', 'status'], 'idx_referrals_invited_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['is_suspended', 'score'], 'idx_users_suspended_score');
        });

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->index('user_id', 'idx_push_subs_user');
        });
    }

    public function down(): void
    {
        Schema::table('beach_current_statuses', function (Blueprint $table) {
            $table->dropIndex('idx_current_status_flag');
        });

        Schema::table('official_alerts', function (Blueprint $table) {
            $table->dropIndex('idx_official_alerts_beach_dates');
            $table->dropIndex('idx_official_alerts_dates');
        });

        Schema::table('score_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_score_transactions_user');
            $table->dropIndex('idx_score_transactions_status');
            $table->dropIndex('idx_score_tx_type_status_created');
            $table->dropIndex('idx_score_tx_user_type');
            $table->dropIndex('idx_score_tx_user_status_created');
        });

        Schema::table('flag_reports', function (Blueprint $table) {
            $table->dropIndex('idx_flag_reports_reported_at');
            $table->dropIndex('idx_flag_reports_user_beach_status_time');
            $table->dropIndex('idx_flag_reports_user_reported');
            $table->dropIndex('idx_flag_reports_created_at');
        });

        Schema::table('beaches', function (Blueprint $table) {
            $table->dropIndex('idx_beaches_active_name');
            $table->dropIndex('idx_beaches_active_region_name');
            $table->dropIndex('idx_beaches_active_municipality');
            $table->dropIndex('idx_beaches_active_district');
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex('idx_referrals_referrer_status');
            $table->dropIndex('idx_referrals_invited_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_suspended_score');
        });

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_push_subs_user');
        });
    }
};
