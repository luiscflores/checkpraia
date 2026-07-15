<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        try {
            Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('beach_hourly_snapshots');
                $exists = collect($indexes)->has('idx_beach_hourly_snapshots_unique');
                if (!$exists) {
                    $table->unique(['beach_id', 'captured_at'], 'idx_beach_hourly_snapshots_unique');
                }
            });
        } catch (\Exception $e) {
            // SQLite may not support this check gracefully; try directly
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
