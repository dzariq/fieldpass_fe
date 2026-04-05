@extends('backend.layouts.master')

@section('title')
Player Edit - Admin Panel
@endsection

@section('styles')
<style>
    .form-check-label {
        text-transform: capitalize;
    }
    .required-field::after {
        content: " *";
        color: red;
        font-weight: bold;
    }
    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        margin-top: 5px;
    }
    .status-ACTIVE {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .status-INACTIVE {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .status-INVITED {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
</style>
@endsection

@section('admin-content')
@php
    $allowFullPlayerFieldEdit = $allowFullPlayerFieldEdit ?? true;
    $clubsForTermination = $clubsForTermination ?? collect();
@endphp

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Player Edit</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.players.index') }}">All Players</a></li>
                    <li><span>Edit Player - {{ $player->name }}</span></li>
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
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <h4 class="header-title mb-0">Edit Player - {{ $player->name }}</h4>
                        @if (auth()->user()->can('players.edit'))
                            <button type="button" class="btn btn-info btn-sm text-white js-club-history-performance mt-2 mt-sm-0" data-url="{{ route('admin.players.club-history-performance', ['player' => $player->id], false) }}">{{ __('Club history & performance') }}</button>
                        @endif
                    </div>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.players.update', $player->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Player Image</label>
                                <div style="display:flex; align-items:center; gap:16px;">
                                    <img
                                        id="avatar-preview"
                                        src="{{ $player->avatar ? asset($player->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                                        alt="Player Avatar"
                                        style="width:72px;height:72px;border-radius:10px;object-fit:cover;border:1px solid #e5e7eb;background:#fff;"
                                    >
                                    <div>
                                        <input type="file" class="form-control-file" name="avatar" id="avatar" accept="image/*">
                                        <small class="form-text text-muted">Optional. PNG/JPG/WEBP, max 1MB.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name" class="required-field">Player Name</label>
                                @if ($allowFullPlayerFieldEdit)
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="{{ old('name', $player->name) }}" required autofocus>
                                @else
                                    <p class="form-control-plaintext border rounded px-3 py-2 bg-light mb-0">{{ $player->name }}</p>
                                @endif
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="email">Player Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email (Optional)" value="{{ old('email', $player->email) }}">
                                <small class="form-text text-muted">Optional</small>
                            </div>
                        </div>

                          <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="jersey_number" >Jersey No.</label>
                                <input type="number" min="1" step="1" class="form-control" id="jersey_number" name="jersey_number" placeholder="Enter Jersey Number" value="{{ old('jersey_number', $player->jersey_number) }}"  autofocus>
                            </div>
                            
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="username">Admin Username</label>
                                <input type="text" class="form-control bg-light" id="username" name="username" placeholder="Username" value="{{ old('username', $player->username) }}" readonly>
                                <small class="form-text text-muted">Username cannot be changed</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="salary">Salary</label>
                                <input type="number" min="0" max="9999999999" class="form-control" id="salary" name="salary" value="{{ old('salary', $player->contracts->first()->salary ?? 0) }}">
                                <small class="form-text text-muted">Optional - Default: 0</small>
                            </div>

                            <div class="form-group col-md-6 col-sm-6">
                                <label for="position" class="required-field">Position</label>
                                @if ($allowFullPlayerFieldEdit)
                                    <select class="form-control" name="position" id="position" required>
                                        <option value="">{{ __('Select Position') }}</option>
                                        <option value="Goalkeeper" {{ old('position', $player->position) == 'Goalkeeper' ? 'selected' : '' }}>Goalkeeper</option>
                                        <option value="Defender" {{ old('position', $player->position) == 'Defender' ? 'selected' : '' }}>Defender</option>
                                        <option value="Midfielder" {{ old('position', $player->position) == 'Midfielder' ? 'selected' : '' }}>Midfielder</option>
                                        <option value="Forward" {{ old('position', $player->position) == 'Forward' ? 'selected' : '' }}>Forward</option>
                                    </select>
                                @else
                                    <p class="form-control-plaintext border rounded px-3 py-2 bg-light mb-0">{{ $player->position ?: '—' }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="identity_type" class="required-field">Identity type</label>
                                @if ($allowFullPlayerFieldEdit)
                                    <select class="form-control" name="identity_type" id="identity_type" required>
                                        @php $idt = old('identity_type', $player->identity_type ?? 'malaysia_ic'); @endphp
                                        <option value="malaysia_ic" {{ $idt === 'malaysia_ic' ? 'selected' : '' }}>Malaysia IC</option>
                                        <option value="foreign_id" {{ $idt === 'foreign_id' ? 'selected' : '' }}>Foreign ID</option>
                                    </select>
                                @else
                                    <p class="form-control-plaintext border rounded px-3 py-2 bg-light mb-0">
                                        {{ ($player->identity_type ?? 'malaysia_ic') === 'foreign_id' ? 'Foreign ID' : 'Malaysia IC' }}
                                    </p>
                                @endif
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="identity_number" class="required-field"><span id="identity_number_label">Identity number</span></label>
                                @if ($allowFullPlayerFieldEdit)
                                    <input type="text" class="form-control" id="identity_number" name="identity_number" placeholder="XXXXXX-XX-XXXX" required value="{{ old('identity_number', $player->identity_number) }}" maxlength="14" autocomplete="off">
                                    <small class="form-text text-muted" id="identity_number_hint"></small>
                                @else
                                    <p class="form-control-plaintext border rounded px-3 py-2 bg-light mb-0 text-monospace">{{ $player->identity_number }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="country_code">Country Code</label>
                                <select name="country_code" id="country_code" class="form-control">
                                    <option value="">{{ __('Optional') }}</option>
                                    <option value="60" {{ old('country_code', $player->country_code) == '60' ? 'selected' : '' }}>+60 (Malaysia)</option>
                                    <option value="65" {{ old('country_code', $player->country_code) == '65' ? 'selected' : '' }}>+65 (Singapore)</option>
                                    <option value="62" {{ old('country_code', $player->country_code) == '62' ? 'selected' : '' }}>+62 (Indonesia)</option>
                                    <option value="66" {{ old('country_code', $player->country_code) == '66' ? 'selected' : '' }}>+66 (Thailand)</option>
                                    <option value="63" {{ old('country_code', $player->country_code) == '63' ? 'selected' : '' }}>+63 (Philippines)</option>
                                    <option value="84" {{ old('country_code', $player->country_code) == '84' ? 'selected' : '' }}>+84 (Vietnam)</option>
                                    <option value="95" {{ old('country_code', $player->country_code) == '95' ? 'selected' : '' }}>+95 (Myanmar)</option>
                                    <option value="856" {{ old('country_code', $player->country_code) == '856' ? 'selected' : '' }}>+856 (Laos)</option>
                                    <option value="855" {{ old('country_code', $player->country_code) == '855' ? 'selected' : '' }}>+855 (Cambodia)</option>
                                    <option value="673" {{ old('country_code', $player->country_code) == '673' ? 'selected' : '' }}>+673 (Brunei)</option>
                                    <option value="670" {{ old('country_code', $player->country_code) == '670' ? 'selected' : '' }}>+670 (East Timor)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="phone">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="{{ __('Optional — 7–15 digits') }}" value="{{ old('phone', $player->phone) }}" inputmode="numeric" maxlength="15" title="{{ __('Digits only, 7–15 characters if provided') }}">
                                <small class="form-text text-muted">{{ __('Optional. Enter without country code (e.g. 123456789).') }}</small>
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="status">Player Status</label>
                                <div>
                                    <span class="status-badge status-{{ $player->status }}">
                                        {{ $player->status }}
                                    </span>
                                </div>
                                <small class="form-text text-muted">Status cannot be changed manually</small>
                                <input type="hidden" name="status" value="{{ $player->status }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start" value="{{ old('start', $player->contracts->first()->start_date ?? date('Y-m-d')) }}">
                                <small class="form-text text-muted">Contract start date</small>
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end" value="{{ old('end', $player->contracts->first()->end_date ?? '') }}">
                                <small class="form-text text-muted">Contract end date</small>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary pr-4 pl-4">
                                <i class="fa fa-save"></i> Update Player
                            </button>
                            <a href="{{ route('admin.players.index') }}" class="btn btn-secondary pr-4 pl-4">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            
                            @if($player->status == 'INVITED')
                            <button type="button" class="btn btn-warning pr-4 pl-4" onclick="document.getElementById('reinvite-form').submit();">
                                <i class="fa fa-envelope"></i> Re-Invite Player
                            </button>
                            @endif
                        </div>
                    </form>

                    @if($player->status == 'INVITED')
                    <!-- Re-invite Form (separate form) -->
                    <form id="reinvite-form" action="{{ route('admin.players.reinvite', $player->id) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    @endif

                    @if ($clubsForTermination->isNotEmpty())
                    <hr class="my-4">
                    <div class="d-flex flex-wrap align-items-center">
                        <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#terminateContractModal">
                            <i class="fa fa-user-times"></i> Terminate Contract
                        </button>
                        <small class="text-muted ml-3">Remove the player from a club, record a remark, and mark the club contract as terminated.</small>
                    </div>

                    <div class="modal fade" id="terminateContractModal" tabindex="-1" role="dialog" aria-labelledby="terminateContractModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form action="{{ route('admin.players.terminate-contract', $player->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="terminateContractModalLabel">Terminate contract</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        @if ($clubsForTermination->count() === 1)
                                            @php $termClub = $clubsForTermination->first(); @endphp
                                            <p class="mb-2">Club: <strong>{{ $termClub->name }}</strong></p>
                                            <input type="hidden" name="club_id" value="{{ $termClub->id }}">
                                        @else
                                            <div class="form-group">
                                                <label for="terminate_club_id" class="required-field">Club</label>
                                                <select name="club_id" id="terminate_club_id" class="form-control" required>
                                                    <option value="">— Select club —</option>
                                                    @foreach ($clubsForTermination as $c)
                                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                        <div class="form-group mb-0">
                                            <label for="terminate_remark" class="required-field">Remark</label>
                                            <textarea name="remark" id="terminate_remark" class="form-control" rows="4" required maxlength="5000" placeholder="Reason or notes for this termination">{{ old('remark') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Confirm termination</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if (auth()->user()->can('players.edit'))
                        @include('backend.pages.players.partials.club-history-modal')
                    @endif
                </div>
            </div>
        </div>
        <!-- data table end -->

    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        @if ($allowFullPlayerFieldEdit)
        function applyIdentityTypeUi() {
            var t = $('#identity_type').val();
            var $in = $('#identity_number');
            if (t === 'malaysia_ic') {
                $('#identity_number_label').text('Identity number (IC)');
                $in.attr('placeholder', 'XXXXXX-XX-XXXX');
                $in.attr('maxlength', '14');
                $('#identity_number_hint').text('Format: XXXXXX-XX-XXXX — type up to 12 digits; dashes are inserted automatically.');
            } else {
                $('#identity_number_label').text('Identity number (foreign ID)');
                $in.attr('placeholder', 'Passport or national ID');
                $in.attr('maxlength', '50');
                $('#identity_number_hint').text('Free text, max 50 characters.');
            }
        }
        $('#identity_type').on('change', function () {
            applyIdentityTypeUi();
        });
        applyIdentityTypeUi();
        $('#identity_number').on('input', function () {
            if ($('#identity_type').val() !== 'malaysia_ic') {
                return;
            }
            var digits = this.value.replace(/\D/g, '').substring(0, 12);
            var out = '';
            if (digits.length > 0) {
                out = digits.substring(0, 6);
            }
            if (digits.length > 6) {
                out += '-' + digits.substring(6, 8);
            }
            if (digits.length > 8) {
                out += '-' + digits.substring(8, 12);
            }
            this.value = out;
        });
        @endif

        // Validate phone number (numbers only)
        $('#phone').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Preview avatar on select
        $('#avatar').on('change', function(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                $('#avatar-preview').attr('src', ev.target.result);
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@if (auth()->user()->can('players.edit'))
    @include('backend.pages.players.partials.club-history-modal-script')
@endif
@endsection