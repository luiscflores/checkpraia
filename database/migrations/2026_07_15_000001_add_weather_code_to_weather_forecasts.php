<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weather_forecasts', function (Blueprint $table) {
            $table->smallInteger('weather_code')->nullable()->after('uv_index');
        });
    }

    public function down(): void
    {
        Schema::table('weather_forecasts', function (Blueprint $table) {
            $table->dropColumn('weather_code');
        });
    }
};
