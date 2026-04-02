@extends('playerbackend.auth.auth_master')

@section('auth_title')
    Login | Player Dashboard Panel
@endsection

@section('auth-content')
<style>
    .login-area-modern {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px 20px;
    }

    .login-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        max-width: 450px;
        width: 100%;
        animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .login-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px 30px;
        text-align: center;
        color: white;
    }

    .logo-wrapper {
        width: 100px;
        height: 100px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        padding: 15px;
    }

    .logo-wrapper img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .login-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: white;
    }

    .login-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 14px;
    }

    .login-body {
        padding: 40px 30px;
    }

    .form-group-modern { margin-bottom: 22px; }
    .form-label-modern {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .phone-row {
        display: flex;
        align-items: stretch;
        gap: 0;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }
    .phone-row:focus-within {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    .phone-prefix {
        flex: 0 0 auto;
        padding: 12px 14px;
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
        border-right: 2px solid #e0e0e0;
    }
    .phone-input-wrap { flex: 1; }
    .phone-input-wrap .otp-phone-input {
        width: 100%;
        border: none;
        border-radius: 0;
        padding: 12px 16px;
        font-size: 15px;
        background: #f8f9fa;
    }
    .phone-input-wrap .otp-phone-input:focus {
        outline: none;
        background: #fff;
    }
    .phone-readonly {
        flex: 1;
        padding: 12px 16px;
        font-weight: 600;
        color: #333;
        background: #f8f9fa;
    }

    .form-hint {
        display: block;
        margin-top: 8px;
        font-size: 12px;
        color: #666;
    }
    .form-hint a { color: #667eea; font-weight: 600; }

    .otp-input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }
    .otp-input:focus {
        outline: none;
        border-color: #667eea;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    .otp-input.is-invalid { border-color: #e74c3c; background-color: #fff5f5; }

    .invalid-feedback, .error-message {
        display: block;
        color: #e74c3c;
        font-size: 13px;
        margin-top: 6px;
    }

    .btn-login {
        width: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 15px;
        font-weight: 600;
        font-size: 16px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    @media (max-width: 576px) {
        .login-header { padding: 30px 20px; }
        .login-header h2 { font-size: 24px; }
        .login-body { padding: 30px 20px; }
        .logo-wrapper { width: 80px; height: 80px; }
    }
</style>

<div class="login-area-modern">
    <div class="login-container">
        <div class="login-header">
            <div class="logo-wrapper">
                <img src="{{ asset('backend/assets/images/media/logo.png') }}" alt="FieldPass">
            </div>
            <h2>Player Login</h2>
            <p>Sign in with mobile OTP (+60)</p>
        </div>

        <div class="login-body">
            @include('playerbackend.layouts.partials.messages')

            @if(empty($otp_sent))
            <form method="POST" action="{{ route('player.login.send-otp') }}" id="send-otp-form" autocomplete="on">
                @csrf
                <input type="hidden" name="country_code" value="+60">

                <div class="form-group-modern">
                    <label for="phone" class="form-label-modern">Mobile number</label>
                    <div class="phone-row">
                        <span class="phone-prefix" aria-hidden="true">+60</span>
                        <div class="phone-input-wrap">
                            <input
                                type="tel"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                id="phone"
                                name="phone"
                                class="otp-phone-input @error('phone') is-invalid @enderror"
                                placeholder="e.g. 123456789"
                                value="{{ old('phone') }}"
                                required
                                maxlength="15"
                                autocomplete="tel-national"
                            >
                        </div>
                    </div>
                    <small class="form-hint">Malaysia (+60) only. Numbers only, no spaces.</small>
                    @error('phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-login">
                    Send OTP
                    <i class="ti-arrow-right"></i>
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('player.login.verify-otp') }}" id="verify-otp-form" autocomplete="one-time-code">
                @csrf
                <input type="hidden" name="country_code" value="+60">
                <input type="hidden" name="phone" value="{{ old('phone', $pending_phone ?? '') }}">

                <div class="form-group-modern">
                    <span class="form-label-modern">Mobile number</span>
                    <div class="phone-row">
                        <span class="phone-prefix">+60</span>
                        <div class="phone-readonly">{{ old('phone', $pending_phone ?? '') }}</div>
                    </div>
                    <p class="form-hint"><a href="{{ route('player.login', ['change_phone' => 1]) }}">Use a different number</a></p>
                </div>

                <div class="form-group-modern">
                    <label for="otp" class="form-label-modern">OTP code</label>
                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        id="otp"
                        name="otp"
                        class="otp-input @error('otp') is-invalid @enderror"
                        placeholder="Enter OTP"
                        value="{{ old('otp') }}"
                        required
                        maxlength="10"
                        autocomplete="one-time-code"
                    >
                    @error('otp')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-login">
                    Verify &amp; Sign In
                    <i class="ti-arrow-right"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var sendForm = document.getElementById('send-otp-form');
    if (sendForm) {
        sendForm.addEventListener('submit', function () {
            var el = document.getElementById('phone');
            if (el) el.value = (el.value || '').replace(/\D/g, '').trim();
        });
    }
    var verifyForm = document.getElementById('verify-otp-form');
    if (verifyForm) {
        verifyForm.addEventListener('submit', function () {
            var phone = verifyForm.querySelector('input[name="phone"]');
            var otp = document.getElementById('otp');
            if (phone) phone.value = (phone.value || '').replace(/\D/g, '').trim();
            if (otp) otp.value = (otp.value || '').replace(/\D/g, '').trim();
        });
    }
})();
</script>
@endsection
