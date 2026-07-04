<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('weather_forecasts', function (Blueprint $table) {
            $table->dropColumn('jellyfish_risk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weather_forecasts', function (Blueprint $table) {
            $table->string('jellyfish_risk')->nullable();
        });
    }
};
