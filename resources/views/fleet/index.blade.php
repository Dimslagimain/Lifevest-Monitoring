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

    <!-- Simple & Clean Tab Switcher -->
    <div class="tab-pill-container" style="display: flex; gap: 4px; margin-bottom: 2.5rem; background: var(--bg-card-solid); padding: 5px; border-radius: 12px; width: fit-content; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
        <a href="{{ route('fleet.index', ['tab' => 'aircraft']) }}"
            class="tab-pill {{ $tab === 'aircraft' ? 'active' : '' }}"
            style="padding: 0.7rem 2rem; border-radius: 10px; font-weight: 700; font-size: 0.85rem; text-decoration: none; text-transform: uppercase; letter-spacing: 0.08em; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 8px;
            {{ $tab === 'aircraft' ? 'background: var(--primary); color: white; box-shadow: var(--shadow-primary);' : 'color: var(--text-muted);' }};">
            Aircraft <span style="opacity: 0.6; font-size: 0.75rem;">({{ $fleet->count() }})</span>
        </a>
        <a href="{{ route('fleet.index', ['tab' => 'airlines']) }}"
            class="tab-pill {{ $tab === 'airlines' ? 'active' : '' }}"
            style="padding: 0.7rem 2rem; border-radius: 10px; font-weight: 700; font-size: 0.85rem; text-decoration: none; text-transform: uppercase; letter-spacing: 0.08em; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 8px;
            {{ $tab === 'airlines' ? 'background: var(--primary); color: white; box-shadow: var(--shadow-primary);' : 'color: var(--text-muted);' }};">
            Airlines <span style="opacity: 0.6; font-size: 0.75rem;">({{ $airlines->count() }})</span>
        </a>
    </div>

    @if($tab === 'aircraft')
        <!-- Aircraft Tab Content -->
        <!-- Practical Filters -->
        <div class="filter-bar" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; margin-bottom: 1.5rem; background: var(--bg-card); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--border-subtle);">
            <div style="position: relative; flex: 1; min-width: 200px; max-width: 300px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" id="fleetSearch" placeholder="Search registration..." class="form-input"
                    style="width: 100%; padding-left: 36px;">
            </div>

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

            <button type="button" id="clearFilters" class="btn btn-secondary" style="padding: 0.5rem 1.25rem; font-weight: 600;">Reset Filters</button>
        </div>

        <div class="fleet-table-wrapper" style="background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-subtle); overflow: hidden;">
            <table class="fleet-table">
                <thead>
                    <tr>
                        <th class="fleet-th" style="width: 50px; background: rgba(0,0,0,0.05);">#</th>
                        <th class="fleet-th" style="background: rgba(0,0,0,0.05);">Registration</th>
                        <th class="fleet-th" style="background: rgba(0,0,0,0.05);">Airline</th>
                        <th class="fleet-th" style="background: rgba(0,0,0,0.05);">Type</th>
                        <th class="fleet-th" style="background: rgba(0,0,0,0.05);">Layout Code</th>
                        <th class="fleet-th" style="background: rgba(0,0,0,0.05);">Status</th>
                        <th class="fleet-th text-right" style="background: rgba(0,0,0,0.05);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fleet as $aircraft)
                        <tr>
                            <td class="fleet-td text-muted">{{ $loop->iteration }}</td>
                            <td class="fleet-td font-bold" style="color: var(--primary-light); font-family: 'JetBrains Mono', monospace;">{{ $aircraft->registration }}</td>
                            <td class="fleet-td">
                                {{ $aircraft->airline?->name ?? '-' }}
                            </td>
                            <td class="fleet-td">{{ $aircraft->type }}</td>
                            <td class="fleet-td" style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; opacity: 0.8;">{{ $aircraft->layout }}</td>
                            <td class="fleet-td">
                                <span class="status-badge {{ $aircraft->status }}">
                                    {{ strtoupper($aircraft->status) }}
                                </span>
                            </td>
                            <td class="fleet-td text-right">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ route('fleet.edit', $aircraft->id) }}" class="btn-icon" title="Edit Aircraft">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form action="{{ route('fleet.destroy', $aircraft->id) }}" method="POST"
                                        style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon confirm-submit" 
                                            data-confirm-title="Remove Aircraft?" 
                                            data-confirm-text="Are you sure you want to delete aircraft {{ $aircraft->registration }}?"
                                            data-confirm-icon="warning"
                                            data-confirm-button-text="Yes, Delete"
                                            data-confirm-variant="danger"
                                            style="color: var(--danger);" title="Delete Aircraft">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <!-- Empty State Row (Hidden by default) -->
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="7" style="padding: 4rem 2rem; text-align: center;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                                <div style="width: 64px; height: 64px; background: rgba(148, 163, 184, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                </div>
                                <div style="font-weight: 700; color: var(--text-primary); font-size: 1.1rem;">No aircraft found</div>
                                <div style="color: var(--text-muted); font-size: 0.9rem;">We couldn't find any registration matching "<span id="searchQueryDisplay"></span>"</div>
                                <button type="button" onclick="document.getElementById('clearFilters').click()" class="btn btn-outline" style="margin-top: 0.5rem;">Clear all filters</button>
                            </div>
                        </td>
                    </tr>
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
                                        style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon confirm-submit" 
                                            data-confirm-title="Remove Airline?" 
                                            data-confirm-text="Are you sure you want to delete {{ $airline->name }}?"
                                            data-confirm-icon="warning"
                                            data-confirm-button-text="Yes, Delete"
                                            data-confirm-variant="danger"
                                            style="color: var(--danger);" title="Delete Airline">
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

            const noResultsRow = document.getElementById('noResultsRow');
            const searchQueryDisplay = document.getElementById('searchQueryDisplay');

            function applyFilters() {
                const searchTerm = searchInput?.value.toLowerCase() || '';
                const airlineFilter = filterAirline?.value || '';
                const statusFilter = filterStatus?.value || '';
                const typeFilter = filterType?.value || '';

                let visibleCount = 0;

                rows.forEach(row => {
                    if (row.id === 'noResultsRow') return;

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
                        visibleCount++;
                        row.cells[0].textContent = visibleCount;
                    }
                });

                // Handle Empty State
                if (noResultsRow) {
                    if (visibleCount === 0) {
                        noResultsRow.style.display = '';
                        if (searchQueryDisplay) searchQueryDisplay.textContent = searchInput?.value || 'selected filters';
                    } else {
                        noResultsRow.style.display = 'none';
                    }
                }
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