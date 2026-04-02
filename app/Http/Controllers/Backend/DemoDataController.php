<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DemoDataController extends Controller
{
    /** Current demo fingerprint (16 players per club × 6 clubs = 96 players). */
    public const DEMO_KEY = 'assoc:1|comp:7|clubs:6|players_per_club:16';

    private const CLUB_COUNT = 6;

    private const PLAYERS_PER_CLUB = 16;

    /** Earlier demo runs (10 players/club); still honored for disable + dashboard. */
    private const DEMO_KEYS_LEGACY = [
        'assoc:1|comp:7|clubs:6|players_per_club:10',
    ];

    /**
     * @return list<string>
     */
    public static function allDemoKeys(): array
    {
        return array_merge([self::DEMO_KEY], self::DEMO_KEYS_LEGACY);
    }

    public function enable(): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['association.create']); // superadmin

        $alreadyEnabled = DB::table('demo_data_runs')
            ->whereIn('key', self::allDemoKeys())
            ->where('enabled', 1)
            ->exists();

        if ($alreadyEnabled) {
            return back()->with('success', 'Demo data is already enabled.');
        }

        try {
            DB::beginTransaction();

            $runId = DB::table('demo_data_runs')->insertGetId([
                'key' => self::DEMO_KEY, // fingerprint includes players_per_club:16
                'enabled' => 1,
                'created_by_admin_id' => auth()->user()->id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $clubsToCreate = [
                ['code' => 'KPM', 'long_name' => 'Kelab Puteri Melur'],
                ['code' => 'SKJ', 'long_name' => 'Kelab Srikandi Jaya'],
                ['code' => 'BRF', 'long_name' => 'Kelab Bunga Raya FC'],
                ['code' => 'KMS', 'long_name' => 'Kelab Mawar Selatan'],
                ['code' => 'SWU', 'long_name' => 'Kelab Seri Wangi United'],
                ['code' => 'KAM', 'long_name' => 'Kelab Anggun Muda'],
            ];

            $clubIds = [];
            $hasLongName = DB::table('information_schema.columns')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', 'club')
                ->where('column_name', 'long_name')
                ->exists();

            foreach ($clubsToCreate as $club) {
                $clubInsert = [
                    'name' => $club['code'], // 3-char code
                    'association_id' => 1,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if ($hasLongName) {
                    $clubInsert['long_name'] = $club['long_name'];
                }

                $clubId = DB::table('club')->insertGetId($clubInsert);
                $clubIds[$club['code']] = $clubId;

                DB::table('demo_data_items')->insert([
                    'run_id' => $runId,
                    'table_name' => 'club',
                    'record_id' => $clubId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $competitionClubId = DB::table('competition_club')->insertGetId([
                    'competition_id' => 7,
                    'club_id' => $clubId,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('demo_data_items')->insert([
                    'run_id' => $runId,
                    'table_name' => 'competition_club',
                    'record_id' => $competitionClubId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $malayWomenNames = [
                'Aisyah', 'Nurul', 'Siti', 'Hana', 'Farah', 'Nadia', 'Amira', 'Balqis', 'Syafiqah', 'Nurin',
                'Aina', 'Izzah', 'Sofea', 'Dania', 'Husna', 'Aleesya', 'Qistina', 'Adriana', 'Shazwani', 'Nabila',
                'Atiqah', 'Alia', 'Najwa', 'Yasmin', 'Haziqah', 'Arissa', 'Mira', 'Anis', 'Maryam', 'Zulaikha',
                'Aqilah', 'Hawa', 'Zahra', 'Intan', 'Solehah', 'Humaira', 'Alyaa', 'Nadiah', 'Sabrina', 'Khadijah',
                'Salwa', 'Mardhiah', 'Putri', 'Alya', 'Hidayah', 'Natasya', 'Syazana', 'Jannah', 'Maisarah', 'Zarina',
                'Raihana', 'Natasha', 'Hanis', 'Anisya', 'Hajar', 'Nursyuhada', 'Afiqah', 'Wardah', 'Farhana', 'Irdina',
                'Syuhada', 'Dalili', 'Suraya', 'Amani', 'Damia', 'Insyirah', 'Juwairiah', 'Kalilah', 'Liyana', 'Fatinah',
                'Najiha', 'Oliya', 'Qalesya', 'Rina', 'Safiyyah', 'Tengku', 'Umaira', 'Wanie', 'Yumna', 'Zarith',
            ];

            $positions = ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'];
            $passwordHash = Hash::make('password');
            $playerCounter = 1;

            foreach ($clubIds as $clubCode => $clubId) {
                for ($i = 1; $i <= self::PLAYERS_PER_CLUB; $i++) {
                    $name = $malayWomenNames[($playerCounter - 1) % count($malayWomenNames)];
                    $identity = sprintf('DEMO-%d-%03d', $runId, $playerCounter);
                    $email = sprintf('demo%u_p%03d@fieldpass.local', $runId, $playerCounter);
                    $username = sprintf('demo%u_p%03d', $runId, $playerCounter);
                    $phone = '6011' . str_pad((string) (10000000 + $playerCounter), 8, '0', STR_PAD_LEFT);
                    $position = $positions[($playerCounter - 1) % count($positions)];

                    $playerId = DB::table('players')->insertGetId([
                        'name' => $name,
                        'full_name' => $name,
                        'identity_number' => $identity,
                        'phone' => $phone,
                        'email' => $email,
                        'username' => $username,
                        'password' => $passwordHash,
                        'status' => 'ACTIVE',
                        'position' => $position,
                        'market_value' => 50.00,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('demo_data_items')->insert([
                        'run_id' => $runId,
                        'table_name' => 'players',
                        'record_id' => $playerId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $playerClubId = DB::table('player_club')->insertGetId([
                        'player_id' => $playerId,
                        'club_id' => $clubId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('demo_data_items')->insert([
                        'run_id' => $runId,
                        'table_name' => 'player_club',
                        'record_id' => $playerClubId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $contractId = DB::table('player_contracts')->insertGetId([
                        'player_id' => $playerId,
                        'club_id' => $clubId,
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->addYear()->toDateString(),
                        'salary' => 50.00,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('demo_data_items')->insert([
                        'run_id' => $runId,
                        'table_name' => 'player_contracts',
                        'record_id' => $contractId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $playerCounter++;
                }
            }

            DB::commit();
            $totalPlayers = self::CLUB_COUNT * self::PLAYERS_PER_CLUB;

            return back()->with('success', sprintf(
                'Demo data enabled: %d clubs + %d players (%d each) added and joined to competition #7.',
                self::CLUB_COUNT,
                $totalPlayers,
                self::PLAYERS_PER_CLUB
            ));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Demo data enable failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to enable demo data: ' . $e->getMessage()]);
        }
    }

    public function disable(): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['association.create']); // superadmin

        $run = DB::table('demo_data_runs')
            ->whereIn('key', self::allDemoKeys())
            ->where('enabled', 1)
            ->orderByDesc('id')
            ->first();

        if (!$run) {
            return back()->with('success', 'Demo data is already disabled.');
        }

        try {
            DB::beginTransaction();

            $items = DB::table('demo_data_items')
                ->where('run_id', $run->id)
                ->get()
                ->groupBy('table_name');

            $deleteByTable = function (string $table) use ($items) {
                if (!isset($items[$table]) || $items[$table]->isEmpty()) {
                    return;
                }

                $ids = $items[$table]->pluck('record_id')->map(fn ($v) => (int) $v)->values()->all();
                DB::table($table)->whereIn('id', $ids)->delete();
            };

            // Delete in dependency-safe order
            $deleteByTable('player_contracts');
            $deleteByTable('player_club');
            $deleteByTable('players');
            $deleteByTable('competition_club');
            $deleteByTable('club');

            DB::table('demo_data_runs')->where('id', $run->id)->update([
                'enabled' => 0,
                'updated_at' => now(),
            ]);

            DB::commit();
            return back()->with('success', 'Demo data disabled and rolled back.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Demo data disable failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to disable demo data: ' . $e->getMessage()]);
        }
    }
}

