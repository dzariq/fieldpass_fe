<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Registration - FieldPass</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .register-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }

        .register-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: white;
        }

        .register-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .register-body {
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

        .required-field::after {
            content: " *";
            color: #e74c3c;
            font-weight: bold;
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

        .form-control-modern[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
            color: #666;
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

        .helper-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            margin-left: 5px;
            display: flex;
            align-items: center;
        }

        .helper-text i {
            margin-right: 5px;
            color: #667eea;
            font-size: 12px;
        }

        .btn-register {
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
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .password-strength {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { 
            width: 33%; 
            background: #e74c3c; 
        }

        .strength-medium { 
            width: 66%; 
            background: #f39c12; 
        }

        .strength-strong { 
            width: 100%; 
            background: #27ae60; 
        }

        @media (max-width: 576px) {
            .register-header {
                padding: 30px 20px;
            }

            .register-header h2 {
                font-size: 24px;
            }

            .register-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="register-icon">⚽</div>
            <h2>Player Registration</h2>
            <p>Complete your profile to get started</p>
        </div>

        <div class="register-body">
            <form method="POST" action="{{ route('playerregister.submit') }}" id="registerForm">
                @csrf
                <input type="hidden" name="code" value="{{ old('code', $user->code) }}">

                <!-- Name Field -->
                <div class="form-group-modern">
                    <label for="name" class="form-label-modern required-field">Full Name</label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-user"></i>
                        <input 
                            id="name" 
                            type="text" 
                            class="form-control-modern @error('name') is-invalid @enderror" 
                            name="name" 
                            value="{{ old('name', $user->name) }}" 
                            required 
                            autocomplete="name" 
                            autofocus
                            placeholder="Enter your full name">
                    </div>
                    @error('name')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="form-group-modern">
                    <label for="email" class="form-label-modern">Email Address</label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-envelope"></i>
                        <input 
                            id="email" 
                            type="email" 
                            class="form-control-modern @error('email') is-invalid @enderror" 
                            name="email" 
                            value="{{ old('email', $user->email) }}" 
                            autocomplete="email"
                            placeholder="your.email@example.com">
                    </div>
                    <small class="helper-text">
                        <i class="fas fa-info-circle"></i> Optional - for account recovery
                    </small>
                    @error('email')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <!-- Username Field -->
                <div class="form-group-modern">
                    <label for="username" class="form-label-modern">Username</label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-at"></i>
                        <input 
                            id="username" 
                            type="text" 
                            readonly 
                            class="form-control-modern" 
                            name="username" 
                            value="{{ old('username', $user->username) }}" 
                            autocomplete="username">
                    </div>
                    <small class="helper-text">
                        <i class="fas fa-lock"></i> Auto-generated username
                    </small>
                </div>

                <!-- Password Field -->
                <div class="form-group-modern">
                    <label for="password" class="form-label-modern required-field">Password</label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-lock"></i>
                        <input 
                            id="password" 
                            type="password" 
                            class="form-control-modern @error('password') is-invalid @enderror" 
                            name="password" 
                            required 
                            autocomplete="new-password"
                            placeholder="Create a strong password">
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <small class="helper-text" id="strengthText">
                        <i class="fas fa-info-circle"></i> Minimum 6 characters
                    </small>
                    @error('password')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group-modern">
                    <label for="password-confirm" class="form-label-modern required-field">Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-lock"></i>
                        <input 
                            id="password-confirm" 
                            type="password" 
                            class="form-control-modern" 
                            name="password_confirmation" 
                            required 
                            autocomplete="new-password"
                            placeholder="Re-enter your password">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Complete Registration
                </button>
            </form>
        </div>
    </div>

    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;

            strengthBar.className = 'password-strength-bar';
            
            if (password.length === 0) {
                strengthText.innerHTML = '<i class="fas fa-info-circle"></i> Minimum 6 characters';
                strengthText.style.color = '#666';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                strengthText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Weak password';
                strengthText.style.color = '#e74c3c';
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
                strengthText.innerHTML = '<i class="fas fa-check-circle"></i> Medium strength';
                strengthText.style.color = '#f39c12';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.innerHTML = '<i class="fas fa-shield-alt"></i> Strong password';
                strengthText.style.color = '#27ae60';
            }
        });

        // Password match validation
        const confirmPassword = document.getElementById('password-confirm');
        const form = document.getElementById('registerForm');

        form.addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPassword.focus();
            }
        });
    </script>
</body>
</html>