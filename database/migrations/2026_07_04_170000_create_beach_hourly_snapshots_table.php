<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->timestamps();

            $table->index(['beach_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beach_hourly_snapshots');
    }
};
