@php $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y'; @endphp
<!-- Cockpit Section --><x-cockpit-section :seats="$seats" :isPdfExport="$isPdfExport ?? false" />

<!-- Attendant D11 & D21 (Forward - 4 seats: D11 3 left stacked, D21 1 right) -->
<section class="cabin-section">
    <h2>Attendant D11 & D21</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/d11-LL1">LL</span>
            <span class="col-label col-header" data-col="att/d11-LR">LR</span>
            <span class="row-label"></span>
            <span class="seat-placeholder"></span>
            <span class="col-label col-header" data-col="att/d21-R">R</span>
        </div>
        <!-- Row 1: D11-LL1 only, D21-R -->
        <div class="seat-row grid-row-2-2">
            @php
                $seatId = 'att/d11-LL1';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="LL1">
                <div class="seat-id">D11-LL1</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            <div class="seat-placeholder"></div>
            <div class="row-number">D11/D21</div>
            <div class="seat-placeholder"></div>
            @php
                $seatId = 'att/d21-R';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="R">
                <div class="seat-id">D21-R</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
        </div>
        <!-- Row 2: D11-LL2, D11-LR -->
        <div class="seat-row grid-row-2-2">
            @php
                $seatId = 'att/d11-LL2';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="LL2">
                <div class="seat-id">D11-LL2</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            @php
                $seatId = 'att/d11-LR';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="LR">
                <div class="seat-id">D11-LR</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            <div class="row-number"></div>
            <div class="seat-placeholder"></div>
            <div class="seat-placeholder"></div>
        </div>
    </div>
</section>

<!-- Cargo Section (Visual Only - Forward Cargo) -->
<section class="cabin-section cargo-section">
    <h2>Cargo Area (Forward)</h2>
    <div class="cargo-info">
        <div class="cargo-visual">
            <div class="cargo-icon"></div>
            <div class="cargo-text">
                <span class="cargo-title">Forward Cargo Compartment</span>
                <span class="cargo-subtitle">Area kargo depan</span>
            </div>
        </div>
    </div>
</section>

<!-- Attendant D12 & D22 (Door 2) - 3 seats: 1 left, 2 right -->
<section class="cabin-section">
    <h2>Attendant D12 & D22</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/d12-L">L</span>
            <span class="seat-placeholder"></span>
            <span class="row-label"></span>
            <span class="col-label col-header" data-col="att/d22-RL">RL</span>
            <span class="col-label col-header" data-col="att/d22-RR">RR</span>
        </div>
        <div class="seat-row grid-row-2-2">
            @php
                $seatId = 'att/d12-L';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="L">
                <div class="seat-id">D12-L</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            <div class="seat-placeholder"></div>
            <div class="row-number">D12/D22</div>
            @php
                $seatId = 'att/d22-RL';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="RL">
                <div class="seat-id">D22-RL</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            @php
                $seatId = 'att/d22-RR';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="RR">
                <div class="seat-id">D22-RR</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cargo Section (Visual Only - Not Selectable) -->
<section class="cabin-section cargo-section">
    <h2>Cargo Area (Main Deck)</h2>
    <div class="cargo-info">
        <div class="cargo-visual">
            <div class="cargo-icon"></div>
            <div class="cargo-text">
                <span class="cargo-title">Main Deck Cargo</span>
                <span class="cargo-subtitle">Area ini digunakan untuk kargo - tidak ada kursi penumpang</span>
            </div>
        </div>
    </div>
</section>

<!-- Attendant D13 & D23 (Door 3) - 2 seats: L / R -->
<section class="cabin-section">
    <h2>Attendant D13 & D23</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/d13-L">L</span>
            <span class="seat-placeholder"></span>
            <span class="row-label"></span>
            <span class="seat-placeholder"></span>
            <span class="col-label col-header" data-col="att/d23-R">R</span>
        </div>
        <div class="seat-row grid-row-2-2">
            @php
                $seatId = 'att/d13-L';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="L">
                <div class="seat-id">D13-L</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            <div class="seat-placeholder"></div>
            <div class="row-number">D13/D23</div>
            <div class="seat-placeholder"></div>
            @php
                $seatId = 'att/d23-R';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="R">
                <div class="seat-id">D23-R</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cargo Section (Visual Only - Lower Deck) -->
<section class="cabin-section cargo-section">
    <h2>Cargo Area (Lower Deck)</h2>
    <div class="cargo-info">
        <div class="cargo-visual">
            <div class="cargo-icon"></div>
            <div class="cargo-text">
                <span class="cargo-title">Lower Deck Cargo</span>
                <span class="cargo-subtitle">Belly cargo compartment</span>
            </div>
        </div>
    </div>
</section>

<!-- Attendant D14 & D24 + Aft Galley (Aft) - 6 seats: 2 left, 2 center, 2 right -->
<section class="cabin-section">
    <h2>Attendant D14 & D24 + Aft Galley</h2>
    <div style="display: flex; justify-content: center; align-items: flex-start; gap: 2rem;">
        <!-- D14 (Left - 2 seats) -->
        <div style="text-align: center;">
            <div class="col-label col-header" style="margin-bottom: 0.5rem;">Att/D-14</div>
            <div style="display: flex; gap: 0.5rem;">
                @php
                    $seatId = 'att/d14-LL';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="LL">
                    <div class="seat-id">D14-LL</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                @php
                    $seatId = 'att/d14-LR';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="LR">
                    <div class="seat-id">D14-LR</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Aft Galley (Center - 2 seats) -->
        <div style="text-align: center;">
            <div class="col-label col-header" style="margin-bottom: 0.5rem;">Aft Galley</div>
            <div style="display: flex; gap: 0.5rem;">
                @php
                    $seatId = 'att/aft-LC';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="LC">
                    <div class="seat-id">Aft-LC</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                @php
                    $seatId = 'att/aft-RC';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="RC">
                    <div class="seat-id">Aft-RC</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
        </div>

        <!-- D24 (Right - 2 seats) -->
        <div style="text-align: center;">
            <div class="col-label col-header" style="margin-bottom: 0.5rem;">Att/D-24</div>
            <div style="display: flex; gap: 0.5rem;">
                @php
                    $seatId = 'att/d24-RL';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="RL">
                    <div class="seat-id">D24-RL</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                @php
                    $seatId = 'att/d24-RR';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="RR">
                    <div class="seat-id">D24-RR</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Spare Section: PAX & INF -->
<section class="cabin-section">
    <h2>Spare</h2>
    <div class="spare-grid">
        <!-- PAX Column -->
        <div class="spare-column" id="pax-column">
            <h3>PAX</h3>
            <div class="spare-items" id="pax-items">
                @php
                    $paxSeats = collect($seats)->filter(fn($s, $id) => str_starts_with($id, 'pax-'))->sortBy(fn($s, $id) => (int) str_replace('pax-', '', $id));
                @endphp
                @forelse($paxSeats as $seatId => $seat)
                    @php
                        $num = str_replace('pax-', '', $seatId);
                        $status = $seat?->status ?? 'no-data';
                        $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                    @endphp
                    <div class="seat-card spare-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}">
                        <button type="button" class="btn-delete-spare" title="Delete">&times;</button>
                        <div class="seat-id">{{ $num }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $expiryDate }}
                        </div>
                    </div>
                @empty
                    <p class="empty-message">Belum ada data PAX</p>
                @endforelse
            </div>
            <button type="button" class="btn btn-add-spare" data-type="pax">+ Add PAX</button>
        </div>

        <!-- INF Column -->
        <div class="spare-column" id="inf-column">
            <h3>INF</h3>
            <div class="spare-items" id="inf-items">
                @php
                    $infSeats = collect($seats)->filter(fn($s, $id) => str_starts_with($id, 'inf-'))->sortBy(fn($s, $id) => (int) str_replace('inf-', '', $id));
                @endphp
                @forelse($infSeats as $seatId => $seat)
                    @php
                        $num = str_replace('inf-', '', $seatId);
                        $status = $seat?->status ?? 'no-data';
                        $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                    @endphp
                    <div class="seat-card spare-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}">
                        <button type="button" class="btn-delete-spare" title="Delete">&times;</button>
                        <div class="seat-id">{{ $num }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $expiryDate }}
                        </div>
                    </div>
                @empty
                    <p class="empty-message">Belum ada data INF</p>
                @endforelse
            </div>
            <button type="button" class="btn btn-add-spare" data-type="inf">+ Add INF</button>
        </div>
    </div>
</section>