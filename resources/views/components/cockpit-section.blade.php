@props(['seats', 'isPdfExport' => false])

<!-- Cockpit Section -->
<section class="cockpit-section">
    <h2>Cockpit</h2>
    <div class="cockpit-grid">
        @foreach(['pilot', 'observer1', 'observer2', 'copilot'] as $seatId)
            @php
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? 'Click to set';
            @endphp
            <div class="seat-card cockpit-seat status-{{ $status }}" data-seat="{{ $seatId }}" data-row="Cockpit">
                <div class="seat-label">{{ ucfirst(str_replace(['1', '2'], [' 1', ' 2'], $seatId)) }}</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
        @endforeach
    </div>
</section>