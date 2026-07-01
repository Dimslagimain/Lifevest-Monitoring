@props(['type' => 'success', 'message' => '', 'dismissible' => false])

@php
    $isSuccess = $type === 'success';
    $bgColor = $isSuccess ? 'rgba(34, 197, 94, 0.1)' : 'rgba(239, 68, 68, 0.1)';
    $textColor = $isSuccess ? '#22c55e' : '#ef4444';
    $borderColor = $isSuccess ? 'rgba(34, 197, 94, 0.2)' : 'rgba(239, 68, 68, 0.2)';
@endphp

@if($message || !$slot->isEmpty())
    <div class="alert alert-{{ $type }}" style="margin-top: 1.5rem; background: {{ $bgColor }}; color: {{ $textColor }}; padding: 1rem; border-radius: 8px; border: 1px solid {{ $borderColor }}; display: flex; align-items: center; gap: 0.5rem;">
        @if($isSuccess)
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        @else
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        @endif
        <span style="flex: 1;">{{ $message ?: $slot }}</span>
    </div>
@endif
