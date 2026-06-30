@extends('layouts.app')

@section('content')
<div style="height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 2rem;">
    <div style="position: relative; margin-bottom: 2rem;">
        <h1 style="font-size: 12rem; font-weight: 900; margin: 0; line-height: 1; background: linear-gradient(135deg, #f59e0b 0%, #eab308 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; opacity: 0.15;">419</h1>
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%;">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #f59e0b; margin-bottom: 1rem;">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <h2 style="font-size: 2.5rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.02em;">Session Expired</h2>
        </div>
    </div>
    
    <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 500px; line-height: 1.6; margin-bottom: 2.5rem;">
        Your session has expired due to inactivity. Please log in again to continue.
    </p>

    <div style="display: flex; gap: 1rem;">
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg" style="padding: 1rem 2.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem; box-shadow: var(--shadow-md);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Log In Again
        </a>
    </div>
</div>
@endsection
