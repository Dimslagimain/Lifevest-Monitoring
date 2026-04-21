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
                    <div style="width: 16px; height: 16px; border-radius: 50%; background: var(--bg-card); border: 3px solid {{ $dotColor }}; flex-shrink: 0; margin-top: 4px;"></div>

                    <div style="flex-grow: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">
                                {{ $log->user->name }} 
                                <span style="font-weight: 400; color: var(--text-muted); font-size: 0.9rem;">
                                    @if($log->action === 'update')
                                        updated {{ $log->details['seat_count'] ?? 0 }} seats
                                    @elseif($log->action === 'delete')
                                        deleted spare vest <strong>{{ $log->details['seat_id'] ?? '' }}</strong>
                                    @elseif($log->action === 'batch')
                                        performed batch update
                                    @else
                                        {{ $log->action }} action
                                    @endif
                                </span>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; white-space: nowrap; background: var(--bg-secondary); padding: 2px 8px; border-radius: 12px;">
                                {{ $log->created_at->diffForHumans() }}
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            @if($showRegistration && $log->registration)
                                <a href="{{ route('aircraft.show', $log->registration) }}" style="text-decoration: none;">
                                    <span style="font-family: 'JetBrains Mono'; font-size: 0.8rem; background: var(--primary-light); color: var(--primary); padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                        {{ $log->registration }}
                                    </span>
                                </a>
                            @endif

                            @if(isset($log->details['expiry_date']))
                                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                                    Exp: <span style="color: var(--text-primary); font-family: 'JetBrains Mono';">{{ \Carbon\Carbon::parse($log->details['expiry_date'])->format('d M Y') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
