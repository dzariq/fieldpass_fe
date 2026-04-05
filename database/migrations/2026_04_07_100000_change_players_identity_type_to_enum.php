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
        if (! Schema::hasColumn('players', 'identity_type')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        DB::table('players')
            ->where(function ($q) {
                $q->whereNull('identity_type')
                    ->orWhereNotIn('identity_type', ['malaysia_ic', 'foreign_id']);
            })
            ->update(['identity_type' => 'malaysia_ic']);

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE `players` MODIFY `identity_type` ENUM('malaysia_ic', 'foreign_id') NOT NULL DEFAULT 'malaysia_ic'"
            );

            return;
        }

        Schema::table('players', function (Blueprint $table) {
            $table->enum('identity_type', ['malaysia_ic', 'foreign_id'])
                ->default('malaysia_ic')
                ->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('players', 'identity_type')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE `players` MODIFY `identity_type` VARCHAR(24) NOT NULL DEFAULT 'malaysia_ic'"
            );

            return;
        }

        Schema::table('players', function (Blueprint $table) {
            $table->string('identity_type', 24)->default('malaysia_ic')->change();
        });
    }
};
