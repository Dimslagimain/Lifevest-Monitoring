@props(['padding' => '1.5rem', 'class' => '', 'radius' => '12px', 'shadow' => false])

<div class="card {{ $class }}" style="background: var(--bg-card); border-radius: {{ $radius }}; border: 1px solid var(--border-subtle);{{ $shadow ? ' box-shadow: var(--shadow-sm);' : '' }} padding: {{ $padding }};">
    {{ $slot }}
</div>
