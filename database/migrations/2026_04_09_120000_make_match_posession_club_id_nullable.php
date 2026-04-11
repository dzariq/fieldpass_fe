<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('match_posession')) {
            return;
        }

        Schema::table('match_posession', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
        });

        Schema::table('match_posession', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->change();
        });

        Schema::table('match_posession', function (Blueprint $table) {
            $table->foreign('club_id')->references('id')->on('club')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('match_posession')) {
            return;
        }

        Schema::table('match_posession', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
        });

        DB::table('match_posession')->whereNull('club_id')->delete();

        Schema::table('match_posession', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable(false)->change();
        });

        Schema::table('match_posession', function (Blueprint $table) {
            $table->foreign('club_id')->references('id')->on('club')->cascadeOnDelete();
        });
    }
};
