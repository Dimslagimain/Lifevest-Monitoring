@php $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y'; @endphp
<!-- Cockpit Section -->
<x-cockpit-section :seats="$seats" :isPdfExport="$isPdfExport ?? false" />

<!-- Attendant D11 (Forward - 2 seats left) -->
<section class="cabin-section">
    <h2>Attendant D11</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-3-3">
            <span class="col-label col-header" data-col="att/d11-LL" data-row="D11">LL</span>
            <span class="col-label col-header" data-col="att/d11-LR" data-row="D11">LR</span>
            <span class="seat-placeholder"></span>
            <span class="row-label"></span>
            <span class="seat-placeholder"></span>
            <span class="seat-placeholder"></span>
            <span class="seat-placeholder"></span>
        </div>
        <div class="seat-row grid-row-3-3">
            @php
                $seatId = 'att/d11-LL';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D11" data-col="LL">
                <div class="seat-id">D11-LL</div>
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
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D11" data-col="LR">
                <div class="seat-id">D11-LR</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            <div class="seat-placeholder"></div>
            <div class="row-number" data-row="D11">D11</div>
            <div class="seat-placeholder"></div>
            <div class="seat-placeholder"></div>
            <div class="seat-placeholder"></div>
        </div>
    </div>
</section>

<!-- Economy Class - Rows 1-31 (3-3 layout: A B C - D E F, skip row 13) -->
<section class="cabin-section">
    <h2>Economy Class - Rows 1-31</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-3-3">
            <span class="col-label col-header" data-col="A">A</span>
            <span class="col-label col-header" data-col="B">B</span>
            <span class="col-label col-header" data-col="C">C</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="D">D</span>
            <span class="col-label col-header" data-col="E">E</span>
            <span class="col-label col-header" data-col="F">F</span>
        </div>
        @foreach(range(1, 31) as $row)
            @if($row == 13)
                @continue
            @endif
            @php
                $rowCols = ['A', 'B', 'C', 'D', 'E', 'F'];
            @endphp
            <div class="seat-row grid-row-3-3" data-row="{{ $row }}">
                @foreach(['A', 'B', 'C'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['D', 'E', 'F'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
            </div>
        @endforeach
    </div>
</section>

<!-- Attendant D12 & D22 (Aft - 1 left, 2 right) -->
<section class="cabin-section">
    <h2>Attendant D12 & D22</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-3-3">
            <span class="col-label col-header" data-col="att/d12-L" data-row="D12/D22">L</span>
            <span class="seat-placeholder"></span>
            <span class="seat-placeholder"></span>
            <span class="row-label"></span>
            <span class="seat-placeholder"></span>
            <span class="col-label col-header" data-col="att/d22-RL" data-row="D12/D22">RL</span>
            <span class="col-label col-header" data-col="att/d22-RR" data-row="D12/D22">RR</span>
        </div>
        <div class="seat-row grid-row-3-3">
            @php
                $seatId = 'att/d12-L';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D12/D22" data-col="L">
                <div class="seat-id">D12-L</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            <div class="seat-placeholder"></div>
            <div class="seat-placeholder"></div>
            <div class="row-number" data-row="D12/D22">D12/D22</div>
            <div class="seat-placeholder"></div>
            @php
                $seatId = 'att/d22-RL';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D12/D22" data-col="RL">
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
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D12/D22" data-col="RR">
                <div class="seat-id">D22-RR</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
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