<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill assigned events from player_club pivot rows only.
     * Uses in-memory dedupe keys to avoid N+1 queries against player_club_history.
     */
    public function up(): void
    {
        if (! Schema::hasTable('player_club_history')) {
            return;
        }

        $now = now()->format('Y-m-d H:i:s');
        $assignedDayKeys = $this->loadAssignedDayKeys();
        $playerIds = $this->idLookupSet('players');
        $clubIds = $this->idLookupSet('club');

        $batch = [];

        if (Schema::hasTable('player_club')) {
            foreach (DB::table('player_club')->orderBy('id')->cursor() as $p) {
                if (! isset($playerIds[(int) $p->player_id], $clubIds[(int) $p->club_id])) {
                    continue;
                }

                $eventAt = $p->created_at
                    ? Carbon::parse($p->created_at)
                    : ($p->updated_at ? Carbon::parse($p->updated_at) : Carbon::parse($now));

                $key = $this->dayKey((int) $p->player_id, (int) $p->club_id, $eventAt);
                if (isset($assignedDayKeys[$key])) {
                    continue;
                }

                $assignedDayKeys[$key] = true;
                $batch[] = [
                    'player_id' => $p->player_id,
                    'club_id' => $p->club_id,
                    'event_type' => 'assigned',
                    'event_at' => $eventAt->format('Y-m-d H:i:s'),
                    'admin_id' => null,
                    'remark' => 'Backfill: player_club pivot #'.$p->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= 250) {
                    $this->flushBatch($batch);
                    $batch = [];
                }
            }
        }

        $this->flushBatch($batch);
    }

    public function down(): void
    {
        if (! Schema::hasTable('player_club_history')) {
            return;
        }

        DB::table('player_club_history')
            ->where('event_type', 'assigned')
            ->where('remark', 'like', 'Backfill: player_club pivot #%')
            ->delete();
    }

    /**
     * @return array<string, true>
     */
    private function loadAssignedDayKeys(): array
    {
        $keys = [];

        DB::table('player_club_history')
            ->where('event_type', 'assigned')
            ->orderBy('id')
            ->chunkById(5000, function ($rows) use (&$keys) {
                foreach ($rows as $r) {
                    $keys[$this->dayKey(
                        (int) $r->player_id,
                        (int) $r->club_id,
                        Carbon::parse($r->event_at),
                    )] = true;
                }
            });

        return $keys;
    }

    /**
     * @return array<int, true>
     */
    private function idLookupSet(string $table): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return array_flip(DB::table($table)->pluck('id')->all());
    }

    private function dayKey(int $playerId, int $clubId, Carbon $eventAt): string
    {
        return $playerId.'|'.$clubId.'|'.$eventAt->format('Y-m-d');
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function flushBatch(array &$rows): void
    {
        if ($rows === []) {
            return;
        }

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('player_club_history')->insert($chunk);
        }

        $rows = [];
    }
};
