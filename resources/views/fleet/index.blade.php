@extends('layouts.app')

@section('content')
    <div class="header-section" style="background: var(--bg-card); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
        <div>
            <h2 class="form-header" style="text-align: left; margin: 0; font-weight: 800; letter-spacing: -0.03em;">Fleet Manager</h2>
            <p style="margin-top: 0.25rem; opacity: 0.7; font-size: 0.9rem;">Management of aircraft and airlines system-wide.</p>
        </div>
        <div class="header-actions">
            @if($tab === 'aircraft')
                <a href="{{ route('fleet.create') }}" class="btn btn-primary" style="padding: 0.7rem 1.4rem; font-weight: 700;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 4px;"><path d="M12 5v14M5 12h14"/></svg>
                    New Aircraft
                </a>
            @else
                <a href="{{ route('airlines.create') }}" class="btn btn-primary" style="padding: 0.7rem 1.4rem; font-weight: 700;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 4px;"><path d="M12 5v14M5 12h14"/></svg>
                    New Airline
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert-box alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-box alert-danger"
            style="background: rgba(239, 68, 68, 0.1); border-color: var(--danger); color: var(--danger);">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="tab-nav" style="display: flex; gap: 4px; margin-bottom: 2rem; background: rgba(0,0,0,0.1); padding: 4px; border-radius: 12px; width: fit-content; border: 1px solid var(--border-subtle);">
        <a href="{{ route('fleet.index', ['tab' => 'aircraft']) }}"
            class="tab-link {{ $tab === 'aircraft' ? 'active' : '' }}"
            style="padding: 0.6rem 1.75rem; border-radius: 9px; font-weight: 700; font-size: 0.85rem; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            {{ $tab === 'aircraft' ? 'background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);' : 'color: var(--text-muted);' }};">
            Aircraft ({{ $fleet->count() }})
        </a>
        <a href="{{ route('fleet.index', ['tab' => 'airlines']) }}"
            class="tab-link {{ $tab === 'airlines' ? 'active' : '' }}"
            style="padding: 0.6rem 1.75rem; border-radius: 9px; font-weight: 700; font-size: 0.85rem; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            {{ $tab === 'airlines' ? 'background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);' : 'color: var(--text-muted);' }};">
            Airlines ({{ $airlines->count() }})
        </a>
    </div>

    @if($tab === 'aircraft')
        <!-- Aircraft Tab Content -->
        <!-- Practical Filters -->
        <div class="filter-bar" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
            <input type="text" id="fleetSearch" placeholder="Search registration..." class="form-input"
                style="flex: 1; min-width: 200px; max-width: 300px;">

            <select id="filterAirline" class="form-select" style="min-width: 180px; cursor: pointer;">
                <option value="">All Airlines</option>
                @foreach($airlines as $airline)
                    <option value="{{ $airline->name }}">{{ $airline->name }}</option>
                @endforeach
            </select>

            <select id="filterStatus" class="form-select" style="min-width: 130px; cursor: pointer;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="prolong">Prolong</option>
            </select>

            <select id="filterType" class="form-select" style="min-width: 150px; cursor: pointer;">
                <option value="">All Types</option>
                @php
                    $uniqueTypes = $fleet->pluck('type')->unique()->sort();
                @endphp
                @foreach($uniqueTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>

            <button type="button" id="clearFilters" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Clear</button>
        </div>

        <div class="fleet-table-wrapper">
            <table class="fleet-table">
                <thead>
                    <tr>
                        <th class="fleet-th" style="width: 50px;">#</th>
                        <th class="fleet-th">Registration</th>
                        <th class="fleet-th">Airline</th>
                        <th class="fleet-th">Type</th>
                        <th class="fleet-th">Layout Code</th>
                        <th class="fleet-th">Status</th>
                        <th class="fleet-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fleet as $aircraft)
                        <tr>
                            <td class="fleet-td text-muted">{{ $loop->iteration }}</td>
                            <td class="fleet-td font-bold">{{ $aircraft->registration }}</td>
                            <td class="fleet-td">
                                {{ $aircraft->airline?->name ?? '-' }}
                            </td>
                            <td class="fleet-td">{{ $aircraft->type }}</td>
                            <td class="fleet-td font-mono">{{ $aircraft->layout }}</td>
                            <td class="fleet-td">
                                <span class="status-badge {{ $aircraft->status }}">
                                    {{ strtoupper($aircraft->status) }}
                                </span>
                            </td>
                            <td class="fleet-td text-right">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ route('fleet.edit', $aircraft->id) }}" class="btn btn-icon" title="Edit Aircraft">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form action="{{ route('fleet.destroy', $aircraft->id) }}" method="POST"
                                        style="display: inline-block;"
                                        onsubmit="return confirm('Type DELETE to confirm removal of {{ $aircraft->registration }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon" style="color: var(--danger);" title="Delete Aircraft">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <!-- Airlines Tab Content -->
        <div class="fleet-table-wrapper">
            <table class="fleet-table">
                <thead>
                    <tr>
                        <th class="fleet-th" style="width: 50px;">#</th>
                        <th class="fleet-th">Name</th>
                        <th class="fleet-th">Code</th>
                        <th class="fleet-th">Aircraft Count</th>
                        <th class="fleet-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($airlines as $airline)
                        <tr>
                            <td class="fleet-td text-muted">{{ $loop->iteration }}</td>
                            <td class="fleet-td font-bold">{{ $airline->name }}</td>
                            <td class="fleet-td font-mono">{{ $airline->code ?? '-' }}</td>
                            <td class="fleet-td">
                                <span class="status-badge active">{{ $airline->aircraft_count }} aircraft</span>
                            </td>
                            <td class="fleet-td text-right">
                                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; align-items: center;">
                                    <a href="{{ route('airlines.edit', $airline->id) }}" class="btn btn-icon" title="Edit Airline">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form action="{{ route('airlines.destroy', $airline->id) }}" method="POST"
                                        style="display: inline-block;"
                                        onsubmit="return confirm('Type DELETE to confirm removal of {{ $airline->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon" style="color: var(--danger);" title="Delete Airline">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('fleetSearch');
            const filterAirline = document.getElementById('filterAirline');
            const filterStatus = document.getElementById('filterStatus');
            const filterType = document.getElementById('filterType');
            const clearBtn = document.getElementById('clearFilters');
            const tableBody = document.querySelector('.fleet-table tbody');

            if (!tableBody) return;

            const rows = Array.from(tableBody.querySelectorAll('tr'));

            function applyFilters() {
                const searchTerm = searchInput?.value.toLowerCase() || '';
                const airlineFilter = filterAirline?.value || '';
                const statusFilter = filterStatus?.value || '';
                const typeFilter = filterType?.value || '';

                let visibleIndex = 0;

                rows.forEach(row => {
                    const registration = row.cells[1]?.textContent.toLowerCase() || '';
                    const airline = row.cells[2]?.textContent.trim() || '';
                    const type = row.cells[3]?.textContent.trim() || '';
                    const status = row.cells[5]?.textContent.trim().toLowerCase() || '';

                    let show = true;

                    // Search filter (registration only)
                    if (searchTerm && !registration.includes(searchTerm)) {
                        show = false;
                    }

                    // Airline filter
                    if (airlineFilter && !airline.includes(airlineFilter)) {
                        show = false;
                    }

                    // Status filter
                    if (statusFilter && status !== statusFilter) {
                        show = false;
                    }

                    // Type filter
                    if (typeFilter && type !== typeFilter) {
                        show = false;
                    }

                    row.style.display = show ? '' : 'none';

                    // Update row number
                    if (show) {
                        visibleIndex++;
                        row.cells[0].textContent = visibleIndex;
                    }
                });
            }

            function updateTypeDropdown() {
                const selectedAirline = filterAirline?.value || '';
                const currentSelectedType = filterType?.value || '';
                
                const validTypes = new Set();
                rows.forEach(row => {
                    const airline = row.cells[2]?.textContent.trim() || '';
                    const type = row.cells[3]?.textContent.trim() || '';
                    if (!selectedAirline || airline.includes(selectedAirline)) {
                        if (type) validTypes.add(type);
                    }
                });
                
                if (filterType) {
                    while (filterType.options.length > 1) {
                        filterType.remove(1);
                    }
                    
                    Array.from(validTypes).sort().forEach(type => {
                        const option = document.createElement('option');
                        option.value = type;
                        option.textContent = type;
                        filterType.appendChild(option);
                    });
                    
                    if (validTypes.has(currentSelectedType)) {
                        filterType.value = currentSelectedType;
                    } else {
                        filterType.value = '';
                    }
                }
            }

            // Event listeners
            searchInput?.addEventListener('input', applyFilters);
            filterAirline?.addEventListener('change', function() {
                updateTypeDropdown();
                applyFilters();
            });
            filterStatus?.addEventListener('change', applyFilters);
            filterType?.addEventListener('change', applyFilters);

            clearBtn?.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                if (filterAirline) filterAirline.value = '';
                if (filterStatus) filterStatus.value = '';
                if (filterType) filterType.value = '';
                updateTypeDropdown();
                applyFilters();
            });
        });
    </script>
@endpush