@props(['id' => '', 'title' => '', 'size' => 'md', 'class' => ''])

@php
    $widths = ['sm' => '420px', 'md' => '520px', 'lg' => '680px', 'xl' => '840px'];
    $width = $widths[$size] ?? $widths['md'];
@endphp

<div class="modal-overlay-premium" id="{{ $id }}" style="display: none;">
    <div class="modal-content-premium" style="max-width: {{ $width }};">
        <div class="modal-header-premium">
            <h2>{{ $title }}</h2>
            <button class="modal-close-premium" onclick="document.getElementById('{{ $id }}').style.display='none'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body-premium" style="padding: var(--spacing-lg);">
            {{ $slot }}
        </div>
    </div>
</div>
