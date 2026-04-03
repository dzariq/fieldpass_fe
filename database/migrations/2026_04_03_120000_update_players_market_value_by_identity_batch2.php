<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Second batch: update players.market_value by identity_number (one-time data fix).
     */
    public function up(): void
    {
        $path = __DIR__.'/data/player_market_batch2.tsv';
        if (! is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, 'identity_number')) {
                continue;
            }
            $parts = preg_split("/\t+/", $line, 2);
            if (count($parts) < 2) {
                continue;
            }
            [$identityNumber, $value] = $parts;
            $identityNumber = trim($identityNumber);
            $marketValue = (int) trim($value);
            DB::table('players')
                ->where('identity_number', $identityNumber)
                ->update(['market_value' => $marketValue]);
        }
    }

    public function down(): void
    {
        // No reliable rollback without storing previous values.
    }
};
