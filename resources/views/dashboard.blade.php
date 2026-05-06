@extends('layouts.app')

@php
    $currentView = request()->query('view', 'fleet-overview');
    $isFullScreenView = $currentView !== 'all';
@endphp

@section('content')
    <!-- View Mode Styles for Fleet Overview -->
    <style>
        .list-view-active .fleet-cards[style*="grid"] {
            display: flex !important;
        }
        .list-view-active .fleet-cards {
            flex-direction: column;
            gap: 0.5rem;
        }
        .list-view-active .fleet-card {
            flex-direction: row;
            align-items: center;
            padding: 0.75rem 1.5rem;
            height: auto;
            justify-content: space-between;
        }
        .list-view-active .fleet-card-header {
            width: 250px;
            margin-bottom: 0;
            flex-shrink: 0;
            flex-direction: row-reverse;
            justify-content: flex-end;
            align-items: center;
            gap: 1rem;
        }
        .list-view-active .fleet-card-icon {
            font-size: 1.5rem;
            margin-bottom: 0;
        }
        .list-view-active .fleet-card-stats {
            flex-direction: row;
            border-top: none;
            margin-top: 0;
            padding-top: 0;
            gap: 2rem;
            align-items: center;
            justify-content: flex-start;
            flex-grow: 1;
        }
        .list-view-active .fleet-stat {
            flex-direction: row;
            gap: 0.6rem;
            align-items: center;
        }
        .list-view-active .fleet-stat-value {
            font-size: 1.1rem;
        }
        .list-view-active .fleet-card-progress {
            display: none;
        }
        .list-view-active .fleet-card-footer {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
            flex-shrink: 0;
            width: auto;
            justify-content: flex-end;
        }
        .list-view-active .fleet-card-action {
            display: none;
        }
    </style>

    <!-- Full-Screen View Styles -->
    @if($isFullScreenView)
        <style>
            .dashboard-container {
                display: flex;
                flex-direction: column;
                height: 100%;
            }
            
            .dashboard-content {
                flex: 1;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
            }
            
            .summary-section { display: block; }
            .airline-section { display: block; }
            #life-vest-summary-section { display: block; }
            #top-pn-insights-section { display: block; }
            #activity-log-section { display: block; }
            .replacement-interval-section { display: block; }
            
            /* Filter only shown in full view */
            #top { display: {{ ($currentView === 'fleet-overview' || $currentView === 'all') ? 'flex' : 'none' }}; }
            #filterPanel { display: none; }
            
            /* Back button for full-screen view */
            .view-back-btn {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                margin-bottom: 1rem;
                background: var(--bg-card);
                border: 1px solid var(--border-subtle);
                border-radius: 6px;
                color: var(--primary);
                text-decoration: none;
                cursor: pointer;
                font-weight: 500;
                transition: all 0.2s ease;
            }
            
            .view-back-btn:hover {
                background: var(--bg-hover);
                border-color: var(--primary);
            }

            /* View Transition Animations */
            @keyframes fadeInSlide {
                0% { opacity: 0; transform: translateY(12px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            .animate-view {
                animation: fadeInSlide 0.3s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            }
        </style>
    @endif

<div class="dashboard-content">
    <!-- Back Button for Full-Screen Views -->
    @if($isFullScreenView)
        <a href="{{ route('dashboard') }}" class="view-back-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
            <span>Back to Dashboard</span>
        </a>
    @endif

    <!-- Header & Quick Navigation -->
    @if($currentView === 'fleet-overview' || $currentView === 'all')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin: 0; font-size: 2rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">Fleet Overview</h1>
    </div>
    @endif






    @if($currentView === 'fleet-overview' || $currentView === 'all')
        {{-- Summary Cards (Clean & Responsive) --}}
        <section class="summary-section animate-view" style="margin-bottom: 3rem;">
            <div class="summary-cards">
                <!-- SAFE -->
                <div class="summary-card safe">
                    <div class="summary-icon">🟢</div>
                    <div class="summary-value">{{ $totalStats['safe'] }}</div>
                    <div class="summary-label">Safe</div>
                    <div class="summary-desc">> 6 months</div>
                </div>

                <!-- WARNING -->
                <div class="summary-card warning">
                    <div class="summary-icon">🟡</div>
                    <div class="summary-value">{{ $totalStats['warning'] }}</div>
                    <div class="summary-label">Warning</div>
                    <div class="summary-desc">3-6 months</div>
                </div>

                <!-- CRITICAL -->
                <div class="summary-card critical">
                    <div class="summary-icon">🔴</div>
                    <div class="summary-value">{{ $totalStats['critical'] }}</div>
                    <div class="summary-label">Critical</div>
                    <div class="summary-desc">< 3 months</div>
                </div>

                <!-- EXPIRED -->
                <div class="summary-card expired">
                    <div class="summary-icon">🟣</div>
                    <div class="summary-value">{{ $totalStats['expired'] }}</div>
                    <div class="summary-label">Expired</div>
                    <div class="summary-desc">Past due</div>
                </div>
            </div>
        </section>

        {{-- Airline Master Overview Section --}}
        <section class="master-airline-section animate-view" id="airline-master-overview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; margin-top: 1rem;">
        <!-- Smart Sorting Control -->
        <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
            <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Sort By:</span>
            <select id="airlineSortControl" class="form-select" style="min-width: 200px; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 500; cursor: pointer; background-color: var(--bg-card); border-color: var(--border-subtle);" onchange="sortMasterAirlines(this.value)">
                <option value="name_asc">Alphabetical (A-Z)</option>
                <option value="health_asc">Lowest Health First</option>
                <option value="expired_desc">Most Expired Vests</option>
            </select>
        </div>

        @foreach($fleetByAirline as $airlineId => $airline)
            @php
                $aSafe = 0; $aWarn = 0; $aCrit = 0; $aExp = 0;
                foreach($airline['types'] as $typeGroup) {
                    foreach($typeGroup['aircraft'] as $ac) {
                        $aSafe += $ac['stats']['safe'] ?? 0;
                        $aWarn += $ac['stats']['warning'] ?? 0;
                        $aCrit += $ac['stats']['critical'] ?? 0;
                        $aExp += $ac['stats']['expired'] ?? 0;
                    }
                }
                $aTotal = $aSafe + $aWarn + $aCrit + $aExp;
                $aHealth = $aTotal > 0 ? round((($aSafe + ($aWarn * 0.5)) / $aTotal) * 100) : 100;
            @endphp
            <div class="fleet-card airline-master-card" data-name="{{ strtolower($airline['name']) }}" data-health="{{ $aHealth }}" data-expired="{{ $aExp }}" style="cursor: pointer; padding: 2rem 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; border: 1px solid var(--border-subtle); transition: transform 0.2s, box-shadow 0.2s; background: var(--bg-card); border-radius: 12px; position: relative; overflow: hidden;" onclick="showAirlineDetails('{{ $airline['name'] }}')" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='var(--shadow-lg)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow)';">
                <div style="margin-bottom: 1.5rem;">
                    <h2 style="margin: 0; font-size: 1.6rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.01em;">{{ $airline['name'] }}</h2>
                    <span style="color: var(--text-muted); font-size: 0.9rem;">{{ $airline['code'] }} • {{ $airline['aircraft_count'] }} Aircraft</span>
                </div>
                
                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; margin-bottom: 1.5rem;">
                    @php
                        $healthColor = $aHealth >= 70 ? 'var(--success)' : ($aHealth >= 40 ? 'var(--warning)' : 'var(--danger)');
                        $bgColor = $aHealth >= 70 ? 'rgba(46, 204, 113, 0.15)' : ($aHealth >= 40 ? 'rgba(241, 196, 15, 0.15)' : 'rgba(231, 76, 60, 0.15)');
                    @endphp
                    <div style="
                        position: relative; 
                        width: 130px; 
                        height: 130px; 
                        border-radius: 50%; 
                        background: conic-gradient({{ $healthColor }} {{ $aHealth }}%, {{ $bgColor }} 0);
                        display: flex; 
                        align-items: center; 
                        justify-content: center;
                        margin-bottom: 1.25rem;
                        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                        z-index: 1;
                    ">
                        <div style="
                            position: relative;
                            width: 106px; 
                            height: 106px; 
                            background-color: var(--bg-card);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            box-shadow: inset 0 3px 6px rgba(0,0,0,0.1);
                        ">
                            <span style="font-weight: 800; font-size: 2.25rem; color: {{ $healthColor }}; leading-height: 1;">{{ $aHealth }}<span style="font-size: 1.25rem;">%</span></span>
                        </div>
                    </div>
                    
                    <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">Overall Fleet Health</div>
                </div>

                <div style="display: flex; gap: 1rem; font-size: 0.85rem; width: 100%; justify-content: center; margin-bottom: 1.5rem;">
                    <span style="color: var(--success); font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">🟢 {{ $aSafe }}</span>
                    <span style="color: var(--warning); font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">🟡 {{ $aWarn }}</span>
                    <span style="color: var(--danger); font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">🔴 {{ $aCrit }}</span>
                    <span style="color: purple; font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">🟣 {{ $aExp }}</span>
                </div>

                <div style="width: 100%; height: 8px; background: rgba(0,0,0,0.1); border-radius: 10px; display: flex; overflow: hidden; margin-top: auto; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);">
                    @if($aTotal > 0)
                        <div style="width: {{ ($aSafe/$aTotal)*100 }}%; background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, var(--success) 50%, rgba(0,0,0,0.1) 100%); height: 100%;"></div>
                        <div style="width: {{ ($aWarn/$aTotal)*100 }}%; background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, var(--warning) 50%, rgba(0,0,0,0.1) 100%); height: 100%;"></div>
                        <div style="width: {{ (($aCrit+$aExp)/$aTotal)*100 }}%; background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, var(--danger) 50%, rgba(0,0,0,0.1) 100%); height: 100%;"></div>
                    @endif
                </div>
            </div>
        @endforeach
    </section>

        {{-- Fleet Details Container --}}
        {{-- Fleet Details Container (Dynamic Header) --}}
        <div id="airline-fleet-details" class="animate-view" style="display: none; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: 2px solid var(--border-subtle); margin-bottom: 1.5rem;">
                <div>
                    <h1 id="airline-details-title" style="margin: 0; font-size: 2rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">Airline Fleet</h1>
                    <p id="airline-details-subtitle" style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.9rem; font-weight: 500;"></p>
                </div>
                <button type="button" onclick="hideAirlineDetails()" class="btn-premium" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 8px; background: var(--bg-card); border: 1px solid var(--border-subtle); color: var(--primary); font-weight: 700; cursor: pointer; transition: all 0.2s;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back to Overview
                </button>
            </div>
        </div>

    @if(empty($fleetByAirline))
        <div style="padding: 5rem 2rem; text-align: center; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-subtle); margin-top: 2rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 1.5rem; opacity: 0.5;">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0;">No Aircraft Found</h3>
            <p style="color: var(--text-muted); margin-top: 0.5rem; max-width: 400px; margin-left: auto; margin-right: auto;">It looks like there are no aircraft registered in the system yet. Please contact an administrator to add your fleet.</p>
        </div>
    @endif

    {{-- Fleet Cards Section --}}
    @foreach($fleetByAirline as $airlineId => $airline)
            <section class="airline-section" data-airline="{{ $airline['name'] }}" style="margin-bottom: 2rem; display: none;">
            <div class="airline-header"
                style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);">
                <div>
                    <h2 style="margin: 0; font-size: 1.35rem; font-weight: 700; letter-spacing: -0.02em;">{{ $airline['name'] }}</h2>
                    <span style="color: var(--text-muted); font-size: 0.8rem;">{{ $airline['code'] }} ·
                        <span class="airline-count">{{ $airline['aircraft_count'] }}</span> aircraft</span>
                </div>
            </div>

            @foreach($airline['types'] as $baseType => $typeGroup)
                <section class="fleet-section" style="margin-left: 0.5rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.65rem; font-size: 1rem; font-weight: 700; color: var(--text-secondary); cursor: pointer;"
                        onclick="const cards = this.nextElementSibling; const isHidden = cards.style.display==='none'; cards.style.display=isHidden?(document.body.classList.contains('list-view-active')?'flex':'grid'):'none'; this.querySelector('.collapse-icon').style.transform=isHidden?'rotate(90deg)':'rotate(0deg)';">
                        <span class="collapse-icon" style="font-size: 0.7em; transition: transform 0.2s; transform: rotate(0deg); display: inline-block;">▶</span>
                         {{ $typeGroup['name'] }}
                        <span class="type-count"
                            style="color: var(--text-muted); font-weight: 400; font-size: 0.8rem;">({{ count($typeGroup['aircraft']) }})</span>
                    </h3>
                    <div class="fleet-cards" style="display: none;">
                        @foreach($typeGroup['aircraft'] as $registration => $aircraft)
                            <a href="{{ route('aircraft.show', $registration) }}"
                                class="fleet-card {{ $aircraft['health'] >= 70 ? 'healthy' : ($aircraft['health'] >= 40 ? 'warning' : 'critical') }}"
                                data-status="{{ $aircraft['status'] ?? 'active' }}"
                                data-health="{{ $aircraft['health'] >= 70 ? 'safe' : ($aircraft['health'] >= 40 ? 'warning' : 'critical') }}"
                                data-airline="{{ $airline['name'] }}" data-type="{{ $aircraft['type'] }}">
                                <div class="fleet-card-header">
                                    <div>
                                        <div class="fleet-card-type">
                                            {{ $aircraft['type'] }}
                                            <span class="status-badge {{ $aircraft['status'] ?? 'active' }}">
                                                {{ strtoupper($aircraft['status'] ?? 'active') }}
                                            </span>
                                        </div>
                                        <div class="fleet-card-reg">{{ $registration }}</div>
                                    </div>
                                </div>
                                <div class="fleet-card-stats">
                                    <div class="fleet-stat safe fleet-stat-clickable" onclick="openSeatStatusModal('{{ $registration }}', 'safe', event)" title="Klik untuk detail">
                                        <div class="fleet-stat-value">{{ $aircraft['stats']['safe'] }}</div>
                                        <div class="fleet-stat-label">Safe</div>
                                    </div>
                                    <div class="fleet-stat warning fleet-stat-clickable" onclick="openSeatStatusModal('{{ $registration }}', 'warning', event)" title="Klik untuk detail">
                                        <div class="fleet-stat-value">{{ $aircraft['stats']['warning'] }}</div>
                                        <div class="fleet-stat-label">Warning</div>
                                    </div>
                                    <div class="fleet-stat critical fleet-stat-clickable" onclick="openSeatStatusModal('{{ $registration }}', 'critical', event)" title="Klik untuk detail">
                                        <div class="fleet-stat-value">{{ $aircraft['stats']['critical'] }}</div>
                                        <div class="fleet-stat-label">Critical</div>
                                    </div>
                                    <div class="fleet-stat expired fleet-stat-clickable" onclick="openSeatStatusModal('{{ $registration }}', 'expired', event)" title="Klik untuk detail">
                                        <div class="fleet-stat-value">{{ $aircraft['stats']['expired'] }}</div>
                                        <div class="fleet-stat-label">Expired</div>
                                    </div>
                                </div>
                                <div class="fleet-card-progress">
                                    @php
                                        $total = array_sum($aircraft['stats']) ?: 1;
                                    @endphp
                                    <div class="progress-bar">
                                        <div class="progress-segment safe"
                                            style="width: {{ ($aircraft['stats']['safe'] / $total) * 100 }}%"></div>
                                        <div class="progress-segment warning"
                                            style="width: {{ ($aircraft['stats']['warning'] / $total) * 100 }}%"></div>
                                        <div class="progress-segment critical"
                                            style="width: {{ ($aircraft['stats']['critical'] / $total) * 100 }}%"></div>
                                        <div class="progress-segment expired"
                                            style="width: {{ ($aircraft['stats']['expired'] / $total) * 100 }}%"></div>
                                        <div class="progress-segment no-data"
                                            style="width: {{ ($aircraft['stats']['no_data'] / $total) * 100 }}%"></div>
                                    </div>
                                </div>
                                <div class="fleet-card-footer">
                                    <span
                                        class="health-score {{ $aircraft['health'] >= 70 ? 'good' : ($aircraft['health'] >= 40 ? 'medium' : 'bad') }}">
                                        {{ $aircraft['health'] }}% Health
                                    </span>
                                    <span class="fleet-card-action">Open →</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </section>
    @endforeach


    <!-- Empty State (No Results Found) -->
    <div id="empty-state" style="display: none; padding: 4rem 2rem; text-align: center; background: var(--bg-card); border: 2px dashed var(--border); border-radius: 12px; margin: 2rem 0;">
        <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">🔍</div>
        <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.5rem;">Tidak Ada Hasil Ditemukan</h3>
        <p style="color: var(--text-muted); font-size: 1rem; max-width: 400px; margin: 0 auto 1.5rem;">Maaf, tidak ada pesawat atau maskapai yang cocok dengan kriteria pencarian Anda. Silakan coba kata kunci lain atau bersihkan filter.</p>
        <button type="button" onclick="document.getElementById('clearFilters').click();" class="btn-premium" style="padding: 0.6rem 1.5rem;">Bersihkan Semua Filter</button>
    </div>

    <!-- Seat Status Detail Modal -->
    <div id="seatStatusModal" class="seat-status-modal-overlay" style="display: none;" onclick="if(event.target===this) closeSeatStatusModal()">
        <div class="seat-status-modal">
            <div class="seat-status-modal-header">
                <div>
                    <h3 id="seatModalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--text-primary);"></h3>
                    <p id="seatModalSubtitle" style="margin: 0.25rem 0 0; font-size: 0.85rem; color: var(--text-muted);"></p>
                </div>
                <button type="button" onclick="closeSeatStatusModal()" class="seat-status-modal-close" title="Tutup">&times;</button>
            </div>
            <div id="seatModalBody" class="seat-status-modal-body">
                <!-- Content loaded via JS -->
            </div>
        </div>
    </div>
    
    </div>
    @endif

    {{-- Life Vest Replacement Summary --}}
    @if(count($pnSummary) > 0 && $currentView === 'life-vest-summary')
        <section class="replacement-section animate-view" id="life-vest-summary-section">
            <h2 style="margin-bottom: 1.5rem;">Life Vest Replacement Summary</h2>
            <div class="replacement-grid">
                @foreach($pnSummary as $idx => $item)
                    @php
                        $hasAttention = $item['expired'] > 0 || $item['critical'] > 0 || $item['warning'] > 0;
                    @endphp
                    <div class="replacement-card {{ $hasAttention ? 'has-expired' : 'all-good' }}">
                        <div class="replacement-header">
                            <div>
                                <span class="replacement-pn">{{ $item['pn'] }}</span>
                                <span
                                    class="replacement-category {{ $item['category'] }}">{{ strtoupper($item['category']) }}</span>
                            </div>
                            <div class="replacement-counts">
                                <span class="replacement-total">{{ $item['total'] }} total</span>
                            </div>
                        </div>

                        {{-- Clickable status badges (act as filter tabs) --}}
                        <div class="replacement-badges">
                            @if($item['expired'] > 0)
                                <span class="badge-btn badge-expired {{ $item['expired'] > 0 ? 'active' : '' }}" data-tab="expired"
                                    data-card="{{ $idx }}">🟣 {{ $item['expired'] }} expired</span>
                            @endif
                            @if($item['critical'] > 0)
                                <span
                                    class="badge-btn badge-critical {{ $item['expired'] == 0 && $item['critical'] > 0 ? 'active' : '' }}"
                                    data-tab="critical" data-card="{{ $idx }}">🔴 {{ $item['critical'] }} critical</span>
                            @endif
                            @if($item['warning'] > 0)
                                <span
                                    class="badge-btn badge-warning {{ $item['expired'] == 0 && $item['critical'] == 0 && $item['warning'] > 0 ? 'active' : '' }}"
                                    data-tab="warning" data-card="{{ $idx }}">🟡 {{ $item['warning'] }} warning</span>
                            @endif
                            @if(!$hasAttention)
                                <span class="replacement-ok">All safe</span>
                            @endif
                        </div>

                        {{-- Breakdowns --}}
                        @if(count($item['aircraft']) > 0)
                            <div class="replacement-breakdown" data-card="{{ $idx }}" data-type="expired"
                                style="{{ $item['expired'] > 0 ? '' : 'display:none' }}">
                                @foreach($item['aircraft'] as $ac)
                                    @if($ac['expired'] > 0)
                                        <span class="breakdown-item bd-expired">{{ $ac['reg'] }}: {{ $ac['expired'] }}</span>
                                    @endif
                                @endforeach
                            </div>

                            <div class="replacement-breakdown" data-card="{{ $idx }}" data-type="critical"
                                style="{{ $item['expired'] == 0 && $item['critical'] > 0 ? '' : 'display:none' }}">
                                @foreach($item['aircraft'] as $ac)
                                    @if($ac['critical'] > 0)
                                        <span class="breakdown-item bd-critical">{{ $ac['reg'] }}: {{ $ac['critical'] }}</span>
                                    @endif
                                @endforeach
                            </div>

                            <div class="replacement-breakdown" data-card="{{ $idx }}" data-type="warning"
                                style="{{ $item['expired'] == 0 && $item['critical'] == 0 && $item['warning'] > 0 ? '' : 'display:none' }}">
                                @foreach($item['aircraft'] as $ac)
                                    @if($ac['warning'] > 0)
                                        <span class="breakdown-item bd-warning">{{ $ac['reg'] }}: {{ $ac['warning'] }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Dedicated Activity Log Section (Full Width) --}}
    @if(auth()->user() && auth()->user()->isAdmin() && $currentView === 'activity-log')
        <section class="replacement-section animate-view" id="activity-log-section">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h2 style="margin: 0;">Global Activity Log</h2>
                    <p style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.85rem;">Historical record of all administrative changes across the fleet</p>
                </div>
            </div>

            <x-activity-history :logs="$recentLogs" title="Full Fleet Traceability" />
        </section>
    @endif

    {{-- TOP P/N INSIGHTS SECTION --}}
    @if(count($pnSummary) > 0 && $currentView === 'top-pn-insights')
        <section class="replacement-section animate-view" id="top-pn-insights-section">
            
            @php
                $totalExpired = collect($pnSummary)->sum('expired');
                $totalCritical = collect($pnSummary)->sum('critical');
                $totalWarning = collect($pnSummary)->sum('warning');
                $totalActionRequired = $totalExpired + $totalCritical + $totalWarning;
            @endphp

            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                {{-- Charts & Summary --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
                        <div class="replacement-card" style="border-left: 3px solid var(--primary); text-align: center; padding: 1rem;">
                            <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary);">{{ $totalActionRequired }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;">Total Action Required</div>
                        </div>
                        <div class="replacement-card" style="border-left: 3px solid #8b5cf6; text-align: center; padding: 1rem;">
                            <div style="font-size: 1.8rem; font-weight: 800; color: #c4b5fd;">{{ $totalExpired }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;">Expired</div>
                        </div>
                        <div class="replacement-card" style="border-left: 3px solid #ef4444; text-align: center; padding: 1rem;">
                            <div style="font-size: 1.8rem; font-weight: 800; color: #f87171;">{{ $totalCritical }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;">Critical</div>
                        </div>
                        <div class="replacement-card" style="border-left: 3px solid #f59e0b; text-align: center; padding: 1rem;">
                            <div style="font-size: 1.8rem; font-weight: 800; color: #fbbf24;">{{ $totalWarning }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;">Warning</div>
                        </div>
                    </div>

                    {{-- Chart --}}
                    <div class="replacement-card" style="padding: 1.5rem; border-left: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                Part Numbers by Urgency Level
                            </h3>
                            <select id="pnCategoryFilter" class="form-select" style="padding: 0.4rem 1rem; font-size: 0.85rem; width: auto !important; min-width: 160px; max-width: 220px; cursor: pointer; border-radius: 8px;">
                                <option value="all">All Categories</option>
                                <option value="adult">Adult Vests</option>
                                <option value="crew">Crew Vests</option>
                                <option value="infant">Infant Vests</option>
                            </select>
                        </div>
                        <div style="position: relative; height: 380px;">
                            <canvas id="pnInsightsChart"></canvas>
                        </div>
                    </div>

                    {{-- Data Table --}}
                    <div class="replacement-card" style="padding: 1.5rem; border-left: none; overflow-x: auto;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-primary);">Detailed Breakdown</h3>
                            <a href="{{ route('reports.summary') }}" class="btn-premium btn-premium-success">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Export Insights
                            </a>
                        </div>
                        <table class="fleet-table" style="width: 100%; border: none;">
                            <thead>
                                <tr>
                                    <th class="fleet-th">#</th>
                                    <th class="fleet-th">Part Number</th>
                                    <th class="fleet-th">Category</th>
                                    <th class="fleet-th" style="text-align: center;">Exp</th>
                                    <th class="fleet-th" style="text-align: center;">Crit</th>
                                    <th class="fleet-th" style="text-align: center;">Warn</th>
                                    <th class="fleet-th" style="text-align: center;">Total</th>
                                    <th class="fleet-th">Affected</th>
                                </tr>
                            </thead>
                            <tbody id="pnInsightsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Inject P/N data for JavaScript (always available) --}}
    <script>
        window.__pnSummary = @json($pnSummary);
    </script>

    {{-- Replacement Plans --}}
    @if(isset($replacementPlans))
        @foreach(['weekly', 'monthly', 'yearly'] as $interval)
            @php
                $plan = $replacementPlans[$interval];
                $viewKey = 'replacement-' . $interval;
                $isPlanVisible = ($currentView === $viewKey);
                $titleText = ucfirst($interval) . ' Replacement Plan';
                $subtitleText = 'Timeline kebutuhan penggantian life vest per ' . ($interval === 'weekly' ? 'minggu' : ($interval === 'monthly' ? 'bulan' : 'tahun'));
            @endphp
            
            @if($isPlanVisible)
                @if(count($plan) > 0)
                    <section class="replacement-section replacement-interval-section animate-view" data-interval="{{ $interval }}" id="replacement-{{ $interval }}-plan">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <h2>{{ $titleText }}</h2>
                            <span class="monthly-plan-subtitle">{{ $subtitleText }}</span>
                        </div>
                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <a href="{{ route('reports.excel') }}" class="btn-premium btn-premium-success" title="Download Excel Report">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Export Schedule
                            </a>
                            <button type="button" class="btn-premium" id="toggleAllPlanBtn-{{ $interval }}" onclick="toggleAllPlan('{{ $interval }}')" style="cursor: pointer; height: 100%; border-radius: 8px;">Expand All</button>
                        </div>
                    </div>

                    {{-- Grand Total Summary --}}
                    @php
                        $grandTotal = collect($plan)->sum('total');
                        $overdueTotal = isset($plan['overdue']) ? $plan['overdue']['total'] : 0;
                    @endphp
                    <div class="monthly-grand-summary">
                        <div class="monthly-grand-item">
                            <span class="monthly-grand-value">{{ $grandTotal }}</span>
                            <span class="monthly-grand-label">Total Life Vests</span>
                        </div>
                        <div class="monthly-grand-item overdue">
                            <span class="monthly-grand-value">{{ $overdueTotal }}</span>
                            <span class="monthly-grand-label">Overdue</span>
                        </div>
                        <div class="monthly-grand-item">
                            <span class="monthly-grand-value">{{ count($plan) - (isset($plan['overdue']) ? 1 : 0) }}</span>
                            <span class="monthly-grand-label">Periods Ahead</span>
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="monthly-timeline" id="timeline-{{$interval}}">
                        @foreach($plan as $bucketKey => $bucket)
                            <div class="monthly-card {{ $bucket['urgency'] }}" data-month="{{ $bucketKey }}">
                                {{-- Header (clickable) --}}
                                <div class="monthly-card-header" onclick="toggleMonth('{{ $interval }}-{{ $bucketKey }}')">
                                    <div class="monthly-card-left">
                                        <span class="monthly-urgency-dot {{ $bucket['urgency'] }}"></span>
                                        <div>
                                            <div class="monthly-card-title">
                                                {{ $bucket['label'] }}
                                                @if($bucket['urgency'] === 'overdue')
                                                    <span class="monthly-badge overdue">OVERDUE</span>
                                                @elseif($bucket['urgency'] === 'critical')
                                                    <span class="monthly-badge critical">CRITICAL</span>
                                                @elseif($bucket['urgency'] === 'warning')
                                                    <span class="monthly-badge warning">WARNING</span>
                                                @endif
                                                @if($bucket['isCurrentMonth'] ?? false)
                                                    <span class="monthly-badge current-month">CURRENT PERIOD</span>
                                                @endif
                                            </div>
                                            <div class="monthly-card-meta">
                                                {{ count($bucket['pn_breakdown']) }} Part Number(s) • {{ count($bucket['aircraft_breakdown']) }} Aircraft
                                            </div>
                                        </div>
                                    </div>
                                    <div class="monthly-card-right">
                                        <span class="monthly-card-total">{{ $bucket['total'] }}</span>
                                        <span class="monthly-card-unit">vests</span>
                                        <span class="monthly-card-arrow" id="arrow-{{ $interval }}-{{ $bucketKey }}">▼</span>
                                    </div>
                                </div>

                                {{-- Detail (collapsible) --}}
                                <div class="monthly-card-body" id="body-{{ $interval }}-{{ $bucketKey }}" style="display: none;">
                                    {{-- P/N Breakdown --}}
                                    @foreach($bucket['pn_breakdown'] as $pnKey => $pnData)
                                        <div class="monthly-pn-row">
                                            <div class="monthly-pn-header" onclick="togglePnDetails('{{ $interval }}-{{ $bucketKey }}-{{ str_replace('|', '-', $pnKey) }}'); event.stopPropagation();">
                                                <div style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; flex: 1;">
                                                    <span class="monthly-pn-toggle" id="toggle-{{ $interval }}-{{ $bucketKey }}-{{ str_replace('|', '-', $pnKey) }}">▶</span>
                                                    <div class="monthly-pn-info">
                                                        <span class="monthly-pn-name">{{ $pnData['pn'] }}</span>
                                                        <span class="monthly-pn-category {{ $pnData['category'] }}">{{ strtoupper($pnData['category']) }}</span>
                                                    </div>
                                                </div>
                                                <span class="monthly-pn-count">× {{ $pnData['count'] }}</span>
                                            </div>
                                            <div class="monthly-aircraft-list" id="details-{{ $interval }}-{{ $bucketKey }}-{{ str_replace('|', '-', $pnKey) }}" style="display: none;">
                                                @foreach($pnData['aircraft'] as $reg => $count)
                                                    <a href="{{ route('aircraft.show', $reg) }}" class="monthly-aircraft-chip" title="Open {{ $reg }}">
                                                        {{ $reg }}: {{ $count }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Aircraft Summary --}}
                                    <div class="monthly-aircraft-summary">
                                        <div class="monthly-aircraft-summary-title">Aircraft Summary:</div>
                                        <div class="monthly-aircraft-summary-list">
                                            @foreach($bucket['aircraft_breakdown'] as $reg => $acData)
                                                <a href="{{ route('aircraft.show', $reg) }}" class="monthly-ac-summary-chip">
                                                    <span class="monthly-ac-reg">{{ $reg }}</span>
                                                    <span class="monthly-ac-type">{{ $acData['type'] }}</span>
                                                    <span class="monthly-ac-count">{{ $acData['count'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
                @else
                    <section class="replacement-section replacement-interval-section animate-view" data-interval="{{ $interval }}" id="replacement-{{ $interval }}-plan" style="padding: 4rem 2rem; text-align: center; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-subtle); margin-top: 1rem;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--success); margin-bottom: 1rem; opacity: 0.6;">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 0;">All Clear!</h3>
                        <p style="color: var(--text-muted); margin-top: 0.5rem;">No life vests are scheduled for replacement in this period.</p>
                    </section>
                @endif
            @endif
        @endforeach
        
        {{-- Map specific data for Excel export function (Currently Monthly, as default) --}}
        <script>
            window.monthlyPlanData = @json($replacementPlans['monthly'] ?? []);
        </script>
    @endif

    <!-- Quick Stats -->
    <section class="stats-section" style="margin-top: 2rem; margin-bottom: 3rem;">
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Quick Stats
        </h3>
        <div style="display: flex; align-items: center; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 1.5rem; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="flex: 1; text-align: center; border-right: 1px solid var(--border-subtle); padding: 0 1rem;">
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1;">{{ count($fleetByAirline) }}</div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.5rem;">Airlines</div>
            </div>
            <div style="flex: 1; text-align: center; border-right: 1px solid var(--border-subtle); padding: 0 1rem;">
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1;">{{ count($fleet) }}</div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.5rem;">Aircraft</div>
            </div>
            <div style="flex: 1.5; text-align: center; border-right: 1px solid var(--border-subtle); padding: 0 1rem;">
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1;">{{ number_format(array_sum($totalStats)) }}</div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.5rem;">Total Seats Tracked</div>
            </div>
            <div style="flex: 1; text-align: center; border-right: 1px solid var(--border-subtle); padding: 0 1rem;">
                @php
                    $totalTracked = $totalStats['safe'] + $totalStats['warning'] + $totalStats['critical'] + $totalStats['expired'];
                    $healthScore = $totalTracked > 0 ? round(($totalStats['safe'] / $totalTracked) * 100) : 0;
                @endphp
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--success); line-height: 1;">{{ $healthScore }}%</div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.5rem;">Health Score</div>
            </div>
            <div style="flex: 1; text-align: center; padding: 0 1rem;">
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--danger); line-height: 1;">{{ $totalStats['critical'] + $totalStats['expired'] }}</div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.5rem;">Needs Attention</div>
            </div>
        </div>
    </section>
    </div>
@endsection

@push('scripts')
    <script>
        // ============================================
        // Seat Status Detail Modal (Fleet Card Stat Boxes)
        // ============================================
        const statusLabels = { safe: 'Safe', warning: 'Warning', critical: 'Critical', expired: 'Expired' };
        const statusEmojis = { safe: '🟢', warning: '🟡', critical: '🔴', expired: '🟣' };
        const statusColors = { safe: 'var(--success)', warning: 'var(--warning)', critical: 'var(--danger)', expired: 'var(--expired)' };

        function openSeatStatusModal(registration, status, event) {
            event.preventDefault();
            event.stopPropagation();

            const modal = document.getElementById('seatStatusModal');
            const title = document.getElementById('seatModalTitle');
            const subtitle = document.getElementById('seatModalSubtitle');
            const body = document.getElementById('seatModalBody');

            title.innerHTML = `${statusEmojis[status] || ''} ${registration} — <span style="color: ${statusColors[status]}">${statusLabels[status] || status}</span>`;
            subtitle.textContent = 'Memuat data...';
            body.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; padding: 3rem; gap: 0.75rem;">
                    <div class="seat-modal-spinner"></div>
                    <span style="color: var(--text-muted); font-weight: 500;">Memuat detail seat...</span>
                </div>`;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            fetch(`/aircraft/${registration}/seat-status/${status}`)
                .then(res => {
                    if (!res.ok) throw new Error('Gagal memuat data');
                    return res.json();
                })
                .then(data => {
                    subtitle.textContent = `${data.type} • ${data.total} seats berstatus ${statusLabels[status]}`;

                    if (data.groups.length === 0) {
                        body.innerHTML = `
                            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                <div style="font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.5;"></div>
                                <p style="font-weight: 600;">Tidak ada seat dengan status ${statusLabels[status]}</p>
                            </div>`;
                        return;
                    }

                    // Flatten all seats from all groups into one list
                    let allSeats = [];
                    data.groups.forEach(group => {
                        group.seats.forEach(seat => allSeats.push(seat));
                    });

                    let html = `
                    <div class="seat-modal-table-wrapper" style="max-height: none;">
                        <table class="seat-modal-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Seat ID</th>
                                    <th>Expiry Date</th>
                                    <th style="text-align: right;">Sisa Hari</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    allSeats.forEach((seat, idx) => {
                        const daysColor = seat.days_remaining === null ? 'var(--text-muted)' 
                            : seat.days_remaining < 0 ? 'var(--expired)' 
                            : seat.days_remaining < 90 ? 'var(--danger)' 
                            : seat.days_remaining < 180 ? 'var(--warning)' 
                            : 'var(--success)';
                        const daysText = seat.days_remaining === null ? '-' : 
                            (seat.days_remaining < 0 ? `${Math.abs(seat.days_remaining)}d overdue` : `${seat.days_remaining}d`);

                        html += `
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 0.8rem;">${idx + 1}</td>
                                    <td style="font-weight: 600;">${seat.seat_id}</td>
                                    <td>${seat.expiry_date}</td>
                                    <td style="text-align: right; font-weight: 600; color: ${daysColor}; font-size: 0.85rem;">${daysText}</td>
                                </tr>`;
                    });

                    html += `
                            </tbody>
                        </table>
                    </div>`;

                    body.innerHTML = html;
                })
                .catch(err => {
                    body.innerHTML = `
                        <div style="text-align: center; padding: 3rem; color: var(--danger);">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">⚠️</div>
                            <p style="font-weight: 600;">${err.message}</p>
                        </div>`;
                });
        }

        function closeSeatStatusModal() {
            document.getElementById('seatStatusModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSeatStatusModal();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Fleet Overview Multi-Select Logic
            const fleetCheckboxes = document.querySelectorAll('.fleet-checkbox');
            const checkAllBox = document.getElementById('fleetCheckAll');
            const overviewSafe = document.getElementById('overviewSafe');
            const overviewWarning = document.getElementById('overviewWarning');
            const overviewCritical = document.getElementById('overviewCritical');
            const overviewExpired = document.getElementById('overviewExpired');

            // Initial totals (all checked by default or none checked = all)
            const initialStats = {
                safe: parseInt(overviewSafe.dataset.initial),
                warning: parseInt(overviewWarning.dataset.initial),
                critical: parseInt(overviewCritical.dataset.initial),
                expired: parseInt(overviewExpired.dataset.initial)
            };

            function updateOverview() {
                let totalSafe = 0, totalWarning = 0, totalCritical = 0, totalExpired = 0;
                let checkedCount = 0;

                fleetCheckboxes.forEach(cb => {
                    if (cb.checked) {
                        checkedCount++;
                        totalSafe += parseInt(cb.dataset.safe);
                        totalWarning += parseInt(cb.dataset.warning);
                        totalCritical += parseInt(cb.dataset.critical);
                        totalExpired += parseInt(cb.dataset.expired);
                    }
                });

                // If nothing checked, show ALL (or show 0? Usually "All" is better UX, but let's stick to selection)
                // Let's make it: if nothing checked -> Show 0 (or revert to All? Let's revert to All for better UX)
                if (!checkedCount) { // Changed from !anyChecked to !checkedCount
                    totalSafe = initialStats.safe;
                    totalWarning = initialStats.warning;
                    totalCritical = initialStats.critical;
                    totalExpired = initialStats.expired;
                }

                // Update "Check All" state
                if (checkAllBox) {
                    checkAllBox.checked = (checkedCount === fleetCheckboxes.length);
                    checkAllBox.indeterminate = (checkedCount > 0 && checkedCount < fleetCheckboxes.length);
                }

                overviewSafe.textContent = totalSafe;
                overviewWarning.textContent = totalWarning;
                overviewCritical.textContent = totalCritical;
                overviewExpired.textContent = totalExpired;

                // Simple animation
                [overviewSafe, overviewWarning, overviewCritical, overviewExpired].forEach(el => {
                    el.style.transform = 'scale(1.15)';
                    setTimeout(() => el.style.transform = 'scale(1)', 200);
                });
            }

            // "Check All" Event Listener
            checkAllBox?.addEventListener('change', function () {
                const isChecked = this.checked;
                fleetCheckboxes.forEach(cb => {
                    cb.checked = isChecked;
                });
                updateOverview();
            });

            // Individual Checkbox Listener
            fleetCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateOverview);
            });

            // Initial update to set correct state for "Check All" and overview totals
            updateOverview();

            // Toggle Dropdown
            const dropdownBtn = document.getElementById('fleetDropdownBtn');
            const dropdownMenu = document.getElementById('fleetDropdownMenu');

            dropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
            const toggleBtn = document.getElementById('toggleFilters');
            const filterPanel = document.getElementById('filterPanel');
            const filterArrow = document.getElementById('filterArrow');
            const filterAirline = document.getElementById('filterAirline');
            const filterType = document.getElementById('filterType');
            const filterStatus = document.getElementById('filterStatus');
            const filterHealth = document.getElementById('filterHealth');
            const searchInput = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearFilters');
            const filterCount = document.getElementById('filterCount');

            const cards = Array.from(document.querySelectorAll('.fleet-card'));
            const airlineSections = Array.from(document.querySelectorAll('.airline-section'));
            const fleetSections = Array.from(document.querySelectorAll('.fleet-section'));
            const emptyState = document.getElementById('empty-state');
            const airlineMasterOverview = document.getElementById('airline-master-overview');

            // Toggle filter panel
            toggleBtn?.addEventListener('click', function () {
                const isHidden = filterPanel.style.display === 'none';
                filterPanel.style.display = isHidden ? 'flex' : 'none';
                filterArrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            });

            function applyFilters() {
                // If we are in Airline Detail view, don't run global filters
                const fleetDetails = document.getElementById('airline-fleet-details');
                if (fleetDetails && fleetDetails.style.display === 'block') {
                    return;
                }

                const airlineFilter = filterAirline?.value || '';
                const typeFilter = filterType?.value || '';
                const statusFilter = filterStatus?.value || '';
                const healthFilter = filterHealth?.value || '';
                const searchQuery = (searchInput?.value || '').toLowerCase().trim();

                let visibleCount = 0;
                const totalCount = cards.length;

                cards.forEach(card => {
                    const cardAirline = card.dataset.airline || '';
                    const cardType = card.dataset.type || '';
                    const cardStatus = card.dataset.status || '';
                    const cardHealth = card.dataset.health || '';
                    // Get registration from the card (looking for fleet-card-reg class)
                    const cardRegElement = card.querySelector('.fleet-card-reg');
                    const cardReg = (cardRegElement?.textContent || '').toLowerCase();

                    let show = true;

                    // Registration search filter
                    if (searchQuery && !cardReg.includes(searchQuery)) {
                        show = false;
                    }

                    // Airline filter
                    if (airlineFilter && cardAirline !== airlineFilter) {
                        show = false;
                    }

                    // Type filter
                    if (typeFilter && cardType !== typeFilter) {
                        show = false;
                    }

                    // Status filter
                    if (statusFilter && cardStatus !== statusFilter) {
                        show = false;
                    }

                    // Health filter
                    if (healthFilter && cardHealth !== healthFilter) {
                        show = false;
                    }

                    card.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                // Update Visibility for Airline Sections and Fleet Sections
                airlineSections.forEach(section => {
                    const sectionAirline = (section.dataset.airline || '').trim().toLowerCase();
                    const currentFilter = (airlineFilter || '').trim().toLowerCase();
                    
                    if (currentFilter) {
                        if (sectionAirline === currentFilter) {
                            section.style.display = 'block';
                            // Explicitly show all fleet sections inside the active airline
                            section.querySelectorAll('.fleet-section').forEach(fs => {
                                fs.style.display = 'block';
                            });
                        } else {
                            section.style.display = 'none';
                        }
                    } else {
                        // Overview mode
                        const visibleCards = section.querySelectorAll('.fleet-card:not([style*="display: none"])');
                        section.style.display = (visibleCards.length > 0 || searchQuery) ? 'block' : 'none';
                        
                        section.querySelectorAll('.fleet-section').forEach(fs => {
                            const cardsInType = fs.querySelectorAll('.fleet-card:not([style*="display: none"])');
                            fs.style.display = (cardsInType.length > 0 || searchQuery) ? 'block' : 'none';
                        });
                    }
                });

                // Hide/Hide Empty State
                if (emptyState) {
                    emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            }

            function updateTypeDropdown() {
                const selectedAirline = filterAirline?.value || '';
                const currentSelectedType = filterType?.value || '';
                
                // Get all valid types for the selected airline
                const validTypes = new Set();
                cards.forEach(card => {
                    const cardAirline = card.dataset.airline || '';
                    const cardType = card.dataset.type || '';
                    if (!selectedAirline || cardAirline === selectedAirline) {
                        if (cardType) validTypes.add(cardType);
                    }
                });
                
                // Update dropdown options
                if (filterType) {
                    // Keep the first option "All Types"
                    while (filterType.options.length > 1) {
                        filterType.remove(1);
                    }
                    
                    // Populate with valid types, sorted alphabetically
                    Array.from(validTypes).sort().forEach(type => {
                        const option = document.createElement('option');
                        option.value = type;
                        option.textContent = type;
                        filterType.appendChild(option);
                    });
                    
                    // Maintain previous selection if still valid, otherwise reset
                    if (validTypes.has(currentSelectedType)) {
                        filterType.value = currentSelectedType;
                    } else {
                        filterType.value = '';
                    }
                }
            }

            // Event listeners
            filterAirline?.addEventListener('change', function() {
                updateTypeDropdown();
                applyFilters();
            });
            filterType?.addEventListener('change', applyFilters);
            filterStatus?.addEventListener('change', applyFilters);
            filterHealth?.addEventListener('change', applyFilters);
            searchInput?.addEventListener('input', applyFilters); // Real-time search

            clearBtn?.addEventListener('click', function () {
                if (filterAirline) filterAirline.value = '';
                if (filterType) filterType.value = '';
                if (filterStatus) filterStatus.value = '';
                if (filterHealth) filterHealth.value = '';
                if (searchInput) searchInput.value = '';
                updateTypeDropdown(); // Ensure dropdown options reset
                applyFilters();
            });

            // Replacement Summary - Clickable Badge Filtering
            document.querySelectorAll('.badge-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const cardIdx = this.dataset.card;
                    const tab = this.dataset.tab;

                    // Toggle active badge
                    document.querySelectorAll(`.badge-btn[data-card="${cardIdx}"]`).forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Toggle breakdown visibility
                    document.querySelectorAll(`.replacement-breakdown[data-card="${cardIdx}"]`).forEach(bd => {
                        bd.style.display = bd.dataset.type === tab ? '' : 'none';
                    });
                });
            });

            // Replacement Plan - Toggle All (now handled by global function toggleAllPlan)

            // SPA-like tab switching for instantaneous load times between Dashboard views
            const sidebarLinks = document.querySelectorAll('.sidebar-nav-item');

            // Shared utility to sync dashboard view state
            function syncDashboardView(targetView) {
                // 1. Update active styling on sidebar
                sidebarLinks.forEach(l => {
                    l.classList.remove('active');
                    if (l.href) {
                        const isTarget = l.href.includes(`view=${targetView}`) || 
                                       (targetView === 'fleet-overview' && !l.href.includes('view=') && l.href.includes('/dashboard'));
                        
                        if (isTarget) {
                            l.classList.add('active');
                            
                            // Handle parent dropdown highlight
                            if (targetView.startsWith('replacement-')) {
                                const parentDropdownMenu = l.closest('.dropdown-submenu');
                                if (parentDropdownMenu) {
                                    const toggleBtn = parentDropdownMenu.previousElementSibling;
                                    if (toggleBtn) toggleBtn.classList.add('active');
                                }
                            }
                        }
                    }
                });

                // 2. Hide all main dashboard sections
                const sections = [
                    '.summary-section', '.airline-section', '.master-airline-section', 
                    '#airline-fleet-details', '#life-vest-summary-section', 
                    '#top-pn-insights-section', '.replacement-interval-section', 
                    '.stats-section', '.view-back-btn'
                ];
                sections.forEach(s => {
                    document.querySelectorAll(s).forEach(el => el.style.display = 'none');
                });
                const filterTop = document.getElementById('top');
                if (filterTop) filterTop.style.display = 'none';

                // 3. Toggle target sections
                if (targetView === 'fleet-overview' || targetView === 'all') {
                    document.querySelectorAll('.summary-section').forEach(el => el.style.display = 'block');
                    if (targetView === 'all') {
                        document.querySelectorAll('.airline-section').forEach(el => el.style.display = 'block');
                        document.getElementById('airline-fleet-details').style.display = 'block';
                    } else {
                        hideAirlineDetails();
                    }
                    if (filterTop) filterTop.style.display = 'flex';
                }

                if (targetView === 'life-vest-summary' || targetView === 'all') {
                    document.querySelectorAll('#life-vest-summary-section').forEach(el => el.style.display = 'block');
                }

                if (targetView === 'top-pn-insights' || targetView === 'all') {
                    document.querySelectorAll('#top-pn-insights-section').forEach(el => el.style.display = 'block');
                }

                if (targetView.startsWith('replacement-') || targetView === 'all') {
                    document.querySelectorAll('.replacement-interval-section').forEach(el => {
                        if (targetView === 'all' || ('replacement-' + el.dataset.interval) === targetView) {
                            el.style.display = 'block';
                        }
                    });
                    document.querySelectorAll('.stats-section').forEach(el => el.style.display = 'block');
                }

                // Toggle Back button
                if (targetView !== 'fleet-overview' && targetView !== 'all') {
                    document.querySelectorAll('.view-back-btn').forEach(el => el.style.display = 'flex');
                }

                // Scroll behavior
                window.scrollTo({top: 0, behavior: 'instant'});
            }
            
            sidebarLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    // Check if the link points to the dashboard
                    const isDashboardLink = link.href.includes('view=') && (link.href.includes('/dashboard') || link.href.includes('localhost') || link.href.includes('127.0.0.1'));
                    if (isDashboardLink) {
                        try {
                            const url = new URL(link.href);
                            const targetView = url.searchParams.get('view') || 'fleet-overview';
                            
                            // Only handle the dashboard views
                            if (['fleet-overview', 'life-vest-summary', 'top-pn-insights', 'replacement-weekly', 'replacement-monthly', 'replacement-yearly', 'all'].includes(targetView)) {
                                const currentUrl = new URL(window.location.href);
                                const currentView = currentUrl.searchParams.get('view') || 'fleet-overview';
                                
                                // Prevent full page reload
                                e.preventDefault();
                                
                                if (targetView !== currentView) {
                                    // Change the URL without reloading
                                    history.pushState(null, '', url.href);
                                    syncDashboardView(targetView);
                                }
                            }
                        } catch(err) {
                            console.error('Routing error:', err);
                        }
                    }
                });
            });
            
            // Handle browser back/forward buttons
            window.addEventListener('popstate', (e) => {
                const url = new URL(window.location.href);
                if (url.pathname === '/' || url.pathname.includes('dashboard')) {
                    const targetView = url.searchParams.get('view') || 'fleet-overview';
                    syncDashboardView(targetView);
                }
            });



            /*
            // Auto-expand overdue and critical (Disabled by user request)
            document.querySelectorAll('.monthly-card.overdue, .monthly-card.critical').forEach(card => {
                const monthKey = card.dataset.month;
                const body = document.getElementById('body-' + monthKey);
                const arrow = document.getElementById('arrow-' + monthKey);
                if (body) {
                    body.style.display = 'block';
                    card.classList.add('expanded');
                }
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            });
            */
        });

        // Monthly Plan - Toggle All (global function for onclick)
        const _planExpandState = {};
        function toggleAllPlan(interval) {
            _planExpandState[interval] = !_planExpandState[interval];
            const allExpanded = _planExpandState[interval];
            const section = document.getElementById('timeline-' + interval);
            const btn = document.getElementById('toggleAllPlanBtn-' + interval);

            if (!section) return;

            section.querySelectorAll('.monthly-card-body').forEach(body => {
                body.style.display = allExpanded ? 'block' : 'none';
            });
            section.querySelectorAll('.monthly-card-arrow').forEach(arrow => {
                arrow.style.transform = allExpanded ? 'rotate(180deg)' : 'rotate(0deg)';
            });
            section.querySelectorAll('.monthly-card').forEach(card => {
                if (allExpanded) {
                    card.classList.add('expanded');
                } else {
                    card.classList.remove('expanded');
                }
            });
            if (btn) btn.textContent = allExpanded ? 'Collapse All' : 'Expand All';
        }

        // Monthly Plan - Toggle individual month (must be global function for onclick)
        function toggleMonth(monthKey) {
            const body = document.getElementById('body-' + monthKey);
            const arrow = document.getElementById('arrow-' + monthKey);
            const card = document.querySelector(`.monthly-card[data-month="${monthKey}"]`);

            if (body) {
                const isHidden = body.style.display === 'none';
                body.style.display = isHidden ? 'block' : 'none';
                if (card) card.classList.toggle('expanded', isHidden);
                if (arrow) arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }

        // Monthly Plan - Toggle P/N details within month
        function togglePnDetails(pnId) {
            const detailsEl = document.getElementById('details-' + pnId);
            const toggleEl = document.getElementById('toggle-' + pnId);

            if (detailsEl) {
                const isHidden = detailsEl.style.display === 'none';
                detailsEl.style.display = isHidden ? 'block' : 'none';
                if (toggleEl) toggleEl.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
            }
        }

        // Smart Sorting for Airline Master Cards
        function sortMasterAirlines(criteria) {
            const container = document.getElementById('airline-master-overview');
            if (!container) return;
            
            const cards = Array.from(container.querySelectorAll('.airline-master-card'));
            
            cards.sort((a, b) => {
                const nameA = a.dataset.name;
                const nameB = b.dataset.name;
                const healthA = parseInt(a.dataset.health, 10);
                const healthB = parseInt(b.dataset.health, 10);
                const expA = parseInt(a.dataset.expired, 10);
                const expB = parseInt(b.dataset.expired, 10);

                if (criteria === 'health_asc') {
                    if (healthA !== healthB) return healthA - healthB;
                    return nameA.localeCompare(nameB);
                } else if (criteria === 'expired_desc') {
                    if (expA !== expB) return expB - expA;
                    return nameA.localeCompare(nameB);
                } else {
                    return nameA.localeCompare(nameB);
                }
            });

            cards.forEach(card => container.appendChild(card));
        }


        function showAirlineDetails(airlineName) {
            // Hide overview, show details container
            const masterOverview = document.getElementById('airline-master-overview');
            const fleetDetails = document.getElementById('airline-fleet-details');
            const summarySection = document.querySelector('.summary-section');
            const statsSection = document.querySelector('.stats-section');
            
            if (masterOverview) masterOverview.style.display = 'none';
            if (summarySection) summarySection.style.display = 'none';
            if (statsSection) statsSection.style.display = 'block';
            if (fleetDetails) fleetDetails.style.display = 'block';
            
            // Set Titles
            const titleEl = document.getElementById('airline-details-title');
            const subtitleEl = document.getElementById('airline-details-subtitle');
            if (titleEl) titleEl.textContent = airlineName;
            
            // Show only the target airline section
            document.querySelectorAll('.airline-section').forEach(section => {
                const sectionName = (section.dataset.airline || '').trim().toLowerCase();
                const targetName = (airlineName || '').trim().toLowerCase();
                
                if (sectionName === targetName) {
                    section.style.display = 'block';
                    // Show all fleet type groups inside
                    section.querySelectorAll('.fleet-section').forEach(fs => {
                        fs.style.display = 'block';
                    });
                } else {
                    section.style.display = 'none';
                }
            });
            
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        function hideAirlineDetails() {
            const masterOverview = document.getElementById('airline-master-overview');
            const fleetDetails = document.getElementById('airline-fleet-details');
            const summarySection = document.querySelector('.summary-section');
            const statsSection = document.querySelector('.stats-section');
            
            if (masterOverview) masterOverview.style.display = 'grid';
            if (summarySection) summarySection.style.display = 'block';
            if (statsSection) statsSection.style.display = 'block';
            if (fleetDetails) fleetDetails.style.display = 'none';
            
            // Reset airline filter to ALL
            const filterAirline = document.getElementById('filterAirline');
            if(filterAirline) {
                filterAirline.value = '';
                filterAirline.dispatchEvent(new Event('change'));
            }
            
            // View sections will be hidden by applyFilters() when filterAirline is empty
            
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        // ====================================================
        // TOP P/N INSIGHTS — Chart.js + Table
        // ====================================================
        (function() {
            const rawData = window.__pnSummary || [];
            if (!rawData.length) return;

            let chartInstance = null;

            function getFilteredData(category) {
                let data = rawData.filter(item => {
                    const action = item.expired + item.critical + item.warning;
                    return action > 0;
                });
                if (category !== 'all') {
                    data = data.filter(item => item.category === category);
                }
                // Sort by Total Action descending
                data.sort((a, b) => {
                    const aTotal = a.expired + a.critical + a.warning;
                    const bTotal = b.expired + b.critical + b.warning;
                    return bTotal - aTotal;
                });
                return data.slice(0, 15); // Top 15
            }

            function isDarkMode() {
                return document.documentElement.getAttribute('data-theme') === 'dark';
            }

            function renderChart(data) {
                const ctx = document.getElementById('pnInsightsChart');
                if (!ctx) return;

                if (chartInstance) {
                    chartInstance.destroy();
                }

                const labels = data.map(d => d.pn + ' (' + d.category.toUpperCase() + ')');
                const dark = isDarkMode();

                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Expired',
                                data: data.map(d => d.expired),
                                backgroundColor: 'rgba(139, 92, 246, 0.8)',
                                borderColor: 'rgba(139, 92, 246, 1)',
                                borderWidth: 1,
                                borderRadius: 4,
                            },
                            {
                                label: 'Critical',
                                data: data.map(d => d.critical),
                                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                borderColor: 'rgba(239, 68, 68, 1)',
                                borderWidth: 1,
                                borderRadius: 4,
                            },
                            {
                                label: 'Warning',
                                data: data.map(d => d.warning),
                                backgroundColor: 'rgba(245, 158, 11, 0.8)',
                                borderColor: 'rgba(245, 158, 11, 1)',
                                borderWidth: 1,
                                borderRadius: 4,
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    color: dark ? '#cbd5e1' : '#000000',
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 13, weight: '900' },
                                    usePointStyle: true,
                                    pointStyle: 'rectRounded',
                                    padding: 16,
                                }
                            },
                            tooltip: {
                                backgroundColor: dark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                titleColor: dark ? '#e2e8f0' : '#0f172a',
                                bodyColor: dark ? '#cbd5e1' : '#334155',
                                borderColor: dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                                borderWidth: 1,
                                padding: 12,
                                displayColors: true,
                                titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: '700' },
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif" },
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    color: dark ? '#94a3b8' : '#000000',
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '800' },
                                    stepSize: 1,
                                },
                                grid: {
                                    color: dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
                                },
                            },
                            y: {
                                stacked: true,
                                ticks: {
                                    color: dark ? '#e2e8f0' : '#000000',
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 13, weight: '900' },
                                },
                                grid: {
                                    display: false,
                                },
                            }
                        }
                    }
                });
            }

            function renderTable(data) {
                const tbody = document.getElementById('pnInsightsTableBody');
                if (!tbody) return;

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="fleet-td" style="text-align: center; color: var(--text-muted); padding: 2rem;">Semua Part Number dalam kondisi aman untuk kategori ini.</td></tr>';
                    return;
                }

                const dark = isDarkMode();
                let html = '';
                data.forEach((item, idx) => {
                    const totalAction = item.expired + item.critical + item.warning;
                    const aircraftList = (item.aircraft || [])
                        .map(ac => ac.reg)
                        .slice(0, 8)
                        .join(', ');
                    const moreCount = (item.aircraft || []).length > 8 ? ' +' + ((item.aircraft || []).length - 8) + ' more' : '';

                    html += `<tr>
                        <td class="fleet-td" style="font-weight: 600; color: var(--text-secondary);">${idx + 1}</td>
                        <td class="fleet-td" style="font-weight: 700; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">${item.pn}</td>
                        <td class="fleet-td">
                            <span style="font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.5px;
                                background: ${item.category === 'adult' ? (dark ? 'rgba(96,165,250,0.2)' : 'rgba(37,99,235,0.15)') : item.category === 'crew' ? (dark ? 'rgba(251,191,36,0.2)' : 'rgba(217,119,6,0.15)') : (dark ? 'rgba(244,114,182,0.2)' : 'rgba(219,39,119,0.15)')};
                                color: ${item.category === 'adult' ? (dark ? '#60a5fa' : '#2563eb') : item.category === 'crew' ? (dark ? '#fbbf24' : '#d97706') : (dark ? '#f472b6' : '#db2777')};">
                                ${item.category}
                            </span>
                        </td>
                        <td class="fleet-td" style="text-align: center; font-weight: 700; color: ${item.expired > 0 ? '#c4b5fd' : 'var(--text-muted)'};">${item.expired}</td>
                        <td class="fleet-td" style="text-align: center; font-weight: 700; color: ${item.critical > 0 ? '#f87171' : 'var(--text-muted)'};">${item.critical}</td>
                        <td class="fleet-td" style="text-align: center; font-weight: 700; color: ${item.warning > 0 ? '#fbbf24' : 'var(--text-muted)'};">${item.warning}</td>
                        <td class="fleet-td" style="text-align: center; font-weight: 800; font-size: 1.05rem; color: var(--primary);">${totalAction}</td>
                        <td class="fleet-td" style="font-size: 0.8rem; color: var(--text-secondary); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${aircraftList}${moreCount}</td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            }

            function updateAll() {
                const category = document.getElementById('pnCategoryFilter')?.value || 'all';
                const data = getFilteredData(category);
                renderChart(data);
                renderTable(data);
            }

            // Listen for filter changes
            const filterEl = document.getElementById('pnCategoryFilter');
            if (filterEl) {
                filterEl.addEventListener('change', updateAll);
            }

            // Listen for theme changes to re-render chart
            const themeToggle = document.getElementById('theme-toggle-sidebar');
            if (themeToggle) {
                themeToggle.addEventListener('change', () => {
                    setTimeout(updateAll, 100);
                });
            }

            // Initial render
            updateAll();
        })();
    </script>
@endpush