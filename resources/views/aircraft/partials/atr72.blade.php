@php $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y'; @endphp
<!-- Cockpit Section --><x-cockpit-section :seats="$seats" :isPdfExport="$isPdfExport ?? false" />

<!-- Attendant FWD (Forward - 1 seat left) -->
<section class="cabin-section">
    <h2>Attendant FWD</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/fwd-L">L</span>
            <span class="seat-placeholder"></span>
            <span class="row-label"></span>
            <span class="seat-placeholder"></span>
            <span class="seat-placeholder"></span>
        </div>
        <div class="seat-row grid-row-2-2">
            @php
                $seatId = 'att/fwd-L';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="L">
                <div class="seat-id">FWD-L</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            <div class="seat-placeholder"></div>
            <div class="row-number">FWD</div>
            <div class="seat-placeholder"></div>
            <div class="seat-placeholder"></div>
        </div>
    </div>
</section>

<!-- Economy Class - Rows 1-19 (2-2 layout: A C - H K) -->
<section class="cabin-section">
    <h2>Economy Class - Rows 1-19</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="A">A</span>
            <span class="col-label col-header" data-col="B">B</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="C">C</span>
            <span class="col-label col-header" data-col="D">D</span>
        </div>
        @php
            $exceptions = [
                19 => ['A', 'B'],
            ];
        @endphp
        @foreach(range(1, 19) as $row)
            @if($row == 13)
                @continue
            @endif
            @php
                $rowCols = $exceptions[$row] ?? ['A', 'B', 'C', 'D'];
            @endphp
            <div class="seat-row grid-row-2-2" data-row="{{ $row }}">
                @foreach(['A', 'B'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['C', 'D'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
            </div>
        @endforeach
    </div>
</section>

<!-- Attendant AFT (Aft - 1 seat left) -->
<section class="cabin-section">
    <h2>Attendant AFT</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/aft-L">L</span>
            <span class="seat-placeholder"></span>
            <span class="row-label"></span>
            <span class="seat-placeholder"></span>
            <span class="seat-placeholder"></span>
        </div>
        <div class="seat-row grid-row-2-2">
            @php
                $seatId = 'att/aft-L';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="L">
                <div class="seat-id">AFT-L</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            <div class="seat-placeholder"></div>
            <div class="row-number">AFT</div>
            <div class="seat-placeholder"></div>
            <div class="seat-placeholder"></div>
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