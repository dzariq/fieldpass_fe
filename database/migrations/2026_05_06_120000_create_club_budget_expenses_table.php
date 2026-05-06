<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_budget_expense', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->string('name');
            $table->decimal('tax_percentage', 6, 2)->nullable()->comment('VAT / tax rate in percent');
            $table->string('currency', 3);
            $table->decimal('amount', 14, 2);
            $table->string('recurrence', 20);
            $table->string('bill_to');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('club')->cascadeOnDelete();
            $table->index(['club_id', 'year', 'month']);
        });

        $group = 'budget_expense';
        $names = [
            'budget_expense.view',
            'budget_expense.create',
            'budget_expense.edit',
            'budget_expense.delete',
        ];

        foreach ($names as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'admin'],
                ['group_name' => $group]
            );
        }

        $clubManager = Role::where('name', 'Club Manager')->where('guard_name', 'admin')->first();
        if ($clubManager !== null) {
            $clubManager->givePermissionTo($names);
        }

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $names = [
            'budget_expense.view',
            'budget_expense.create',
            'budget_expense.edit',
            'budget_expense.delete',
        ];

        $clubManager = Role::where('name', 'Club Manager')->where('guard_name', 'admin')->first();
        if ($clubManager !== null) {
            foreach ($names as $name) {
                $clubManager->revokePermissionTo($name);
            }
        }

        foreach ($names as $name) {
            Permission::where('name', $name)->where('guard_name', 'admin')->delete();
        }

        Schema::dropIfExists('club_budget_expense');

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
