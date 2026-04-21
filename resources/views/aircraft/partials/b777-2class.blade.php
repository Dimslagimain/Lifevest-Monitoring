@php $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y'; @endphp
<!-- Cockpit Section --><x-cockpit-section :seats="$seats" :isPdfExport="$isPdfExport ?? false" />

    <!-- Attendant Door 1 (Forward) - 4 seats: door-1L, center door-1 x2, door-1R -->
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

    <!-- Business Class - Rows 6-12 (Staggered: even=C,E,F,H odd=A,D,G,K, row 12 only E,F) -->
    <section class="cabin-section">
        <h2>Business Class - Rows 6-12</h2>
        <div class="seat-grid grid-business">
            <div class="grid-header-business">
                <span class="col-label">A/C</span>
                <span class="aisle-gap">🚶</span>
                <span class="col-label">D/E</span>
                <span class="col-label row-label">Row</span>
                <span class="col-label">G/F</span>
                <span class="aisle-gap">🚶</span>
                <span class="col-label">K/H</span>
            </div>
            @php
                // PK-GIA Staggered pattern
                $staggeredPattern = [
                    6 => ['C', 'E', 'F', 'H'],
                    7 => ['A', 'D', 'G', 'K'],
                    8 => ['C', 'E', 'F', 'H'],
                    9 => ['A', 'D', 'G', 'K'],
                    10 => ['C', 'E', 'F', 'H'],
                    11 => ['A', 'D', 'G', 'K'],
                    12 => ['E', 'F'], // Only center 2 seats
                ];
            @endphp
            @foreach($staggeredPattern as $row => $cols)
                <div class="seat-row-business" data-row="{{ $row }}">
                    @if(count($cols) == 4)
                        {{-- Full row with 4 seats --}}
                        @php $col = $cols[0];
                            $seat = $seats["{$row}{$col}"] ?? null;
                        $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                            data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                                {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                        <div class="aisle-gap"></div>
                        @php $col = $cols[1];
                            $seat = $seats["{$row}{$col}"] ?? null;
                        $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                            data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                                {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                        <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                        @php $col = $cols[2];
                            $seat = $seats["{$row}{$col}"] ?? null;
                        $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                            data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                                {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                        <div class="aisle-gap"></div>
                        @php $col = $cols[3];
                            $seat = $seats["{$row}{$col}"] ?? null;
                        $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                            data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                                {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                    @else
                        {{-- Row 12: Only center 2 seats (E, F) --}}
                        <div class="seat-placeholder"></div>
                        <div class="aisle-gap"></div>
                        @php $col = $cols[0];
                            $seat = $seats["{$row}{$col}"] ?? null;
                        $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                            data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                                {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                        <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                        @php $col = $cols[1];
                            $seat = $seats["{$row}{$col}"] ?? null;
                        $status = $seat?->status ?? 'no-data'; @endphp
                        <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                            data-col="{{ $col }}">
                            <div class="seat-id">{{ $row }}{{ $col }}</div>
                            <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                                {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                        </div>
                        <div class="aisle-gap"></div>
                        <div class="seat-placeholder"></div>
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

    <!-- Economy Class 1 - Rows 21-36 (3-3-3 layout: ABC - DFG - HJK) -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 21-36</h2>
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
                $exceptions = [
                    36 => ['D', 'F', 'G'], // Only center
                ];
            @endphp
            @for($row = 21; $row <= 36; $row++)
                @if($row == 24) @continue @endif
                @php $rowCols = $exceptions[$row] ?? $allCols; @endphp
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

    <!-- Attendant Door 3 (Between row 36-37) - 2 seats: A and K -->
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

    <!-- Economy Class 2 - Rows 37-49 -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 37-49</h2>
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
                $exceptions = [
                    49 => ['A', 'B', 'C', 'H', 'J', 'K'], // No center section (D,F,G)
                ];
            @endphp
            @for($row = 37; $row <= 49; $row++)
                @php $rowCols = $exceptions[$row] ?? $allCols; @endphp
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

    <!-- Attendant Door 4 (Between row 49-50) - 2 seats: A and K -->
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

    <!-- Economy Class 3 - Rows 50-63 (row 63 has exceptions) -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 50-63</h2>
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
                $exceptions = [
                    63 => ['A', 'B', 'D', 'F', 'G', 'J', 'K'], // No C, no H
                ];
            @endphp
            @for($row = 50; $row <= 63; $row++)
                @php $rowCols = $exceptions[$row] ?? $allCols; @endphp
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

    <!-- Attendant Door 5 (Aft, below row 63) - 6 seats: LL, LC, LR, RL, RC, RR -->
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