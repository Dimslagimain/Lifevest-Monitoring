@php $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y'; @endphp
<!-- Cockpit Section --><x-cockpit-section :seats="$seats" :isPdfExport="$isPdfExport ?? false" />

    <!-- Attendant D11 & D21 (Forward - 4 seats: D11 3 left, D21 1 right, stacked 2 rows) -->
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
            <!-- Row 1: D11-LL1, D11-LR, D21-R -->
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
            <!-- Row 2: D11-LL2 only -->
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
                <div class="seat-placeholder"></div>
                <div class="row-number"></div>
                <div class="seat-placeholder"></div>
                <div class="seat-placeholder"></div>
            </div>
        </div>
    </section>

    <!-- Business Class - Rows 6-11 (1-1-1-1 layout: A D G K with aisle gaps) -->
    <section class="cabin-section">
        <h2>Business Class - Rows 6-11</h2>
        <div class="seat-grid grid-business">
            <div class="grid-header-business">
                <span class="col-label">A</span>
                <span class="aisle-gap">🚶</span>
                <span class="col-label">D</span>
                <span class="col-label row-label">Row</span>
                <span class="col-label">G</span>
                <span class="aisle-gap">🚶</span>
                <span class="col-label">K</span>
            </div>
            @foreach([6, 7, 8, 9, 10, 11] as $row)
                <div class="seat-row-business" data-row="{{ $row }}">
                    @php $col = 'A';
                        $seat = $seats["{$row}{$col}"] ?? null;
                    $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                        data-col="{{ $col }}">
                        <div class="seat-id">{{ $row }}{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                    <div class="aisle-gap"></div>
                    @php $col = 'D';
                        $seat = $seats["{$row}{$col}"] ?? null;
                    $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                        data-col="{{ $col }}">
                        <div class="seat-id">{{ $row }}{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                    <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                    @php $col = 'G';
                        $seat = $seats["{$row}{$col}"] ?? null;
                    $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                        data-col="{{ $col }}">
                        <div class="seat-id">{{ $row }}{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                    <div class="aisle-gap"></div>
                    @php $col = 'K';
                        $seat = $seats["{$row}{$col}"] ?? null;
                    $status = $seat?->status ?? 'no-data'; @endphp
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $row }}{{ $col }}" data-row="{{ $row }}"
                        data-col="{{ $col }}">
                        <div class="seat-id">{{ $row }}{{ $col }}</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $seat?->expiry_date?->format($dateFormat) ?? '-' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Attendant D12 & D22 (Between Business and Economy) - 4 seats stacked: 2 left, 2 right -->
    <section class="cabin-section">
        <h2>Attendant D12 & D22</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-2-2">
                <span class="col-label col-header" data-col="att/d12-LL1">LL</span>
                <span class="seat-placeholder"></span>
                <span class="row-label"></span>
                <span class="seat-placeholder"></span>
                <span class="col-label col-header" data-col="att/d22-RR1">RR</span>
            </div>
            <!-- Row 1: D12-LL1, D22-RR1 -->
            <div class="seat-row grid-row-2-2">
                @php
                    $seatId = 'att/d12-LL1';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="LL1">
                    <div class="seat-id">D12-LL1</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                <div class="seat-placeholder"></div>
                <div class="row-number">D12/D22</div>
                <div class="seat-placeholder"></div>
                @php
                    $seatId = 'att/d22-RR1';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="RR1">
                    <div class="seat-id">D22-RR1</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
            <!-- Row 2: D12-LL2, D22-RR2 -->
            <div class="seat-row grid-row-2-2">
                @php
                    $seatId = 'att/d12-LL2';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="LL2">
                    <div class="seat-id">D12-LL2</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                <div class="seat-placeholder"></div>
                <div class="row-number"></div>
                <div class="seat-placeholder"></div>
                @php
                    $seatId = 'att/d22-RR2';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="{{ $seatId }}" data-col="RR2">
                    <div class="seat-id">D22-RR2</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Economy Class 1 - Rows 21-40 (2-4-2 layout) -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 21-40</h2>
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
                $skipRows = [24];
                $exceptions = [
                    40 => ['A', 'C', 'H', 'K'],
                ];
            @endphp
            @for($row = 21; $row <= 40; $row++)
                @if(in_array($row, $skipRows))
                    @continue
                @endif
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
            @endfor
        </div>
    </section>

    <!-- Attendant D13 & D23 (Between row 40-41) - 2 seats: L / R -->
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

    <!-- Economy Class 2 - Rows 41-58 -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 41-58</h2>
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
                    41 => ['D', 'E', 'F', 'G'],
                    54 => ['A', 'C', 'D', 'F', 'G', 'H', 'K'],
                    55 => ['A', 'C', 'D', 'F', 'G', 'H', 'K'],
                    56 => ['A', 'C', 'D', 'F', 'G', 'H', 'K'],
                    57 => ['A', 'C', 'D', 'F', 'G'],
                    58 => ['D', 'F', 'G'],
                ];
            @endphp
            @for($row = 41; $row <= 58; $row++)
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
            @endfor
        </div>
    </section>

    <!-- Attendant D14 & D24 (Below row 58) - 2 seats: L / R -->
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