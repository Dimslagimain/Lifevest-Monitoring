<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Life Vest Tracker</title>
    <meta name="description" content="Login to Life Vest Tracker - GMF AeroAsia Fleet Management System">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Prevent FOUC -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme) {
                    document.documentElement.setAttribute('data-theme', savedTheme);
                } else {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/gmflogo.png') }}">

    @vite(['resources/css/auth.css'])
</head>

<body>
    <div class="auth-split-container">
        <!-- Left Side: Image & Branding -->
        <div class="auth-image-side">
            <div class="auth-logo-overlay">
                <img src="{{ asset('images/gmflogo.png') }}" alt="GMF AeroAsia Logo" class="auth-large-logo">
            </div>
            <div class="auth-image-content">
                <div class="auth-image-badge">Aviation Maintenance Portal</div>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="auth-form-side">
            <!-- Theme Toggle -->
            <button class="theme-toggle" id="themeToggle" title="Toggle Theme">
                <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>

            <div class="auth-form-container">
                <div class="auth-form-header">
                    <span class="welcome-accent">Welcome</span>
                    <h1>Sign in</h1>
                    <p>Enter your credentials to access Life Vest Tracker</p>
                </div>

                <!-- Error message -->
                @if ($errors->any())
                    <div class="auth-error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Login form -->
                <form class="auth-form" method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    <div class="auth-field">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="user@example.com" required autofocus autocomplete="email">
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" id="passwordToggle" title="Show Password">
                                <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        <!-- Caps Lock Warning -->
                        <div id="capsLockWarning" class="caps-warning" style="display: none;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>Caps Lock is ON</span>
                        </div>
                    </div>

                    <div class="auth-remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me on this device</label>
                    </div>

                    <button type="submit" class="auth-submit" id="loginSubmit">
                        <span class="btn-text">Sign in</span>
                        <div class="btn-loader" style="display: none;"></div>
                    </button>
                </form>

                <div class="auth-form-footer">
                    <p>&copy; {{ date('Y') }} GMF AeroAsia &mdash; Fleet Management</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginSubmit = document.getElementById('loginSubmit');
            const btnText = loginSubmit.querySelector('.btn-text');
            const btnLoader = loginSubmit.querySelector('.btn-loader');
            const passwordInput = document.getElementById('password');
            const capsLockWarning = document.getElementById('capsLockWarning');

            // Theme Toggle
            const themeToggle = document.getElementById('themeToggle');
            const html = document.documentElement;

            function updateTheme(theme) {
                html.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
            }

            themeToggle.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                updateTheme(newTheme);
            });

            // Password Toggle logic
            const passwordToggle = document.getElementById('passwordToggle');
            const eyeOn = passwordToggle.querySelector('.eye-on');
            const eyeOff = passwordToggle.querySelector('.eye-off');

            passwordToggle.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'text') {
                    eyeOn.style.display = 'block';
                    eyeOff.style.display = 'none';
                    passwordToggle.title = "Hide Password";
                } else {
                    eyeOn.style.display = 'none';
                    eyeOff.style.display = 'block';
                    passwordToggle.title = "Show Password";
                }
            });

            // Caps Lock Detection
            passwordInput.addEventListener('keyup', function(event) {
                if (event.getModifierState('CapsLock')) {
                    capsLockWarning.style.display = 'flex';
                } else {
                    capsLockWarning.style.display = 'none';
                }
            });

            // Loading state on submit
            loginForm.addEventListener('submit', function() {
                loginSubmit.disabled = true;
                btnText.style.display = 'none';
                btnLoader.style.display = 'block';
            });
        });
    </script>
</body>

</html>
