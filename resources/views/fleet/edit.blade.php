@extends('layouts.app')

@section('content')
    <div class="form-container-wide" style="max-width: 600px; margin: 0 auto; padding-top: 2rem;">
        <div class="header-section" style="margin-bottom: 2rem; text-align: center;">
            <h2 class="form-header" style="margin: 0; font-weight: 800; letter-spacing: -0.03em;">Edit Aircraft: {{ $aircraft->registration }}</h2>
            <p style="margin-top: 0.5rem; opacity: 0.7;">Update part numbers and operational status for this aircraft.</p>
        </div>

        <form action="{{ route('fleet.update', $aircraft->id) }}" method="POST" class="form-card" style="background: var(--bg-card); padding: 2.5rem; border-radius: var(--radius-xl); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg);">
            @csrf
            @method('PUT')

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group-premium">
                    <label>Airline</label>
                    <div style="position: relative;">
                        <input type="text" value="{{ $aircraft->airline?->name }}" readonly class="input-premium" style="background: rgba(0,0,0,0.2); cursor: not-allowed; opacity: 0.8;">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; opacity: 0.5;">🔒 LOCKED</span>
                    </div>
                </div>

                <div class="form-group-premium">
                    <label>Registration</label>
                    <div style="position: relative;">
                        <input type="text" value="{{ $aircraft->registration }}" readonly class="input-premium" style="background: rgba(0,0,0,0.2); cursor: not-allowed; opacity: 0.8;">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; opacity: 0.5;">🔒 LOCKED</span>
                    </div>
                </div>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group-premium">
                    <label>Aircraft Type</label>
                    <input type="text" value="{{ $aircraft->type }}" readonly class="input-premium" style="background: rgba(0,0,0,0.2); cursor: not-allowed; opacity: 0.8; text-transform: uppercase;">
                </div>

                <div class="form-group-premium">
                    <label>Layout Code</label>
                    <input type="text" value="{{ $aircraft->layout }}" readonly class="input-premium" style="background: rgba(0,0,0,0.2); cursor: not-allowed; opacity: 0.8;">
                </div>
            </div>

            <div style="height: 1px; background: var(--border-subtle); margin: 1rem 0 2rem;"></div>

            <div class="form-group-premium">
                <label>🔧 Part Number - Adult (Passenger)</label>
                <input type="text" name="pn_adult" value="{{ old('pn_adult', $aircraft->pn_adult) }}" class="input-premium"
                    placeholder="e.g. P0723-103W" style="text-transform: uppercase; border-color: rgba(59, 130, 246, 0.3);">
            </div>

            <div class="form-group-premium">
                <label>🔧 Part Number - Crew</label>
                <input type="text" name="pn_crew" value="{{ old('pn_crew', $aircraft->pn_crew) }}" class="input-premium"
                    placeholder="e.g. P0723-103WCN" style="text-transform: uppercase; border-color: rgba(59, 130, 246, 0.3);">
            </div>

            <div class="form-group-premium">
                <label>🔧 Part Number - Infant</label>
                <input type="text" name="pn_infant" value="{{ old('pn_infant', $aircraft->pn_infant) }}" class="input-premium"
                    placeholder="e.g. P0640-101" style="text-transform: uppercase; border-color: rgba(59, 130, 246, 0.3);">
            </div>

            <div class="form-group-premium">
                <label>Operational Status</label>
                <select name="status" class="input-premium select-premium">
                    <option value="active" {{ $aircraft->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="prolong" {{ $aircraft->status == 'prolong' ? 'selected' : '' }}>Prolong</option>
                </select>
            </div>

            <div class="form-actions" style="margin-top: 3rem; display: flex; gap: 1rem; border-top: 1px solid var(--border-subtle); padding-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1rem; font-weight: 700; font-size: 1rem;">Update Aircraft Configuration</button>
                <a href="{{ route('fleet.index') }}" class="btn btn-secondary" style="padding: 1rem 1.5rem; font-weight: 600;">Cancel</a>
            </div>
        </form>
    </div>
@endsection