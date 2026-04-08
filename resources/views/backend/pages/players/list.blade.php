@extends('backend.layouts.master')

@section('title')
All Players - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<style>
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
                                        <th class="text-nowrap">{{ __('Salary') }}</th>
                                        <th class="text-nowrap">{{ __('Market value') }}</th>
                                        <th class="text-nowrap">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($players as $player)
                                        @php
                                            $salary = $player->contracts->first() ? (float) $player->contracts->first()->salary : null;
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
                                            <td class="text-nowrap">{{ $salary !== null ? ('RM '.number_format($salary, 2)) : '—' }}</td>
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