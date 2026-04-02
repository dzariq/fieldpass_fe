@extends('backend.auth.auth_master')

@section('auth_title')
    Login | Football Association Admin
@endsection

@section('auth-content')
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-container">
                    <img src="{{ asset('backend/assets/images/media/logo.png') }}" alt="Football Association" class="auth-logo">
                </div>
                <h1 class="auth-title">Football Association</h1>
                <p class="auth-subtitle">Admin Panel — Sign in with OTP</p>
            </div>

            @include('backend.layouts.partials.messages')

            {{-- Step 1: request OTP --}}
            @if(empty($otp_sent))
            <form method="POST" action="{{ route('admin.login.send-otp') }}" class="auth-form" id="send-otp-form" autocomplete="on">
                @csrf
                <input type="hidden" name="country_code" value="+60">

                <div class="form-group">
                    <label for="phone" class="form-label">Mobile number</label>
                    <div class="phone-row">
                        <span class="phone-prefix" aria-hidden="true">+60</span>
                        <div class="input-wrapper phone-input-wrap">
                            <input
                                type="tel"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                id="phone"
                                name="phone"
                                class="form-input @error('phone') error @enderror"
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

                <button type="submit" class="auth-button">
                    <span>Send OTP</span>
                    <svg class="button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
            @else
            {{-- Step 2: verify OTP --}}
            <form method="POST" action="{{ route('admin.login.verify-otp') }}" class="auth-form" id="verify-otp-form" autocomplete="one-time-code">
                @csrf
                <input type="hidden" name="country_code" value="+60">
                <input type="hidden" name="phone" value="{{ old('phone', $pending_phone ?? '') }}">

                <div class="form-group">
                    <label class="form-label">Mobile number</label>
                    <div class="phone-row">
                        <span class="phone-prefix">+60</span>
                        <div class="phone-readonly">{{ old('phone', $pending_phone ?? '') }}</div>
                    </div>
                    <p class="form-hint"><a href="{{ route('admin.login') }}">Use a different number</a></p>
                </div>

                <div class="form-group">
                    <label for="otp" class="form-label">OTP code</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            id="otp"
                            name="otp"
                            class="form-input @error('otp') error @enderror"
                            placeholder="Enter OTP"
                            value="{{ old('otp') }}"
                            required
                            maxlength="10"
                            autocomplete="one-time-code"
                        >
                    </div>
                    @error('otp')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                </div>

                <button type="submit" class="auth-button">
                    <span>Verify &amp; Sign In</span>
                    <svg class="button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </button>
            </form>
            @endif

            <div class="auth-footer">
                <p>&copy; {{ date('Y') }} Football Association. All rights reserved.</p>
            </div>
        </div>
    </div>
</div>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
.auth-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.auth-container { width: 100%; max-width: 420px; }
.auth-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}
.auth-header {
    text-align: center;
    padding: 40px 30px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}
.logo-container { margin-bottom: 20px; }
.auth-logo { width: 120px; height: 120px; object-fit: contain; }
.auth-title { font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 8px; }
.auth-subtitle { font-size: 14px; color: #6b7280; font-weight: 500; }
.auth-form { padding: 30px; }
.form-group { margin-bottom: 24px; }
.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}
.phone-row {
    display: flex;
    align-items: stretch;
    gap: 0;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}
.phone-prefix {
    flex: 0 0 auto;
    padding: 12px 14px;
    background: #f3f4f6;
    font-weight: 600;
    color: #374151;
    border-right: 1px solid #e5e7eb;
}
.phone-input-wrap { flex: 1; position: relative; }
.phone-input-wrap .form-input {
    border: none;
    border-radius: 0;
    padding-left: 16px;
}
.phone-row:focus-within {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
.phone-readonly {
    flex: 1;
    padding: 12px 16px;
    font-weight: 600;
    color: #1f2937;
    background: #f9fafb;
}
.form-hint {
    display: block;
    margin-top: 8px;
    font-size: 12px;
    color: #6b7280;
}
.form-hint a { color: #667eea; }
.input-wrapper { position: relative; }
.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    color: #1f2937;
    background: #ffffff;
    transition: all 0.3s ease;
    outline: none;
}
.form-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
.form-input.error { border-color: #ef4444; }
.error-message {
    display: block;
    margin-top: 6px;
    font-size: 13px;
    color: #ef4444;
    font-weight: 500;
}
.form-options { margin-bottom: 24px; }
.checkbox-wrapper {
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #374151;
    cursor: pointer;
    user-select: none;
}
.checkbox-wrapper input[type="checkbox"] { display: none; }
.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 4px;
    margin-right: 10px;
    position: relative;
    transition: all 0.3s ease;
}
.checkbox-wrapper input[type="checkbox"]:checked + .checkmark {
    background: #667eea;
    border-color: #667eea;
}
.checkbox-wrapper input[type="checkbox"]:checked + .checkmark::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 1px;
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
.auth-button {
    width: 100%;
    background: #667eea;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 14px 20px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}
.auth-button:hover { background: #5a67d8; }
.button-icon { width: 18px; height: 18px; }
.auth-footer {
    padding: 20px 30px;
    text-align: center;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}
.auth-footer p { font-size: 12px; color: #6b7280; }
</style>
@endsection

@section('scripts')
<script>
(function () {
    var sendForm = document.getElementById('send-otp-form');
    if (sendForm) {
        sendForm.addEventListener('submit', function () {
            var el = document.getElementById('phone');
            if (el) {
                el.value = (el.value || '').replace(/\D/g, '').trim();
            }
        });
    }
    var verifyForm = document.getElementById('verify-otp-form');
    if (verifyForm) {
        verifyForm.addEventListener('submit', function () {
            var phone = verifyForm.querySelector('input[name="phone"]');
            var otp = document.getElementById('otp');
            if (phone) {
                phone.value = (phone.value || '').replace(/\D/g, '').trim();
            }
            if (otp) {
                otp.value = (otp.value || '').replace(/\D/g, '').trim();
            }
        });
    }
})();
</script>
@endsection
