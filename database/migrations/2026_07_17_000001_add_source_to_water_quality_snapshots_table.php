<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('water_quality_snapshots', function (Blueprint $table) {
            $table->string('source')->default('apa_arcgis')->after('quality_class');
        });
    }

    public function down(): void
    {
        Schema::table('water_quality_snapshots', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
