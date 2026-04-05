<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Copy existing player_termination rows into player_club_history (terminated events).
     */
    public function up(): void
    {
        if (! Schema::hasTable('player_club_history') || ! Schema::hasTable('player_termination')) {
            return;
        }

        $rows = DB::table('player_termination')->get();
        foreach ($rows as $t) {
            $exists = DB::table('player_club_history')
                ->where('player_id', $t->player_id)
                ->where('club_id', $t->club_id)
                ->where('event_type', 'terminated')
                ->where('event_at', $t->terminated_at)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('player_club_history')->insert([
                'player_id' => $t->player_id,
                'club_id' => $t->club_id,
                'event_type' => 'terminated',
                'event_at' => $t->terminated_at,
                'admin_id' => $t->admin_id,
                'remark' => $t->remark,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Non-reversible data copy
    }
};
