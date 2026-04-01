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
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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

    .form-group-modern {
        margin-bottom: 25px;
    }

    .form-label-modern {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .input-wrapper {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 18px;
        z-index: 1;
    }

    .form-control-modern {
        width: 100%;
        padding: 14px 15px 14px 45px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .form-control-modern:focus {
        outline: none;
        border-color: #667eea;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .invalid-feedback {
        display: block;
        color: #e74c3c;
        font-size: 13px;
        margin-top: 5px;
        margin-left: 5px;
    }

    .form-control-modern.is-invalid {
        border-color: #e74c3c;
        background-color: #fff5f5;
    }

    .remember-area {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 20px 0;
    }

    .custom-checkbox-modern {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .custom-checkbox-modern input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 8px;
        cursor: pointer;
        accent-color: #667eea;
    }

    .custom-checkbox-modern label {
        font-size: 14px;
        color: #555;
        cursor: pointer;
        user-select: none;
        margin: 0;
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
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login i {
        margin-left: 8px;
        transition: transform 0.3s ease;
    }

    .btn-login:hover i {
        transform: translateX(5px);
    }

    .alert-modern {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
    }

    .alert-modern i {
        margin-right: 10px;
        font-size: 18px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    @media (max-width: 576px) {
        .login-header {
            padding: 30px 20px;
        }

        .login-header h2 {
            font-size: 24px;
        }

        .login-body {
            padding: 30px 20px;
        }

        .logo-wrapper {
            width: 80px;
            height: 80px;
        }
    }
</style>

<!-- login area start -->
<div class="login-area-modern">
    <div class="login-container">
        <div class="login-header">
            <div class="logo-wrapper">
                <img src="{{ asset('backend/assets/images/media/logo.png') }}" alt="FieldPass">
            </div>
            <h2>Player Login</h2>
            <p>Welcome back! Sign in to continue</p>
        </div>

        <div class="login-body">
            @include('playerbackend.layouts.partials.messages')

            <form method="POST" action="{{ route('player.login.submit') }}">
                @csrf

                <!-- Email/Username Field -->
                <div class="form-group-modern">
                    <label for="email" class="form-label-modern">Email or Username</label>
                    <div class="input-wrapper">
                        <i class="input-icon ti-email"></i>
                        <input 
                            type="text" 
                            id="email" 
                            name="email" 
                            class="form-control-modern @error('email') is-invalid @enderror"
                            placeholder="Enter your email or username"
                            value="{{ old('email') }}"
                            autofocus
                            required>
                    </div>
                    @error('email')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group-modern">
                    <label for="password" class="form-label-modern">Password</label>
                    <div class="input-wrapper">
                        <i class="input-icon ti-lock"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control-modern @error('password') is-invalid @enderror"
                            placeholder="Enter your password"
                            required>
                    </div>
                    @error('password')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="remember-area">
                    <div class="custom-checkbox-modern">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Remember Me</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login">
                    Sign In
                    <i class="ti-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</div>
<!-- login area end -->
@endsection