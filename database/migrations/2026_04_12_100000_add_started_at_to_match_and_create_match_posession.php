<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('match') && ! Schema::hasColumn('match', 'started_at')) {
            Schema::table('match', function (Blueprint $table) {
                $table->dateTime('started_at')->nullable();
            });
        }

        if (! Schema::hasTable('match_posession')) {
            Schema::create('match_posession', function (Blueprint $table) {
                $table->id();
                $table->foreignId('match_id')->constrained('match')->cascadeOnDelete();
                $table->foreignId('club_id')->constrained('club')->cascadeOnDelete();
                $table->dateTime('event_at');
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();

                $table->index(['match_id', 'event_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('match_posession');

        if (Schema::hasTable('match') && Schema::hasColumn('match', 'started_at')) {
            Schema::table('match', function (Blueprint $table) {
                $table->dropColumn('started_at');
            });
        }
    }
};
