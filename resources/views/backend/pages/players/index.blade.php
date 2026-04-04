@extends('backend.layouts.master')

@section('title')
    {{ __('Players - Admin Panel') }}
@endsection

@section('styles')
    <!-- Start datatable css -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
    <style>
        .position-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 12px;
        }
        .position-goalkeeper {
            background-color: #ffc107;
            color: #000;
        }
        .position-defender {
            background-color: #28a745;
            color: #fff;
        }
        .position-midfielder {
            background-color: #17a2b8;
            color: #fff;
        }
        .position-forward {
            background-color: #dc3545;
            color: #fff;
        }
        #dataTable.table-players-compact td,
        #dataTable.table-players-compact th {
            padding: 0.3rem 0.35rem;
            font-size: 0.78rem;
            vertical-align: middle;
        }
        #dataTable.table-players-compact .badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.35rem;
            font-weight: 600;
        }
        .player-inline-row .form-control-sm {
            min-width: 0;
            font-size: 0.75rem;
            padding: 0.15rem 0.35rem;
            height: auto;
            line-height: 1.2;
        }
        .player-inline-row .player-inline-phone {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.25rem;
            align-items: center;
            justify-content: flex-start;
            max-width: 100%;
        }
        .player-inline-row .player-inline-phone select {
            max-width: 5.5rem;
            flex: 0 0 auto;
        }
        .player-inline-row .player-inline-phone .player-inline-phone-input-wrap {
            display: flex;
            flex: 1 1 auto;
            align-items: center;
            gap: 0.2rem;
            min-width: 0;
        }
        .player-inline-row .player-inline-phone input[type="text"] {
            flex: 1 1 auto;
            min-width: 3rem;
            max-width: 7rem;
        }
        .player-inline-row .js-send-invitation {
            flex: 0 0 auto;
            line-height: 1;
        }
        .player-inline-avatar {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .player-inline-photo-meta {
            font-size: 0.65rem;
            line-height: 1.1;
        }
        .player-inline-status {
            font-size: 0.65rem;
            min-height: 0.85rem;
            line-height: 1.2;
            margin-top: 0.15rem;
            text-align: left;
        }
        .player-inline-value-cell {
            line-height: 1.15;
        }
        .player-inline-value-cell .btn-xs-inline {
            font-size: 0.65rem;
            padding: 0.1rem 0.35rem;
            line-height: 1.2;
        }
        .player-inline-identity-input {
            min-width: 6.5rem;
            max-width: 10rem;
        }
        .player-inline-position-select {
            min-width: 5.5rem;
            max-width: 7.5rem;
        }
        /* Wide grid: scroll horizontally instead of squashing columns on narrow screens */
        .players-table-outer {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 0.5rem;
        }
        .players-table-outer #dataTable.table-players-compact {
            min-width: 1180px;
            margin-bottom: 0;
        }
        .players-table-outer .dataTables_wrapper {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ __('Players') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><span>{{ __('All Players') }}</span></li>
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
        <!-- data table start -->
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-body py-3 px-3">
                    <h4 class="header-title float-left mb-2">{{ __('Players') }}</h4>
                    <p class="float-right mb-2">
                        @if (auth()->user()->can('players.edit'))
                            <a class="btn btn-primary text-white" href="{{ route('admin.players.create') }}">
                                {{ __('Create New Player') }}
                            </a>
                        @endif
                    </p>
                    <div class="clearfix"></div>
                    <div class="data-tables">
                        @include('backend.layouts.partials.messages')
                        <div class="players-table-outer table-responsive">
                        <table id="dataTable" class="text-center table table-sm table-bordered table-players-compact mb-0">
                            <thead class="bg-light text-capitalize">
                                <tr>
                                    <th class="text-nowrap" style="width:2.5%">{{ __('Sl') }}</th>
                                    <th style="width:7%">{{ __('Photo') }}</th>
                                    <th style="width:12%">{{ __('Name') }}</th>
                                    <th style="width:12%">{{ __('ID') }}</th>
                                    <th style="width:5%">{{ __('Jersey') }}</th>
                                    <th style="width:6%">{{ __('Value') }}</th>
                                    <th style="width:16%">{{ __('Phone') }}</th>
                                    <th style="width:7%">{{ __('Position') }}</th>
                                    <th style="width:11%">{{ __('Clubs') }}</th>
                                    <th style="width:6%">{{ __('Status') }}</th>
                                    <th style="width:9%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                               @foreach ($players as $player)
                               @php
                                   $cc = $player->country_code ? preg_replace('/\D/', '', (string) $player->country_code) : '';
                                   if ($cc !== '' && ! in_array($cc, ['60', '65', '62', '84'], true)) {
                                       $cc = '';
                                   }
                                   $phoneDigits = $player->phone ? preg_replace('/\D/', '', (string) $player->phone) : '';
                                   $mv = $player->market_value !== null ? (int) round((float) $player->market_value) : 40;
                               @endphp
                               {{-- Relative URLs so fetch() uses the current page scheme (avoids mixed-content blocks when APP_URL is http but the site is https). --}}
                               <tr class="player-inline-row" data-player-id="{{ $player->id }}" data-inline-url="{{ route('admin.players.inline-update', ['id' => $player->id], false) }}">
                                    <td>{{ $players->firstItem() ? $players->firstItem() + $loop->index : $loop->iteration }}</td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <div class="d-flex flex-column align-items-center text-center player-inline-photo-meta">
                                                <img
                                                    class="player-inline-avatar js-inline-avatar"
                                                    src="{{ $player->avatar ? asset($player->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                                                    alt=""
                                                >
                                                <label class="mb-0 mt-1 text-primary" style="cursor:pointer;">
                                                    <input type="file" class="d-none js-inline-avatar-input" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                                    {{ __('Change') }}
                                                </label>
                                                <span class="text-muted">1MB</span>
                                            </div>
                                        @else
                                            <img
                                                class="player-inline-avatar"
                                                src="{{ $player->avatar ? asset($player->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                                                alt=""
                                            >
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <input type="text" class="form-control form-control-sm js-inline-field text-left" name="name" value="{{ $player->name }}" maxlength="255" autocomplete="name">
                                        @else
                                            <a href="{{ route('player.details', ['id' => $player->id]) }}">{{ $player->name }}</a>
                                        @endif
                                    </td>
                                    <td class="text-left small">
                                        @if (auth()->user()->can('players.edit'))
                                            <input type="text" class="form-control form-control-sm js-inline-field text-left player-inline-identity-input" name="identity_number" value="{{ $player->identity_number }}" maxlength="50" autocomplete="off" title="{{ __('IC / ID number') }}">
                                        @else
                                            {{ $player->identity_number }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <input type="number" class="form-control form-control-sm js-inline-field" name="jersey_number" value="{{ $player->jersey_number }}" min="1" max="99999" placeholder="—">
                                        @else
                                            {{ $player->jersey_number ?? '—' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <input type="hidden" name="market_value" class="js-inline-mv-value" value="{{ $mv }}">
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="js-mv-display font-weight-bold">{{ $player->market_value !== null ? (int) round((float) $player->market_value) : '—' }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1 js-open-mv-modal" data-update-url="{{ route('admin.players.update.market.value', ['id' => $player->id], false) }}">
                                                    {{ __('Edit') }}
                                                </button>
                                            </div>
                                        @else
                                            {{ $player->market_value !== null ? (int) round((float) $player->market_value) : '—' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <div class="player-inline-phone mx-auto text-left">
                                                <select class="form-control form-control-sm js-inline-field js-inline-invite-gate" name="country_code" title="{{ __('Country') }}">
                                                    <option value="">{{ __('Optional') }}</option>
                                                    <option value="60" {{ $cc === '60' ? 'selected' : '' }}>+60</option>
                                                    <option value="65" {{ $cc === '65' ? 'selected' : '' }}>+65</option>
                                                    <option value="84" {{ $cc === '84' ? 'selected' : '' }}>+84</option>
                                                    <option value="62" {{ $cc === '62' ? 'selected' : '' }}>+62</option>
                                                </select>
                                                <div class="player-inline-phone-input-wrap">
                                                    <input type="text" class="form-control form-control-sm js-inline-field js-inline-phone js-inline-invite-gate" name="phone" value="{{ $phoneDigits }}" inputmode="numeric" pattern="[0-9]*" maxlength="15" placeholder="digits" title="{{ __('Numbers only') }}">
                                                    <button type="button" class="btn btn-sm btn-outline-primary js-send-invitation" data-url="{{ route('admin.players.send-invitation', ['id' => $player->id], false) }}" title="{{ __('Send invitation') }}" aria-label="{{ __('Send invitation') }}">
                                                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="player-inline-status js-inline-msg text-muted"></div>
                                        @else
                                            @if($player->country_code && $player->phone)
                                                +{{ str_replace('+', '', $player->country_code) }}{{ $player->phone }}
                                            @elseif($player->phone)
                                                {{ $player->phone }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <select class="form-control form-control-sm js-inline-field player-inline-position-select" name="position" title="{{ __('Position') }}">
                                                <option value="">—</option>
                                                <option value="Goalkeeper" {{ $player->position === 'Goalkeeper' ? 'selected' : '' }}>Goalkeeper</option>
                                                <option value="Defender" {{ $player->position === 'Defender' ? 'selected' : '' }}>Defender</option>
                                                <option value="Midfielder" {{ $player->position === 'Midfielder' ? 'selected' : '' }}>Midfielder</option>
                                                <option value="Forward" {{ $player->position === 'Forward' ? 'selected' : '' }}>Forward</option>
                                            </select>
                                        @elseif($player->position)
                                            @php
                                                $positionClass = 'position-' . strtolower($player->position);
                                            @endphp
                                            <span class="badge position-badge {{ $positionClass }}">
                                                {{ $player->position }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($player->clubs as $club)
                                            <span class="badge badge-info mr-1">
                                                {{ $club->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge badge-info mr-1">
                                            {{ $player->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <a class="btn btn-success text-white btn-sm" href="{{ route('admin.players.edit', $player->id) }}">Edit</a>
                                        @endif
                                        
                                        @if (auth()->user()->can('players.delete'))
                                        <a class="btn btn-danger text-white btn-sm" href="javascript:void(0);"
                                        onclick="event.preventDefault(); if(confirm('Are you sure you want to delete?')) { document.getElementById('delete-form-{{ $player->id }}').submit(); }">
                                            {{ __('Delete') }}
                                        </a>

                                        <form id="delete-form-{{ $player->id }}" action="{{ route('admin.players.destroy', $player->id) }}" method="POST" style="display: none;">
                                            @method('DELETE')
                                            @csrf
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                               @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- data table end -->
    </div>
</div>

@if (auth()->user()->can('players.edit'))
<div class="modal fade" id="marketValueModal" tabindex="-1" role="dialog" aria-labelledby="marketValueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marketValueModalLabel">{{ __('Edit market value') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="modal_market_value">{{ __('Value') }} (40–150)</label>
                <input type="number" class="form-control" id="modal_market_value" min="40" max="150" step="1" inputmode="numeric">
                <p class="text-muted small mb-0 mt-2">{{ __('Between 40 and 150.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="modal_market_value_save">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
     <!-- Start datatable js -->
     <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
     <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
     <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
     <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
     <script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>
     
     <script>
        if ($('#dataTable').length) {
            $('#dataTable').DataTable({
                scrollX: false,
                autoWidth: false,
                ordering: false,
                responsive: false,
                paging: false,
                info: false,
                searching: false,
                lengthChange: false
            });
        }

        @if (auth()->user()->can('players.edit'))
        (function () {
            var csrf = document.querySelector('meta[name="csrf-token"]');
            csrf = csrf ? csrf.getAttribute('content') : '';
            var timers = {};

            function showMsg($row, text, ok) {
                var $el = $row.find('.js-inline-msg');
                $el.removeClass('text-success text-danger text-muted')
                    .addClass(ok ? 'text-success' : 'text-danger')
                    .text(text);
                if (ok) {
                    setTimeout(function () {
                        $el.text('').removeClass('text-success text-danger').addClass('text-muted');
                    }, 2200);
                }
            }

            /** Read body as text, then JSON if possible — avoids "Network error" when server returns HTML (419/500). */
            function parseFetchResponse(r) {
                return r.text().then(function (text) {
                    var data = {};
                    if (text) {
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            var plain = String(text).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                            if (plain.length > 160) {
                                plain = plain.slice(0, 160) + '…';
                            }
                            data = { message: plain || 'Invalid response from server.' };
                        }
                    }
                    return { ok: r.ok, status: r.status, data: data };
                });
            }

            function httpErrorHint(status) {
                if (status === 419) {
                    return 'Session expired. Refresh the page and try again.';
                }
                if (status === 401 || status === 403) {
                    return 'Not allowed or session ended. Refresh and sign in again.';
                }
                if (status === 502 || status === 503) {
                    return 'Server temporarily unavailable. Try again later.';
                }
                if (status >= 500) {
                    return 'Server error. Try again or contact support if it persists.';
                }
                return '';
            }

            /** If the page is HTTPS but a data-URL is still http://same-host (misconfigured APP_URL), upgrade so fetch is not mixed-content blocked. */
            function fetchUrlForPage(url) {
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
                } catch (e) {}
                return url;
            }

            function buildFormData($row, file) {
                var fd = new FormData();
                fd.append('_token', csrf);
                fd.append('name', $row.find('[name="name"]').val() || '');
                var $idn = $row.find('[name="identity_number"]');
                if ($idn.length) {
                    fd.append('identity_number', String($idn.val() || '').trim());
                }
                var $pos = $row.find('[name="position"]');
                if ($pos.length) {
                    fd.append('position', String($pos.val() || ''));
                }
                var jn = $row.find('[name="jersey_number"]').val();
                if (jn !== '' && jn != null) {
                    fd.append('jersey_number', jn);
                }
                fd.append('country_code', $row.find('[name="country_code"]').val() || '');
                var $phone = $row.find('[name="phone"]');
                var phone = String($phone.val() || '').replace(/\D/g, '');
                $phone.val(phone);
                fd.append('phone', phone);
                var $mv = $row.find('[name="market_value"]');
                if ($mv.length) {
                    fd.append('market_value', $mv.val());
                }
                if (file) {
                    fd.append('avatar', file);
                }
                return fd;
            }

            function phoneInviteReady($row) {
                var cc = String($row.find('[name="country_code"]').val() || '').replace(/\D/g, '');
                var phone = String($row.find('[name="phone"]').val() || '').replace(/\D/g, '');
                return ['60', '65', '62', '84'].indexOf(cc) !== -1 && phone.length >= 7 && phone.length <= 15;
            }

            function syncInviteButton($row) {
                var $btn = $row.find('.js-send-invitation');
                if (!$btn.length) {
                    return;
                }
                $btn.prop('disabled', !phoneInviteReady($row));
            }

            /** Resolves when inline save succeeds; rejects after showing error (use for invite flow). */
            function saveRowPromise($row, opts) {
                opts = opts || {};
                var url = fetchUrlForPage($row.attr('data-inline-url') || '');
                if (!url) {
                    return Promise.reject(new Error('No save URL'));
                }
                var fd = buildFormData($row, opts.file);
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd,
                    credentials: 'same-origin'
                }).then(parseFetchResponse).then(function (res) {
                    if (!res.ok) {
                        var hint = httpErrorHint(res.status);
                        var msg;
                        if (res.status === 422 && res.data.errors) {
                            var first = Object.values(res.data.errors)[0];
                            msg = Array.isArray(first) ? first[0] : String(first);
                        } else {
                            msg = hint || (res.data && res.data.message) || 'Save failed';
                        }
                        showMsg($row, msg, false);
                        var err = new Error('inline_save_failed');
                        err.inlineSaveFailed = true;
                        throw err;
                    }
                    showMsg($row, (res.data && res.data.message) ? res.data.message : 'Saved', true);
                    if (res.data && res.data.avatar_url) {
                        var u = res.data.avatar_url + (res.data.avatar_url.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now();
                        $row.find('.js-inline-avatar').attr('src', u);
                    }
                    if (opts.fileInput) {
                        opts.fileInput.value = '';
                    }
                    syncInviteButton($row);
                    return res;
                }).catch(function (err) {
                    if (err && err.inlineSaveFailed) {
                        throw err;
                    }
                    var msg = (err && err.message) ? String(err.message) : 'Network error';
                    if (msg === 'Failed to fetch' || msg.indexOf('NetworkError') !== -1) {
                        msg = 'Connection failed. Check your network and try again.';
                    }
                    showMsg($row, msg, false);
                    var wrapped = new Error(msg);
                    wrapped.saveRowErrorShown = true;
                    throw wrapped;
                });
            }

            function saveRow($row, opts) {
                saveRowPromise($row, opts).catch(function () {});
            }

            function scheduleSave($row) {
                var id = $row.data('player-id');
                clearTimeout(timers[id]);
                timers[id] = setTimeout(function () {
                    saveRow($row);
                }, 450);
            }

            $(document).on('input', '.player-inline-row .js-inline-phone', function () {
                var v = this.value.replace(/\D/g, '');
                if (this.value !== v) {
                    this.value = v;
                }
                syncInviteButton($(this).closest('.player-inline-row'));
            });

            $(document).on('blur change', '.player-inline-row .js-inline-field', function () {
                var $row = $(this).closest('.player-inline-row');
                scheduleSave($row);
                syncInviteButton($row);
            });

            $('.player-inline-row').each(function () {
                syncInviteButton($(this));
            });

            $(document).on('change', '.js-inline-avatar-input', function () {
                var file = this.files && this.files[0];
                if (!file) return;
                var maxBytes = 1048576;
                if (file.size > maxBytes) {
                    var $row = $(this).closest('.player-inline-row');
                    showMsg($row, 'Image must be 1MB or smaller.', false);
                    this.value = '';
                    return;
                }
                saveRow($(this).closest('.player-inline-row'), { file: file, fileInput: this });
            });

            $(document).on('click', '.js-send-invitation', function () {
                var $btn = $(this);
                var $row = $btn.closest('.player-inline-row');
                if ($btn.prop('disabled')) {
                    return;
                }
                var inviteUrl = fetchUrlForPage($btn.attr('data-url') || '');
                if (!inviteUrl) {
                    return;
                }
                var countryCode = String($row.find('[name="country_code"]').val() || '').replace(/\D/g, '');
                var phone = String($row.find('[name="phone"]').val() || '').replace(/\D/g, '');
                $row.find('[name="phone"]').val(phone);
                if (!phoneInviteReady($row)) {
                    showMsg($row, 'Select a country code and enter a valid phone (7–15 digits).', false);
                    return;
                }
                var playerId = $row.data('player-id');
                clearTimeout(timers[playerId]);
                $btn.prop('disabled', true);
                saveRowPromise($row).then(function () {
                    return fetch(inviteUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ country_code: countryCode, phone: phone }),
                        credentials: 'same-origin'
                    });
                }).then(parseFetchResponse).then(function (res) {
                    if (!res.ok) {
                        var hint = httpErrorHint(res.status);
                        if (res.status === 422 && res.data.errors) {
                            var first = Object.values(res.data.errors)[0];
                            showMsg($row, Array.isArray(first) ? first[0] : String(first), false);
                        } else {
                            showMsg($row, hint || (res.data && res.data.message) || 'Could not send invitation.', false);
                        }
                        return;
                    }
                    showMsg($row, (res.data && res.data.message) ? res.data.message : 'Invitation sent.', true);
                }).catch(function (err) {
                    if (err && err.inlineSaveFailed) {
                        return;
                    }
                    if (err && err.saveRowErrorShown) {
                        return;
                    }
                    var msg = (err && err.message) ? String(err.message) : 'Network error';
                    if (msg === 'Failed to fetch' || msg.indexOf('NetworkError') !== -1) {
                        msg = 'Connection failed. Check your network and try again.';
                    }
                    showMsg($row, msg, false);
                }).finally(function () {
                    syncInviteButton($row);
                });
            });

            var mvModalUrl = '';
            var $mvModalRow = null;

            $(document).on('click', '.js-open-mv-modal', function () {
                $mvModalRow = $(this).closest('.player-inline-row');
                mvModalUrl = fetchUrlForPage($(this).attr('data-update-url') || '');
                var v = $mvModalRow.find('.js-inline-mv-value').val();
                var n = parseInt(v, 10);
                if (isNaN(n) || n < 40) {
                    n = 40;
                }
                if (n > 150) {
                    n = 150;
                }
                $('#modal_market_value').val(n);
                $('#marketValueModal').modal('show');
            });

            $('#modal_market_value_save').on('click', function () {
                if (!$mvModalRow || !$mvModalRow.length || !mvModalUrl) {
                    return;
                }
                var mv = parseInt($('#modal_market_value').val(), 10);
                if (isNaN(mv) || mv < 40 || mv > 150) {
                    showMsg($mvModalRow, 'Enter a value between 40 and 150.', false);
                    return;
                }
                var $row = $mvModalRow;
                var $saveBtn = $(this);
                $saveBtn.prop('disabled', true);
                var fd = new FormData();
                fd.append('_token', csrf);
                fd.append('market_value', String(mv));
                fetch(fetchUrlForPage(mvModalUrl), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd,
                    credentials: 'same-origin'
                }).then(parseFetchResponse).then(function (res) {
                    if (!res.ok) {
                        var hint = httpErrorHint(res.status);
                        var msg = hint || (res.data && res.data.message) || 'Could not save.';
                        if (res.status === 422 && res.data.errors) {
                            var first = Object.values(res.data.errors)[0];
                            msg = Array.isArray(first) ? first[0] : String(first);
                        }
                        showMsg($row, msg, false);
                        return;
                    }
                    $row.find('.js-inline-mv-value').val(mv);
                    $row.find('.js-mv-display').text(mv);
                    $('#marketValueModal').modal('hide');
                    showMsg($row, (res.data && res.data.message) ? res.data.message : 'Saved', true);
                }).catch(function (err) {
                    var msg = (err && err.message) ? String(err.message) : 'Network error';
                    if (msg === 'Failed to fetch' || msg.indexOf('NetworkError') !== -1) {
                        msg = 'Connection failed. Check your network and try again.';
                    }
                    showMsg($row, msg, false);
                }).finally(function () {
                    $saveBtn.prop('disabled', false);
                });
            });
        })();
        @endif
     </script>
@endsection