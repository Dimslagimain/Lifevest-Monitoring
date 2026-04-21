@php $dateFormat = isset($isPdfExport) && $isPdfExport ? 'd M Y' : 'j M Y'; @endphp
<!-- Cockpit Section --><x-cockpit-section :seats="$seats" :isPdfExport="$isPdfExport ?? false" />

    <!-- Attendant D11 & D21 (Forward - 4 seats: D11 3 left stacked, D21 1 right) -->
    <section class="cabin-section">
        <h2>Attendant D11 & D21</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-2-2">
                <span class="col-label col-header" data-col="att/d11-LL1" data-row="D11/D21">LL</span>
                <span class="col-label col-header" data-col="att/d11-LR" data-row="D11/D21">LR</span>
                <span class="row-label"></span>
                <span class="seat-placeholder"></span>
                <span class="col-label col-header" data-col="att/d21-R" data-row="D11/D21">R</span>
            </div>
            <!-- Row 1: D11-LL1 only, D21-R -->
            <div class="seat-row grid-row-2-2">
                @php
                    $seatId = 'att/d11-LL1';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D11/D21" data-col="LL1">
                    <div class="seat-id">D11-LL1</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                <div class="seat-placeholder"></div>
                <div class="row-number" data-row="D11/D21">D11/D21</div>
                <div class="seat-placeholder"></div>
                @php
                    $seatId = 'att/d21-R';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D11/D21" data-col="R">
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
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D11/D21" data-col="LL2">
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
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D11/D21" data-col="LR">
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

    <!-- Attendant D12 & D22 (Between Business and Economy) - 2 seats: L / R -->
    <section class="cabin-section">
        <h2>Attendant D12 & D22</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-2-2">
                <span class="col-label col-header" data-col="att/d12-L" data-row="D12/D22">L</span>
                <span class="seat-placeholder"></span>
                <span class="row-label"></span>
                <span class="seat-placeholder"></span>
                <span class="col-label col-header" data-col="att/d22-R" data-row="D12/D22">R</span>
            </div>
            <div class="seat-row grid-row-2-2">
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
                <div class="row-number" data-row="D12/D22">D12/D22</div>
                <div class="seat-placeholder"></div>
                @php
                    $seatId = 'att/d22-R';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D12/D22" data-col="R">
                    <div class="seat-id">D22-R</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Economy Class 1 - Rows 21-39 (2-4-2 layout, skip row 24) -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 21-39</h2>
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
            @foreach(range(21, 39) as $row)
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

    <!-- Attendant D13 & D23 (Between row 39-40) - 2 seats: L / R -->
    <section class="cabin-section">
        <h2>Attendant D13 & D23</h2>
        <div class="seat-grid">
            <div class="grid-header grid-row-2-2">
                <span class="col-label col-header" data-col="att/d13-L" data-row="D13/D23">L</span>
                <span class="seat-placeholder"></span>
                <span class="row-label"></span>
                <span class="seat-placeholder"></span>
                <span class="col-label col-header" data-col="att/d23-R" data-row="D13/D23">R</span>
            </div>
            <div class="seat-row grid-row-2-2">
                @php
                    $seatId = 'att/d13-L';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D13/D23" data-col="L">
                    <div class="seat-id">D13-L</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
                <div class="seat-placeholder"></div>
                <div class="row-number" data-row="D13/D23">D13/D23</div>
                <div class="seat-placeholder"></div>
                @php
                    $seatId = 'att/d23-R';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D13/D23" data-col="R">
                    <div class="seat-id">D23-R</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Economy Class 2 - Rows 40-55 (with exceptions) -->
    <section class="cabin-section">
        <h2>Economy Class - Rows 40-55</h2>
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
            @foreach(range(40, 55) as $row)
                @php
                    // Define columns per row
                    if ($row == 55) {
                        $rowCols = ['D', 'F', 'G'];
                    } elseif ($row >= 51 && $row <= 54) {
                        $rowCols = ['A', 'C', 'D', 'F', 'G', 'H', 'K'];
                    } else {
                        $rowCols = ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'];
                    }
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

    <!-- Attendant D14 & D24 + Aft Galley (Below row 55) - 4 seats: D14 (1 left), Aft Galley (2 center), D24 (1 right) -->
    <section class="cabin-section">
        <h2>Attendant D14 & D24 + Aft Galley</h2>
        <div style="display: flex; justify-content: center; align-items: flex-start; gap: 2rem;">
            <!-- D14 (Left) -->
            <div style="text-align: center;">
                <div class="col-label col-header" style="margin-bottom: 0.5rem;">Att/D-14</div>
                @php
                    $seatId = 'att/d14-L';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D14/D24" data-col="L">
                    <div class="seat-id">D14-L</div>
                    <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                        {{ $expiryDate }}
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
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="Aft-Galley" data-col="LC">
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
                    <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="Aft-Galley" data-col="RC">
                        <div class="seat-id">Aft-RC</div>
                        <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                            {{ $expiryDate }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- D24 (Right) -->
            <div style="text-align: center;">
                <div class="col-label col-header" style="margin-bottom: 0.5rem;">Att/D-24</div>
                @php
                    $seatId = 'att/d24-R';
                    $seat = $seats[$seatId] ?? null;
                    $status = $seat?->status ?? 'no-data';
                    $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                @endphp
                <div class="seat-card status-{{ $status }}" data-seat="{{ $seatId }}" data-row="D14/D24" data-col="R">
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