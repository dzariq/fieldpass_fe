@extends('backend.layouts.master')

@section('title')
Budget &amp; expenses - Admin Panel
@endsection

@section('admin-content')
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Budget &amp; expenses</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li><span>Budget &amp; expenses</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="main-content-inner">
    @if(($clubId ?? null) === null)
        <div class="alert alert-warning">
            <strong>{{ __('No club linked') }}</strong>
            <p class="mb-0">{{ __('Your admin account is not linked to a club. Ask an administrator to assign you in admin_club before using budget & expenses.') }}</p>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    @php
        $sortLink = function (string $col) use ($filterParams, $sort, $direction) {
            $textCols = ['name', 'bill_to', 'recurrence', 'currency'];
            $defaultDir = in_array($col, $textCols, true) ? 'asc' : 'desc';
            $nextDir = ($sort === $col) ? ($direction === 'asc' ? 'desc' : 'asc') : $defaultDir;
            return route('admin.budget-expenses.index', array_merge($filterParams, ['sort' => $col, 'direction' => $nextDir]));
        };
        $canMutate = auth()->guard('admin')->user()->hasRole('Club Manager')
            || auth()->guard('admin')->user()->can('budget_expense.create')
            || auth()->guard('admin')->user()->can('budget_expense.edit')
            || auth()->guard('admin')->user()->can('budget_expense.delete');
    @endphp

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('admin.budget-expenses.index') }}" class="form-inline flex-wrap align-items-end">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <div class="form-group mr-3 mb-2">
                    <label for="filter_year" class="mr-2">Year</label>
                    <select name="filter_year" id="filter_year" class="form-control">
                        <option value="">All years</option>
                        @foreach($distinctYears ?? [] as $y)
                            <option value="{{ $y }}" {{ (string) ($filters['year'] ?? '') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-3 mb-2">
                    <label for="filter_month" class="mr-2">Month</label>
                    <select name="filter_month" id="filter_month" class="form-control">
                        <option value="">All months</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (string) ($filters['month'] ?? '') === (string) $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(2020, $m, 1)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2 mr-2">Apply filters</button>
                <a href="{{ route('admin.budget-expenses.index', ['sort' => $sort, 'direction' => $direction]) }}" class="btn btn-outline-secondary mb-2">Reset</a>
            </form>
        </div>
    </div>

    @if(($clubId ?? null) !== null && ($canMutate && (auth()->guard('admin')->user()->hasRole('Club Manager') || auth()->guard('admin')->user()->can('budget_expense.create'))))
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="header-title mb-0">Add expense</h4>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.budget-expenses.store') }}">
                @csrf
                @foreach(['filter_year', 'filter_month', 'sort', 'direction'] as $k)
                    @if(request()->filled($k))
                        <input type="hidden" name="{{ $k }}" value="{{ request($k) }}">
                    @endif
                @endforeach
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label for="add_name">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="add_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="add_tax_percentage">Tax (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_percentage" id="add_tax_percentage" class="form-control @error('tax_percentage') is-invalid @enderror" value="{{ old('tax_percentage') }}" placeholder="Optional">
                        @error('tax_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="add_currency">Currency <span class="text-danger">*</span></label>
                        <select name="currency" id="add_currency" class="form-control @error('currency') is-invalid @enderror" required>
                            @foreach($currencyOptions ?? [] as $code => $label)
                                <option value="{{ $code }}" {{ old('currency', 'USD') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="add_amount">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="amount" id="add_amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="add_recurrence">Recurrence <span class="text-danger">*</span></label>
                        <select name="recurrence" id="add_recurrence" class="form-control @error('recurrence') is-invalid @enderror" required>
                            @foreach($recurrenceLabels as $value => $rlabel)
                                <option value="{{ $value }}" {{ old('recurrence') === $value ? 'selected' : '' }}>{{ $rlabel }}</option>
                            @endforeach
                        </select>
                        @error('recurrence')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="add_bill_to">Bill to <span class="text-danger">*</span></label>
                        <input type="text" name="bill_to" id="add_bill_to" class="form-control @error('bill_to') is-invalid @enderror" value="{{ old('bill_to') }}" required>
                        @error('bill_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="add_year">Year <span class="text-danger">*</span></label>
                        <input type="number" name="year" id="add_year" class="form-control @error('year') is-invalid @enderror" value="{{ old('year', date('Y')) }}" min="2000" max="2100" required>
                        @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="add_month">Month <span class="text-danger">*</span></label>
                        <select name="month" id="add_month" class="form-control @error('month') is-invalid @enderror" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int) old('month', (int) date('n')) === $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(2020, $m, 1)->format('M') }}
                                </option>
                            @endfor
                        </select>
                        @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Save expense</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(($clubId ?? null) !== null && ($expenseCount ?? 0) === 0)
        <div class="alert alert-info">{{ __('No expenses yet. Add one above, or widen your year/month filters.') }}</div>
    @elseif(($clubId ?? null) !== null)
        @foreach($grouped as $year => $months)
            <div class="mb-4">
                <h3 class="text-primary border-bottom pb-2">{{ $year }}</h3>
                @foreach($months as $month => $expenses)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>{{ \Carbon\Carbon::createFromDate((int) $year, (int) $month, 1)->format('F Y') }}</strong>
                            <span class="badge badge-secondary">{{ $expenses->count() }} {{ \Illuminate\Support\Str::plural('item', $expenses->count()) }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><a href="{{ $sortLink('name') }}">Name @if($sort==='name')<i class="fa fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th>
                                            <th><a href="{{ $sortLink('tax_percentage') }}">Tax % @if($sort==='tax_percentage')<i class="fa fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th>
                                            <th><a href="{{ $sortLink('currency') }}">Currency @if($sort==='currency')<i class="fa fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th>
                                            <th><a href="{{ $sortLink('amount') }}">Amount @if($sort==='amount')<i class="fa fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th>
                                            <th><a href="{{ $sortLink('recurrence') }}">Recurrence @if($sort==='recurrence')<i class="fa fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th>
                                            <th><a href="{{ $sortLink('bill_to') }}">Bill to @if($sort==='bill_to')<i class="fa fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th>
                                            @if(($canMutate ?? false) && (auth()->guard('admin')->user()->hasRole('Club Manager') || auth()->guard('admin')->user()->can('budget_expense.edit') || auth()->guard('admin')->user()->can('budget_expense.delete')))
                                                <th class="text-right" style="min-width: 140px;">Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expenses as $row)
                                            <tr>
                                                <td>{{ $row->name }}</td>
                                                <td>@if($row->tax_percentage !== null){{ number_format((float) $row->tax_percentage, 2) }}@else—@endif</td>
                                                <td>{{ $row->currency }}</td>
                                                <td>{{ number_format((float) $row->amount, 2) }}</td>
                                                <td>{{ $recurrenceLabels[$row->recurrence] ?? $row->recurrence }}</td>
                                                <td>{{ $row->bill_to }}</td>
                                                @if(($canMutate ?? false) && (auth()->guard('admin')->user()->hasRole('Club Manager') || auth()->guard('admin')->user()->can('budget_expense.edit') || auth()->guard('admin')->user()->can('budget_expense.delete')))
                                                    <td class="text-right text-nowrap">
                                                        @if(auth()->guard('admin')->user()->hasRole('Club Manager') || auth()->guard('admin')->user()->can('budget_expense.edit'))
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary btn-edit-expense"
                                                                data-id="{{ $row->id }}"
                                                                data-name="{{ e($row->name) }}"
                                                                data-tax-percentage="{{ $row->tax_percentage }}"
                                                                data-currency="{{ $row->currency }}"
                                                                data-amount="{{ $row->amount }}"
                                                                data-recurrence="{{ $row->recurrence }}"
                                                                data-bill-to="{{ e($row->bill_to) }}"
                                                                data-year="{{ $row->year }}"
                                                                data-month="{{ $row->month }}"
                                                                data-toggle="modal"
                                                                data-target="#modalEditExpense">
                                                                Edit
                                                            </button>
                                                        @endif
                                                        @if(auth()->guard('admin')->user()->hasRole('Club Manager') || auth()->guard('admin')->user()->can('budget_expense.delete'))
                                                            <form action="{{ route('admin.budget-expenses.destroy', $row->id) }}" method="post" class="d-inline" onsubmit="return confirm({{ json_encode(__('Delete this expense?')) }});">
                                                                @csrf
                                                                @method('DELETE')
                                                                @foreach(['filter_year', 'filter_month', 'sort', 'direction'] as $k)
                                                                    @if(request()->filled($k))
                                                                        <input type="hidden" name="{{ $k }}" value="{{ request($k) }}">
                                                                    @endif
                                                                @endforeach
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif
@if(($clubId ?? null) !== null && ($canMutate ?? false) && (auth()->guard('admin')->user()->hasRole('Club Manager') || auth()->guard('admin')->user()->can('budget_expense.edit')))
<div class="modal fade" id="modalEditExpense" tabindex="-1" role="dialog" aria-labelledby="modalEditExpenseLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" id="formEditExpense">
                @csrf
                @method('PUT')
                @foreach(['filter_year', 'filter_month', 'sort', 'direction'] as $k)
                    @if(request()->filled($k))
                        <input type="hidden" name="{{ $k }}" value="{{ request($k) }}">
                    @endif
                @endforeach
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditExpenseLabel">Edit expense</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="edit_name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="edit_tax_percentage">Tax (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="tax_percentage" id="edit_tax_percentage" class="form-control">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="edit_currency">Currency <span class="text-danger">*</span></label>
                            <select name="currency" id="edit_currency" class="form-control" required>
                                @foreach($currencyOptions ?? [] as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="edit_amount">Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount" id="edit_amount" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="edit_recurrence">Recurrence <span class="text-danger">*</span></label>
                            <select name="recurrence" id="edit_recurrence" class="form-control" required>
                                @foreach($recurrenceLabels as $value => $rlabel)
                                    <option value="{{ $value }}">{{ $rlabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="edit_bill_to">Bill to <span class="text-danger">*</span></label>
                            <input type="text" name="bill_to" id="edit_bill_to" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="edit_year">Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" id="edit_year" class="form-control" min="2000" max="2100" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="edit_month">Month <span class="text-danger">*</span></label>
                            <select name="month" id="edit_month" class="form-control" required>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(2020, $m, 1)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
</div>

@endsection

@section('scripts')
<script>
(function () {
    var baseUpdate = {{ json_encode(rtrim(url('/admin/budget-expenses'), '/')) }};
    document.querySelectorAll('.btn-edit-expense').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-id');
            var form = document.getElementById('formEditExpense');
            if (form) {
                form.action = baseUpdate + '/' + id;
            }
            var elName = document.getElementById('edit_name');
            if (elName) { elName.value = btn.getAttribute('data-name') || ''; }
            var tax = btn.getAttribute('data-tax-percentage');
            var elTax = document.getElementById('edit_tax_percentage');
            if (elTax) { elTax.value = (tax === '' || tax === 'null' || tax === null) ? '' : tax; }
            var elCur = document.getElementById('edit_currency');
            if (elCur) { elCur.value = btn.getAttribute('data-currency') || 'USD'; }
            var elAmt = document.getElementById('edit_amount');
            if (elAmt) { elAmt.value = btn.getAttribute('data-amount') || ''; }
            var elRec = document.getElementById('edit_recurrence');
            if (elRec) { elRec.value = btn.getAttribute('data-recurrence') || ''; }
            var elBill = document.getElementById('edit_bill_to');
            if (elBill) { elBill.value = btn.getAttribute('data-bill-to') || ''; }
            var elY = document.getElementById('edit_year');
            if (elY) { elY.value = btn.getAttribute('data-year') || ''; }
            var elM = document.getElementById('edit_month');
            if (elM) { elM.value = btn.getAttribute('data-month') || ''; }
        });
    });
})();
</script>
@endsection
