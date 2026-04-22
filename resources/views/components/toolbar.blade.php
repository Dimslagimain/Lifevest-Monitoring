@props(['registration' => null])

<!-- Toolbar Component -->
<div class="toolbar">
    <div class="toolbar-left">
        @if(auth()->user() && auth()->user()->isAdmin())
            <button class="btn-premium" id="btnSetDate" disabled>
                Set Date
            </button>
            <div class="divider"></div>
            <button class="btn-premium" id="btnClearSelection"
                style="background: transparent; border: 1px solid transparent; box-shadow: none;">
                Clear Selection
            </button>
            <div class="divider"></div>
            <span class="selection-info" id="selectionInfo">No seats selected</span>
        @else
            <span class="selection-info" style="opacity: 0.7;">View only</span>
        @endif
    </div>
    <div class="toolbar-right">
        @if(auth()->user() && auth()->user()->isAdmin())
            <p class="toolbar-hint">Klik nomor baris atau huruf kolom untuk select cepat</p>
        @endif
        @if($registration && Route::has('reports.pdf'))
            <div class="divider"></div>
            <a href="{{ route('reports.pdf', $registration) }}" target="_blank" class="btn-premium-download">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export PDF
            </a>
        @endif
        @if($registration && Route::has('reports.blank'))
            <a href="{{ route('reports.blank', $registration) }}" target="_blank" class="btn-premium-download">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Blank Form
            </a>
        @endif
        @if(auth()->user() && auth()->user()->isAdmin())
            @if($registration && Route::has('aircraft.batchInput'))
                <a href="{{ route('aircraft.batchInput', $registration) }}" class="btn-premium btn-premium-warning">
                    Batch Input
                </a>
            @endif
        @endif
    </div>
</div>