@extends('playerbackend.layouts.master')

@section('title')
Player Dashboard
@endsection

@section('styles')
<style>
    .profile-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 30px;
        text-align: center;
        color: white;
    }

    .profile-header h4 {
        color: white;
        margin: 0;
        font-weight: 600;
        font-size: 24px;
    }

    .avatar-section {
        margin: 30px 0;
        text-align: center;
    }

    .club-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 999px;
        background: rgba(102, 126, 234, 0.10);
        border: 1px solid rgba(102, 126, 234, 0.22);
        color: #2d3748;
        font-weight: 700;
        margin-top: 14px;
        max-width: 100%;
    }
    .club-badge__logo {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        background: #fff;
        border: 2px solid #fff;
        box-shadow: 0 6px 16px rgba(0,0,0,0.10);
        flex: 0 0 auto;
    }
    .club-badge__name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: min(320px, 65vw);
        text-align: left;
    }

    .avatar-wrapper {
        position: relative;
        display: inline-block;
    }

    .avatar-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        object-fit: cover;
    }

    .avatar-upload-btn {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: #667eea;
        color: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .avatar-upload-btn:hover {
        background: #764ba2;
        transform: scale(1.1);
    }

    .avatar-upload-btn i {
        font-size: 18px;
    }

    #avatar {
        display: none;
    }

    .form-section {
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 10px;
        color: #667eea;
        font-size: 20px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    .form-control[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .form-label {
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .required-field::after {
        content: " *";
        color: #dc3545;
        font-weight: bold;
    }

    .info-text {
        display: flex;
        align-items: center;
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }

    .info-text i {
        margin-right: 5px;
        color: #667eea;
    }

    .alert-modern {
        border: none;
        border-radius: 10px;
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-left: 4px solid #667eea;
        padding: 15px 20px;
        margin-top: 20px;
    }

    .alert-modern strong {
        color: #667eea;
    }

    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 12px 40px;
        font-weight: 600;
        font-size: 15px;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .input-icon .form-control {
        padding-left: 40px;
    }

    @media (max-width: 768px) {
        .profile-header {
            padding: 20px;
        }

        .section-title {
            font-size: 16px;
        }
    }
</style>
@endsection

@section('player-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Dashboard</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="index.html">Home</a></li>
                    <li><span>Profile</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('playerbackend.layouts.partials.logout')
        </div>
    </div>
</div>
<!-- page title area end -->

<div class="main-content-inner">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-12 mt-5">
            <div class="card profile-card">
                <div class="profile-header">
                    <h4><i class="fa fa-user-circle"></i> Update Profile</h4>
                    <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">Keep your information up to date</p>
                </div>

                <div class="card-body" style="padding: 40px;">
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('player.dashboard.update', $player->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf

                        <!-- Avatar Section -->
                        <div class="avatar-section">
                            <div class="avatar-wrapper">
                                <img src="{{ $player->avatar ? asset($player->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                                    alt="Avatar"
                                    class="avatar-image"
                                    id="avatar-preview">
                                <label for="avatar" class="avatar-upload-btn">
                                    <i class="fa fa-camera"></i>
                                </label>
                                <input type="file" class="form-control-file" name="avatar" id="avatar" accept="image/*">
                            </div>
                            <small class="form-text text-muted" style="display: block; margin-top: 15px;">
                                Click the camera icon to upload a new avatar (Max: 1MB)
                            </small>

                            @if(!empty($currentClub))
                                <div class="club-badge" title="{{ $currentClub->name }}">
                                    @php
                                        $clubLogoPath = isset($currentClub->avatar) ? $currentClub->avatar : null;
                                        $clubDisplayName = $currentClub->long_name ?? $currentClub->name ?? '';
                                    @endphp
                                    <img class="club-badge__logo"
                                         src="{{ $clubLogoPath ? asset($clubLogoPath) : asset('backend/assets/images/default-avatar.png') }}"
                                         alt="{{ $clubDisplayName }}">
                                    <div class="club-badge__name">
                                        {{ __('Current Club') }}: {{ $clubDisplayName }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Personal Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fa fa-user"></i> Personal Information
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label required-field">Full Name</label>
                                    <div class="input-icon">
                                        <i class="fa fa-user"></i>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" value="{{ $player->name }}" required autofocus>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-icon">
                                        <i class="fa fa-envelope"></i>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="{{ $player->email }}">
                                    </div>
                                    <small class="info-text">
                                        <i class="fa fa-info-circle"></i> Optional field
                                    </small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <div class="input-icon">
                                        <i class="fa fa-at"></i>
                                        <input readonly type="text" class="form-control" id="username" name="username" value="{{ $player->username }}">
                                    </div>
                                    <small class="info-text">
                                        <i class="fa fa-lock"></i> Username cannot be changed
                                    </small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="identity_number" class="form-label">{{ __('Identity number (IC / passport)') }}</label>
                                    <div class="input-icon">
                                        <i class="fa fa-id-card"></i>
                                        <input type="text" readonly class="form-control" value="{{ $player->identity_number }}" id="identity_number" autocomplete="off">
                                    </div>
                                    <small class="info-text">
                                        <i class="fa fa-shield-alt"></i> {{ __('Contact your club administrator to change your identity number.') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fa fa-phone"></i> Contact Information
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="country_code" class="form-label">Country Code</label>
                                    <div class="input-icon">
                                        <i class="fa fa-globe"></i>
                                        <input type="text" readonly class="form-control" value="{{ $player->country_code }}" id="country_code" name="country_code" placeholder="+60">
                                    </div>
                                    <small class="info-text">
                                        <i class="fa fa-shield-alt"></i> Contact your club admin to change
                                    </small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <div class="input-icon">
                                        <i class="fa fa-mobile-alt"></i>
                                        <input type="text" readonly class="form-control" id="phone" value="{{ $player->phone }}" name="phone" placeholder="123456789">
                                    </div>
                                    <small class="info-text">
                                        <i class="fa fa-shield-alt"></i> Contact your club admin to change
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <div class="alert alert-modern">
                            <strong><i class="fa fa-info-circle"></i> Security Notice:</strong> 
                            For security purposes, changes to your country code and phone number must be requested from your club administrator.
                        </div>

                        <input type="hidden" value="{{ $player->status }}" name="status" />

                        <div class="text-center" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-save">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @include('playerbackend.pages.dashboard.partials.club-history-performance')
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Preview avatar on file select
    document.getElementById('avatar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@if(!empty($matchPerformance['points_by_month_chart']['labels']) && !empty($matchPerformance['points_by_month_chart']['datasets']))
<script>
(function () {
    var chartCfg = @json($matchPerformance['points_by_month_chart']);
    if (typeof Chart === 'undefined' || !chartCfg || !chartCfg.labels || !chartCfg.labels.length) {
        return;
    }
    var canvas = document.getElementById('playerDashboardPerfChart');
    if (!canvas) {
        return;
    }
    var mapped = (chartCfg.datasets || []).map(function (ds) {
        return {
            label: ds.label,
            data: ds.data,
            borderColor: ds.borderColor || '#667eea',
            backgroundColor: 'transparent',
            fill: false,
            lineTension: 0.2,
            pointRadius: 3,
            pointHitRadius: 10
        };
    });
    new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: chartCfg.labels,
            datasets: mapped
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12, fontSize: 11 }
            },
            scales: {
                xAxes: [{ gridLines: { display: false } }],
                yAxes: [{ ticks: { beginAtZero: true } }]
            }
        }
    });
})();
</script>
@endif
@endsection