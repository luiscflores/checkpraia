<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Beaches table
        Schema::create('beaches', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('region');
            $table->string('district')->nullable();
            $table->string('municipality');
            $table->string('island')->nullable();
            $table->string('type')->default('oceanic');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_supervised')->default(true);
            $table->date('season_start')->nullable();
            $table->date('season_end')->nullable();
            $table->time('lifeguard_start')->nullable();
            $table->time('lifeguard_end')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('blue_flag')->default(false);
            $table->boolean('accessible')->default(false);
            $table->string('tide_station_id')->nullable();
            $table->string('weather_zone')->nullable();
            $table->string('ocean_zone')->nullable();
            $table->timestamps();
        });

        if (config('database.default') === 'pgsql') {
            $hasPostgis = false;
            try {
                $hasPostgis = count(DB::select("SELECT 1 FROM pg_extension WHERE extname = 'postgis'")) > 0;
            } catch (\Exception $e) {
                //
            }

            if ($hasPostgis) {
                DB::statement("ALTER TABLE beaches ADD COLUMN location geography(Point, 4326)");
                DB::statement("CREATE INDEX beaches_location_gix ON beaches USING GIST (location)");
            }
        }

        // 2. Beach Translations
        Schema::create('beach_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['beach_id', 'locale']);
        });

        // 3. Beach Services
        Schema::create('beach_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('parking')->default(false);
            $table->boolean('bathrooms')->default(false);
            $table->boolean('showers')->default(false);
            $table->boolean('accessible')->default(false);
            $table->boolean('amphibious_chair')->default(false);
            $table->boolean('first_aid')->default(false);
            $table->boolean('lifeguard_post')->default(false);
            $table->boolean('bar')->default(false);
            $table->boolean('restaurant')->default(false);
            $table->boolean('surf_school')->default(false);
            $table->boolean('equipment_rental')->default(false);
            $table->timestamps();
        });

        // 4. Beach Features
        Schema::create('beach_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->unique()->constrained()->onDelete('cascade');
            $table->string('coast_orientation', 5)->nullable();
            $table->string('exposure_direction', 5)->nullable();
            $table->decimal('exposure_factor', 4, 2)->default(1.0);
            $table->decimal('shelter_factor', 4, 2)->default(1.0);
            $table->string('beach_type')->nullable();
            $table->string('bottom_type')->nullable();
            $table->string('slope')->nullable();
            $table->string('current_risk')->nullable();
            $table->boolean('has_jetties')->default(false);
            $table->boolean('has_bays')->default(false);
            $table->boolean('has_cliffs')->default(false);
            $table->boolean('has_rocks')->default(false);
            $table->boolean('river_influence')->default(false);
            $table->timestamps();
        });

        // 5. Beach Prediction Profile
        Schema::create('beach_prediction_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('exposure_factor', 4, 2)->default(1.0);
            $table->decimal('shelter_factor', 4, 2)->default(1.0);
            $table->decimal('current_risk_factor', 4, 2)->default(1.0);
            $table->decimal('wave_height_weight', 4, 2)->default(1.0);
            $table->decimal('wave_period_weight', 4, 2)->default(1.0);
            $table->decimal('wave_direction_weight', 4, 2)->default(1.0);
            $table->decimal('wind_weight', 4, 2)->default(1.0);
            $table->decimal('tide_weight', 4, 2)->default(1.0);
            $table->decimal('warning_weight', 4, 2)->default(1.0);
            $table->decimal('water_quality_weight', 4, 2)->default(1.0);
            $table->string('algorithm_version')->default('1.0');
            $table->timestamps();
        });

        // 6. Ocean Forecasts
        Schema::create('ocean_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->decimal('wave_height_min', 4, 2)->nullable();
            $table->decimal('wave_height_max', 4, 2)->nullable();
            $table->decimal('wave_period_min', 4, 2)->nullable();
            $table->decimal('wave_period_max', 4, 2)->nullable();
            $table->string('wave_direction', 5)->nullable();
            $table->decimal('water_temp', 4, 1)->nullable();
            $table->timestamp('forecasted_at');
            $table->timestamps();

            $table->index(['beach_id', 'forecasted_at']);
        });

        // 7. Weather Forecasts
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->string('wind_direction', 5)->nullable();
            $table->decimal('precipitation', 4, 1)->nullable();
            $table->string('visibility')->nullable();
            $table->decimal('temp', 4, 1)->nullable();
            $table->decimal('uv_index', 3, 1)->nullable();
            $table->timestamp('forecasted_at');
            $table->timestamps();

            $table->index(['beach_id', 'forecasted_at']);
        });

        // 8. Tide Forecasts
        Schema::create('tide_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('tide_station_id')->index();
            $table->timestamp('tide_time');
            $table->string('tide_type', 4);
            $table->decimal('tide_height', 4, 2);
            $table->decimal('moon_phase', 4, 2)->nullable();
            $table->timestamps();

            $table->unique(['tide_station_id', 'tide_time']);
        });

        // 9. Water Quality Snapshots
        Schema::create('water_quality_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->string('quality_class');
            $table->date('sampled_at');
            $table->timestamps();

            $table->index(['beach_id', 'sampled_at']);
        });

        // 10. Official Alerts
        Schema::create('official_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type');
            $table->text('description');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // 11. Flag Predictions
        Schema::create('flag_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->integer('green_probability')->default(0);
            $table->integer('yellow_probability')->default(0);
            $table->integer('red_probability')->default(0);
            $table->string('selected_flag');
            $table->integer('confidence')->default(100);
            $table->string('algorithm_version')->default('1.0');
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['beach_id', 'calculated_at']);
        });

        // 12. Flag Reports
        Schema::create('flag_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->string('flag');
            $table->integer('vote_weight')->default(1);
            $table->string('status')->default('pending');
            $table->decimal('distance_to_beach', 6, 3);
            $table->decimal('gps_accuracy', 5, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['beach_id', 'status', 'reported_at']);
        });

        // 13. Beach Current Statuses
        Schema::create('beach_current_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->unique()->constrained()->onDelete('cascade');
            $table->string('source')->default('prediction');
            $table->string('flag')->default('gray');
            $table->integer('confidence')->default(100);
            $table->integer('consensus_reports_count')->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 14. Score Transactions
        Schema::create('score_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('flag_report_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('referral_id')->nullable();
            $table->string('type');
            $table->integer('points');
            $table->string('status')->default('pending');
            $table->string('description');
            $table->timestamps();
        });

        // 15. Referrals
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invited_user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('code');
            $table->string('status')->default('pending');
            $table->timestamp('qualified_at')->nullable();
            $table->timestamps();
        });

        // 16. Restaurants
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique();
            $table->string('source');
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->string('cuisine_type')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('reviews_count')->default(0);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address')->nullable();
            $table->decimal('average_price', 8, 2)->nullable();
            $table->string('booking_url')->nullable();
            $table->string('external_url')->nullable();
            $table->timestamps();
        });

        // 17. Beach-Restaurants pivot
        Schema::create('beach_restaurants', function (Blueprint $table) {
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->decimal('distance', 6, 3);
            $table->primary(['beach_id', 'restaurant_id']);
        });

        // 18. Favorites
        Schema::create('favorites', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->primary(['user_id', 'beach_id']);
        });

        // 19. Admin Score Adjustments
        Schema::create('admin_score_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_user_id')->constrained('users')->onDelete('cascade');
            $table->integer('previous_points');
            $table->integer('new_points');
            $table->integer('difference');
            $table->text('justification');
            $table->timestamps();
        });

        // 20. Push Subscriptions
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('endpoint');
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('content_encoding')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamps();

            $table->index('endpoint');
        });

        // 21. Beach Hourly Snapshots
        Schema::create('beach_hourly_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->constrained()->cascadeOnDelete();
            $table->string('flag', 20);
            $table->string('source', 20);
            $table->unsignedInteger('confidence');
            $table->decimal('wave_height', 5, 2)->nullable();
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->decimal('water_temp', 4, 1)->nullable();
            $table->decimal('air_temp', 4, 1)->nullable();
            $table->string('water_quality', 30)->nullable();
            $table->dateTime('captured_at');
            $table->dateTime('vote_time')->nullable();
            $table->timestamps();

            $table->unique(['beach_id', 'captured_at'], 'idx_beach_hourly_snapshots_unique');
            $table->index(['beach_id', 'captured_at']);
        });

        // 22. Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('beach_hourly_snapshots');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('admin_score_adjustments');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('beach_restaurants');
        Schema::dropIfExists('restaurants');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('score_transactions');
        Schema::dropIfExists('beach_current_statuses');
        Schema::dropIfExists('flag_reports');
        Schema::dropIfExists('flag_predictions');
        Schema::dropIfExists('official_alerts');
        Schema::dropIfExists('water_quality_snapshots');
        Schema::dropIfExists('tide_forecasts');
        Schema::dropIfExists('weather_forecasts');
        Schema::dropIfExists('ocean_forecasts');
        Schema::dropIfExists('beach_prediction_profiles');
        Schema::dropIfExists('beach_features');
        Schema::dropIfExists('beach_services');
        Schema::dropIfExists('beach_translations');
        Schema::dropIfExists('beaches');
    }
};
