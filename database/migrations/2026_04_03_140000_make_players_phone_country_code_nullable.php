<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'phone')) {
                $table->string('phone')->nullable()->change();
            }
            if (Schema::hasColumn('players', 'country_code')) {
                $table->string('country_code')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'phone')) {
                $table->string('phone')->nullable(false)->change();
            }
        });
    }
};
