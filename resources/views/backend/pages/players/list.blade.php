@extends('backend.layouts.master')

@section('title')
All Players - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .jersey-value-display {
        display: inline-block;
        min-width: 2rem;
        font-weight: 500;
    }
    .market-value-display {
        font-weight: 600;
        color: #28a745;
    }
    .edit-market-value {
        padding: 0 5px;
        color: #007bff;
    }
    .edit-market-value:hover {
        color: #0056b3;
    }
    .market-value-input-group {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .market-value-input {
        width: 120px;
        display: inline-block;
        padding: 4px 8px;
        font-size: 0.9rem;
    }
    .filter-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .filter-row {
        display: flex;
        gap: 15px;
        align-items: end;
        flex-wrap: wrap;
    }
    .filter-item {
        flex: 1;
        min-width: 200px;
    }
    .filter-item label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #495057;
    }
    .player-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .player-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .player-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    .player-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }
    .player-username {
        color: #6c757d;
        font-size: 0.9rem;
    }
    .player-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin-bottom: 10px;
    }
    .info-item {
        display: flex;
        align-items: center;
    }
    .info-label {
        font-weight: 600;
        color: #6c757d;
        margin-right: 5px;
        font-size: 0.85rem;
    }
    .info-value {
        color: #212529;
        font-size: 0.9rem;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .status-active {
        background: #28a745;
        color: white;
    }
    .status-inactive {
        background: #6c757d;
        color: white;
    }
    .status-invited {
        background: #ffc107;
        color: #212529;
    }
    .position-badge {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .position-goalkeeper {
        background: #ffd700;
        color: #212529;
    }
    .position-defender {
        background: #28a745;
        color: white;
    }
    .position-midfielder {
        background: #007bff;
        color: white;
    }
    .position-forward {
        background: #dc3545;
        color: white;
    }
    .clubs-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 10px;
    }
    .club-badge {
        background: #e7f3ff;
        color: #007bff;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        border: 1px solid #b3d9ff;
    }
    .stats-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }
    .stat-item {
        text-align: center;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .no-players {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    .no-players i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    @media (max-width: 768px) {
        .player-info {
            grid-template-columns: 1fr;
        }
        .filter-row {
            flex-direction: column;
        }
        .filter-item {
            width: 100%;
        }
    }
</style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">All Players</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>All Players</span></li>
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
            
            @include('backend.layouts.partials.messages')

            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $players->total() }}</div>
                        <div class="stat-label">Total Players</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ \App\Models\Player::where('status', 'ACTIVE')->count() }}</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ \App\Models\Player::where('status', 'INVITED')->count() }}</div>
                        <div class="stat-label">Invited</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $clubs->count() }}</div>
                        <div class="stat-label">Clubs</div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="filter-card">
                <form action="{{ route('admin.players.list') }}" method="GET" id="filterForm">
                    <div class="filter-row">
                        <div class="filter-item">
                            <label for="club_id">Filter by Club</label>
                            <select name="club_id" id="club_id" class="form-control select2">
                                <option value="">All Clubs</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                        {{ $club->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="filter-item">
                            <label for="position">Position</label>
                            <select name="position" id="position" class="form-control">
                                <option value="">All Positions</option>
                                <option value="Goalkeeper" {{ request('position') == 'Goalkeeper' ? 'selected' : '' }}>Goalkeeper</option>
                                <option value="Defender" {{ request('position') == 'Defender' ? 'selected' : '' }}>Defender</option>
                                <option value="Midfielder" {{ request('position') == 'Midfielder' ? 'selected' : '' }}>Midfielder</option>
                                <option value="Forward" {{ request('position') == 'Forward' ? 'selected' : '' }}>Forward</option>
                            </select>
                        </div>
                        
                        <div class="filter-item">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                                <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                                <option value="INVITED" {{ request('status') == 'INVITED' ? 'selected' : '' }}>Invited</option>
                            </select>
                        </div>
                        
                        <div class="filter-item">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Name, IC, Phone..." value="{{ request('search') }}">
                        </div>
                        
                        <div class="filter-item" style="min-width: auto;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.players.list') }}" class="btn btn-secondary">
                                <i class="fa fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Players List -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-0">
                            Players List 
                            @if($selectedClubId)
                                @php $filterClub = $clubs->firstWhere('id', (int) $selectedClubId); @endphp
                                @if($filterClub)
                                    - {{ $filterClub->name }}
                                @endif
                            @endif
                        </h4>
                        <div>
                            @if (! auth()->user()->hasRole('Club Manager') && (auth()->user()->can('players.create') || auth()->user()->can('players.edit') || auth()->user()->hasRole('Association Manager')))
                            <a href="{{ route('admin.players.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Player
                            </a>
                            @endif
                            <a href="{{ route('admin.players.bulk.form') }}" class="btn btn-success btn-sm">
                                <i class="fa fa-upload"></i> Bulk Upload
                            </a>
                        </div>
                    </div>

                    @if($players->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th class="text-nowrap">{{ __('IC / ID') }}</th>
                                        <th class="text-nowrap">{{ __('Phone') }}</th>
                                        <th>{{ __('Clubs') }}</th>
                                        <th class="text-nowrap">{{ __('Status') }}</th>
                                        <th class="text-nowrap">{{ __('Position') }}</th>
                                        <th class="text-nowrap">{{ __('Jersey') }}</th>
                                        <th class="text-nowrap">{{ __('Market value') }}</th>
                                        <th class="text-nowrap">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($players as $player)
                                        @php
                                            $phoneDisplay = ($player->country_code && $player->phone)
                                                ? ('+'.preg_replace('/\\D/', '', (string) $player->country_code).preg_replace('/\\D/', '', (string) $player->phone))
                                                : ($player->phone ? preg_replace('/\\D/', '', (string) $player->phone) : '—');
                                        @endphp
                                        <tr>
                                            <td class="text-left">
                                                <strong>{{ $player->name }}</strong>
                                                <span class="text-muted">({{ $player->username }})</span>
                                            </td>
                                            <td class="text-monospace">{{ $player->identity_number }}</td>
                                            <td class="text-nowrap">{{ $phoneDisplay }}</td>
                                            <td class="text-left">
                                                @if($player->clubs->count() > 0)
                                                    @foreach($player->clubs as $club)
                                                        <span class="badge badge-pill badge-light border">{{ $club->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <span class="badge badge-success">{{ $player->status }}</span>
                                            </td>
                                            <td class="text-nowrap">{{ $player->position }}</td>
                                            <td class="text-nowrap">
                                                @if (auth()->user()->can('players.edit') || auth()->user()->hasRole('Association Manager'))
                                                    @php $jerseyVal = $player->jersey_number; @endphp
                                                    <span class="jersey-value-display" id="jersey-display-{{ $player->id }}">{{ $jerseyVal !== null && $jerseyVal !== '' ? $jerseyVal : '—' }}</span>
                                                    <button type="button"
                                                            class="btn btn-sm btn-link edit-jersey p-0 ml-1 align-baseline"
                                                            title="{{ __('Edit jersey number') }}"
                                                            data-player-id="{{ $player->id }}"
                                                            data-url="{{ route('admin.players.inline-jersey', ['id' => $player->id], false) }}"
                                                            data-current-value="{{ $jerseyVal ?? '' }}">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                @else
                                                    {{ $player->jersey_number ?? '—' }}
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <span class="market-value-display" id="market-value-display-{{ $player->id }}">
                                                    RM {{ number_format($player->market_value ?? 0, 2) }}
                                                </span>
                                                <button type="button" class="btn btn-sm btn-link edit-market-value p-0 ml-1" data-player-id="{{ $player->id }}" data-current-value="{{ $player->market_value ?? 0 }}">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                            </td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('admin.players.edit', $player->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-edit"></i> {{ __('Edit') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $players->links() }}
                        </div>
                    @else
                        <div class="no-players">
                            <i class="fa fa-users"></i>
                            <h5>No players found</h5>
                            <p>Try adjusting your filters or add new players.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select a club',
            allowClear: true
        });

        // Auto-submit on select change
        $('#club_id, #position, #status').on('change', function() {
            $('#filterForm').submit();
        });

        // Submit on Enter in search
        $('#search').on('keypress', function(e) {
            if (e.which === 13) {
                $('#filterForm').submit();
            }
        });

        function fetchUrlHttpsPlayersList(url) {
            if (!url || typeof url !== 'string') {
                return url;
            }
            if (window.location.protocol !== 'https:') {
                return url;
            }
            try {
                var u = new URL(url, window.location.href);
                if (u.protocol === 'http:' && u.host === window.location.host) {
                    u.protocol = 'https:';
                    return u.href;
                }
            } catch (e) { /* ignore */ }
            return url;
        }

        function postJerseyInline(url, jerseyPayload) {
            var fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('jersey_number', jerseyPayload);
            return fetch(url, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(function (r) {
                return r.text().then(function (text) {
                    var data = {};
                    try {
                        data = text ? JSON.parse(text) : {};
                    } catch (e) {
                        data = { message: text || 'Invalid response' };
                    }
                    return { ok: r.ok, status: r.status, data: data };
                });
            });
        }

        $(document).on('click', '.edit-jersey', function () {
            var $btn = $(this);
            var playerId = String($btn.data('player-id') || '');
            var url = fetchUrlHttpsPlayersList($btn.attr('data-url') || '');
            var current = $btn.attr('data-current-value');
            if (current === undefined || current === null) {
                current = '';
            }

            Swal.fire({
                title: '{{ __("Edit jersey number") }}',
                html:
                    '<div class="form-group text-left mb-0">' +
                    '<label for="swal-jersey-input" class="d-block">{{ __("Jersey number") }}</label>' +
                    '<input type="number" id="swal-jersey-input" class="form-control" ' +
                    'min="1" max="99999" step="1" placeholder="{{ __("Leave empty to clear") }}">' +
                    '<small class="form-text text-muted">{{ __("Optional. 1–99999, or clear to remove.") }}</small>' +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: '{{ __("Save") }}',
                cancelButtonText: '{{ __("Cancel") }}',
                focusConfirm: false,
                didOpen: function () {
                    var el = document.getElementById('swal-jersey-input');
                    if (el) {
                        el.value = current === '' ? '' : String(current);
                        el.focus();
                        el.select();
                    }
                },
                preConfirm: function () {
                    var el = document.getElementById('swal-jersey-input');
                    var raw = el ? String(el.value).trim() : '';
                    if (raw === '') {
                        return '';
                    }
                    var n = parseInt(raw.replace(/\D/g, ''), 10);
                    if (isNaN(n) || n < 1 || n > 99999) {
                        Swal.showValidationMessage('{{ __("Enter a number from 1 to 99999, or leave empty to clear.") }}');
                        return false;
                    }
                    return String(n);
                }
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                var payload = result.value;
                Swal.fire({
                    title: '{{ __("Saving…") }}',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });
                postJerseyInline(url, payload).then(function (res) {
                    if (!res.ok) {
                        var msg = (res.data && res.data.message) ? res.data.message : '{{ __("Could not save jersey number.") }}';
                        if (res.data && res.data.errors) {
                            msg = Object.values(res.data.errors).flat().join(' ');
                        }
                        Swal.fire({ icon: 'error', title: '{{ __("Error") }}', text: msg });
                        return;
                    }
                    var jn = res.data.jersey_number;
                    var display = (jn === null || jn === undefined || jn === '') ? '—' : String(jn);
                    $('#jersey-display-' + playerId).text(display);
                    $btn.attr('data-current-value', (jn === null || jn === undefined || jn === '') ? '' : String(jn));
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __("Saved") }}',
                        text: (res.data && res.data.message) ? res.data.message : '{{ __("Jersey number updated.") }}',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }).catch(function () {
                    Swal.fire({ icon: 'error', title: '{{ __("Error") }}', text: '{{ __("Network error") }}' });
                });
            });
        });

        // Edit Market Value
        $('.edit-market-value').on('click', function() {
            const playerId = $(this).data('player-id');
            const currentValue = $(this).data('current-value');
            const displayElement = $('#market-value-display-' + playerId);
            
            Swal.fire({
                title: 'Update Market Value',
                html: `
                    <div class="form-group text-left">
                        <label for="market-value-input">Market Value (RM)</label>
                        <input type="number" id="market-value-input" class="form-control" 
                               value="${currentValue}" min="0" step="0.01" 
                               placeholder="Enter market value">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                preConfirm: () => {
                    const value = document.getElementById('market-value-input').value;
                    
                    if (!value || value < 0) {
                        Swal.showValidationMessage('Please enter a valid market value');
                        return false;
                    }
                    
                    return value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    updateMarketValue(playerId, result.value, displayElement);
                }
            });
        });

        function updateMarketValue(playerId, marketValue, displayElement) {
            // Show loading
            Swal.fire({
                title: 'Updating...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/admin/players/${playerId}/update-market-value`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    market_value: marketValue
                },
                success: function(response) {
                    if (response.success) {
                        // Update display
                        displayElement.text('RM ' + response.market_value);
                        
                        // Update data attribute
                        displayElement.siblings('.edit-market-value').data('current-value', marketValue);
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update market value',
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred. Please try again.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                    });
                }
            });
        }
    });
</script>
@endsection