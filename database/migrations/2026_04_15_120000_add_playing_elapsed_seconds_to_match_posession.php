<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('match_posession') && ! Schema::hasColumn('match_posession', 'playing_elapsed_seconds')) {
            Schema::table('match_posession', function (Blueprint $table) {
                $table->unsignedInteger('playing_elapsed_seconds')->nullable()->after('event_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('match_posession') && Schema::hasColumn('match_posession', 'playing_elapsed_seconds')) {
            Schema::table('match_posession', function (Blueprint $table) {
                $table->dropColumn('playing_elapsed_seconds');
            });
        }
    }
};
