@extends('backend.layouts.master')

@section('title')
Invite Player - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .form-check-label {
        text-transform: capitalize;
    }
    .required-field::after {
        content: " *";
        color: red;
        font-weight: bold;
    }
    .player-info-card {
        background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        display: none;
    }
    .player-info-card.show {
        display: block;
        animation: slideDown 0.3s ease-out;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .info-item {
        margin-bottom: 10px;
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        margin-right: 10px;
    }
    .info-value {
        color: #212529;
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
    .search-section {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
    }
    .invite-form-section {
        display: none;
    }
    .invite-form-section.show {
        display: block;
    }
</style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Invite Player</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.players.index') }}">All Players</a></li>
                    <li><span>Invite Player</span></li>
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
                    <h4 class="header-title">Invite Player to Club</h4>
                    @include('backend.layouts.partials.messages')

                    <!-- Search Section -->
                    <div class="search-section">
                        <h5 class="mb-3">Step 1: Search Player</h5>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="search_type" class="required-field">Search By</label>
                                <select class="form-control" id="search_type" required>
                                    <option value="identity_number">IC Number</option>
                                    <option value="name">Name</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="search_value" class="required-field">
                                    <span id="searchLabel">Identity Number (IC)</span>
                                </label>
                                <input type="text" class="form-control" id="search_value" placeholder="Enter IC Number or Name" required>
                            </div>
                            <div class="form-group col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary btn-block" id="searchBtn">
                                    <i class="fa fa-search"></i> Search Player
                                </button>
                            </div>
                        </div>
                        
                        <!-- Multiple Players Result -->
                        <div class="alert alert-info mt-3" id="multiplePlayersAlert" style="display: none;">
                            <h6 class="mb-3">Multiple players found. Please select one:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="multiplePlayersTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>IC Number</th>
                                            <th>Position</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="multiplePlayersBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Player Info Card -->
                    <div class="player-info-card" id="playerInfoCard">
                        <h5 class="mb-3">Player Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value" id="playerName">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value" id="playerEmail">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Phone:</span>
                                    <span class="info-value" id="playerPhone">-</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">IC Number:</span>
                                    <span class="info-value" id="playerIC">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Position:</span>
                                    <span class="info-value" id="playerPosition">-</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Status:</span>
                                    <span class="status-badge" id="playerStatus">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invite Form Section -->
                    <div class="invite-form-section" id="inviteFormSection">
                        <hr class="my-4">
                        <h5 class="mb-3">Step 2: Invite Player</h5>
                        
                        <form action="{{ route('admin.players.invite.send') }}" method="POST" id="inviteForm">
                            @csrf
                            <input type="hidden" name="player_id" id="playerId">

                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label for="clubs" class="required-field">Assign Clubs</label>
                                    <select name="club_ids[]" id="clubs" class="form-control select2" multiple required>
                                        @foreach ($clubs as $club)
                                        <option value="{{ $club->id }}" 
                                            {{ auth()->user()->clubs->contains('id', $club->id) ? 'selected' : '' }}>
                                            {{ $club->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Your clubs are pre-selected</small>
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label for="salary">Salary</label>
                                    <input type="number" min="0" max="9999999999" class="form-control" id="salary" name="salary" value="0">
                                    <small class="form-text text-muted">Optional - Default: 0</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-6">
                                    <label for="start_date" class="required-field">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start" value="{{ date('Y-m-d') }}" required>
                                    <small class="form-text text-muted">Default: Today</small>
                                </div>
                                <div class="form-group col-md-6 col-sm-6">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end">
                                    <small class="form-text text-muted">Default: 1 year from start date</small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success mt-3 pr-4 pl-4">
                                <i class="fa fa-paper-plane"></i> Send Invitation
                            </button>
                            <button type="button" class="btn btn-secondary mt-3 pr-4 pl-4" id="resetBtn">
                                <i class="fa fa-redo"></i> Reset
                            </button>
                        </form>
                    </div>

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
        // Initialize select2
        $('.select2').select2();

        // Store default selected clubs (admin's clubs)
        const defaultClubs = [];
        @foreach(auth()->user()->clubs as $club)
            defaultClubs.push('{{ $club->id }}');
        @endforeach

        // Change label based on search type
        $('#search_type').on('change', function() {
            const searchType = $(this).val();
            if (searchType === 'name') {
                $('#searchLabel').text('Player Name');
                $('#search_value').attr('placeholder', 'Enter Player Name');
            } else {
                $('#searchLabel').text('Identity Number (IC)');
                $('#search_value').attr('placeholder', 'Enter IC Number');
            }
            $('#search_value').val('');
            resetMultipleResults();
        });

        // Search Player
        $('#searchBtn').on('click', function(e) {
            e.preventDefault();
            searchPlayer();
        });

        // Search on Enter key
        $('#search_value').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                searchPlayer();
            }
        });

        function searchPlayer() {
            const searchValueInput = $('#search_value');
            const searchTypeInput = $('#search_type');
            
            const searchValue = searchValueInput.val();
            const searchType = searchTypeInput.val();
            
            console.log('Search Value:', searchValue);
            console.log('Search Type:', searchType);

            if (!searchValue || searchValue.trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required',
                    text: 'Please enter search value',
                });
                return;
            }

            // Hide previous results
            resetMultipleResults();
            resetForm(false); // Don't clear input

            // Show loading
            Swal.fire({
                title: 'Searching...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("admin.players.invite.search") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    search_value: searchValue.trim(),
                    search_type: searchType
                },
                success: function(response) {
                    Swal.close();

                    if (response.success) {
                        // Show player info
                        displayPlayerInfo(response.player);
                        
                        // Show invite form
                        $('#inviteFormSection').addClass('show');
                        $('#playerId').val(response.player.id);
                        
                        // Reset clubs to default (admin's clubs)
                        $('#clubs').val(defaultClubs).trigger('change');
                    } else if (response.multiple) {
                        // Multiple players found
                        displayMultiplePlayers(response.players);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Not Found',
                            text: response.message,
                        });
                        resetForm(true);
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    let errorMessage = 'An error occurred. Please try again.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                    });
                }
            });
        }

        // Display multiple players
        function displayMultiplePlayers(players) {
            const tbody = $('#multiplePlayersBody');
            tbody.empty();

            players.forEach(function(player) {
                const statusClass = player.status === 'ACTIVE' ? 'status-active' : 
                                  player.status === 'INACTIVE' ? 'status-inactive' : 'status-invited';
                
                const row = `
                    <tr>
                        <td>${player.name}</td>
                        <td>${player.identity_number}</td>
                        <td>${player.position}</td>
                        <td><span class="status-badge ${statusClass}">${player.status}</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary select-player-btn" 
                                    data-ic="${player.identity_number}">
                                <i class="fa fa-check"></i> Select
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            $('#multiplePlayersAlert').show();

            // Handle select button click
            $('.select-player-btn').off('click').on('click', function(e) {
                e.preventDefault();
                const icNumber = $(this).data('ic');
                $('#search_type').val('identity_number');
                $('#search_value').val(icNumber);
                $('#searchLabel').text('Identity Number (IC)');
                $('#search_value').attr('placeholder', 'Enter IC Number');
                resetMultipleResults();
                searchPlayer();
            });
        }

        function resetMultipleResults() {
            $('#multiplePlayersAlert').hide();
            $('#multiplePlayersBody').empty();
        }

        // Display player info
        function displayPlayerInfo(player) {
            $('#playerName').text(player.name);
            $('#playerEmail').text(player.email || 'N/A');
            $('#playerPhone').text(player.country_code && player.phone ? '+' + player.country_code + player.phone : (player.phone || 'N/A'));
            $('#playerIC').text(player.identity_number);
            $('#playerPosition').text(player.position);
            
            // Status badge
            const statusBadge = $('#playerStatus');
            statusBadge.text(player.status);
            statusBadge.removeClass('status-active status-inactive status-invited');
            
            if (player.status === 'ACTIVE') {
                statusBadge.addClass('status-active');
            } else if (player.status === 'INACTIVE') {
                statusBadge.addClass('status-inactive');
            } else if (player.status === 'INVITED') {
                statusBadge.addClass('status-invited');
            }

            $('#playerInfoCard').addClass('show');
        }

        // Reset form - with option to keep search value
        $('#resetBtn').on('click', function(e) {
            e.preventDefault();
            resetForm(true);
        });

        function resetForm(clearInput = true) {
            if (clearInput) {
                $('#search_value').val('');
                $('#search_type').val('identity_number');
                $('#searchLabel').text('Identity Number (IC)');
                $('#search_value').attr('placeholder', 'Enter IC Number');
            }
            $('#playerInfoCard').removeClass('show');
            $('#inviteFormSection').removeClass('show');
            $('#inviteForm')[0].reset();
            $('#playerId').val('');
            
            // Reset to default clubs (admin's clubs)
            $('#clubs').val(defaultClubs).trigger('change');
            
            resetMultipleResults();
        }
    });
</script>
@endsection