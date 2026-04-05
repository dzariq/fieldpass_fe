<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('match')) {
            return;
        }

        Schema::table('match', function (Blueprint $table) {
            if (! Schema::hasColumn('match', 'timer_pause_started_at')) {
                $table->dateTime('timer_pause_started_at')->nullable();
            }
            if (! Schema::hasColumn('match', 'timer_paused_seconds')) {
                $table->unsignedInteger('timer_paused_seconds')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('match')) {
            return;
        }

        Schema::table('match', function (Blueprint $table) {
            if (Schema::hasColumn('match', 'timer_paused_seconds')) {
                $table->dropColumn('timer_paused_seconds');
            }
            if (Schema::hasColumn('match', 'timer_pause_started_at')) {
                $table->dropColumn('timer_pause_started_at');
            }
        });
    }
};
