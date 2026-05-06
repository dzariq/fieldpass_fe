<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ClubBudgetExpense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $tax = $this->input('tax_percentage');
        if ($tax === '' || $tax === null) {
            $this->merge(['tax_percentage' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'currency' => ['required', 'string', 'size:3'],
            'amount' => ['required', 'numeric', 'min:0'],
            'recurrence' => ['required', 'string', Rule::in([
                ClubBudgetExpense::RECURRENCE_MONTHLY,
                ClubBudgetExpense::RECURRENCE_QUARTERLY,
                ClubBudgetExpense::RECURRENCE_YEARLY,
                ClubBudgetExpense::RECURRENCE_ONE_TIME,
            ])],
            'bill_to' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
}
