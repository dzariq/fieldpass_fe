@extends('backend.layouts.master')

@section('title')
Invite players - Admin Panel
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
    .invite-multiple-player-photo {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
        border: 1px solid #dee2e6;
        background: #fff;
    }
</style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Invite players</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.players.index') }}">All Players</a></li>
                    <li><span>Invite players</span></li>
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
                    <h4 class="header-title">Invite players to club</h4>
                    @include('backend.layouts.partials.messages')

                    <!-- Search Section -->
                    <div class="search-section">
                        <h5 class="mb-3">Step 1: Search Player</h5>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="search_type" class="required-field">Search By</label>
                                <select class="form-control" id="search_type" required>
                                    <option value="name" selected>Name</option>
                                    <option value="identity_number">Identity number</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3" id="identity_search_type_wrap">
                                <label for="identity_search_type" class="required-field">ID type</label>
                                <select class="form-control" id="identity_search_type">
                                    <option value="malaysia_ic">Malaysia IC</option>
                                    <option value="foreign_id">Foreign ID</option>
                                </select>
                                <small class="form-text text-muted">Required when searching by identity number</small>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="search_value" class="required-field">
                                    <span id="searchLabel">Identity number</span>
                                </label>
                                <input type="text" class="form-control" id="search_value" placeholder="Enter IC or foreign ID" required>
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-primary btn-block" id="searchBtn">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        
                        <!-- Multiple Players Result -->
                        <div class="alert alert-info mt-3" id="multiplePlayersAlert" style="display: none;">
                            <h6 class="mb-3">Multiple players found</h6>
                            <p class="small text-muted mb-2">Club shows long name when set. Use <strong>Invite</strong> only for players with no club; others are listed for reference.</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="multiplePlayersTable">
                                    <thead>
                                        <tr>
                                            <th>Player</th>
                                            <th>Club</th>
                                            <th>Identity</th>
                                            <th>Position</th>
                                            <th>Status</th>
                                            @if (auth()->user()->can('players.view') || auth()->user()->can('players.edit'))
                                                <th>{{ __('History & performance') }}</th>
                                            @endif
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
                                <div class="info-item">
                                    <span class="info-label">Club:</span>
                                    <span class="info-value" id="playerClubs">-</span>
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
                    <div class="invite-form-section @if(old('player_id')) show @endif" id="inviteFormSection">
                        <hr class="my-4">
                        <h5 class="mb-3">Step 2: Invite Player</h5>
                        
                        <form action="{{ route('admin.players.invite.send') }}" method="POST" id="inviteForm">
                            @csrf
                            <input type="hidden" name="player_id" id="playerId" value="{{ old('player_id') }}">

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

                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-6">
                                    <label for="invite_country_code">{{ __('Country code') }}</label>
                                    @php $inviteCc = old('country_code'); @endphp
                                    <select name="country_code" id="invite_country_code" class="form-control">
                                        <option value="">{{ __('Optional') }}</option>
                                        <option value="60" {{ $inviteCc == '60' ? 'selected' : '' }}>+60 (Malaysia)</option>
                                        <option value="65" {{ $inviteCc == '65' ? 'selected' : '' }}>+65 (Singapore)</option>
                                        <option value="62" {{ $inviteCc == '62' ? 'selected' : '' }}>+62 (Indonesia)</option>
                                        <option value="66" {{ $inviteCc == '66' ? 'selected' : '' }}>+66 (Thailand)</option>
                                        <option value="63" {{ $inviteCc == '63' ? 'selected' : '' }}>+63 (Philippines)</option>
                                        <option value="84" {{ $inviteCc == '84' ? 'selected' : '' }}>+84 (Vietnam)</option>
                                        <option value="95" {{ $inviteCc == '95' ? 'selected' : '' }}>+95 (Myanmar)</option>
                                        <option value="856" {{ $inviteCc == '856' ? 'selected' : '' }}>+856 (Laos)</option>
                                        <option value="855" {{ $inviteCc == '855' ? 'selected' : '' }}>+855 (Cambodia)</option>
                                        <option value="673" {{ $inviteCc == '673' ? 'selected' : '' }}>+673 (Brunei)</option>
                                        <option value="670" {{ $inviteCc == '670' ? 'selected' : '' }}>+670 (East Timor)</option>
                                    </select>
                                    <small class="form-text text-muted">{{ __('Optional. Required if you enter a phone number.') }}</small>
                                </div>
                                <div class="form-group col-md-6 col-sm-6">
                                    <label for="invite_phone">{{ __('Phone number') }}</label>
                                    <input type="text" class="form-control" id="invite_phone" name="phone" value="{{ old('phone') }}" placeholder="{{ __('Optional — 7–15 digits, no country code') }}" inputmode="numeric" maxlength="15" autocomplete="tel-national">
                                    <small class="form-text text-muted">{{ __('Optional. Digits only, without country code.') }}</small>
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
        @include('backend.pages.players.partials.club-history-modal')
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

        const quickInviteAssignUrl = @json(route('admin.players.invite.quick-assign', [], false));
        const csrfTokenInvite = '{{ csrf_token() }}';
        const inviteDefaultPlayerPhoto = @json(asset('backend/assets/images/default-avatar.png'));
        @php
            $inviteClubHistoryPh = 848474748474;
        @endphp
        const inviteClubHistoryUrlTpl = @json(route('admin.players.club-history-performance', ['player' => $inviteClubHistoryPh], false));
        const inviteClubHistoryPh = @json($inviteClubHistoryPh);
        const inviteCanViewClubHistory = @json(auth()->user()->can('players.view') || auth()->user()->can('players.edit'));
        const showAjaxErrorDetails = @json((bool) (config('app.debug') || config('app.show_ajax_error_details')));

        function escapeHtmlInvite(s) {
            return $('<div>').text(String(s)).html();
        }

        function formatInviteSearchAjaxError(xhr, fallbackMessage) {
            let main = '';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.errors) {
                    const flat = Object.values(xhr.responseJSON.errors).flat();
                    if (flat.length) {
                        main = flat.join('\n');
                    }
                }
                if (!main && xhr.responseJSON.message) {
                    main = String(xhr.responseJSON.message);
                }
            }
            if (!main) {
                main = fallbackMessage;
            }

            const parts = [main];
            if (showAjaxErrorDetails) {
                parts.unshift('HTTP ' + xhr.status + (xhr.statusText ? ' ' + xhr.statusText : ''));
                if (xhr.responseJSON && xhr.responseJSON.exception) {
                    parts.push(String(xhr.responseJSON.exception));
                }
                if (xhr.responseJSON) {
                    try {
                        parts.push(JSON.stringify(xhr.responseJSON, null, 2));
                    } catch (e) { /* ignore */ }
                } else if (xhr.responseText) {
                    const stripped = String(xhr.responseText).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                    if (stripped.length) {
                        parts.push(stripped.slice(0, 2000) + (stripped.length > 2000 ? '…' : ''));
                    }
                }
            } else if (!xhr.responseJSON) {
                parts.push('HTTP ' + xhr.status + (xhr.statusText ? ' ' + xhr.statusText : ''));
            }

            return parts.filter(Boolean).join('\n\n');
        }

        function inviteClubHistoryPerformanceUrl(playerId) {
            return inviteClubHistoryUrlTpl.split(String(inviteClubHistoryPh)).join(String(playerId));
        }

        function syncSearchByUi() {
            const searchType = $('#search_type').val();
            if (searchType === 'name') {
                $('#identity_search_type_wrap').addClass('d-none');
                $('#searchLabel').text('Player name');
                $('#search_value').attr('placeholder', 'Start of name (matches beginning only)');
            } else {
                $('#identity_search_type_wrap').removeClass('d-none');
                const idt = $('#identity_search_type').val();
                if (idt === 'foreign_id') {
                    $('#searchLabel').text('Foreign ID');
                    $('#search_value').attr('placeholder', 'Passport or national ID');
                } else {
                    $('#searchLabel').text('Malaysia IC');
                    $('#search_value').attr('placeholder', 'XXXXXX-XX-XXXX or 12 digits');
                }
            }
        }

        $('#search_type').on('change', function() {
            $('#search_value').val('');
            resetMultipleResults();
            syncSearchByUi();
        });

        $('#identity_search_type').on('change', function() {
            $('#search_value').val('');
            resetMultipleResults();
            syncSearchByUi();
        });

        syncSearchByUi();

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

            const postData = {
                _token: '{{ csrf_token() }}',
                search_value: searchValue.trim(),
                search_type: searchType
            };
            if (searchType === 'identity_number') {
                postData.identity_search_type = $('#identity_search_type').val();
            }

            $.ajax({
                url: @json(route('admin.players.invite.search', [], false)),
                method: 'POST',
                data: postData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
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
                    const fallback = 'An error occurred. Please try again.';
                    const detailText = formatInviteSearchAjaxError(xhr, fallback);
                    const swalOpts = {
                        icon: 'error',
                        title: 'Error',
                        width: showAjaxErrorDetails ? '36rem' : undefined,
                    };
                    if (showAjaxErrorDetails) {
                        swalOpts.html = '<pre style="text-align:left;font-size:12px;max-height:55vh;overflow:auto;white-space:pre-wrap;margin:0">' +
                            escapeHtmlInvite(detailText) + '</pre>';
                    } else {
                        swalOpts.text = detailText;
                    }
                    Swal.fire(swalOpts);
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

                const $tr = $('<tr>');
                const photoSrc = player.avatar_url || inviteDefaultPlayerPhoto;
                const $playerCell = $('<td>');
                const $playerRow = $('<div class="d-flex align-items-center">');
                const $img = $('<img>', {
                    src: photoSrc,
                    alt: '',
                    class: 'invite-multiple-player-photo mr-2'
                });
                $img.on('error', function() {
                    $(this).off('error').attr('src', inviteDefaultPlayerPhoto);
                });
                $playerRow.append($img);
                $playerRow.append($('<span>').text(player.name));
                $playerCell.append($playerRow);
                $tr.append($playerCell);
                $tr.append($('<td>').text(player.clubs_display || '—'));
                $tr.append($('<td>').text(player.identity_number));
                $tr.append($('<td>').text(player.position || ''));
                $tr.append($('<td>').append(
                    $('<span>').addClass('status-badge ' + statusClass).text(player.status)
                ));
                if (inviteCanViewClubHistory) {
                    const $histTd = $('<td>');
                    const $histBtn = $('<button type="button" class="btn btn-info text-white btn-sm js-club-history-performance">')
                        .text(@json(__('Club & stats')))
                        .attr('data-url', inviteClubHistoryPerformanceUrl(player.id));
                    $histTd.append($histBtn);
                    $tr.append($histTd);
                }
                const $action = $('<td>');
                if (!player.has_club) {
                    const $btn = $('<button type="button" class="btn btn-sm btn-success js-quick-invite-btn">')
                        .html('<i class="fa fa-user-plus"></i> Invite')
                        .data('player-id', player.id);
                    $action.append($btn);
                } else {
                    $action.append($('<span class="text-muted">—</span>'));
                }
                $tr.append($action);
                tbody.append($tr);
            });

            $('#multiplePlayersAlert').show();

            $('.js-quick-invite-btn').off('click').on('click', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const playerId = $btn.data('player-id');
                if (!playerId) return;
                $btn.prop('disabled', true);
                fetch(quickInviteAssignUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfTokenInvite,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ _token: csrfTokenInvite, player_id: playerId }),
                    credentials: 'same-origin'
                }).then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
                .then(function(res) {
                    if (!res.ok) {
                        Swal.fire({ icon: 'error', title: 'Could not invite', text: (res.data && res.data.message) ? res.data.message : 'Request failed.' });
                        $btn.prop('disabled', false);
                        return;
                    }
                    Swal.fire({ icon: 'success', title: 'Done', text: res.data.message || 'Player added.' });
                    $btn.closest('tr').remove();
                    if ($('#multiplePlayersBody tr').length === 0) {
                        resetMultipleResults();
                    }
                }).catch(function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error.' });
                    $btn.prop('disabled', false);
                });
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
            $('#playerClubs').text(player.clubs_display || '—');
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

            if (player.country_code) {
                $('#invite_country_code').val(String(player.country_code));
            } else {
                $('#invite_country_code').val('');
            }
            $('#invite_phone').val(player.phone ? String(player.phone) : '');
        }

        // Reset form - with option to keep search value
        $('#resetBtn').on('click', function(e) {
            e.preventDefault();
            resetForm(true);
        });

        function resetForm(clearInput = true) {
            if (clearInput) {
                $('#search_value').val('');
                $('#search_type').val('name');
                $('#identity_search_type').val('malaysia_ic');
                syncSearchByUi();
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
@include('backend.pages.players.partials.club-history-modal-script')
@endsection