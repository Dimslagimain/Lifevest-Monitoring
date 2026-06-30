@extends('layouts.app')

@section('content')
<div style="height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 2rem;">
    <div style="position: relative; margin-bottom: 2rem;">
        <h1 style="font-size: 12rem; font-weight: 900; margin: 0; line-height: 1; background: linear-gradient(135deg, #ef4444 0%, #f97316 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; opacity: 0.15;">403</h1>
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%;">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #ef4444; margin-bottom: 1rem;">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <h2 style="font-size: 2.5rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.02em;">Access Denied</h2>
        </div>
    </div>
    
    <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 500px; line-height: 1.6; margin-bottom: 2.5rem;">
        You don't have permission to access this page. If you believe this is a mistake, please contact your administrator.
    </p>

    <div style="display: flex; gap: 1rem;">
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg" style="padding: 1rem 2.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem; box-shadow: var(--shadow-md);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Dashboard
        </a>
    </div>
</div>
@endsection
