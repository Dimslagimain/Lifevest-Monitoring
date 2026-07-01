@props(['title' => '', 'subtitle' => '', 'class' => ''])

<div class="header-section {{ $class }}" style="background: var(--bg-card); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
    <div>
        <h1 class="page-title" style="margin: 0; font-weight: 800; letter-spacing: -0.03em;">{{ $title }}</h1>
        @if($subtitle)
            <p class="page-subtitle" style="margin-top: 0.5rem; opacity: 0.8;">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="header-actions">
            {{ $actions }}
        </div>
    @endif
</div>
