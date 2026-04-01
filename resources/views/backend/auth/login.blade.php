@extends('backend.auth.auth_master')

@section('auth_title')
    Login | Football Association Admin
@endsection

@section('auth-content')
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header Section -->
            <div class="auth-header">
                <div class="logo-container">
                    <img src="{{ asset('backend/assets/images/media/logo.png') }}" alt="Football Association" class="auth-logo">
                </div>
                <h1 class="auth-title">Football Association</h1>
                <p class="auth-subtitle">Admin Panel Login</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('admin.login.submit') }}" class="auth-form">
                @csrf
                
                @include('backend.layouts.partials.messages')

                <div class="form-group">
                    <label for="email" class="form-label">Email or Username</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="email" 
                            name="email" 
                            class="form-input @error('email') error @enderror"
                            placeholder="Enter your email or username"
                            value="{{ old('email') }}"
                            required
                        >
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                        </svg>
                    </div>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input @error('password') error @enderror"
                            placeholder="Enter your password"
                            required
                        >
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                </div>

                <button type="submit" class="auth-button">
                    <span>Sign In</span>
                    <svg class="button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </button>
            </form>

            <!-- Footer -->
            <div class="auth-footer">
                <p>&copy; {{ date('Y') }} Football Association. All rights reserved.</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.auth-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.auth-container {
    width: 100%;
    max-width: 420px;
}

.auth-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

/* Header Styles */
.auth-header {
    text-align: center;
    padding: 40px 30px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.logo-container {
    margin-bottom: 20px;
}

.auth-logo {
    width: 120px;
    height: 120px;
    object-fit: contain;
}

.auth-title {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}

.auth-subtitle {
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
}

/* Form Styles */
.auth-form {
    padding: 30px;
}

.form-group {
    margin-bottom: 24px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.input-wrapper {
    position: relative;
}

.form-input {
    width: 100%;
    padding: 12px 16px 12px 45px;
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

.form-input.error {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-input::placeholder {
    color: #9ca3af;
}

.input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: #6b7280;
    pointer-events: none;
}

.error-message {
    display: block;
    margin-top: 6px;
    font-size: 13px;
    color: #ef4444;
    font-weight: 500;
}

/* Checkbox Styles */
.form-options {
    margin-bottom: 30px;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #374151;
    cursor: pointer;
    user-select: none;
}

.checkbox-wrapper input[type="checkbox"] {
    display: none;
}

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

/* Button Styles */
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
    outline: none;
}

.auth-button:hover {
    background: #5a67d8;
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.auth-button:active {
    transform: translateY(0);
}

.button-icon {
    width: 18px;
    height: 18px;
}

/* Footer Styles */
.auth-footer {
    padding: 20px 30px;
    text-align: center;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.auth-footer p {
    font-size: 12px;
    color: #6b7280;
}

/* Messages Styles */
.alert {
    margin-bottom: 20px;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
}

.alert-success {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert-info {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

/* Responsive Design */
@media (max-width: 480px) {
    .auth-wrapper {
        padding: 15px;
    }
    
    .auth-header,
    .auth-form {
        padding: 20px;
    }
    
    .auth-title {
        font-size: 20px;
    }
    
    .form-input {
        font-size: 16px; /* Prevent zoom on iOS */
    }
}

/* Loading State */
.auth-button:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.auth-button:disabled:hover {
    background: #9ca3af;
    transform: none;
    box-shadow: none;
}
</style>
@endsection