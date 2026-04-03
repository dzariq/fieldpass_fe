@extends('backend.layouts.master')

@section('title')
Player Edit - Admin Panel
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
                    <h4 class="header-title">Edit Player - {{ $player->name }}</h4>
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
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="{{ old('name', $player->name) }}" required autofocus>
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
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="username">Admin Username</label>
                                <input type="text" class="form-control bg-light" id="username" name="username" placeholder="Username" value="{{ old('username', $player->username) }}" readonly>
                                <small class="form-text text-muted">Username cannot be changed</small>
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="clubs">Assign Clubs</label>
                                <select name="club_ids[]" id="clubs" class="form-control select2" multiple>
                                    @foreach ($clubs as $club)
                                    <option value="{{ $club->id }}" {{ (old('club_ids') ? in_array($club->id, old('club_ids')) : $player->clubs->contains('id', $club->id)) ? 'selected' : '' }}>{{ $club->name }}</option>
                                    @endforeach
                                </select>
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
                                <select class="form-control" name="position" id="position" required>
                                    <option value="">{{ __('Select Position') }}</option>
                                    <option value="Goalkeeper" {{ old('position', $player->position) == 'Goalkeeper' ? 'selected' : '' }}>Goalkeeper</option>
                                    <option value="Defender" {{ old('position', $player->position) == 'Defender' ? 'selected' : '' }}>Defender</option>
                                    <option value="Midfielder" {{ old('position', $player->position) == 'Midfielder' ? 'selected' : '' }}>Midfielder</option>
                                    <option value="Forward" {{ old('position', $player->position) == 'Forward' ? 'selected' : '' }}>Forward</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="identity_number" class="required-field">Identity Number (IC)</label>
                                <input type="text" class="form-control" id="identity_number" name="identity_number" placeholder="XXXXXX-XX-XXXX" required value="{{ old('identity_number', $player->identity_number) }}" maxlength="14">
                                <!-- <small class="form-text text-muted">Format: XXXXXX-XX-XXXX (e.g., 900101-01-1234)</small> -->
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="country_code" class="required-field">Country Code</label>
                                <select name="country_code" id="country_code" class="form-control" required>
                                    <option value="">Select Country Code</option>
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
                                <label for="phone" class="required-field">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="123456789" required value="{{ old('phone', $player->phone) }}" pattern="[0-9]{7,15}" title="Please enter 7-15 digits">
                                <small class="form-text text-muted">Enter without country code (e.g., 123456789)</small>
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
                </div>
            </div>
        </div>
        <!-- data table end -->

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();

        // Auto-format IC number to XXXXXX-XX-XXXX
        // $('#identity_number').on('input', function() {
        //     let value = this.value.replace(/[^0-9]/g, ''); // Remove non-digits
            
        //     if (value.length > 6) {
        //         value = value.substring(0, 6) + '-' + value.substring(6);
        //     }
        //     if (value.length > 9) {
        //         value = value.substring(0, 9) + '-' + value.substring(9);
        //     }
        //     if (value.length > 14) {
        //         value = value.substring(0, 14);
        //     }
            
        //     this.value = value;
        // });

        // Validate IC format on blur
        $('#identity_number').on('blur', function() {
            // let value = this.value;
            // let pattern = /^\d{6}-\d{2}-\d{4}$/;
            
            // if (value && !pattern.test(value)) {
            //     alert('Invalid IC format. Please use format: XXXXXX-XX-XXXX (e.g., 900101-01-1234)');
            //     $(this).focus();
            // }
        });

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
@endsection