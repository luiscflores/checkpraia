<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop redundant non-unique index on beach_hourly_snapshots.
        // The UNIQUE constraint `idx_beach_hourly_snapshots_unique` on (beach_id, captured_at)
        // already creates a covering index. The two additional non-unique indexes on the
        // same columns waste write throughput and storage on every INSERT/UPDATE.
        if ($this->hasIndex('beach_hourly_snapshots', 'beach_hourly_snapshots_beach_id_captured_at_index')) {
            Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
                $table->dropIndex('beach_hourly_snapshots_beach_id_captured_at_index');
            });
        }

        if ($this->hasIndex('beach_hourly_snapshots', 'idx_beach_hourly_snapshots_beach_captured')) {
            Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
                $table->dropIndex('idx_beach_hourly_snapshots_beach_captured');
            });
        }

        // Drop redundant index on flag_predictions.
        // The original `idx_flag_predictions` on (beach_id, calculated_at) already covers
        // the "latest prediction" query. The named duplicate is unnecessary.
        if ($this->hasIndex('flag_predictions', 'idx_flag_predictions_beach_calculated')) {
            Schema::table('flag_predictions', function (Blueprint $table) {
                $table->dropIndex('idx_flag_predictions_beach_calculated');
            });
        }
    }

    public function down(): void
    {
        Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
            $table->index(['beach_id', 'captured_at']);
            $table->index(['beach_id', 'captured_at'], 'idx_beach_hourly_snapshots_beach_captured');
        });

        Schema::table('flag_predictions', function (Blueprint $table) {
            $table->index(['beach_id', 'calculated_at'], 'idx_flag_predictions_beach_calculated');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list(\"{$table}\")");
            foreach ($indexes as $index) {
                if ($index->name === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?',
                [$table, $indexName]
            );

            return $result !== null;
        }

        return false;
    }
};
