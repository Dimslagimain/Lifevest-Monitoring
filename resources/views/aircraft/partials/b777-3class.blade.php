@php $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y'; @endphp
<!-- Cockpit Section --><x-cockpit-section :seats="$seats" :isPdfExport="$isPdfExport ?? false" />

    <!-- Attendant Door 1 (Forward) - 4 seats: L, CL, CR, R -->
    <section class="cabin-section">
        <h2>Attendant Door 1</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-2-2">
                <span class="col-label col-header" data-col="att/d1-L">L</span>
                <span class="col-label col-header" data-col="att/d1-CL">CL</span>
                <span class="row-label"></span>
                <span class="col-label col-header" data-col="att/d1-CR">CR</span>
                <span class="col-label col-header" data-col="att/d1-R">R</span>
            </div>
            <div class="seat-row grid-row-2-2">
                @foreach(['L', 'CL'] as $col)
                    @php
                        $seatId = 'att/d1-' . $col;
                        $seat = $seats[$seatId] ?? null;
                        $status = $seat?->status ?? 'no-data';
                        $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                    @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="{{ $col }}">
                        <div class="seat-id">D1-{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $expiryDate }}
                        </div>
                    </div>
                @endforeach
                <div class="row-number">D1</div>
                @foreach(['CR', 'R'] as $col)
                    @php
                        $seatId = 'att/d1-' . $col;
                        $seat = $seats[$seatId] ?? null;
                        $status = $seat?->status ?? 'no-data';
                        $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                    @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="{{ $col }}">
                        <div class="seat-id">D1-{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $expiryDate }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- First Class - Rows 1-2 (A D G K) -->
    <section class="cabin-section">
        <h2>First Class - Rows 1-2</h2>
        <div class="seat-grid grid-business">
            <div class="grid-header-business">
                <span class="col-label">A</span>
                <span class="aisle-gap"></span>
                <span class="col-label">D</span>
                <span class="col-label row-label">Row</span>
                <span class="col-label">G</span>
                <span class="aisle-gap"></span>
                <span class="col-label">K</span>
            </div>
            @foreach([1, 2] as $row)
                <div class="seat-row-business" data-row="{{ $row }}">
                    @php $seat = $seats["{$row}A"] ?? null;
                    $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}A" data-row="{{ $row }}" data-col="A">
                        <div class="seat-id">{{ $row }}A</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                    <div class="aisle-gap"></div>
                    @php $seat = $seats["{$row}D"] ?? null;
                    $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}D" data-row="{{ $row }}" data-col="D">
                        <div class="seat-id">{{ $row }}D</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                    <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                    @php $seat = $seats["{$row}G"] ?? null;
                    $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}G" data-row="{{ $row }}" data-col="G">
                        <div class="seat-id">{{ $row }}G</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                    <div class="aisle-gap"></div>
                    @php $seat = $seats["{$row}K"] ?? null;
                    $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}K" data-row="{{ $row }}" data-col="K">
                        <div class="seat-id">{{ $row }}K</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Business Class Section 1 - Rows 6-8 (Staggered) -->
    <section class="cabin-section">
        <h2>Business Class - Rows 6-8</h2>
        <div class="seat-grid grid-business">
            <div class="grid-header-business">
                <span class="col-label">A/C</span>
                <span class="aisle-gap"></span>
                <span class="col-label">D/E</span>
                <span class="col-label row-label">Row</span>
                <span class="col-label">G/F</span>
                <span class="aisle-gap"></span>
                <span class="col-label">K/H</span>
            </div>
            @php
                $staggeredPattern1 = [
                    6 => ['A', 'E', 'F', 'K'],
                    7 => ['C', 'D', 'G', 'H'],
                    8 => ['A', 'K'],  // Only 2 seats
                ];
            @endphp
            @foreach($staggeredPattern1 as $row => $cols)
                <div class="seat-row-business" data-row="{{ $row }}">
                    @if(count($cols) == 4)
                        @php $col = $cols[0]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                        <div class="aisle-gap"></div>
                        @php $col = $cols[1]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                        <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                        @php $col = $cols[2]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                        <div class="aisle-gap"></div>
                        @php $col = $cols[3]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                    @else
                        {{-- Row 8: A and K only --}}
                        @php $col = $cols[0]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                        <div class="aisle-gap"></div>
                        <div class="seat-placeholder"></div>
                        <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                        <div class="seat-placeholder"></div>
                        <div class="aisle-gap"></div>
                        @php $col = $cols[1]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <!-- Attendant Door 2 (Above Economy) - 4 seats stacked: D2-L1, D2-L2 (left), D2-R1, D2-R2 (right) -->
    <section class="cabin-section">
        <h2>Attendant Door 2</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-2-2">
                <span class="col-label col-header" data-col="att/d2-L">L</span>
                <span class="seat-placeholder"></span>
                <span class="row-label"></span>
                <span class="seat-placeholder"></span>
                <span class="col-label col-header" data-col="att/d2-R">R</span>
            </div>
            <!-- Row 1: D2-L1, D2-R1 -->
            <div class="seat-row grid-row-2-2">
                @php
                    $seatId = 'att/d2-L1';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="L1">
                    <div class="seat-id">D2-L1</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                <div class="seat-placeholder"></div>
                <div class="row-number">D2</div>
                <div class="seat-placeholder"></div>
                @php
                    $seatId = 'att/d2-R1';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="R1">
                    <div class="seat-id">D2-R1</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
            <!-- Row 2: D2-L2, D2-R2 -->
            <div class="seat-row grid-row-2-2">
                @php
                    $seatId = 'att/d2-L2';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="L2">
                    <div class="seat-id">D2-L2</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                <div class="seat-placeholder"></div>
                <div class="row-number"></div>
                <div class="seat-placeholder"></div>
                @php
                    $seatId = 'att/d2-R2';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="R2">
                    <div class="seat-id">D2-R2</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Business Class Section 2 - Rows 9-16 (Staggered) -->
    <section class="cabin-section">
        <h2>Business Class - Rows 9-16</h2>
        <div class="seat-grid grid-business">
            <div class="grid-header-business">
                <span class="col-label">A/C</span>
                <span class="aisle-gap"></span>
                <span class="col-label">D/E</span>
                <span class="col-label row-label">Row</span>
                <span class="col-label">G/F</span>
                <span class="aisle-gap"></span>
                <span class="col-label">K/H</span>
            </div>
            @php
                $staggeredPattern2 = [
                    9 => ['A', 'E', 'F', 'K'],
                    10 => ['C', 'D', 'G', 'H'],
                    11 => ['A', 'E', 'F', 'K'],
                    12 => ['C', 'D', 'G', 'H'],
                    14 => ['A', 'E', 'F', 'K'],
                    15 => ['C', 'D', 'G', 'H'],
                    16 => ['A', 'E', 'F', 'K'],
                ];
            @endphp
            @foreach($staggeredPattern2 as $row => $cols)
                <div class="seat-row-business" data-row="{{ $row }}">
                    @php $col = $cols[0]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                        <div class="seat-id">{{ $row }}{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                    <div class="aisle-gap"></div>
                    @php $col = $cols[1]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                        <div class="seat-id">{{ $row }}{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                    <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                    @php $col = $cols[2]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                        <div class="seat-id">{{ $row }}{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                    <div class="aisle-gap"></div>
                    @php $col = $cols[3]; $seat = $seats["{$row}{$col}"] ?? null; $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}" data-col="{{ $col }}">
                        <div class="seat-id">{{ $row }}{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">{{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Economy Class 1 - Rows 21-25 -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 21-25</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-3-3-3">
                <span class="col-label col-header" data-col="A">A</span>
                <span class="col-label col-header" data-col="B">B</span>
                <span class="col-label col-header" data-col="C">C</span>
                <span class="row-label">Row</span>
                <span class="col-label col-header" data-col="D">D</span>
                <span class="col-label col-header" data-col="F">F</span>
                <span class="col-label col-header" data-col="G">G</span>
                <span class="row-label">Row</span>
                <span class="col-label col-header" data-col="H">H</span>
                <span class="col-label col-header" data-col="J">J</span>
                <span class="col-label col-header" data-col="K">K</span>
            </div>
            @php
                $allCols = ['A', 'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K'];
                $exceptions1 = [
                    24 => [], // Skipped
                    25 => ['D', 'F', 'G'], // Center only
                ];
            @endphp
            @for($row = 21; $row <= 25; $row++)
                @if($row == 24) @continue @endif
                @php $rowCols = $exceptions1[$row] ?? $allCols; @endphp
                <div class="seat-row grid-row-3-3-3" data-row="{{ $row }}">
                    @foreach(['A', 'B', 'C'] as $col)
                        @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                    @endforeach
                    <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                    @foreach(['D', 'F', 'G'] as $col)
                        @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                    @endforeach
                    <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                    @foreach(['H', 'J', 'K'] as $col)
                        @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                    @endforeach
                </div>
            @endfor
        </div>
    </section>

    <!-- Attendant Door 3 (Between row 25-26) - 2 seats: L and R -->
    <section class="cabin-section">
        <h2>Attendant Door 3</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-2-2">
                <span class="col-label col-header" data-col="att/d3-L">L</span>
                <span class="seat-placeholder"></span>
                <span class="row-label"></span>
                <span class="seat-placeholder"></span>
                <span class="col-label col-header" data-col="att/d3-R">R</span>
            </div>
            <div class="seat-row grid-row-2-2">
                @php
                    $seatId = 'att/d3-L';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="L">
                    <div class="seat-id">D3-L</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                <div class="seat-placeholder"></div>
                <div class="row-number">D3</div>
                <div class="seat-placeholder"></div>
                @php
                    $seatId = 'att/d3-R';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="R">
                    <div class="seat-id">D3-R</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Economy Class 2 - Rows 26-38 -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 26-38</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-3-3-3">
                <span class="col-label col-header" data-col="A">A</span>
                <span class="col-label col-header" data-col="B">B</span>
                <span class="col-label col-header" data-col="C">C</span>
                <span class="row-label">Row</span>
                <span class="col-label col-header" data-col="D">D</span>
                <span class="col-label col-header" data-col="F">F</span>
                <span class="col-label col-header" data-col="G">G</span>
                <span class="row-label">Row</span>
                <span class="col-label col-header" data-col="H">H</span>
                <span class="col-label col-header" data-col="J">J</span>
                <span class="col-label col-header" data-col="K">K</span>
            </div>
            @php
                $exceptions2 = [
                    38 => ['A', 'B', 'C', 'H', 'J', 'K'], // No center
                ];
            @endphp
            @for($row = 26; $row <= 38; $row++)
                @php $rowCols = $exceptions2[$row] ?? $allCols; @endphp
                <div class="seat-row grid-row-3-3-3" data-row="{{ $row }}">
                    @foreach(['A', 'B', 'C'] as $col)
                        @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                    @endforeach
                    <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                    @foreach(['D', 'F', 'G'] as $col)
                        @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                    @endforeach
                    <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                    @foreach(['H', 'J', 'K'] as $col)
                        @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                    @endforeach
                </div>
            @endfor
        </div>
    </section>

    <!-- Attendant Door 4 (Between row 38-39) - 2 seats: L and R -->
    <section class="cabin-section">
        <h2>Attendant Door 4</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-2-2">
                <span class="col-label col-header" data-col="att/d4-L">L</span>
                <span class="seat-placeholder"></span>
                <span class="row-label"></span>
                <span class="seat-placeholder"></span>
                <span class="col-label col-header" data-col="att/d4-R">R</span>
            </div>
            <div class="seat-row grid-row-2-2">
                @php
                    $seatId = 'att/d4-L';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="L">
                    <div class="seat-id">D4-L</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                <div class="seat-placeholder"></div>
                <div class="row-number">D4</div>
                <div class="seat-placeholder"></div>
                @php
                    $seatId = 'att/d4-R';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="R">
                    <div class="seat-id">D4-R</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Economy Class 3 - Rows 39-52 -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 39-52</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-3-3-3">
                <span class="col-label col-header" data-col="A">A</span>
                <span class="col-label col-header" data-col="B">B</span>
                <span class="col-label col-header" data-col="C">C</span>
                <span class="row-label">Row</span>
                <span class="col-label col-header" data-col="D">D</span>
                <span class="col-label col-header" data-col="F">F</span>
                <span class="col-label col-header" data-col="G">G</span>
                <span class="row-label">Row</span>
                <span class="col-label col-header" data-col="H">H</span>
                <span class="col-label col-header" data-col="J">J</span>
                <span class="col-label col-header" data-col="K">K</span>
            </div>
            @php
                $exceptions3 = [
                    52 => ['A', 'C', 'D', 'F', 'G', 'H', 'K'],
                ];
            @endphp
            @for($row = 39; $row <= 52; $row++)
                @php $rowCols = $exceptions3[$row] ?? $allCols; @endphp
                <div class="seat-row grid-row-3-3-3" data-row="{{ $row }}">
                    @foreach(['A', 'B', 'C'] as $col)
                        @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                    @endforeach
                    <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                    @foreach(['D', 'F', 'G'] as $col)
                        @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                    @endforeach
                    <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                    @foreach(['H', 'J', 'K'] as $col)
                        @include('components.seat-cell', ['row' => $row, 'col' => $col, 'rowCols' => $rowCols, 'seats' => $seats])
                    @endforeach
                </div>
            @endfor
        </div>
    </section>

    <!-- Attendant Door 5 (Aft, below row 52) - 6 seats: LL, LC, LR, RL, RC, RR -->
    <section class="cabin-section">
        <h2>Attendant Door 5</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-3-3">
                <span class="col-label col-header" data-col="att/d5-LL">LL</span>
                <span class="col-label col-header" data-col="att/d5-LC">LC</span>
                <span class="col-label col-header" data-col="att/d5-LR">LR</span>
                <span class="row-label"></span>
                <span class="col-label col-header" data-col="att/d5-RL">RL</span>
                <span class="col-label col-header" data-col="att/d5-RC">RC</span>
                <span class="col-label col-header" data-col="att/d5-RR">RR</span>
            </div>
            <div class="seat-row grid-row-3-3">
                @foreach(['LL', 'LC', 'LR'] as $col)
                    @php
                        $seatId = 'att/d5-' . $col;
                        $seat = $seats[$seatId] ?? null;
                        $status = $seat?->status ?? 'no-data';
                        $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                    @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="{{ $col }}">
                        <div class="seat-id">D5-{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $expiryDate }}
                        </div>
                    </div>
                @endforeach
                <div class="row-number">D5</div>
                @foreach(['RL', 'RC', 'RR'] as $col)
                    @php
                        $seatId = 'att/d5-' . $col;
                        $seat = $seats[$seatId] ?? null;
                        $status = $seat?->status ?? 'no-data';
                        $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                    @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="{{ $col }}">
                        <div class="seat-id">D5-{{ $col }}</div>
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