<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (! Schema::hasColumn('players', 'identity_type')) {
                $table->enum('identity_type', ['malaysia_ic', 'foreign_id'])->default('malaysia_ic');
            }
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'identity_type')) {
                $table->dropColumn('identity_type');
            }
        });
    }
};
