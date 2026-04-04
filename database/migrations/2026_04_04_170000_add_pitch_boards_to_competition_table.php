<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition', function (Blueprint $table) {
            if (! Schema::hasColumn('competition', 'pitch_board_1')) {
                $table->string('pitch_board_1', 500)->nullable();
            }
            if (! Schema::hasColumn('competition', 'pitch_board_2')) {
                $table->string('pitch_board_2', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('competition', function (Blueprint $table) {
            if (Schema::hasColumn('competition', 'pitch_board_1')) {
                $table->dropColumn('pitch_board_1');
            }
            if (Schema::hasColumn('competition', 'pitch_board_2')) {
                $table->dropColumn('pitch_board_2');
            }
        });
    }
};
