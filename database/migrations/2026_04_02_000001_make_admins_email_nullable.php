<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (DB::table('admins')->whereNull('email')->get() as $row) {
            DB::table('admins')->where('id', $row->id)->update([
                'email' => 'legacy-admin-'.$row->id.'@noreply.fieldpass.local',
            ]);
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
