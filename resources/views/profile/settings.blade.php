@extends('layouts.app')

@section('content')
<div class="dashboard-container" style="max-width: 800px; margin: 0 auto;">
    <div class="header-section" style="background: var(--bg-card); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
        <div>
            <h1 class="page-title" style="margin: 0; font-weight: 800; letter-spacing: -0.03em;">Account Settings</h1>
            <p class="page-subtitle" style="margin-top: 0.5rem; opacity: 0.8;">Manage your personal profile and security settings.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-top: 1.5rem; background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 1rem; border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.2); display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="margin-top: 1.5rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="card" style="margin-top: 2rem; padding: 2.5rem;">
        <!-- Profile Info (ReadOnly) -->
        <div style="margin-bottom: 3rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Profile Information
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="form-group-premium">
                    <label>Full Name</label>
                    <input type="text" class="input-premium" value="{{ $user->name }}" disabled style="background: rgba(var(--bg-dark-rgb), 0.5); cursor: not-allowed;">
                </div>
                <div class="form-group-premium">
                    <label>Email Address</label>
                    <input type="email" class="input-premium" value="{{ $user->email }}" disabled style="background: rgba(var(--bg-dark-rgb), 0.5); cursor: not-allowed;">
                </div>
                <div class="form-group-premium">
                    <label>Account Role</label>
                    <div style="margin-top: 0.5rem;">
                        <span class="navbar-role-badge navbar-role-{{ $user->role }}" style="font-size: 0.9rem; padding: 0.4rem 1rem;">{{ ucfirst($user->role) }}</span>
                    </div>
                </div>
            </div>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">To change your profile information, please contact your System Administrator.</p>
        </div>

        <div style="border-top: 1px solid var(--border-subtle); padding-top: 2.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Update Password
            </h3>

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; max-width: 500px;">
                    <div class="form-group-premium">
                        <label for="current_password">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="input-premium" required placeholder="••••••••">
                    </div>

                    <div class="form-group-premium">
                        <label for="password">New Password</label>
                        <input type="password" name="password" id="password" class="input-premium" required placeholder="Min. 8 characters">
                    </div>

                    <div class="form-group-premium">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="input-premium" required placeholder="Repeat new password">
                    </div>

                    <div style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary confirm-submit" 
                            data-confirm-title="Update Password?"
                            data-confirm-text="Your session will remain active, but you must use the new password for future logins."
                            data-confirm-icon="info"
                            data-confirm-button-text="Yes, Update Password"
                            data-confirm-variant="primary"
                            style="padding: 0.8rem 2.5rem; font-weight: 700;">
                            Change Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
