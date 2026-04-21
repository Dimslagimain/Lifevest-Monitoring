@php $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y'; @endphp
<!-- Cockpit Section --><x-cockpit-section :seats="$seats" :isPdfExport="$isPdfExport ?? false" />

<!-- Attendant D11 & D21 (Forward - 3 seats: D11 LL,LR left / D21 R right) -->
<section class="cabin-section">
    <h2>Attendant D11 & D21</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/d11-LL">LL</span>
            <span class="col-label col-header" data-col="att/d11-LR">LR</span>
            <span class="row-label"></span>
            <span class="seat-placeholder"></span>
            <span class="col-label col-header" data-col="att/d21-R">R</span>
        </div>
        <div class="seat-row grid-row-2-2">
            @foreach(['LL', 'LR'] as $col)
                @php
                    $seatId = 'att/d11-' . $col;
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="{{ $col }}">
                    <div class="seat-id">D11-{{ $col }}</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            @endforeach
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
    </div>
</section>

<!-- Business Class - Rows 6-8 (2-2-2 layout: A C - D G - H K) -->
<section class="cabin-section">
    <h2>Business Class - Rows 6-8</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2-2">
            <span class="col-label col-header" data-col="A">A</span>
            <span class="col-label col-header" data-col="C">C</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="D">D</span>
            <span class="col-label col-header" data-col="G">G</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="H">H</span>
            <span class="col-label col-header" data-col="K">K</span>
        </div>
        @foreach(range(6, 8) as $row)
            @php
                $rowCols = ['A', 'C', 'D', 'G', 'H', 'K'];
            @endphp
            <div class="seat-row grid-row-2-2-2" data-row="{{ $row }}">
                @foreach(['A', 'C'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['D', 'G'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['H', 'K'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
            </div>
        @endforeach
    </div>
</section>

<!-- Attendant D12 & D22 (Between Business and Economy) - 4 seats: D12 LL,LR / D22 RL,RR -->
<section class="cabin-section">
    <h2>Attendant D12 & D22</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/d12-LL">LL</span>
            <span class="col-label col-header" data-col="att/d12-LR">LR</span>
            <span class="row-label"></span>
            <span class="col-label col-header" data-col="att/d22-RL">RL</span>
            <span class="col-label col-header" data-col="att/d22-RR">RR</span>
        </div>
        <div class="seat-row grid-row-2-2">
            @foreach(['LL', 'LR'] as $col)
                @php
                    $seatId = 'att/d12-' . $col;
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="{{ $col }}">
                    <div class="seat-id">D12-{{ $col }}</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            @endforeach
            <div class="row-number">D12/D22</div>
            @foreach(['RL', 'RR'] as $col)
                @php
                    $seatId = 'att/d22-' . $col;
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="{{ $col }}">
                    <div class="seat-id">D22-{{ $col }}</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Economy Class - Rows 21-38 (2-4-2 layout, skip row 24) -->
<section class="cabin-section">
    <h2>Economy Class - Rows 21-38</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-4-2">
            <span class="col-label col-header" data-col="A">A</span>
            <span class="col-label col-header" data-col="C">C</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="D">D</span>
            <span class="col-label col-header" data-col="E">E</span>
            <span class="col-label col-header" data-col="F">F</span>
            <span class="col-label col-header" data-col="G">G</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="H">H</span>
            <span class="col-label col-header" data-col="K">K</span>
        </div>
        @foreach(range(21, 38) as $row)
            @if($row == 24)
                @continue
            @endif
            @php
                $rowCols = ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'];
            @endphp
            <div class="seat-row grid-row-2-4-2" data-row="{{ $row }}">
                @foreach(['A', 'C'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['D', 'E', 'F', 'G'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['H', 'K'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
            </div>
        @endforeach
    </div>
</section>

<!-- Attendant D13 & D23 (Between row 38-39) - 2 seats: D13-L / D23-R -->
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

<!-- Economy Class - Rows 39-52 (2-4-2 layout with tail exceptions) -->
<section class="cabin-section">
    <h2>Economy Class - Rows 39-52</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-4-2">
            <span class="col-label col-header" data-col="A">A</span>
            <span class="col-label col-header" data-col="C">C</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="D">D</span>
            <span class="col-label col-header" data-col="E">E</span>
            <span class="col-label col-header" data-col="F">F</span>
            <span class="col-label col-header" data-col="G">G</span>
            <span class="row-label">Row</span>
            <span class="col-label col-header" data-col="H">H</span>
            <span class="col-label col-header" data-col="K">K</span>
        </div>
        @php
            $exceptions = [
                49 => ['A', 'C', 'D', 'F', 'G', 'H', 'K'],
                50 => ['A', 'C', 'D', 'F', 'G', 'H', 'K'],
                51 => ['A', 'C', 'D', 'F', 'G', 'H', 'K'],
                52 => ['A', 'C', 'D', 'F', 'G'],
            ];
        @endphp
        @foreach(range(39, 52) as $row)
            @php
                $rowCols = $exceptions[$row] ?? ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'];
            @endphp
            <div class="seat-row grid-row-2-4-2" data-row="{{ $row }}">
                @foreach(['A', 'C'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['D', 'E', 'F', 'G'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
                <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                @foreach(['H', 'K'] as $col)
                    @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                @endforeach
            </div>
        @endforeach
    </div>
</section>

<!-- Attendant D14 & D24 (Below row 52) - 2 seats: D14-L / D24-R -->
<section class="cabin-section">
    <h2>Attendant D14 & D24</h2>
    <div class="seat-grid">
        <div class="grid-header grid-row-2-2">
            <span class="col-label col-header" data-col="att/d14-L">L</span>
            <span class="seat-placeholder"></span>
            <span class="row-label"></span>
            <span class="seat-placeholder"></span>
            <span class="col-label col-header" data-col="att/d24-R">R</span>
        </div>
        <div class="seat-row grid-row-2-2">
            @php
                $seatId = 'att/d14-L';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="L">
                <div class="seat-id">D14-L</div>
                <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                    {{ $expiryDate }}
                </div>
            </div>
            <div class="seat-placeholder"></div>
            <div class="row-number">D14/D24</div>
            <div class="seat-placeholder"></div>
            @php
                $seatId = 'att/d24-R';
                $seat = $seats[$seatId] ?? null;
                $status = $seat?->status ?? 'no-data';
                $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
            @endphp
            <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="R">
                <div class="seat-id">D24-R</div>
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