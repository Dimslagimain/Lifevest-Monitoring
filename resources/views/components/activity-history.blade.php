@props(['logs', 'title' => 'Recent Activity', 'showRegistration' => true])

<div class="replacement-card" style="padding: 1.25rem; border-left: none;" id="activityLogContainer">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.6rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
            {{ $title }}
        </h3>
        @if(!$logs->isEmpty())
            <button onclick="exportActivityToExcel()" class="btn-premium-download">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Export Excel</span>
            </button>
        @endif
    </div>

    @if($logs->isEmpty())
        <div style="padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
            <p>No activity recorded yet.</p>
        </div>
    @else
        <div class="activity-timeline" style="display: flex; flex-direction: column; gap: 0.75rem; position: relative;">
            {{-- Vertical Line --}}
            <div style="position: absolute; left: 7px; top: 5px; bottom: 5px; width: 2px; background: var(--border-subtle); z-index: 0; opacity: 0.5;"></div>

            @foreach($logs as $log)
                <div class="activity-item" style="display: flex; gap: 1rem; position: relative; z-index: 1;">
                    {{-- Status Dot --}}
                    @php
                        $dotColor = $log->action === 'delete' ? '#ef4444' : ($log->action === 'add' ? '#10b981' : '#3b82f6');
                    @endphp
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: var(--bg-card); border: 2.5px solid {{ $dotColor }}; flex-shrink: 0; margin-top: 5px; box-shadow: 0 0 0 3px var(--bg-card);"></div>

                    <div style="flex-grow: 1; background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid var(--border-subtle); display: flex; justify-content: space-between; gap: 1rem; transition: all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary-light)'; this.style.background='var(--bg-card)'" onmouseout="this.style.borderColor='var(--border-subtle)'; this.style.background='var(--bg-secondary)'">
                        {{-- Left Side: Action Details --}}
                        <div style="flex-grow: 1;">
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.85rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                {{ $log->user->name }}
                                <span style="font-weight: 500; color: var(--text-muted); font-size: 0.8rem; opacity: 0.8;">
                                    @if($log->action === 'update') updated seats @elseif($log->action === 'delete') deleted seat @elseif($log->action === 'batch') batch input @elseif($log->action === 'pn_update') updated P/N @else {{ $log->action }} @endif
                                </span>
                            </div>

                            <div class="action-content">
                                @if($log->action === 'update')
                                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">
                                        {{ $log->details['seat_count'] ?? 0 }} Seats Updated
                                    </div>
                                    @php
                                        $pnDisplay = null;
                                        if (isset($log->details['seats'][0]) && $log->aircraft) {
                                            $firstSeat = $log->details['seats'][0];
                                            if (str_starts_with($firstSeat, 'inf-')) { $pnDisplay = $log->aircraft->pn_infant; }
                                            elseif (in_array($firstSeat, ['captain', 'copilot', 'observer1', 'observer2']) || str_starts_with($firstSeat, 'att/')) { $pnDisplay = $log->aircraft->pn_crew; }
                                            else { $pnDisplay = $log->aircraft->pn_adult; }
                                        }
                                    @endphp
                                    @if($pnDisplay)
                                        <div style="margin-top: 6px;">
                                            <span style="font-size: 0.75rem; color: var(--primary); background: var(--bg-card); padding: 2px 8px; border-radius: 6px; border: 1.5px solid var(--primary-light); font-family: 'JetBrains Mono', monospace; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                                {{ $pnDisplay }}
                                            </span>
                                        </div>
                                    @endif
                                    @if(isset($log->details['seats']))
                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 6px; line-height: 1.4; padding: 4px 8px; background: rgba(0,0,0,0.015); border-radius: 4px; border-left: 2px solid var(--border-subtle);">
                                            {{ implode(', ', array_slice($log->details['seats'], 0, 15)) }}{{ count($log->details['seats']) > 15 ? '...' : '' }}
                                        </div>
                                    @endif

                                @elseif($log->action === 'pn_update')
                                    <div style="display: flex; flex-direction: column; gap: 4px; margin-top: 6px;">
                                        @foreach(['adult', 'crew', 'infant'] as $type)
                                            @if(($log->details['old'][$type] ?? '') !== ($log->details['new'][$type] ?? ''))
                                                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem;">
                                                    <span style="color: var(--text-muted); font-weight: 600; min-width: 45px;">{{ ucfirst($type) }}</span>
                                                    <div style="font-family: 'JetBrains Mono', monospace; display: flex; align-items: center; gap: 6px; background: var(--bg-card); padding: 1px 6px; border-radius: 4px; border: 1px solid var(--border-subtle);">
                                                        <span style="opacity: 0.3; text-decoration: line-through; font-size: 0.75rem;">{{ $log->details['old'][$type] ?: '---' }}</span>
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                                        <span style="color: var(--primary); font-weight: 700;">{{ $log->details['new'][$type] ?: '---' }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @elseif($log->action === 'delete')
                                    <div style="font-size: 0.95rem; font-weight: 700; color: #ef4444;">
                                        Deleted {{ $log->details['seat_id'] ?? 'Seat' }}
                                    </div>
                                @elseif($log->action === 'batch')
                                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">
                                        Batch: {{ $log->details['seat_count'] ?? 0 }} Seats Updated
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Right Side: Metadata --}}
                        <div style="display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; text-align: right; min-width: 120px;">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                                <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; background: var(--bg-card); padding: 2px 10px; border-radius: 12px; border: 1px solid var(--border-subtle);">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                                
                                @if($showRegistration && $log->registration)
                                    <a href="{{ route('aircraft.show', $log->registration) }}" style="text-decoration: none;">
                                        <div style="font-family: 'JetBrains Mono'; font-size: 0.75rem; background: var(--bg-card); color: var(--text-primary); padding: 2px 10px; border-radius: 6px; border: 1px solid var(--border-subtle); font-weight: 700; transition: all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-subtle)'; this.style.color='var(--text-primary)'">
                                            {{ $log->registration }}
                                        </div>
                                    </a>
                                @endif
                            </div>

                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 500; opacity: 0.7;">
                                {{ $log->created_at->format('d M Y · H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    function exportActivityToExcel() {
        if (typeof XLSX === 'undefined') {
            alert('Excel library not loaded. Please refresh the page.');
            return;
        }

        @php
            $exportData = $logs->map(function($log) {
                // Determine Part Number for the log
                $pn = '-';
                if ($log->action === 'update' || $log->action === 'batch') {
                    if (isset($log->details['seats'][0]) && $log->aircraft) {
                        $firstSeat = $log->details['seats'][0];
                        if (str_starts_with($firstSeat, 'inf-')) { $pn = $log->aircraft->pn_infant; }
                        elseif (in_array($firstSeat, ['captain', 'copilot', 'observer1', 'observer2']) || str_starts_with($firstSeat, 'att/')) { $pn = $log->aircraft->pn_crew; }
                        else { $pn = $log->aircraft->pn_adult; }
                    }
                } elseif ($log->action === 'pn_update') {
                    $changes = [];
                    foreach(['adult', 'crew', 'infant'] as $t) {
                        if(($log->details['old'][$t] ?? '') !== ($log->details['new'][$t] ?? '')) {
                            $changes[] = strtoupper($t) . ": " . ($log->details['old'][$t] ?: '---') . " -> " . ($log->details['new'][$t] ?: '---');
                        }
                    }
                    $pn = implode(' | ', $changes);
                }

                return [
                    'DATE & TIME' => $log->created_at->format('d/m/Y H:i'),
                    'ADMIN' => strtoupper($log->user->name ?? 'SYSTEM'),
                    'AIRCRAFT' => $log->registration ?? '-',
                    'ACTIVITY' => strtoupper($log->action === 'pn_update' ? 'P/N UPDATE' : ($log->action === 'batch' ? 'BATCH INPUT' : $log->action)),
                    'PART NUMBER' => $pn,
                    'SEATS COUNT' => $log->details['seat_count'] ?? (isset($log->details['seats']) ? count($log->details['seats']) : '-'),
                    'SEATS LIST' => isset($log->details['seats']) ? implode(', ', $log->details['seats']) : ($log->details['seat_id'] ?? '-'),
                    'EXPIRY DATE' => isset($log->details['expiry_date']) ? \Carbon\Carbon::parse($log->details['expiry_date'])->format('d/m/Y') : '-'
                ];
            });
        @endphp

        const logs = {!! json_encode($exportData) !!};

        if (logs.length === 0) {
            alert('No data to export.');
            return;
        }

        const ws = XLSX.utils.json_to_sheet(logs);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Life Vest Activity Log");
        
        // Column widths
        const colWidths = [
            { wch: 18 }, // DATE
            { wch: 15 }, // ADMIN
            { wch: 12 }, // AIRCRAFT
            { wch: 15 }, // ACTIVITY
            { wch: 20 }, // P/N
            { wch: 12 }, // COUNT
            { wch: 40 }, // LIST
            { wch: 12 }  // EXPIRY
        ];
        ws['!cols'] = colWidths;

        XLSX.writeFile(wb, "LifeVest_Activity_Log_" + new Date().toISOString().slice(0, 10) + ".xlsx");
    }
</script>
