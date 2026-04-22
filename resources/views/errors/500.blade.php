@extends('layouts.app')

@section('content')
<div style="height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 2rem;">
    <div style="position: relative; margin-bottom: 2rem;">
        <h1 style="font-size: 12rem; font-weight: 900; margin: 0; line-height: 1; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; opacity: 0.15;">500</h1>
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%;">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #ef4444; margin-bottom: 1rem;">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            <h2 style="font-size: 2.5rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.02em;">System Error</h2>
        </div>
    </div>
    
    <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 500px; line-height: 1.6; margin-bottom: 2.5rem;">
        Something went wrong on our end. We've been notified and are working to fix it. Please try again in a few moments.
    </p>

    <div style="display: flex; gap: 1rem;">
        <button onclick="window.location.reload()" class="btn btn-secondary btn-lg" style="padding: 1rem 2rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Try Refreshing
        </button>
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg" style="padding: 1rem 2.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem; box-shadow: var(--shadow-md);">
            Back to Dashboard
        </a>
    </div>
</div>
@endsection
