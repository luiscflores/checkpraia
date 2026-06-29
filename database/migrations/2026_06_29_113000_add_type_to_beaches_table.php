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
        Schema::table('beaches', function (Blueprint $table) {
            $table->string('type')->default('oceanic'); // oceanic, estuarine, fluvial, lagoon
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beaches', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
