<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Life Vest Tracker' }} - GMF AeroAsia</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- Prevent FOUC & allow correct initial theme evaluation for scripts (ChartJS) -->
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
    <link rel="icon" type="image/jpeg" href="{{ asset('images/cabinx.jpeg') }}">

    <!-- CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Sticky Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <!-- Left: Logo & Back Button -->
            <div class="navbar-left">
                <!-- Desktop Sidebar Toggle Button -->
                <button type="button" id="sidebarToggleDesktopBtn" class="sidebar-toggle-desktop" style="background: transparent; border: none; color: var(--text-primary); cursor: pointer; display: flex; align-items: center; justify-content: center; margin-right: 0.5rem; padding: 0.25rem;" title="Toggle Sidebar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>



                <a href="{{ route('dashboard') }}" class="navbar-brand">
                    <div class="navbar-logo-wrap" style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; overflow: hidden; background: transparent;">
                        <img src="{{ asset('images/cabinx.jpeg') }}" alt="CabinX Logo" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <span class="navbar-title">Life Vest Tracker</span>
                </a>
                <span class="navbar-badge" style="margin-left:auto;">GMF AeroAsia</span>
            </div>

            <!-- Center: Header area -->
            <div class="navbar-center">
                @yield('header-right')
            </div>

            <!-- Right: Update Info + User -->
            <div class="navbar-right">
                @if(isset($lastUpdate))
                    <div class="navbar-update">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span class="update-value">{{ $lastUpdate->format('d M Y, H:i') }}</span>
                    </div>
                @endif

                <button type="button" id="theme-toggle" class="theme-toggle-btn-nav" title="Toggle Theme">
                    <!-- Sun Icon (visible in light theme) -->
                    <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="18.36" x2="5.64" y2="16.93"></line>
                        <line x1="18.36" y1="4.22" x2="19.78" y2="5.64"></line>
                    </svg>
                    <!-- Moon Icon (visible in dark theme) -->
                    <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                @auth
                    <div class="navbar-user-menu" id="navbarUserMenu">
                        <button type="button" class="navbar-user-btn" id="navbarUserBtn" title="{{ Auth::user()->name }}">
                            <div class="navbar-user-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="navbar-user-name">{{ Auth::user()->name }}</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="navbar-user-dropdown" id="navbarUserDropdown">
                            <div class="navbar-user-dropdown-header">
                                <div style="font-weight: 600; color: var(--text-primary);">{{ Auth::user()->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ Auth::user()->email }}</div>
                                <div style="margin-top: 0.35rem;">
                                    <span class="navbar-role-badge navbar-role-{{ Auth::user()->role }}">{{ ucfirst(Auth::user()->role) }}</span>
                                </div>
                            </div>
                            <div class="navbar-user-dropdown-divider"></div>
                            <a href="{{ route('profile.settings') }}" class="navbar-user-dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Account Settings
                            </a>
                            <div class="navbar-user-dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="navbar-user-dropdown-item navbar-logout-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Spacer for fixed navbar -->
    <div class="navbar-spacer"></div>

    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <button class="sidebar-close-btn" id="sidebarCloseBtn" title="Close sidebar" style="display: none; position: absolute; top: 0.75rem; right: 0.75rem; z-index: 10;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>

            <nav class="sidebar-nav">
                <a href="{{ route('dashboard', ['view' => 'fleet-overview']) }}" class="sidebar-nav-item {{ request()->query('view') === 'fleet-overview' || !request()->query('view') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Fleet Overview</span>
                </a>
                <a href="{{ route('dashboard', ['view' => 'life-vest-summary']) }}" class="sidebar-nav-item {{ request()->query('view') === 'life-vest-summary' ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <span>Life Vest Summary</span>
                </a>
                <a href="{{ route('dashboard', ['view' => 'top-pn-insights']) }}" class="sidebar-nav-item {{ request()->query('view') === 'top-pn-insights' ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span>Top P/N Insights</span>
                </a>
                <div class="sidebar-nav-dropdown">
                    <button type="button" class="sidebar-nav-item dropdown-toggle {{ str_starts_with(request()->query('view', ''), 'replacement-') ? 'active' : '' }}" style="width: 100%; border: none; background: transparent; cursor: pointer;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        <span style="flex-grow: 1; text-align: left;">Replacement Plan</span>
                        <svg class="dropdown-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s; transform: {{ str_starts_with(request()->query('view', ''), 'replacement-') ? 'rotate(180deg)' : 'rotate(0deg)' }};"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="dropdown-submenu" style="display: {{ str_starts_with(request()->query('view', ''), 'replacement-') ? 'block' : 'none' }}; padding-left: 28px; margin-top: 4px;">
                        <a href="{{ route('dashboard', ['view' => 'replacement-weekly']) }}" class="sidebar-nav-item submenu-item {{ request()->query('view') === 'replacement-weekly' ? 'active' : '' }}" style="padding: 0.5rem 0.75rem; min-height: unset; font-size: 0.9em; margin-bottom: 2px;">Weekly</a>
                        <a href="{{ route('dashboard', ['view' => 'replacement-monthly']) }}" class="sidebar-nav-item submenu-item {{ request()->query('view') === 'replacement-monthly' ? 'active' : '' }}" style="padding: 0.5rem 0.75rem; min-height: unset; font-size: 0.9em; margin-bottom: 2px;">Monthly</a>
                        <a href="{{ route('dashboard', ['view' => 'replacement-yearly']) }}" class="sidebar-nav-item submenu-item {{ request()->query('view') === 'replacement-yearly' ? 'active' : '' }}" style="padding: 0.5rem 0.75rem; min-height: unset; font-size: 0.9em;">Yearly</a>
                    </div>
                </div>
                @if(Auth::user() && Auth::user()->isAdmin())
                <a href="{{ route('dashboard', ['view' => 'activity-log']) }}" class="sidebar-nav-item {{ request()->query('view') === 'activity-log' ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                    <span>Activity Log</span>
                </a>
                <a href="{{ route('fleet.index') }}" class="sidebar-nav-item {{ request()->routeIs('fleet.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17v-8c0-1.105-.895-2-2-2H7c-1.105 0-2 .895-2 2v8M9 8V6c0-1.105.895-2 2-2h2c1.105 0 2 .895 2 2v2m-11 0h14m-7 6v4m-4-4v4"/></svg>
                    <span>Fleet Management</span>
                </a>
                @endif

                @if(Auth::user() && Auth::user()->isSuperAdmin())
                <a href="{{ route('superadmin.users') }}" class="sidebar-nav-item {{ request()->routeIs('superadmin.users') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>User Accounts</span>
                </a>
                <a href="{{ route('superadmin.bulk-import') }}" class="sidebar-nav-item {{ request()->routeIs('superadmin.bulk-import') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>Bulk Import</span>
                </a>
                <a href="{{ route('superadmin.pdf-scan') }}" class="sidebar-nav-item {{ request()->routeIs('superadmin.pdf-scan') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 7l-7 5 7 5V7z"></path><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                    <span>Smart PDF Scanner</span>
                </a>
                @endif
            </nav>
        </aside>

        <!-- Sidebar Overlay (for mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar Toggle Button (mobile only, visible via CSS) -->
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Open sidebar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>

        <div class="app-container">
            <!-- Main Content -->
            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast">

        <span class="toast-message">Success!</span>
    </div>

    <!-- SheetJS for Excel Export -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>

    @stack('scripts')
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggleBtn = document.getElementById('theme-toggle');
            const html = document.documentElement;
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const sidebarToggleBtnEl = document.getElementById('sidebarToggleBtn');
            const sidebarToggleDesktopBtn = document.getElementById('sidebarToggleDesktopBtn');
            const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

            // Theme Toggle Click Handler
            themeToggleBtn?.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme') || 'light';
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                // Dispatch custom event for widgets/charts
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: newTheme } }));
            });

            // Desktop Sidebar Toggle
            const isDesktopSidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (isDesktopSidebarCollapsed) {
                document.body.classList.add('sidebar-collapsed');
            }

            sidebarToggleDesktopBtn?.addEventListener('click', () => {
                document.body.classList.toggle('sidebar-collapsed');
                const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', isCollapsed);
            });

            // Sidebar Mobile Toggle
            sidebarToggleBtn?.addEventListener('click', () => {
                sidebar.classList.add('open');
                sidebarOverlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            });

            sidebarCloseBtn?.addEventListener('click', () => {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            });

            sidebarOverlay?.addEventListener('click', () => {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            });

            // Close sidebar on nav item click (mobile)
            document.querySelectorAll('.sidebar-nav-item:not(.dropdown-toggle)').forEach(item => {
                item.addEventListener('click', () => {
                    if (window.innerWidth < 768) {
                        sidebar.classList.remove('open');
                        sidebarOverlay.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                });
            });

            // Sidebar Dropdown Toggle
            document.querySelectorAll('.dropdown-toggle').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const submenu = this.nextElementSibling;
                    const arrow = this.querySelector('.dropdown-arrow');
                    
                    if (submenu.style.display === 'block') {
                        submenu.style.display = 'none';
                        if (arrow) arrow.style.transform = 'rotate(0deg)';
                    } else {
                        submenu.style.display = 'block';
                        if (arrow) arrow.style.transform = 'rotate(180deg)';
                    }
                });
            });

            // Navbar User Dropdown Toggle
            const userBtn = document.getElementById('navbarUserBtn');
            const userDropdown = document.getElementById('navbarUserDropdown');
            if (userBtn && userDropdown) {
                userBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');
                });

                document.addEventListener('click', (e) => {
                    if (!userDropdown.contains(e.target) && !userBtn.contains(e.target)) {
                        userDropdown.classList.remove('show');
                    }
                });
            }
        });

        // SweetAlert2 Global Confirmation Handler
        document.addEventListener('click', function(e) {
            const button = e.target.closest('.confirm-submit');
            if (button) {
                e.preventDefault();
                const form = button.closest('form');
                const title = button.dataset.confirmTitle || 'Are you sure?';
                const text = button.dataset.confirmText || "You won't be able to revert this!";
                const icon = button.dataset.confirmIcon || 'warning';
                const confirmButtonText = button.dataset.confirmButtonText || 'Yes, proceed!';
                const confirmVariant = button.dataset.confirmVariant || 'primary'; // primary, success, danger

                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonText: confirmButtonText,
                    background: getComputedStyle(document.documentElement).getPropertyValue('--bg-card-solid').trim() || (document.documentElement.getAttribute('data-theme') === 'dark' ? '#162238' : '#ffffff'),
                    color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim() || (document.documentElement.getAttribute('data-theme') === 'dark' ? '#ffffff' : '#1e293b'),
                    reverseButtons: true,
                    padding: '2rem',
                    customClass: {
                        popup: 'swal2-premium-popup',
                        title: 'swal2-premium-title',
                        htmlContainer: 'swal2-premium-text',
                        confirmButton: `swal2-premium-confirm swal2-variant-${confirmVariant}`,
                        cancelButton: 'swal2-premium-cancel',
                        icon: 'swal2-premium-icon'
                    },
                    buttonsStyling: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>
    <style>
        .swal2-premium-popup {
            border-radius: var(--radius-lg) !important;
            border: 1px solid var(--border-subtle) !important;
            box-shadow: var(--shadow-xl) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .swal2-premium-title {
            font-size: 1.5rem !important;
            font-weight: 800 !important;
            letter-spacing: -0.03em !important;
            margin-bottom: 0.5rem !important;
            padding-top: 1rem !important;
        }
        .swal2-premium-text {
            font-size: 1rem !important;
            color: var(--text-muted) !important;
            line-height: 1.6 !important;
        }
        .swal2-premium-confirm, .swal2-premium-cancel {
            padding: 0.8rem 2.2rem !important;
            font-weight: 700 !important;
            border-radius: 12px !important;
            margin: 0.5rem !important;
            cursor: pointer !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            font-size: 0.9rem !important;
            border: none !important;
            text-transform: uppercase !important;
            letter-spacing: 0.03em !important;
        }
        
        /* Variants */
        .swal2-variant-primary {
            background-color: var(--primary) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3) !important;
        }
        .swal2-variant-success {
            background-color: var(--success) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3) !important;
        }
        .swal2-variant-danger {
            background-color: var(--danger) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
        }
        
        .swal2-premium-confirm:hover {
            filter: brightness(1.1) !important;
        }
        
        .swal2-premium-cancel {
            background-color: rgba(148, 163, 184, 0.1) !important;
            color: var(--text-secondary) !important;
            border: 1px solid var(--border-subtle) !important;
        }
        .swal2-premium-cancel:hover {
            background-color: rgba(148, 163, 184, 0.2) !important;
            color: var(--text-primary) !important;
        }
        .swal2-premium-icon {
            border-width: 3px !important;
            margin-top: 1.5rem !important;
        }
    </style>
</body>

</html>