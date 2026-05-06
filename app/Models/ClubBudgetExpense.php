<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubBudgetExpense extends Model
{
    protected $table = 'club_budget_expense';

    public const RECURRENCE_MONTHLY = 'monthly';

    public const RECURRENCE_QUARTERLY = 'quarterly';

    public const RECURRENCE_YEARLY = 'yearly';

    public const RECURRENCE_ONE_TIME = 'one_time';

    protected $fillable = [
        'club_id',
        'name',
        'tax_percentage',
        'currency',
        'amount',
        'recurrence',
        'bill_to',
        'year',
        'month',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public static function recurrenceOptions(): array
    {
        return [
            self::RECURRENCE_MONTHLY => 'Monthly',
            self::RECURRENCE_QUARTERLY => 'Quarterly',
            self::RECURRENCE_YEARLY => 'Yearly',
            self::RECURRENCE_ONE_TIME => 'One-time',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ClubBudgetExpense $expense): void {
            $expense->currency = strtoupper((string) $expense->currency);
        });
    }
}
