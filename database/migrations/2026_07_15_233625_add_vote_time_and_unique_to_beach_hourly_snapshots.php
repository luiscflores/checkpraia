<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
            if (!Schema::hasColumn('beach_hourly_snapshots', 'vote_time')) {
                $table->dateTime('vote_time')->nullable();
            }
        });

        $indexExists = DB::selectOne(
            "SELECT 1 FROM sqlite_master WHERE type='index' AND name=?",
            ['idx_beach_hourly_snapshots_unique']
        );

        if (!$indexExists) {
            Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
                $table->unique(['beach_id', 'captured_at'], 'idx_beach_hourly_snapshots_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
            $table->dropColumn('vote_time');
        });

        Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
            $table->dropUnique('idx_beach_hourly_snapshots_unique');
        });
    }
};
