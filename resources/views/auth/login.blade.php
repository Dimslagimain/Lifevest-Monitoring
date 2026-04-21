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

    @vite(['resources/css/auth.css'])
</head>

<body>
    <div class="auth-page">
        <!-- Animated background -->
        <div class="auth-bg"></div>
        <div class="auth-bg-grid"></div>

        <!-- Login card -->
        <div class="auth-card">
            <!-- Branding -->
            <div class="auth-brand">
                <div class="auth-brand-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h1>Life Vest Tracker</h1>
                <p>Fleet Management System</p>
                <span class="auth-brand-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    GMF AeroAsia
                </span>
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
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@tnp.com" required autofocus autocomplete="email">
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="password" required autocomplete="current-password">
                </div>

                <div class="auth-remember">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="auth-submit" id="loginSubmit">
                    Masuk
                </button>
            </form>

            <!-- Footer -->
            <div class="auth-footer">
                <p>&copy; {{ date('Y') }} GMF AeroAsia &mdash; Life Vest Tracker</p>
            </div>
        </div>
    </div>
</body>

</html>
