@extends('layouts.app')

@section('content')
    <div class="form-container-wide" style="max-width: 600px; margin: 0 auto; padding-top: 2rem;">
        <div class="header-section" style="margin-bottom: 2rem; text-align: center;">
            <h2 class="form-header" style="margin: 0; font-weight: 800; letter-spacing: -0.03em;">Add New Aircraft</h2>
            <p style="margin-top: 0.5rem; opacity: 0.7;">Fill in the details to register a new aircraft to the fleet.</p>
        </div>

        <form action="{{ route('fleet.store') }}" method="POST" class="form-card" style="background: var(--bg-card); padding: 2.5rem; border-radius: var(--radius-xl); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg);">
            @csrf

            <div class="form-group-premium">
                <label>Airline Provider</label>
                <select name="airline_id" required class="input-premium select-premium">
                    <option value="" disabled selected>Select Airline...</option>
                    @foreach($airlines as $airline)
                        <option value="{{ $airline->id }}" {{ old('airline_id') == $airline->id ? 'selected' : '' }}>
                            {{ $airline->name }} ({{ $airline->code }})
                        </option>
                    @endforeach
                </select>
                @error('airline_id')
                    <span style="color: var(--danger); font-size: 0.8rem; display: block; margin-top: 0.5rem; font-weight: 600;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group-premium">
                <label>Registration Number (e.g. PK-GPC)</label>
                <input type="text" name="registration" value="{{ old('registration') }}" required placeholder="PK-..."
                    class="input-premium"
                    style="text-transform: uppercase; {{ $errors->has('registration') ? 'border-color: var(--danger);' : '' }}">
                @error('registration')
                    <span style="color: var(--danger); font-size: 0.8rem; display: block; margin-top: 0.5rem; font-weight: 600;">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group-premium">
                    <label>Aircraft Type</label>
                    <input type="text" name="type" value="{{ old('type') }}" required placeholder="B737-800" class="input-premium"
                        style="text-transform: uppercase;">
                </div>

                <div class="form-group-premium">
                    <label>Layout Template</label>
                    <select name="layout" required class="input-premium select-premium">
                        <option value="" disabled selected>Select Layout...</option>
                        @foreach($layoutOptions as $code => $label)
                            <option value="{{ $code }}" {{ old('layout') == $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group-premium">
                <label>Operational Status</label>
                <select name="status" class="input-premium select-premium">
                    <option value="active">Active (Ready for Service)</option>
                    <option value="prolong">Prolong (Extended Service)</option>
                </select>
            </div>

            <div class="form-actions" style="margin-top: 3rem; display: flex; gap: 1rem; border-top: 1px solid var(--border-subtle); padding-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1rem; font-weight: 700; font-size: 1rem;">Save Aircraft to Fleet</button>
                <a href="{{ route('fleet.index') }}" class="btn btn-secondary" style="padding: 1rem 1.5rem; font-weight: 600;">Cancel</a>
            </div>
        </form>
    </div>
@endsection