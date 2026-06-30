<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('advertising_placements');
        Schema::dropIfExists('advertising_campaigns');
    }

    public function down(): void
    {
        // Tables were created in 2026_06_27_213200_create_checkpraia_tables.php
    }
};
