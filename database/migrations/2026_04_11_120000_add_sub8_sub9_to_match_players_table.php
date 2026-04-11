<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('match_players', 'sub8')) {
            Schema::table('match_players', function (Blueprint $table) {
                $table->unsignedBigInteger('sub8')->nullable()->after('sub7');
            });
        }

        if (! Schema::hasColumn('match_players', 'sub9')) {
            Schema::table('match_players', function (Blueprint $table) {
                $table->unsignedBigInteger('sub9')->nullable()->after('sub8');
            });
        }

        try {
            Schema::table('match_players', function (Blueprint $table) {
                $table->foreign('sub8')->references('id')->on('players')->onDelete('set null');
            });
        } catch (\Throwable) {
            /* column or FK already present */
        }

        try {
            Schema::table('match_players', function (Blueprint $table) {
                $table->foreign('sub9')->references('id')->on('players')->onDelete('set null');
            });
        } catch (\Throwable) {
            /* column or FK already present */
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('match_players')) {
            return;
        }

        try {
            Schema::table('match_players', function (Blueprint $table) {
                $table->dropForeign(['sub9']);
            });
        } catch (\Throwable) {
            /* */
        }

        try {
            Schema::table('match_players', function (Blueprint $table) {
                $table->dropForeign(['sub8']);
            });
        } catch (\Throwable) {
            /* */
        }

        if (Schema::hasColumn('match_players', 'sub9')) {
            Schema::table('match_players', function (Blueprint $table) {
                $table->dropColumn('sub9');
            });
        }

        if (Schema::hasColumn('match_players', 'sub8')) {
            Schema::table('match_players', function (Blueprint $table) {
                $table->dropColumn('sub8');
            });
        }
    }
};
