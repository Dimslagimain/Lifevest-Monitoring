@php $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y'; @endphp
<!-- Cockpit Section --><x-cockpit-section :seats="$seats" :isPdfExport="$isPdfExport ?? false" />

<!-- Attendant D11 (Forward) - 2 seats near cockpit -->
<section class="cabin-section">
    <h2>Attendant D11</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/d11-LL" data-row="D11">LL</span>
            <span class="col-label col-header" data-col="att/d11-LR" data-row="D11">LR</span>
            <span class="row-label"></span>
            <span class="seat-placeholder"></span>
            <span class="seat-placeholder"></span>
        </div>
        <div class="seat-row grid-row-2-2">
            @foreach(['LL', 'LR'] as $col)
                @php
                    $seatId = 'att/d11-' . $col;
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D11" data-col="{{ $col }}">
                    <div class="seat-id">D11-{{ $col }}</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            @endforeach
            <div class="row-number" data-row="D11">D11</div>
            <div class="seat-placeholder"></div>
            <div class="seat-placeholder"></div>
        </div>
    </div>
</section>

<!-- Business Class - Rows 6-7 (2-2 layout: A C - Row - H K) -->
<section class="cabin-section">
    <h2>Business Class - Rows 6-7</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="A">A</span>
            <span class="col-label col-header" data-col="C">C</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="H">H</span>
            <span class="col-label col-header" data-col="K">K</span>
        </div>
        @foreach([6, 7] as $row)
            <div class="seat-row grid-row-2-2" data-row="{{ $row }}">
                @foreach(['A', 'C'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => ['A', 'C', 'H', 'K'], 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['H', 'K'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => ['A', 'C', 'H', 'K'], 'seats' => $seats])
                @endforeach
            </div>
        @endforeach
    </div>
</section>

<!-- Economy Class - Rows 21-47 (3-3 layout, row 47 only A B C) -->
<section class="cabin-section">
    <h2>Economy Class - Rows 21-47</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-3-3">
            <span class="col-label col-header" data-col="A">A</span>
            <span class="col-label col-header" data-col="B">B</span>
            <span class="col-label col-header" data-col="C">C</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="H">H</span>
            <span class="col-label col-header" data-col="J">J</span>
            <span class="col-label col-header" data-col="K">K</span>
        </div>
        @foreach(range(21, 47) as $row)
            @if($row == 24)
                @continue
            @endif
            @php
                // Row 47 only has A B C
                $rowCols = ($row == 47) ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'H', 'J', 'K'];
            @endphp
            <div class="seat-row grid-row-3-3" data-row="{{ $row }}">
                @foreach(['A', 'B', 'C'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['H', 'J', 'K'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
            </div>
        @endforeach
    </div>
</section>

<!-- Attendant D12 & D22 (Rear) -->
<section class="cabin-section">
    <h2>Attendant D12 & D22</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/d12-LL" data-row="D12/D22">LL</span>
            <span class="col-label col-header" data-col="att/d12-LR" data-row="D12/D22">LR</span>
            <span class="row-label"></span>
            <span class="col-label col-header" data-col="att/d22-RL" data-row="D12/D22">RL</span>
            <span class="col-label col-header" data-col="att/d22-RR" data-row="D12/D22">RR</span>
        </div>
        <div class="seat-row grid-row-2-2">
            @foreach(['LL', 'LR'] as $col)
                @php
                    $seatId = 'att/d12-' . $col;
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D12/D22" data-col="{{ $col }}">
                    <div class="seat-id">D12-{{ $col }}</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            @endforeach
            <div class="row-number" data-row="D12/D22">D12/D22</div>
            @foreach(['RL', 'RR'] as $col)
                @php
                    $seatId = 'att/d22-' . $col;
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D12/D22" data-col="{{ $col }}">
                    <div class="seat-id">D22-{{ $col }}</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            @endforeach
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