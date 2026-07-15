<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // beach_translations: index on (beach_id, locale) for the JOIN in beach list query.
        // The existing unique constraint creates an index in PostgreSQL but SQLite
        // unique constraints do NOT automatically create a usable covering index for lookups.
        if (! $this->hasIndex('beach_translations', 'idx_beach_translations_beach_locale')) {
            Schema::table('beach_translations', function (Blueprint $table) {
                $table->index(['beach_id', 'locale'], 'idx_beach_translations_beach_locale');
            });
        }

        // weather_forecasts: add explicit index on beach_id alone for the GROUP BY subquery
        // (SELECT beach_id, MAX(id) FROM weather_forecasts GROUP BY beach_id).
        // The existing (beach_id, forecasted_at) composite index can serve this, but
        // an index on just beach_id is faster for pure GROUP BY scans on SQLite.
        if (! $this->hasIndex('weather_forecasts', 'idx_weather_forecasts_beach_id')) {
            Schema::table('weather_forecasts', function (Blueprint $table) {
                $table->index('beach_id', 'idx_weather_forecasts_beach_id');
            });
        }

        // ocean_forecasts: same rationale as weather_forecasts above.
        if (! $this->hasIndex('ocean_forecasts', 'idx_ocean_forecasts_beach_id')) {
            Schema::table('ocean_forecasts', function (Blueprint $table) {
                $table->index('beach_id', 'idx_ocean_forecasts_beach_id');
            });
        }

        // flag_predictions: index on (beach_id, calculated_at DESC) for the
        // "latest prediction" query in BeachDetail.
        if (! $this->hasIndex('flag_predictions', 'idx_flag_predictions_beach_calculated')) {
            Schema::table('flag_predictions', function (Blueprint $table) {
                $table->index(['beach_id', 'calculated_at'], 'idx_flag_predictions_beach_calculated');
            });
        }

        // beach_hourly_snapshots: index on (beach_id, captured_at) for today's snapshots query.
        // Already exists as a unique constraint, but add non-unique for flexibility.
        if (! $this->hasIndex('beach_hourly_snapshots', 'idx_beach_hourly_snapshots_beach_captured')) {
            Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
                $table->index(['beach_id', 'captured_at'], 'idx_beach_hourly_snapshots_beach_captured');
            });
        }

        // official_alerts: index on beach_id for the per-beach alert query in BeachDetail.
        if (! $this->hasIndex('official_alerts', 'idx_official_alerts_beach_id')) {
            Schema::table('official_alerts', function (Blueprint $table) {
                $table->index('beach_id', 'idx_official_alerts_beach_id');
            });
        }

        // score_transactions: index on (user_id, created_at) for the
        // "has points this hour" check in BeachDetail::submitReport().
        if (! $this->hasIndex('score_transactions', 'idx_score_tx_user_created')) {
            Schema::table('score_transactions', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'idx_score_tx_user_created');
            });
        }

        // users: index on username for the search + uniqueness check in rankings.
        if (! $this->hasIndex('users', 'idx_users_username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('username', 'idx_users_username');
            });
        }
    }

    public function down(): void
    {
        Schema::table('beach_translations', function (Blueprint $table) {
            $table->dropIndex('idx_beach_translations_beach_locale');
        });

        Schema::table('weather_forecasts', function (Blueprint $table) {
            $table->dropIndex('idx_weather_forecasts_beach_id');
        });

        Schema::table('ocean_forecasts', function (Blueprint $table) {
            $table->dropIndex('idx_ocean_forecasts_beach_id');
        });

        Schema::table('flag_predictions', function (Blueprint $table) {
            $table->dropIndex('idx_flag_predictions_beach_calculated');
        });

        Schema::table('beach_hourly_snapshots', function (Blueprint $table) {
            $table->dropIndex('idx_beach_hourly_snapshots_beach_captured');
        });

        Schema::table('official_alerts', function (Blueprint $table) {
            $table->dropIndex('idx_official_alerts_beach_id');
        });

        Schema::table('score_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_score_tx_user_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_username');
        });
    }

    /**
     * Check if a named index exists (compatible with SQLite & PostgreSQL).
     */
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

        // MySQL / others: let it fail gracefully
        return false;
    }
};
