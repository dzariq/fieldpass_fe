@extends('backend.layouts.master')

@section('title')
Fantasy Edit - Admin Panel
@endsection

@section('styles')
<style>
    .matchweek-card {
        border-left: 4px solid #007bff;
        margin-bottom: 20px;
    }

    .matchweek-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.matchweek-actions .btn-sm {
    padding: 5px 10px;
    font-size: 0.813rem;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

/* Add animation for delete */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.matchweek-card.deleting {
    animation: shake 0.5s;
    opacity: 0.5;
}

    .matchweek-card.status-done {
        border-left-color: #6c757d;
        opacity: 0.9;
    }

    .matchweek-header {
        background-color: #f8f9fa;
        padding: 15px;
        font-weight: bold;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background-color: #28a745;
        color: white;
    }

    .status-done {
        background-color: #6c757d;
        color: white;
    }

    .status-pending {
        background-color: #ffc107;
        color: #212529;
    }

    .matchweek-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .add-matchweek-card {
        border: 2px dashed #007bff;
        background-color: #f8f9fa;
        margin-bottom: 20px;
    }

    .add-matchweek-header {
        background-color: #e9ecef;
        padding: 15px;
        font-weight: bold;
        border-bottom: 2px dashed #007bff;
    }
</style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ __('Fantasy Rules Edit') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.fantasy.index') }}">{{ __('All Fantasy') }}</a></li>
                    <li><span>{{ __('Edit Fantasy Rules') }} - {{ $competition->name }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>
<!-- page title area end -->

<div class="main-content-inner">
    <div class="row">
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">{{ __('Edit Fantasy Rules') }} - {{ $competition->name }}</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.fantasy.update', $competition->id) }}" method="POST">
                        @method('PUT')
                        @csrf

                        <div class="alert alert-info">
                            <strong>Competition:</strong> {{ $competition->name }}<br>
                            <strong>Total Matchweeks:</strong> {{ $fantasyRules->matchweeks ?? 'N/A' }}<br>
                            <strong>Current Matchweeks:</strong> {{ $timelines->count() }}
                        </div>

                        <!-- Add New Matchweek Button -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-success" onclick="addNewMatchweek()">
                                <i class="fa fa-plus"></i> Add New Matchweek
                            </button>
                        </div>

                        <!-- New Matchweek Template (Hidden) -->
                        <div id="newMatchweekTemplate" class="card add-matchweek-card" style="display: none;">
                            <div class="add-matchweek-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-calendar-plus-o"></i> New Matchweek <span class="matchweek-number"></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeNewMatchweek(this)">
                                        <i class="fa fa-times"></i> Remove
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>
                                                <i class="fa fa-exchange"></i> {{ __('Transfer') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                data-name="new_matchweeks[MATCHWEEK_NUM][transfer]"
                                                value="2"
                                                min="0"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>
                                                <i class="fa fa-users"></i> {{ __('Max Same Club') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                data-name="new_matchweeks[MATCHWEEK_NUM][max_same_club]"
                                                value="3"
                                                min="0"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>
                                                <i class="fa fa-money"></i> {{ __('Credit') }}
                                            </label>
                                            <input type="number"
                                                step="0.01"
                                                class="form-control"
                                                data-name="new_matchweeks[MATCHWEEK_NUM][credit]"
                                                value="1000"
                                                min="0"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <i class="fa fa-rocket"></i> {{ __('Bench Boost') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                data-name="new_matchweeks[MATCHWEEK_NUM][benchboost]"
                                                value="6"
                                                min="0"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <i class="fa fa-star"></i> {{ __('Wildcard') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                data-name="new_matchweeks[MATCHWEEK_NUM][wildcard]"
                                                value="2"
                                                min="0"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <i class="fa fa-trophy"></i> {{ __('Triple Captain') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                data-name="new_matchweeks[MATCHWEEK_NUM][triple]"
                                                value="4"
                                                min="0"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>
                                                <i class="fa fa-clock-o"></i> {{ __('Cutoff Time') }}
                                            </label>
                                            <input type="datetime-local"
                                                class="form-control"
                                                data-name="new_matchweeks[MATCHWEEK_NUM][cutoff_time]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Container for new matchweeks -->
                        <div id="newMatchweeksContainer"></div>

                        @if($timelines->count() > 0)
                        @foreach($timelines as $timeline)
                        <div class="card matchweek-card {{ ($timeline->status ?? 'ACTIVE') === 'DONE' ? 'status-done' : '' }}">

                            <div class="matchweek-header">
                                <div>
                                    <i class="fa fa-calendar"></i> Matchweek {{ $timeline->matchweek }}
                                </div>
                                <div class="matchweek-actions">
                                    <span class="status-badge status-{{ strtolower($timeline->status ?? 'active') }}">
                                        {{ $timeline->status ?? 'ACTIVE' }}
                                    </span>

                                    @if(($timeline->status ?? 'ACTIVE') !== 'DONE')
                                    <button type="button"
                                        class="btn btn-sm btn-secondary"
                                        onclick="markMatchweekAsDone({{ $competition->id }}, {{ $timeline->matchweek }})"
                                        title="Mark as Done">
                                        <i class="fa fa-check"></i> Done
                                    </button>

                                    <!-- ADD DELETE BUTTON -->
                                    <button type="button"
                                        class="btn btn-sm btn-danger"
                                        onclick="deleteMatchweek({{ $competition->id }}, {{ $timeline->matchweek }})"
                                        title="Delete Matchweek">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                    @endif

                                   
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="transfer_{{ $timeline->matchweek }}">
                                                <i class="fa fa-exchange"></i> {{ __('Transfer') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                id="transfer_{{ $timeline->matchweek }}"
                                                name="matchweeks[{{ $timeline->matchweek }}][transfer]"
                                                value="{{ old('matchweeks.'.$timeline->matchweek.'.transfer', $timeline->transfer) }}"
                                                min="0"
                                                required
                                                {{ ($timeline->status ?? 'ACTIVE') === 'DONE' ? 'disabled' : '' }}>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="max_same_club_{{ $timeline->matchweek }}">
                                                <i class="fa fa-users"></i> {{ __('Max Same Club') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                id="max_same_club_{{ $timeline->matchweek }}"
                                                name="matchweeks[{{ $timeline->matchweek }}][max_same_club]"
                                                value="{{ old('matchweeks.'.$timeline->matchweek.'.max_same_club', $timeline->max_same_club) }}"
                                                min="0"
                                                required
                                                {{ ($timeline->status ?? 'ACTIVE') === 'DONE' ? 'disabled' : '' }}>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="credit_{{ $timeline->matchweek }}">
                                                <i class="fa fa-money"></i> {{ __('Credit') }}
                                            </label>
                                            <input type="number"
                                                step="0.01"
                                                class="form-control"
                                                id="credit_{{ $timeline->matchweek }}"
                                                name="matchweeks[{{ $timeline->matchweek }}][credit]"
                                                value="{{ old('matchweeks.'.$timeline->matchweek.'.credit', $timeline->credit) }}"
                                                min="0"
                                                required
                                                {{ ($timeline->status ?? 'ACTIVE') === 'DONE' ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="benchboost_{{ $timeline->matchweek }}">
                                                <i class="fa fa-rocket"></i> {{ __('Bench Boost') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                id="benchboost_{{ $timeline->matchweek }}"
                                                name="matchweeks[{{ $timeline->matchweek }}][benchboost]"
                                                value="{{ old('matchweeks.'.$timeline->matchweek.'.benchboost', $timeline->benchboost) }}"
                                                min="0"
                                                required
                                                {{ ($timeline->status ?? 'ACTIVE') === 'DONE' ? 'disabled' : '' }}>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="wildcard_{{ $timeline->matchweek }}">
                                                <i class="fa fa-star"></i> {{ __('Wildcard') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                id="wildcard_{{ $timeline->matchweek }}"
                                                name="matchweeks[{{ $timeline->matchweek }}][wildcard]"
                                                value="{{ old('matchweeks.'.$timeline->matchweek.'.wildcard', $timeline->wildcard) }}"
                                                min="0"
                                                required
                                                {{ ($timeline->status ?? 'ACTIVE') === 'DONE' ? 'disabled' : '' }}>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="triple_{{ $timeline->matchweek }}">
                                                <i class="fa fa-trophy"></i> {{ __('Triple Captain') }}
                                            </label>
                                            <input type="number"
                                                class="form-control"
                                                id="triple_{{ $timeline->matchweek }}"
                                                name="matchweeks[{{ $timeline->matchweek }}][triple]"
                                                value="{{ old('matchweeks.'.$timeline->matchweek.'.triple', $timeline->triple) }}"
                                                min="0"
                                                required
                                                {{ ($timeline->status ?? 'ACTIVE') === 'DONE' ? 'disabled' : '' }}>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="cutoff_time_{{ $timeline->matchweek }}">
                                                <i class="fa fa-clock-o"></i> {{ __('Cutoff Time') }}
                                            </label>
                                            <input type="datetime-local"
                                                class="form-control"
                                                id="cutoff_time_{{ $timeline->matchweek }}"
                                                name="matchweeks[{{ $timeline->matchweek }}][cutoff_time]"
                                                value="{{ old('matchweeks.'.$timeline->matchweek.'.cutoff_time', $timeline->cutoff_time ? date('Y-m-d\TH:i', $timeline->cutoff_time) : '') }}"
                                                {{ ($timeline->status ?? 'ACTIVE') === 'DONE' ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> No matchweek data found. Please add matchweeks using the button above.
                        </div>
                        @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> {{ __('Save Changes') }}
                            </button>
                            <a href="{{ route('admin.fantasy.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fa fa-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                    <form id="updateMatchweekStatusForm" method="POST" style="display: none;">
                        @csrf
                        @method('PATCH')
                    </form>

                    <!-- Form untuk delete matchweek -->
                    <form id="deleteMatchweekForm" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form untuk update status matchweek -->
<form id="updateMatchweekStatusForm" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

@endsection
@section('scripts')
<script>
    let nextMatchweekNumber = {{ $timelines->max('matchweek') ?? 0 }} + 1;
    let newMatchweekCounter = 0;

    function addNewMatchweek() {
        const template = document.getElementById('newMatchweekTemplate');
        const container = document.getElementById('newMatchweeksContainer');
        
        // Clone the template
        const newMatchweek = template.cloneNode(true);
        newMatchweek.id = 'newMatchweek_' + newMatchweekCounter;
        newMatchweek.style.display = 'block';
        
        // Update matchweek number in the header
        newMatchweek.querySelector('.matchweek-number').textContent = nextMatchweekNumber;
        
        // Update all input - convert data-name to name
        const inputs = newMatchweek.querySelectorAll('input[data-name]');
        inputs.forEach(input => {
            const dataName = input.getAttribute('data-name');
            if (dataName) {
                const actualName = dataName.replace('MATCHWEEK_NUM', nextMatchweekNumber);
                input.setAttribute('name', actualName);
                input.removeAttribute('data-name');
            }
        });
        
        // Add to container
        container.appendChild(newMatchweek);
        
        // Increment counters
        nextMatchweekNumber++;
        newMatchweekCounter++;
        
        // Scroll to the new matchweek
        newMatchweek.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function removeNewMatchweek(button) {
        const card = button.closest('.add-matchweek-card');
        card.remove();
        nextMatchweekNumber--;
    }

    function markMatchweekAsDone(competitionId, matchweek) {
        if (confirm('Are you sure you want to mark Matchweek ' + matchweek + ' as DONE? This action cannot be undone.')) {
            const form = document.getElementById('updateMatchweekStatusForm');
            form.action = '{{ route("admin.fantasy.update-status", ["competition" => ":competition", "matchweek" => ":matchweek"]) }}'
                .replace(':competition', competitionId)
                .replace(':matchweek', matchweek);
            form.submit();
        }
    }

    // NEW DELETE FUNCTION
    function deleteMatchweek(competitionId, matchweek) {
        if (confirm('⚠️ Are you sure you want to DELETE Matchweek ' + matchweek + '?\n\nThis will permanently remove:\n- Matchweek settings\n- All user teams for this matchweek\n- All points and rankings\n\nThis action CANNOT be undone!')) {
            // Double confirmation for safety
            if (confirm('Final confirmation: Delete Matchweek ' + matchweek + '?')) {
                const form = document.getElementById('deleteMatchweekForm');
                form.action = '{{ route("admin.fantasy.delete-matchweek", ["competition" => ":competition", "matchweek" => ":matchweek"]) }}'
                    .replace(':competition', competitionId)
                    .replace(':matchweek', matchweek);
                form.submit();
            }
        }
    }

    $(document).ready(function() {
        console.log('Fantasy edit page loaded');
    });
</script>
@endsection