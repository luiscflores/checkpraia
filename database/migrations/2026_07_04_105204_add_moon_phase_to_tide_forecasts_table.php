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
        Schema::table('tide_forecasts', function (Blueprint $table) {
            $table->decimal('moon_phase', 4, 2)->nullable()->after('tide_height');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tide_forecasts', function (Blueprint $table) {
            $table->dropColumn('moon_phase');
        });
    }
};
