<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Delete duplicate snapshots, keeping the latest one per hour for each beach
        DB::delete('DELETE FROM beach_hourly_snapshots WHERE id NOT IN (
            SELECT MAX(id) FROM beach_hourly_snapshots GROUP BY beach_id, captured_at
        )');

        // 2. Add the unique index constraint
        Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
            $table->unique(['beach_id', 'captured_at'], 'idx_beach_hourly_snapshots_unique');
        });
    }

    public function down(): void
    {
        Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
            $table->dropUnique('idx_beach_hourly_snapshots_unique');
        });
    }
};
