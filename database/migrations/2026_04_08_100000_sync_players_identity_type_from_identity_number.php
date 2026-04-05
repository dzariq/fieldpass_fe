<?php

use App\Models\Player;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Align identity_type with identity_number: XXXXXX-XX-XXXX => malaysia_ic, else foreign_id.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('players', 'identity_type')) {
            return;
        }

        DB::table('players')->orderBy('id')->chunkById(500, function ($rows): void {
            foreach ($rows as $row) {
                $type = Player::inferIdentityTypeFromIdentityNumber($row->identity_number ?? null);
                if (($row->identity_type ?? '') !== $type) {
                    DB::table('players')->where('id', $row->id)->update(['identity_type' => $type]);
                }
            }
        });
    }

    public function down(): void
    {
        // Data migration; no safe rollback.
    }
};
