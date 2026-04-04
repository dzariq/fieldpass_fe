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
            if (Schema::hasColumn('competition', 'pitch_image')) {
                $table->dropColumn('pitch_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('competition', function (Blueprint $table) {
            if (! Schema::hasColumn('competition', 'pitch_image')) {
                $table->string('pitch_image', 500)->nullable();
            }
        });
    }
};
