@extends('layouts.app')

@section('content')
<div style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
    <!-- Modern Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2.5rem; gap: 2rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.03em; margin: 0 0 0.5rem 0;">Review Hasil Ekstraksi</h1>
            <div style="display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted); font-size: 1.05rem;">
                <span>Terdeteksi:</span>
                <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-dark); padding: 0.25rem 0.75rem; border-radius: 10px; border: 1px solid var(--border-subtle);">
                    <input type="text" id="master-registration" value="{{ $registration }}" 
                        style="background: transparent; border: none; color: var(--primary); font-weight: 800; width: 120px; outline: none; font-size: 1.05rem;"
                        title="Edit Master Registration">
                    <span style="color: var(--border-subtle)">|</span>
                    <input type="text" name="aircraft_type" value="{{ $aircraftType }}" 
                        style="background: transparent; border: none; color: var(--text-muted); font-weight: 600; width: 100px; outline: none; font-size: 0.95rem;"
                        form="export-form" title="Edit Aircraft Type">
                </div>
                <span style="background: var(--bg-secondary); padding: 0.25rem 0.75rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">
                    {{ count($extractedData) }} seats
                </span>
            </div>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button type="button" id="btn-ulangi-scan" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"></path></svg>
                Ulangi Scan
            </button>
            <button form="export-form" type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download Excel (Bulk Import)
            </button>
        </div>
    </div>

    {{-- Uncertainty Banner (shown only if dates have ?) --}}
    <div id="uncertainty-banner" style="display: none; background: linear-gradient(135deg, rgba(120, 53, 15, 0.08), rgba(146, 64, 14, 0.06)); border: 1px solid #d97706; border-radius: 12px; padding: 0.75rem 1.25rem; margin-bottom: 1.5rem; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.3rem;">⚠️</span>
        <div>
            <span style="color: #d97706; font-weight: 700; font-size: 0.9rem;">Perhatian: </span>
            <span style="color: var(--text-secondary); font-size: 0.85rem;">Tanggal yang ditandai <span style="background: rgba(251, 191, 36, 0.15); color: #fbbf24; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700;">kuning ⚠</span> kemungkinan salah baca oleh AI (tulisan tangan tidak jelas). Bandingkan dengan gambar scan di sebelah kanan.</span>
        </div>
    </div>

    {{-- Info banner --}}
    <div style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(59, 130, 246, 0.04)); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 0.75rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.3rem;">📋</span>
        <span style="color: var(--text-secondary); font-size: 0.85rem;">Bandingkan data di tabel dengan <strong>gambar scan asli</strong> di sebelah kanan. Edit langsung di tabel jika ada yang salah, lalu Download Excel.</span>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 420px; gap: 1.5rem;">
        <!-- LOPA Layout Container -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <form id="export-form" action="{{ route('superadmin.pdf-scan.export') }}" method="POST">
                @csrf
                <input type="hidden" name="master_registration" id="export-master-registration" value="{{ $registration }}">
                
                @php
                    $cockpitData = [];
                    $groupedData = [];
                    $attendantData = [];
                    $spareData = [];
                    $otherData = [];
                    
                    foreach($extractedData as $index => $item) {
                        $item['index'] = $index;
                        $id = strtolower(trim($item['seat_id']));
                        
                        if (preg_match('/pilot|copil|observer/i', $id)) {
                            $cockpitData[] = $item;
                        } elseif (preg_match('/^(att\/d|aft-|door)/i', $id)) {
                            $attendantData[] = $item;
                        } elseif (preg_match('/^(pax-|inf-|spare)/i', $id)) {
                            $spareData[] = $item;
                        } elseif (preg_match('/^(\d+)([A-Za-z]+)$/', trim($item['seat_id']), $matches)) {
                            $rowNum = (int)$matches[1];
                            $seatLetter = strtoupper($matches[2]);
                            $groupedData[$rowNum][$seatLetter] = $item;
                        } else {
                            $otherData[] = $item;
                        }
                    }
                    ksort($groupedData);
                    foreach($groupedData as $r => $seats) {
                        ksort($groupedData[$r]);
                    }
                @endphp

                <div class="lopa-row-container" style="display: flex; flex-direction: column; gap: 1rem;" id="lopa-container">
                    
                    <!-- Cockpit Section -->
                    @if(count($cockpitData) > 0)
                        <div class="lopa-section" id="cockpit-section" style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px; margin-bottom: 1rem;">
                            <div style="font-weight: 800; font-size: 1rem; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); padding-bottom: 0.5rem; margin-bottom: 0.25rem;">
                                👨‍✈️ Cockpit / Flight Deck
                            </div>
                            <div class="row-seats" id="cockpit-seats-container" style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                                @foreach($cockpitData as $item)
                                    <div class="seat-card" data-index="{{ $item['index'] }}" style="background: var(--bg-dark); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 0.5rem; display: flex; flex-direction: column; width: 110px; position: relative; gap: 0.25rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <input type="text" name="data[{{ $item['index'] }}][seat_id]" value="{{ $item['seat_id'] }}" 
                                                class="seat-id-input"
                                                style="background: transparent; border: none; font-weight: 700; font-size: 0.85rem; color: var(--primary); width: 60px; padding: 0; outline: none;"
                                                title="Edit Seat ID">
                                            <button type="button" class="btn-delete-row" style="color: var(--danger); border: none; background: none; cursor: pointer; padding: 0; opacity: 0.5; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;" title="Hapus">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            </button>
                                        </div>
                                        <input type="text" name="data[{{ $item['index'] }}][expiry_date]" value="{{ $item['expiry_date'] }}" 
                                            class="input-premium expiry-date-input" 
                                            style="width: 100%; padding: 0.25rem 0.4rem; border-radius: 6px; font-size: 0.8rem; height: 28px; text-transform: uppercase;"
                                            placeholder="EXP DATE">
                                        <input type="hidden" name="data[{{ $item['index'] }}][registration]" value="{{ $item['registration'] }}" class="row-registration">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Attendant Section -->
                    @if(count($attendantData) > 0)
                        <div class="lopa-section" id="attendant-section" style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px; margin-bottom: 1rem;">
                            <div style="font-weight: 800; font-size: 1rem; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); padding-bottom: 0.5rem; margin-bottom: 0.25rem;">
                                🚪 Attendant Stations / Doors
                            </div>
                            <div class="row-seats" id="attendant-seats-container" style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                                @foreach($attendantData as $item)
                                    <div class="seat-card" data-index="{{ $item['index'] }}" style="background: var(--bg-dark); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 0.5rem; display: flex; flex-direction: column; width: 110px; position: relative; gap: 0.25rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <input type="text" name="data[{{ $item['index'] }}][seat_id]" value="{{ $item['seat_id'] }}" 
                                                class="seat-id-input"
                                                style="background: transparent; border: none; font-weight: 700; font-size: 0.85rem; color: var(--primary); width: 60px; padding: 0; outline: none;"
                                                title="Edit Seat ID">
                                            <button type="button" class="btn-delete-row" style="color: var(--danger); border: none; background: none; cursor: pointer; padding: 0; opacity: 0.5; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;" title="Hapus">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            </button>
                                        </div>
                                        <input type="text" name="data[{{ $item['index'] }}][expiry_date]" value="{{ $item['expiry_date'] }}" 
                                            class="input-premium expiry-date-input" 
                                            style="width: 100%; padding: 0.25rem 0.4rem; border-radius: 6px; font-size: 0.8rem; height: 28px; text-transform: uppercase;"
                                            placeholder="EXP DATE">
                                        <input type="hidden" name="data[{{ $item['index'] }}][registration]" value="{{ $item['registration'] }}" class="row-registration">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Cabin Passenger Rows -->
                    @forelse($groupedData as $rowNum => $rowSeats)
                        <div class="lopa-row" data-row="{{ $rowNum }}" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px; transition: background 0.2s;">
                            <!-- Row Label -->
                            <div style="min-width: 60px; font-weight: 800; font-size: 1.1rem; color: var(--text-muted); border-right: 2px solid var(--border-subtle); padding-right: 0.75rem; text-align: center;">
                                Row {{ $rowNum }}
                            </div>
                            <!-- Seats in this row -->
                            <div class="row-seats" style="display: flex; flex-wrap: wrap; gap: 0.75rem; flex: 1;">
                                @foreach($rowSeats as $seatLetter => $item)
                                    <div class="seat-card" data-index="{{ $item['index'] }}" style="background: var(--bg-dark); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 0.5rem; display: flex; flex-direction: column; width: 110px; position: relative; gap: 0.25rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <input type="text" name="data[{{ $item['index'] }}][seat_id]" value="{{ $item['seat_id'] }}" 
                                                class="seat-id-input"
                                                style="background: transparent; border: none; font-weight: 700; font-size: 0.85rem; color: var(--primary); width: 60px; padding: 0; outline: none;"
                                                title="Edit Seat ID">
                                            <button type="button" class="btn-delete-row" style="color: var(--danger); border: none; background: none; cursor: pointer; padding: 0; opacity: 0.5; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;" title="Hapus">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            </button>
                                        </div>
                                        <input type="text" name="data[{{ $item['index'] }}][expiry_date]" value="{{ $item['expiry_date'] }}" 
                                            class="input-premium expiry-date-input" 
                                            style="width: 100%; padding: 0.25rem 0.4rem; border-radius: 6px; font-size: 0.8rem; height: 28px; text-transform: uppercase;"
                                            placeholder="EXP DATE">
                                        <input type="hidden" name="data[{{ $item['index'] }}][registration]" value="{{ $item['registration'] }}" class="row-registration">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        @if(empty($otherData) && empty($cockpitData) && empty($attendantData) && empty($spareData))
                            <div style="padding: 4rem 1.5rem; text-align: center; color: var(--text-muted); background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-subtle);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                                Tidak ada data yang terdeteksi secara otomatis. Silakan tambah baris manual atau ulangi scan.
                            </div>
                        @endif
                    @endforelse

                    <!-- Spares & Others Section -->
                    @if(count($spareData) > 0 || count($otherData) > 0)
                        <div class="lopa-section" id="others-row" style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px; margin-top: 1rem;">
                            <div style="font-weight: 800; font-size: 1rem; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); padding-bottom: 0.5rem; margin-bottom: 0.25rem; width: 100%;">
                                📦 Spares & Others
                            </div>
                            <div class="row-seatsothers" id="others-seats-container" style="display: flex; flex-wrap: wrap; gap: 0.75rem; width: 100%;">
                                @foreach(array_merge($spareData, $otherData) as $item)
                                    <div class="seat-card" data-index="{{ $item['index'] }}" style="background: var(--bg-dark); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 0.5rem; display: flex; flex-direction: column; width: 110px; position: relative; gap: 0.25rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <input type="text" name="data[{{ $item['index'] }}][seat_id]" value="{{ $item['seat_id'] }}" 
                                                class="seat-id-input"
                                                style="background: transparent; border: none; font-weight: 700; font-size: 0.85rem; color: var(--primary); width: 60px; padding: 0; outline: none;"
                                                title="Edit Seat ID">
                                            <button type="button" class="btn-delete-row" style="color: var(--danger); border: none; background: none; cursor: pointer; padding: 0; opacity: 0.5; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;" title="Hapus">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            </button>
                                        </div>
                                        <input type="text" name="data[{{ $item['index'] }}][expiry_date]" value="{{ $item['expiry_date'] }}" 
                                            class="input-premium expiry-date-input" 
                                            style="width: 100%; padding: 0.25rem 0.4rem; border-radius: 6px; font-size: 0.8rem; height: 28px; text-transform: uppercase;"
                                            placeholder="EXP DATE">
                                        <input type="hidden" name="data[{{ $item['index'] }}][registration]" value="{{ $item['registration'] }}" class="row-registration">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </form>
            <div style="padding: 1rem; background: var(--bg-dark); border-radius: 12px; border: 1px solid var(--border-subtle);">
                <button type="button" id="add-row" class="btn btn-secondary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Kursi Baru
                </button>
            </div>
        </div>

        <!-- Right Panel: Scan Image Viewer (Sticky) -->
        <div style="display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 80px; align-self: start;">
            <!-- Original Scan Image -->
            @if(!empty($scanImages))
            <div style="background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-subtle); overflow: hidden; box-shadow: var(--shadow-sm);">
                <div style="padding: 0.75rem 1rem; background: var(--bg-dark); border-bottom: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: space-between;">
                    <h3 style="margin: 0; font-size: 0.85rem; font-weight: 700; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        Dokumen Scan Asli
                    </h3>
                    @if(count($scanImages) > 1)
                    <div style="display: flex; gap: 0.5rem;" id="page-nav">
                        @foreach($scanImages as $pIdx => $img)
                        <button type="button" class="page-btn" data-page="{{ $pIdx }}" 
                            style="padding: 0.2rem 0.6rem; border-radius: 6px; border: 1px solid var(--border-subtle); background: {{ $pIdx === 0 ? 'var(--primary)' : 'transparent' }}; color: {{ $pIdx === 0 ? 'white' : 'var(--text-muted)' }}; font-size: 0.75rem; font-weight: 700; cursor: pointer;">
                            P{{ $pIdx + 1 }}
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div id="scan-image-container" style="max-height: calc(100vh - 10rem); overflow-y: auto; background: #1a1a2e; cursor: grab;">
                    @foreach($scanImages as $pIdx => $img)
                    <img src="{{ $img }}" 
                        class="scan-page-img" 
                        data-page="{{ $pIdx }}"
                        style="width: 100%; display: {{ $pIdx === 0 ? 'block' : 'none' }}; {{ $pIdx > 0 ? '' : '' }}"
                        alt="Scan Page {{ $pIdx + 1 }}"
                        draggable="false">
                    @endforeach
                </div>
                <div style="padding: 0.5rem 1rem; background: var(--bg-dark); border-top: 1px solid var(--border-subtle); display: flex; justify-content: center; gap: 0.5rem;">
                    <button type="button" id="zoom-out" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    </button>
                    <span id="zoom-level" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">100%</span>
                    <button type="button" id="zoom-in" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    </button>
                    <button type="button" id="zoom-reset" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">Reset</button>
                </div>
            </div>
            @endif

            <!-- Raw OCR Text (collapsible) -->
            <div style="background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-subtle); overflow: hidden; box-shadow: var(--shadow-sm);">
                <div style="padding: 0.75rem 1rem; cursor: pointer; display: flex; align-items: center; justify-content: space-between;" onclick="document.getElementById('raw-text-content').style.display = document.getElementById('raw-text-content').style.display === 'none' ? 'block' : 'none'; this.querySelector('.chevron').style.transform = document.getElementById('raw-text-content').style.display === 'none' ? '' : 'rotate(180deg)';">
                    <h3 style="margin: 0; font-size: 0.85rem; font-weight: 700; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        Raw OCR Output
                    </h3>
                    <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div id="raw-text-content" style="display: none;">
                    <div style="background: var(--bg-dark); padding: 1rem; font-family: monospace; font-size: 0.8rem; color: var(--text-muted); max-height: 300px; overflow-y: auto; line-height: 1.6; white-space: pre-wrap;">{{ $rawText }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Quick Navigation -->
    <div style="position: fixed; bottom: 2.5rem; right: 2.5rem; display: flex; flex-direction: column; gap: 0.75rem; z-index: 50;">
        <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Lompat ke Atas" style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle); cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.2s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--bg-card)'; this.style.color='var(--text-primary)'; this.style.borderColor='var(--border-subtle)';">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
        </button>
        <button type="button" onclick="window.scrollTo({top: document.documentElement.scrollHeight, behavior: 'smooth'})" title="Lompat ke Bawah" style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle); cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.2s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--bg-card)'; this.style.color='var(--text-primary)'; this.style.borderColor='var(--border-subtle)';">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
        </button>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addRowBtn = document.getElementById('add-row');
        const masterRegInput = document.getElementById('master-registration');
        let rowCount = {{ count($extractedData) }};

        // Sync registration across all rows + hidden export field
        masterRegInput.addEventListener('input', function() {
            const newValue = this.value;
            document.querySelectorAll('.row-registration').forEach(input => {
                input.value = newValue;
            });
            // Sync hidden field for export filename
            const hiddenReg = document.getElementById('export-master-registration');
            if (hiddenReg) hiddenReg.value = newValue;
        });

        addRowBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Tambah Kursi Baru',
                html: `
                    <div style="text-align: left; display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.35rem; display: block;">Seat ID (contoh: 21A, pax-1, dll)</label>
                            <input id="swal-seat-id" class="swal2-input" placeholder="21A" style="margin: 0; width: 100%; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.35rem; display: block;">Expiry Date (contoh: JAN 2030)</label>
                            <input id="swal-expiry-date" class="swal2-input" placeholder="JAN 2030" style="margin: 0; width: 100%; border-radius: 8px; text-transform: uppercase;">
                        </div>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Tambah',
                cancelButtonText: 'Batal',
                background: getComputedStyle(document.documentElement).getPropertyValue('--bg-card-solid').trim() || '#ffffff',
                color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim() || '#1e293b',
                preConfirm: () => {
                    const seatId = document.getElementById('swal-seat-id').value.trim();
                    const expiryDate = document.getElementById('swal-expiry-date').value.trim();
                    if (!seatId) {
                        Swal.showValidationMessage('Seat ID harus diisi');
                        return false;
                    }
                    return { seatId, expiryDate };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const { seatId, expiryDate } = result.value;
                    addSeatToLopa(seatId, expiryDate);
                }
            });
        });

        function addSeatToLopa(seatId, expiryDate) {
            let targetContainer;
            
            const seatCard = document.createElement('div');
            seatCard.className = 'seat-card';
            seatCard.dataset.index = rowCount;
            seatCard.style.cssText = 'background: var(--bg-dark); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 0.5rem; display: flex; flex-direction: column; width: 110px; position: relative; gap: 0.25rem;';
            
            seatCard.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <input type="text" name="data[${rowCount}][seat_id]" value="${seatId}" 
                        class="seat-id-input"
                        style="background: transparent; border: none; font-weight: 700; font-size: 0.85rem; color: var(--primary); width: 60px; padding: 0; outline: none;"
                        title="Edit Seat ID">
                    <button type="button" class="btn-delete-row" style="color: var(--danger); border: none; background: none; cursor: pointer; padding: 0; opacity: 0.5; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;" title="Hapus">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <input type="text" name="data[${rowCount}][expiry_date]" value="${expiryDate.toUpperCase()}" 
                    class="input-premium expiry-date-input" 
                    style="width: 100%; padding: 0.25rem 0.4rem; border-radius: 6px; font-size: 0.8rem; height: 28px; text-transform: uppercase;"
                    placeholder="EXP DATE">
                <input type="hidden" name="data[${rowCount}][registration]" value="${masterRegInput.value}" class="row-registration">
            `;
            
            const isCockpit = /pilot|copil|observer/i.test(seatId);
            const isAttendant = /^(att\/d|aft-|door)/i.test(seatId);
            const isSpare = /^(pax-|inf-|spare)/i.test(seatId);
            const match = seatId.match(/^(\d+)([A-Za-z]+)$/);
            
            if (isCockpit) {
                let sect = document.getElementById('cockpit-section');
                if (!sect) {
                    sect = document.createElement('div');
                    sect.id = 'cockpit-section';
                    sect.className = 'lopa-section';
                    sect.style.cssText = 'display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px; margin-bottom: 1rem;';
                    sect.innerHTML = `
                        <div style="font-weight: 800; font-size: 1rem; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); padding-bottom: 0.5rem; margin-bottom: 0.25rem;">
                            👨‍✈️ Cockpit / Flight Deck
                        </div>
                        <div class="row-seats" id="cockpit-seats-container" style="display: flex; flex-wrap: wrap; gap: 0.75rem;"></div>
                    `;
                    const container = document.getElementById('lopa-container');
                    container.insertBefore(sect, container.firstChild);
                }
                targetContainer = document.getElementById('cockpit-seats-container');
            } else if (isAttendant) {
                let sect = document.getElementById('attendant-section');
                if (!sect) {
                    sect = document.createElement('div');
                    sect.id = 'attendant-section';
                    sect.className = 'lopa-section';
                    sect.style.cssText = 'display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px; margin-bottom: 1rem;';
                    sect.innerHTML = `
                        <div style="font-weight: 800; font-size: 1rem; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); padding-bottom: 0.5rem; margin-bottom: 0.25rem;">
                            🚪 Attendant Stations / Doors
                        </div>
                        <div class="row-seats" id="attendant-seats-container" style="display: flex; flex-wrap: wrap; gap: 0.75rem;"></div>
                    `;
                    const container = document.getElementById('lopa-container');
                    const cockpit = document.getElementById('cockpit-section');
                    if (cockpit && cockpit.nextSibling) {
                        container.insertBefore(sect, cockpit.nextSibling);
                    } else {
                        container.insertBefore(sect, container.firstChild);
                    }
                }
                targetContainer = document.getElementById('attendant-seats-container');
            } else if (match) {
                const rowNum = parseInt(match[1]);
                let rowDiv = document.querySelector(`.lopa-row[data-row="${rowNum}"]`);
                if (!rowDiv) {
                    rowDiv = document.createElement('div');
                    rowDiv.className = 'lopa-row';
                    rowDiv.dataset.row = rowNum;
                    rowDiv.style.cssText = 'display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px; transition: background 0.2s;';
                    rowDiv.innerHTML = `
                        <div style="min-width: 60px; font-weight: 800; font-size: 1.1rem; color: var(--text-muted); border-right: 2px solid var(--border-subtle); padding-right: 0.75rem; text-align: center;">
                            Row ${rowNum}
                        </div>
                        <div class="row-seats" style="display: flex; flex-wrap: wrap; gap: 0.75rem; flex: 1;"></div>
                    `;
                    
                    const lopaContainer = document.getElementById('lopa-container');
                    const existingRows = Array.from(lopaContainer.querySelectorAll('.lopa-row[data-row]'));
                    let inserted = false;
                    for (let existing of existingRows) {
                        const existingRowVal = parseInt(existing.dataset.row);
                        if (existingRowVal > rowNum) {
                            lopaContainer.insertBefore(rowDiv, existing);
                            inserted = true;
                            break;
                        }
                    }
                    if (!inserted) {
                        const othersRow = document.getElementById('others-row');
                        if (othersRow) {
                            lopaContainer.insertBefore(rowDiv, othersRow);
                        } else {
                            lopaContainer.appendChild(rowDiv);
                        }
                    }
                }
                targetContainer = rowDiv.querySelector('.row-seats');
            } else {
                let sect = document.getElementById('others-row');
                if (!sect) {
                    sect = document.createElement('div');
                    sect.id = 'others-row';
                    sect.className = 'lopa-section';
                    sect.style.cssText = 'display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px; margin-top: 1rem;';
                    sect.innerHTML = `
                        <div style="font-weight: 800; font-size: 1rem; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); padding-bottom: 0.5rem; margin-bottom: 0.25rem;">
                            📦 Spares & Others
                        </div>
                        <div class="row-seatsothers" id="others-seats-container" style="display: flex; flex-wrap: wrap; gap: 0.75rem;"></div>
                    `;
                    document.getElementById('lopa-container').appendChild(sect);
                }
                sect.style.display = 'flex';
                targetContainer = document.getElementById('others-seats-container');
            }
            
            targetContainer.appendChild(seatCard);
            rowCount++;
            
            markUncertainDates();
        }

        document.getElementById('export-form').addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.btn-delete-row');
            if (deleteBtn) {
                const seatCard = deleteBtn.closest('.seat-card');
                if (seatCard) {
                    const rowSeatsContainer = seatCard.parentElement;
                    seatCard.remove();
                    
                    if (rowSeatsContainer && rowSeatsContainer.classList.contains('row-seats') && rowSeatsContainer.children.length === 0) {
                        rowSeatsContainer.closest('.lopa-row').remove();
                    } else if (rowSeatsContainer && rowSeatsContainer.id === 'others-seats-container' && rowSeatsContainer.children.length === 0) {
                        document.getElementById('others-row').style.display = 'none';
                    }
                    
                    markUncertainDates();
                }
            }
        });

        // === UNCERTAINTY MARKER SYSTEM ===
        function markUncertainDates() {
            let hasUncertain = false;
            document.querySelectorAll('.expiry-date-input').forEach(input => {
                if (input.value.includes('?')) {
                    hasUncertain = true;
                    input.style.background = 'linear-gradient(135deg, rgba(251, 191, 36, 0.12), rgba(245, 158, 11, 0.08))';
                    input.style.border = '2px solid #d97706';
                    input.style.color = '#fbbf24';
                    input.style.fontWeight = '700';
                    input.title = '⚠ AI tidak yakin dengan pembacaan tanggal ini. Periksa manual!';
                } else {
                    input.style.background = '';
                    input.style.border = '';
                    input.style.color = '';
                    input.style.fontWeight = '';
                    input.title = '';
                }
            });
            const banner = document.getElementById('uncertainty-banner');
            if (banner) banner.style.display = hasUncertain ? 'flex' : 'none';
        }

        markUncertainDates();

        document.getElementById('export-form').addEventListener('input', function(e) {
            if (e.target.classList.contains('expiry-date-input')) {
                markUncertainDates();
            }
        });

        // === SCAN IMAGE ZOOM & PAN ===
        const container = document.getElementById('scan-image-container');
        if (container) {
            let zoomLevel = 100;
            const zoomIn = document.getElementById('zoom-in');
            const zoomOut = document.getElementById('zoom-out');
            const zoomReset = document.getElementById('zoom-reset');
            const zoomLabel = document.getElementById('zoom-level');

            function applyZoom() {
                container.querySelectorAll('.scan-page-img').forEach(img => {
                    img.style.width = zoomLevel + '%';
                });
                zoomLabel.textContent = zoomLevel + '%';
            }

            zoomIn.addEventListener('click', () => { zoomLevel = Math.min(300, zoomLevel + 25); applyZoom(); });
            zoomOut.addEventListener('click', () => { zoomLevel = Math.max(50, zoomLevel - 25); applyZoom(); });
            zoomReset.addEventListener('click', () => { zoomLevel = 100; applyZoom(); });

            // Page navigation
            document.querySelectorAll('.page-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const page = this.dataset.page;
                    container.querySelectorAll('.scan-page-img').forEach(img => {
                        img.style.display = img.dataset.page === page ? 'block' : 'none';
                    });
                    document.querySelectorAll('.page-btn').forEach(b => {
                        b.style.background = 'transparent';
                        b.style.color = 'var(--text-muted)';
                    });
                    this.style.background = 'var(--primary)';
                    this.style.color = 'white';
                });
            });

            // Mouse drag to pan
            let isDragging = false;
            let startX, startY, scrollLeft, scrollTop;

            container.addEventListener('mousedown', (e) => {
                isDragging = true;
                container.style.cursor = 'grabbing';
                startX = e.pageX - container.offsetLeft;
                startY = e.pageY - container.offsetTop;
                scrollLeft = container.scrollLeft;
                scrollTop = container.scrollTop;
            });

            container.addEventListener('mouseleave', () => { isDragging = false; container.style.cursor = 'grab'; });
            container.addEventListener('mouseup', () => { isDragging = false; container.style.cursor = 'grab'; });
            container.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                const y = e.pageY - container.offsetTop;
                container.scrollLeft = scrollLeft - (x - startX);
                container.scrollTop = scrollTop - (y - startY);
            });

            // Mouse wheel zoom
            container.addEventListener('wheel', (e) => {
                e.preventDefault();
                if (e.deltaY < 0) {
                    zoomLevel = Math.min(300, zoomLevel + 15);
                } else {
                    zoomLevel = Math.max(50, zoomLevel - 15);
                }
                applyZoom();
            });
        }

        // Strip '?' before form submit
        document.getElementById('export-form').addEventListener('submit', function() {
            document.querySelectorAll('.expiry-date-input').forEach(input => {
                input.value = input.value.replace(/\?/g, '').trim();
            });
        });

        // === ULANGI SCAN CONFIRMATION ===
        document.getElementById('btn-ulangi-scan')?.addEventListener('click', function() {
            const totalSeats = document.querySelectorAll('.seat-card').length;
            Swal.fire({
                title: 'Ulangi Scan?',
                text: `Data hasil ekstraksi (${totalSeats} baris) akan dihapus dan tidak bisa dikembalikan. Pastikan Anda sudah download Excel jika diperlukan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ulangi Scan',
                cancelButtonText: 'Batal',
                background: getComputedStyle(document.documentElement).getPropertyValue('--bg-card-solid').trim() || (document.documentElement.getAttribute('data-theme') === 'dark' ? '#162238' : '#ffffff'),
                color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim() || (document.documentElement.getAttribute('data-theme') === 'dark' ? '#ffffff' : '#1e293b'),
                reverseButtons: true,
                padding: '2rem',
                customClass: {
                    popup: 'swal2-premium-popup',
                    title: 'swal2-premium-title',
                    htmlContainer: 'swal2-premium-text',
                    confirmButton: 'swal2-premium-confirm swal2-variant-danger',
                    cancelButton: 'swal2-premium-cancel',
                    icon: 'swal2-premium-icon'
                },
                buttonsStyling: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('superadmin.pdf-scan.clear') }}";
                }
            });
        });
    });
</script>
@endpush
@endsection
