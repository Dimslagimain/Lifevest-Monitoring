@extends('layouts.app')

@section('content')
    <div class="dashboard-container" style="padding: 2rem;">
        <div class="header-section" style="background: var(--bg-card); padding: 2.5rem; border-radius: var(--radius-xl); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; font-weight: 800; letter-spacing: -0.03em; font-size: 1.75rem;">Batch Data Entry</h1>
                <p style="margin-top: 0.5rem; opacity: 0.7; font-size: 0.95rem;">
                    Aircraft: <span style="font-weight: 700; color: var(--primary);">{{ $registration }}</span> · 
                    Layout: <span style="font-weight: 700; color: var(--text-primary);">{{ $aircraft->layout }}</span>
                </p>
            </div>
            <a href="{{ route('aircraft.show', $registration) }}" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; font-weight: 600;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 4px;"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Map
            </a>
        </div>

        <div style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.1); padding: 1.25rem 2rem; margin-bottom: 2.5rem; border-radius: var(--radius-lg); display: flex; align-items: flex-start; gap: 1rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="margin-top: 2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <div>
                <p style="color: var(--primary-light); font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Quick Instructions:</p>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0; line-height: 1.6;">Copy expiry columns from Excel and paste here. Valid formats: <strong>Oct-25</strong>, <strong>24-Jan-25</strong>, or <strong>01/03/2030</strong>.</p>
            </div>
        </div>

        <form action="{{ route('aircraft.storeBatchInput', $registration) }}" method="POST">
            @csrf

            {{-- Sections --}}
            @foreach($sections as $sectionIndex => $section)
                <div style="background: var(--bg-card); padding: 2rem; border-radius: var(--radius-xl); margin-bottom: 2.5rem; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
                    <div style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem; display: flex; justify-content: space-between; align-items: baseline;">
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
                            {{ $section['name'] }}
                        </h2>
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                            {{ count($section['rows']) }} Rows Found
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1.5rem;">
                        @foreach($section['columns'] as $col)
                            <div class="form-group-premium" style="margin-bottom: 0;">
                                <label style="text-align: center; font-size: 0.9rem; background: rgba(59, 130, 246, 0.1); color: var(--primary-light); padding: 6px; border-radius: 6px; display: block; margin-bottom: 12px; border: 1px solid rgba(59, 130, 246, 0.15);">Column {{ $col }}</label>
                                <textarea name="section_{{ $sectionIndex }}_col_{{ $col }}"
                                    id="section_{{ $sectionIndex }}_col_{{ $col }}" rows="{{ min(count($section['rows']), 15) }}"
                                    class="input-premium"
                                    style="padding: 1rem; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; height: 350px; scrollbar-width: thin;"
                                    placeholder="Paste data..."></textarea>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Spare/Infant Section --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 3rem;">
                <div style="background: var(--bg-card); padding: 2rem; border-radius: var(--radius-xl); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
                    <div style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary);">Adult SPARE</h2>
                        <p style="font-size: 0.85rem; opacity: 0.6; margin-top: 0.25rem;">Global spare vests (PAX)</p>
                    </div>
                    <textarea name="pax_dates" id="pax_dates" rows="12" class="input-premium"
                        style="padding: 1.25rem; font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; height: 300px;"
                        placeholder="Paste expiry dates list..."></textarea>
                </div>

                <div style="background: var(--bg-card); padding: 2rem; border-radius: var(--radius-xl); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
                    <div style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary);">Infant Vests (INF)</h2>
                        <p style="font-size: 0.85rem; opacity: 0.6; margin-top: 0.25rem;">Standard infant distribution</p>
                    </div>
                    <textarea name="inf_dates" id="inf_dates" rows="12" class="input-premium"
                        style="padding: 1.25rem; font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; height: 300px;"
                        placeholder="Paste expiry dates list..."></textarea>
                </div>
            </div>

            {{-- Sticky Footer --}}
            <div style="display: flex; justify-content: flex-end; gap: 1rem; position: sticky; bottom: 2rem; background: rgba(12, 18, 34, 0.8); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); padding: 1.25rem 2rem; border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 12px 40px rgba(0,0,0,0.3); z-index: 100;">
                <a href="{{ route('aircraft.show', $registration) }}" class="btn btn-secondary" style="padding: 1rem 2rem; font-weight: 600;">Discard Changes</a>
                <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 800; font-size: 1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2zM17 21v-8H7v8M7 3v5h8"/></svg>
                    Save & Process Batch Data
                </button>
            </div>
        </form>
    </div>
@endsection