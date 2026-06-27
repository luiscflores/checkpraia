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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->integer('score')->default(0)->after('password');
            $table->integer('confirmations_count')->default(0)->after('score');
            $table->integer('accepted_confirmations_count')->default(0)->after('confirmations_count');
            $table->integer('penalized_confirmations_count')->default(0)->after('accepted_confirmations_count');
            $table->boolean('is_suspended')->default(false)->after('penalized_confirmations_count');
            $table->string('referral_code')->nullable()->unique()->after('is_suspended');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'score',
                'confirmations_count',
                'accepted_confirmations_count',
                'penalized_confirmations_count',
                'is_suspended',
                'referral_code',
            ]);
        });
    }
};
