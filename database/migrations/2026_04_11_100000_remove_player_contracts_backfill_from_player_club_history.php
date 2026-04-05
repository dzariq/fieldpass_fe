<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove assignment rows created from player_contracts backfill; pivot-only is the source of truth.
     */
    public function up(): void
    {
        if (! Schema::hasTable('player_club_history')) {
            return;
        }

        DB::table('player_club_history')
            ->where('event_type', 'assigned')
            ->where('remark', 'like', 'Backfill: player_contracts #%')
            ->delete();
    }

    public function down(): void
    {
        // Non-reversible data deletion
    }
};
