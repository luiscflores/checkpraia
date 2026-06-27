<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Beaches table
        Schema::create('beaches', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('region'); // Continental, Madeira, Açores
            $table->string('district')->nullable();
            $table->string('municipality');
            $table->string('island')->nullable();
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

        // Try adding spatial column if PostGIS is enabled on PostgreSQL
        if (config('database.default') === 'pgsql') {
            $hasPostgis = false;
            try {
                $hasPostgis = count(DB::select("SELECT 1 FROM pg_extension WHERE extname = 'postgis'")) > 0;
            } catch (\Exception $e) {
                // Ignore query exceptions
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
            $table->string('locale', 5); // pt, en, es, fr
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['beach_id', 'locale']);
        });

        // 3. Beach Services (Booleans for individual features)
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

        // 4. Beach Features (Physical orientations and factors)
        Schema::create('beach_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->unique()->constrained()->onDelete('cascade');
            $table->string('coast_orientation', 5)->nullable(); // N, W, S, E, NW, etc.
            $table->string('exposure_direction', 5)->nullable();
            $table->decimal('exposure_factor', 4, 2)->default(1.0);
            $table->decimal('shelter_factor', 4, 2)->default(1.0);
            $table->string('beach_type')->nullable(); // sandy, rocky
            $table->string('bottom_type')->nullable(); // sand, rock, mixed
            $table->string('slope')->nullable(); // flat, medium, steep
            $table->string('current_risk')->nullable(); // low, medium, high
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

        // 6. Ocean Forecasts (IPMA)
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

        // 7. Weather Forecasts (IPMA)
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->decimal('wind_speed', 5, 2)->nullable(); // knots or km/h
            $table->string('wind_direction', 5)->nullable();
            $table->decimal('precipitation', 4, 1)->nullable(); // mm
            $table->string('visibility')->nullable();
            $table->decimal('temp', 4, 1)->nullable(); // air temp
            $table->decimal('uv_index', 3, 1)->nullable();
            $table->string('jellyfish_risk')->nullable();
            $table->timestamp('forecasted_at');
            $table->timestamps();

            $table->index(['beach_id', 'forecasted_at']);
        });

        // 8. Tide Forecasts
        Schema::create('tide_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('tide_station_id')->index();
            $table->timestamp('tide_time');
            $table->string('tide_type', 4); // high, low
            $table->decimal('tide_height', 4, 2);
            $table->timestamps();

            $table->unique(['tide_station_id', 'tide_time']);
        });

        // 9. Water Quality Snapshots (InfoÁgua)
        Schema::create('water_quality_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->string('quality_class'); // Excellent, Good, Sufficient, Poor
            $table->date('sampled_at');
            $table->timestamps();

            $table->index(['beach_id', 'sampled_at']);
        });

        // 10. Official Alerts (InfoÁgua / Authorities)
        Schema::create('official_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // restriction, interdiction, warning
            $table->text('description');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // 11. Flag Predictions (Computed forecasts)
        Schema::create('flag_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->integer('green_probability')->default(0);
            $table->integer('yellow_probability')->default(0);
            $table->integer('red_probability')->default(0);
            $table->string('selected_flag'); // green, yellow, red
            $table->integer('confidence')->default(100);
            $table->string('algorithm_version')->default('1.0');
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['beach_id', 'calculated_at']);
        });

        // 12. Flag Reports (Community Confirmations)
        Schema::create('flag_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->string('flag'); // green, yellow, red
            $table->integer('vote_weight')->default(1);
            $table->string('status')->default('pending'); // pending, confirmed, rejected, cancelled
            $table->decimal('distance_to_beach', 6, 3); // in km
            $table->decimal('gps_accuracy', 5, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable(); // Cleared nightly
            $table->decimal('longitude', 10, 7)->nullable(); // Cleared nightly
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['beach_id', 'status', 'reported_at']);
        });

        // 13. Current Status (Cached values for immediate rendering)
        Schema::create('beach_current_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beach_id')->unique()->constrained()->onDelete('cascade');
            $table->string('source')->default('prediction'); // prediction, community, alert
            $table->string('flag')->default('gray'); // green, yellow, red, gray, blue_or_neutral
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
            $table->unsignedBigInteger('referral_id')->nullable(); // resolved manually if needed
            $table->string('type'); // report_accepted, report_penalized, first_report_bonus, referral_bonus, admin_adjustment
            $table->integer('points');
            $table->string('status')->default('pending'); // pending, confirmed, cancelled
            $table->string('description');
            $table->timestamps();
        });

        // 15. Referrals
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invited_user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('code');
            $table->string('status')->default('pending'); // pending, qualified
            $table->timestamp('qualified_at')->nullable();
            $table->timestamps();
        });

        // 16. Restaurants
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique(); // tripadvisor/thefork id
            $table->string('source'); // tripadvisor, thefork
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

        // 17. Beach Restaurants pivot
        Schema::create('beach_restaurants', function (Blueprint $table) {
            $table->foreignId('beach_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->decimal('distance', 6, 3); // in km
            $table->primary(['beach_id', 'restaurant_id']);
        });

        // 18. Advertising Campaigns
        Schema::create('advertising_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('type'); // banner, business_featured, recommended
            $table->string('title');
            $table->string('image_path');
            $table->string('link');
            $table->string('placement_type'); // home, beach, list
            $table->foreignId('beach_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('municipality')->nullable();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 19. Advertising Placements (logs or specifications)
        Schema::create('advertising_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertising_campaign_id')->constrained()->onDelete('cascade');
            $table->string('placement_key'); // e.g. home_header, beach_sidebar
            $table->timestamps();
        });

        // 20. Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('advertising_placements');
        Schema::dropIfExists('advertising_campaigns');
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
