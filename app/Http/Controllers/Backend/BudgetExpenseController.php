<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetExpenseRequest;
use App\Models\Admin;
use App\Models\ClubBudgetExpense;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BudgetExpenseController extends Controller
{
    private function currentAdminClubId(): ?int
    {
        $admin = Admin::query()->find(auth()->guard('admin')->id());

        return $admin?->primaryClubId();
    }

    private function authorizeBudgetAccess(string $permission): void
    {
        $user = auth()->guard('admin')->user();
        if ($user !== null && $user->can($permission)) {
            return;
        }
        if ($user !== null && $user->hasRole('Club Manager') && $this->currentAdminClubId() !== null) {
            return;
        }
        abort(403, 'Sorry !! You are unauthorized to perform this action.');
    }

    public function index(Request $request): Renderable
    {
        $this->authorizeBudgetAccess('budget_expense.view');

        $clubId = $this->currentAdminClubId();
        $recurrenceLabels = ClubBudgetExpense::recurrenceOptions();

        if ($clubId === null) {
            return view('backend.pages.budget_expenses.index', [
                'grouped' => collect(),
                'clubId' => null,
                'expenseCount' => 0,
                'recurrenceLabels' => $recurrenceLabels,
                'distinctYears' => collect(),
                'filters' => [
                    'year' => $request->input('filter_year'),
                    'month' => $request->input('filter_month'),
                ],
                'filterParams' => [],
                'sort' => 'year',
                'direction' => 'desc',
                'currencyOptions' => $this->currencyOptions(),
            ]);
        }

        $allowedSorts = ['name', 'tax_percentage', 'currency', 'amount', 'recurrence', 'bill_to', 'year', 'month'];
        $sort = $request->input('sort', 'year');
        if (! is_string($sort) || ! in_array($sort, $allowedSorts, true)) {
            $sort = 'year';
        }
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = ClubBudgetExpense::query()->where('club_id', $clubId);

        if ($request->filled('filter_year') && ctype_digit((string) $request->input('filter_year'))) {
            $query->where('year', (int) $request->input('filter_year'));
        }
        if ($request->filled('filter_month') && ctype_digit((string) $request->input('filter_month'))) {
            $m = (int) $request->input('filter_month');
            if ($m >= 1 && $m <= 12) {
                $query->where('month', $m);
            }
        }

        $query->orderBy($sort, $direction);
        if ($sort !== 'year') {
            $query->orderByDesc('year');
        }
        if ($sort !== 'month') {
            $query->orderByDesc('month');
        }
        if ($sort !== 'name') {
            $query->orderBy('name');
        }

        $items = $query->get();
        $expenseCount = $items->count();

        $grouped = $items->groupBy('year')->map(function (Collection $byYear): Collection {
            return $byYear->groupBy('month')->sortKeysDesc(false);
        })->sortKeysDesc(false);

        $distinctYears = ClubBudgetExpense::query()
            ->where('club_id', $clubId)
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $filterParams = array_filter(
            [
                'filter_year' => $request->input('filter_year'),
                'filter_month' => $request->input('filter_month'),
            ],
            static fn ($v) => $v !== null && $v !== ''
        );

        return view('backend.pages.budget_expenses.index', [
            'grouped' => $grouped,
            'clubId' => $clubId,
            'expenseCount' => $expenseCount,
            'recurrenceLabels' => $recurrenceLabels,
            'distinctYears' => $distinctYears,
            'filters' => [
                'year' => $request->input('filter_year'),
                'month' => $request->input('filter_month'),
            ],
            'filterParams' => $filterParams,
            'sort' => $sort,
            'direction' => $direction,
            'currencyOptions' => $this->currencyOptions(),
        ]);
    }

    public function store(BudgetExpenseRequest $request): RedirectResponse
    {
        $this->authorizeBudgetAccess('budget_expense.create');

        $clubId = $this->currentAdminClubId();
        if ($clubId === null) {
            return redirect()->back()->with('error', __('No club is linked to your account.'));
        }

        ClubBudgetExpense::query()->create(array_merge($request->validated(), [
            'club_id' => $clubId,
            'currency' => strtoupper($request->input('currency')),
        ]));

        return redirect()->route('admin.budget-expenses.index', array_filter([
            'filter_year' => $request->input('filter_year'),
            'filter_month' => $request->input('filter_month'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction'),
        ], static fn ($v) => $v !== null && $v !== ''))
            ->with('success', __('Expense added.'));
    }

    public function update(BudgetExpenseRequest $request, ClubBudgetExpense $club_budget_expense): RedirectResponse
    {
        $this->authorizeBudgetAccess('budget_expense.edit');

        $clubId = $this->currentAdminClubId();
        if ($clubId === null || (int) $club_budget_expense->club_id !== $clubId) {
            abort(404);
        }

        $club_budget_expense->update(array_merge($request->validated(), [
            'currency' => strtoupper($request->input('currency')),
        ]));

        return redirect()->route('admin.budget-expenses.index', array_filter([
            'filter_year' => $request->input('filter_year'),
            'filter_month' => $request->input('filter_month'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction'),
        ], static fn ($v) => $v !== null && $v !== ''))
            ->with('success', __('Expense updated.'));
    }

    public function destroy(Request $request, ClubBudgetExpense $club_budget_expense): RedirectResponse
    {
        $this->authorizeBudgetAccess('budget_expense.delete');

        $clubId = $this->currentAdminClubId();
        if ($clubId === null || (int) $club_budget_expense->club_id !== $clubId) {
            abort(404);
        }

        $club_budget_expense->delete();

        return redirect()->route('admin.budget-expenses.index', array_filter([
            'filter_year' => $request->input('filter_year'),
            'filter_month' => $request->input('filter_month'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction'),
        ], static fn ($v) => $v !== null && $v !== ''))
            ->with('success', __('Expense removed.'));
    }

    /**
     * @return array<string, string>
     */
    private function currencyOptions(): array
    {
        return [
            'USD' => 'USD',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
            'SGD' => 'SGD',
            'MYR' => 'MYR',
            'IDR' => 'IDR',
            'THB' => 'THB',
            'AUD' => 'AUD',
        ];
    }

}
