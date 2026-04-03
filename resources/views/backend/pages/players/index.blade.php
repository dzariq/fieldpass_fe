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
        .player-inline-row .form-control-sm {
            min-width: 0;
            font-size: 0.8125rem;
            padding: 0.25rem 0.4rem;
            height: auto;
        }
        .player-inline-row .player-inline-phone {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            align-items: center;
            justify-content: center;
        }
        .player-inline-row .player-inline-phone select {
            max-width: 7.5rem;
        }
        .player-inline-row .player-inline-phone input[type="text"] {
            flex: 1 1 5rem;
            min-width: 4rem;
            max-width: 8rem;
        }
        .player-inline-avatar {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .player-inline-status {
            font-size: 0.75rem;
            min-height: 1rem;
        }
        .player-inline-mv {
            max-width: 5.5rem;
            margin-left: auto;
            margin-right: auto;
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
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title float-left">{{ __('Players') }}</h4>
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
                        <table id="dataTable" class="text-center">
                            <thead class="bg-light text-capitalize">
                                <tr>
                                    <th width="4%">{{ __('Sl') }}</th>
                                    <th width="8%">{{ __('Photo') }}</th>
                                    <th width="14%">{{ __('Name') }}</th>
                                    <th width="16%">{{ __('ID') }}</th>
                                    <th width="7%">{{ __('Jersey') }}</th>
                                    <th width="16%">{{ __('Phone') }}</th>
                                    <th width="7%">{{ __('Position') }}</th>
                                    <th width="12%">{{ __('Clubs') }}</th>
                                    <th width="7%">{{ __('Status') }}</th>
                                    <th width="9%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                               @foreach ($players as $player)
                               @php
                                   $cc = $player->country_code ? preg_replace('/\D/', '', (string) $player->country_code) : '60';
                                   if (! in_array($cc, ['60', '65', '62', '84'], true)) {
                                       $cc = '60';
                                   }
                                   $phoneDigits = $player->phone ? preg_replace('/\D/', '', (string) $player->phone) : '';
                                   $mv = $player->market_value !== null ? (int) round((float) $player->market_value) : 40;
                               @endphp
                               <tr class="player-inline-row" data-player-id="{{ $player->id }}" data-inline-url="{{ route('admin.players.inline-update', $player->id) }}">
                                    <td>{{ $loop->index+1 }}</td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <div class="d-flex flex-column align-items-center">
                                                <img
                                                    class="player-inline-avatar js-inline-avatar"
                                                    src="{{ $player->avatar ? asset($player->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                                                    alt=""
                                                >
                                                <label class="mb-0 mt-1 small text-primary" style="cursor:pointer;">
                                                    <input type="file" class="d-none js-inline-avatar-input" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                                    {{ __('Change') }}
                                                </label>
                                                <span class="small text-muted">{{ __('max 1MB') }}</span>
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
                                    <td class="text-left small">{{ $player->identity_number }}</td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <input type="number" class="form-control form-control-sm js-inline-field" name="jersey_number" value="{{ $player->jersey_number }}" min="1" max="99999" placeholder="—">
                                        @else
                                            {{ $player->jersey_number ?? '—' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <input type="number" class="form-control form-control-sm js-inline-field player-inline-mv" name="market_value" value="{{ $mv }}" min="40" max="150" step="1" inputmode="numeric" title="{{ __('Between 40 and 150') }}">
                                        @else
                                            {{ $player->market_value !== null ? (int) round((float) $player->market_value) : '—' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <div class="player-inline-phone mx-auto">
                                                <select class="form-control form-control-sm js-inline-field" name="country_code" title="{{ __('Country') }}">
                                                    <option value="60" {{ $cc === '60' ? 'selected' : '' }}>+60 MY</option>
                                                    <option value="65" {{ $cc === '65' ? 'selected' : '' }}>+65 SG</option>
                                                    <option value="84" {{ $cc === '84' ? 'selected' : '' }}>+84 VN</option>
                                                    <option value="62" {{ $cc === '62' ? 'selected' : '' }}>+62 ID</option>
                                                </select>
                                                <input type="text" class="form-control form-control-sm js-inline-field js-inline-phone" name="phone" value="{{ $phoneDigits }}" inputmode="numeric" pattern="[0-9]*" maxlength="15" placeholder="digits" title="{{ __('Numbers only') }}">
                                            </div>
                                            <div class="player-inline-status js-inline-msg text-muted"></div>
                                            <button type="button" class="btn btn-sm btn-outline-primary mt-2 js-send-invitation" data-url="{{ route('admin.players.send-invitation', $player->id) }}">
                                                {{ __('Send invitation') }}
                                            </button>
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
                                        @if($player->position)
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
        <!-- data table end -->
    </div>
</div>
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
                scrollX: true,
                autoWidth: false,
                ordering: false,
                responsive: false
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

            function buildFormData($row, file) {
                var fd = new FormData();
                fd.append('_token', csrf);
                fd.append('name', $row.find('[name="name"]').val() || '');
                var jn = $row.find('[name="jersey_number"]').val();
                if (jn !== '' && jn != null) {
                    fd.append('jersey_number', jn);
                }
                fd.append('country_code', $row.find('[name="country_code"]').val());
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

            function saveRow($row, opts) {
                opts = opts || {};
                var url = $row.data('inline-url');
                if (!url) return;
                var fd = buildFormData($row, opts.file);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd,
                    credentials: 'same-origin'
                }).then(function (r) {
                    return r.json().then(function (data) {
                        return { ok: r.ok, status: r.status, data: data };
                    });
                }).then(function (res) {
                    if (!res.ok) {
                        if (res.status === 422 && res.data.errors) {
                            var first = Object.values(res.data.errors)[0];
                            showMsg($row, Array.isArray(first) ? first[0] : String(first), false);
                        } else {
                            showMsg($row, (res.data && res.data.message) ? res.data.message : 'Save failed', false);
                        }
                        return;
                    }
                    showMsg($row, (res.data && res.data.message) ? res.data.message : 'Saved', true);
                    if (res.data && res.data.avatar_url) {
                        var u = res.data.avatar_url + (res.data.avatar_url.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now();
                        $row.find('.js-inline-avatar').attr('src', u);
                    }
                    if (opts.fileInput) {
                        opts.fileInput.value = '';
                    }
                }).catch(function () {
                    showMsg($row, 'Network error', false);
                });
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
            });

            $(document).on('blur change', '.player-inline-row .js-inline-field', function () {
                scheduleSave($(this).closest('.player-inline-row'));
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
                var url = $btn.data('url');
                if (!url) return;
                var countryCode = String($row.find('[name="country_code"]').val() || '').replace(/\D/g, '');
                var phone = String($row.find('[name="phone"]').val() || '').replace(/\D/g, '');
                $row.find('[name="phone"]').val(phone);
                $btn.prop('disabled', true);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ country_code: countryCode, phone: phone }),
                    credentials: 'same-origin'
                }).then(function (r) {
                    return r.json().then(function (data) {
                        return { ok: r.ok, status: r.status, data: data };
                    });
                }).then(function (res) {
                    if (!res.ok) {
                        showMsg($row, (res.data && res.data.message) ? res.data.message : 'Could not send invitation.', false);
                        return;
                    }
                    showMsg($row, (res.data && res.data.message) ? res.data.message : 'Invitation sent.', true);
                }).catch(function () {
                    showMsg($row, 'Network error', false);
                }).finally(function () {
                    $btn.prop('disabled', false);
                });
            });
        })();
        @endif
     </script>
@endsection