@props(['logs', 'title' => 'Recent Activity', 'showRegistration' => true])

<div class="replacement-card" style="padding: 1.5rem; border-left: none;">
    <h3 style="margin: 0 0 1.25rem 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.75rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
        {{ $title }}
    </h3>

    @if($logs->isEmpty())
        <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
            <p>No activity recorded yet.</p>
        </div>
    @else
        <div class="activity-timeline" style="display: flex; flex-direction: column; gap: 1.25rem; position: relative;">
            {{-- Vertical Line --}}
            <div style="position: absolute; left: 7px; top: 10px; bottom: 10px; width: 2px; background: var(--border-subtle); z-index: 0;"></div>

            @foreach($logs as $log)
                <div class="activity-item" style="display: flex; gap: 1.25rem; position: relative; z-index: 1;">
                    {{-- Status Dot --}}
                    @php
                        $dotColor = $log->action === 'delete' ? '#ef4444' : ($log->action === 'add' ? '#10b981' : '#3b82f6');
                    @endphp
                    <div style="width: 14px; height: 14px; border-radius: 50%; background: var(--bg-card); border: 3px solid {{ $dotColor }}; flex-shrink: 0; margin-top: 6px; box-shadow: 0 0 0 4px var(--bg-card);"></div>

                    <div style="flex-grow: 1; background: var(--bg-secondary); padding: 1rem; border-radius: 12px; border: 1px solid var(--border-subtle); display: flex; justify-content: space-between; gap: 1.5rem; transition: all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary-light)'; this.style.background='var(--bg-card)'" onmouseout="this.style.borderColor='var(--border-subtle)'; this.style.background='var(--bg-secondary)'">
                        {{-- Left Side: Action Details --}}
                        <div style="flex-grow: 1;">
                            <div style="font-weight: 800; color: var(--text-primary); font-size: 0.95rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                {{ $log->user->name }}
                                <span style="width: 4px; height: 4px; border-radius: 50%; background: var(--text-muted); opacity: 0.5;"></span>
                                <span style="font-weight: 500; color: var(--text-muted); font-size: 0.85rem;">
                                    @if($log->action === 'update') updated seats @elseif($log->action === 'delete') deleted seat @elseif($log->action === 'batch') batch input @elseif($log->action === 'pn_update') updated P/N @else {{ $log->action }} @endif
                                </span>
                            </div>

                            <div class="action-content">
                                @if($log->action === 'update')
                                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">
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
                                        <div style="margin-top: 10px;">
                                            <span style="font-size: 0.85rem; color: var(--primary); background: white; padding: 4px 12px; border-radius: 8px; border: 2px solid var(--primary-light); font-family: 'JetBrains Mono', monospace; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.8;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                                <span style="color: var(--text-muted); font-weight: 400; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Part Number</span>
                                                {{ $pnDisplay }}
                                            </span>
                                        </div>
                                    @endif
                                    @if(isset($log->details['seats']))
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 10px; line-height: 1.5; padding: 8px 12px; background: rgba(0,0,0,0.02); border-radius: 6px; border-left: 3px solid var(--border-subtle);">
                                            <span style="font-weight: 600; color: var(--text-primary); margin-right: 4px;">Seats:</span>
                                            {{ implode(', ', array_slice($log->details['seats'], 0, 20)) }}{{ count($log->details['seats']) > 20 ? '...' : '' }}
                                        </div>
                                    @endif

                                @elseif($log->action === 'pn_update')
                                    <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px; padding: 10px; background: white; border-radius: 8px; border: 1px dashed var(--border-subtle);">
                                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Update Details</div>
                                        @foreach(['adult', 'crew', 'infant'] as $type)
                                            @if(($log->details['old'][$type] ?? '') !== ($log->details['new'][$type] ?? ''))
                                                <div style="display: flex; align-items: center; gap: 12px; font-size: 0.85rem;">
                                                    <span style="width: 70px; color: var(--text-primary); font-weight: 700;">{{ ucfirst($type) }}</span>
                                                    <div style="font-family: 'JetBrains Mono', monospace; display: flex; align-items: center; gap: 8px; background: var(--bg-secondary); padding: 4px 10px; border-radius: 6px; border: 1px solid var(--border-subtle);">
                                                        <span style="opacity: 0.4; text-decoration: line-through;">{{ $log->details['old'][$type] ?: '---' }}</span>
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                                        <span style="color: var(--primary); font-weight: 800;">{{ $log->details['new'][$type] ?: '---' }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @elseif($log->action === 'delete')
                                    <div style="font-size: 1.1rem; font-weight: 700; color: #ef4444;">
                                        Deleted {{ $log->details['seat_id'] ?? 'Seat' }}
                                    </div>
                                @elseif($log->action === 'batch')
                                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">
                                        Batch Input: {{ $log->details['seat_count'] ?? 0 }} Seats
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Right Side: Metadata --}}
                        <div style="display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; text-align: right; min-width: 150px;">
                            <div style="display: flex; flex-direction: column; gap: 0.75rem; align-items: flex-end;">
                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; background: white; padding: 4px 12px; border-radius: 20px; border: 1px solid var(--border-subtle); box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                                
                                @if($showRegistration && $log->registration)
                                    <a href="{{ route('aircraft.show', $log->registration) }}" style="text-decoration: none;">
                                        <div style="font-family: 'JetBrains Mono'; font-size: 0.85rem; background: var(--bg-secondary); color: var(--text-primary); padding: 4px 12px; border-radius: 6px; border: 1px solid var(--border-subtle); font-weight: 700; transition: all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-subtle)'; this.style.color='var(--text-primary)'">
                                            {{ $log->registration }}
                                        </div>
                                    </a>
                                @endif
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                                @if(isset($log->details['expiry_date']))
                                    <div style="font-size: 0.8rem; color: var(--text-primary); font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                        <span style="color: var(--text-muted); font-weight: 400; font-size: 0.75rem;">Exp:</span>
                                        {{ \Carbon\Carbon::parse($log->details['expiry_date'])->format('d M Y') }}
                                    </div>
                                @endif
                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 500; opacity: 0.8; letter-spacing: 0.3px;">
                                    {{ $log->created_at->format('d M Y · H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
