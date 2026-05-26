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
            <a href="{{ route('superadmin.pdf-scan.clear') }}" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"></path></svg>
                Ulangi Scan
            </a>
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
        <!-- Data Table -->
        <div style="background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-subtle); overflow: hidden; box-shadow: var(--shadow-md);">
            <form id="export-form" action="{{ route('superadmin.pdf-scan.export') }}" method="POST">
                @csrf
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-dark); border-bottom: 1px solid var(--border-subtle);">
                            <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; width: 40px;">#</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Registration</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Seat ID</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Expiry Date</th>
                            <th style="padding: 0.75rem; width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="data-rows">
                        @forelse($extractedData as $index => $item)
                            <tr style="border-bottom: 1px solid var(--border-subtle); transition: background 0.2s;" 
                                onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 0.5rem 1rem; color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">{{ $index + 1 }}</td>
                                <td style="padding: 0.5rem 0.75rem;">
                                    <input type="text" name="data[{{ $index }}][registration]" value="{{ $item['registration'] }}" class="input-premium row-registration" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.9rem;">
                                </td>
                                <td style="padding: 0.5rem 0.75rem;">
                                    <input type="text" name="data[{{ $index }}][seat_id]" value="{{ $item['seat_id'] }}" class="input-premium" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600;">
                                </td>
                                <td style="padding: 0.5rem 0.75rem;">
                                    <input type="text" name="data[{{ $index }}][expiry_date]" value="{{ $item['expiry_date'] }}" 
                                        class="input-premium expiry-date-input" 
                                        style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.9rem;">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <button type="button" class="btn-delete-row" style="color: var(--danger); border: none; background: none; cursor: pointer; padding: 0.5rem; opacity: 0.5; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.5'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 4rem 1.5rem; text-align: center; color: var(--text-muted);">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                                    Tidak ada data yang terdeteksi secara otomatis. Silakan tambah baris manual atau ulangi scan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
            <div style="padding: 1rem; background: var(--bg-dark); border-top: 1px solid var(--border-subtle);">
                <button type="button" id="add-row" class="btn btn-secondary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Baris Baru
                </button>
            </div>
        </div>

        <!-- Right Panel: Scan Image Viewer -->
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <!-- Original Scan Image -->
            @if(!empty($scanImages))
            <div style="background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-subtle); overflow: hidden; box-shadow: var(--shadow-sm); position: sticky; top: 5rem;">
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
        const dataRows = document.getElementById('data-rows');
        const masterRegInput = document.getElementById('master-registration');
        let rowCount = {{ count($extractedData) }};

        // Sync registration across all rows
        masterRegInput.addEventListener('input', function() {
            const newValue = this.value;
            document.querySelectorAll('.row-registration').forEach(input => {
                input.value = newValue;
            });
        });

        addRowBtn.addEventListener('click', function() {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--border-subtle)';
            tr.innerHTML = `
                <td style="padding: 0.5rem 1rem; color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">${rowCount + 1}</td>
                <td style="padding: 0.5rem 0.75rem;">
                    <input type="text" name="data[${rowCount}][registration]" value="${masterRegInput.value}" class="input-premium row-registration" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.9rem;">
                </td>
                <td style="padding: 0.5rem 0.75rem;">
                    <input type="text" name="data[${rowCount}][seat_id]" placeholder="21A, pax-1, dll" class="input-premium" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.9rem;">
                </td>
                <td style="padding: 0.5rem 0.75rem;">
                    <input type="text" name="data[${rowCount}][expiry_date]" placeholder="JAN 2030" class="input-premium expiry-date-input" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.9rem;">
                </td>
                <td style="padding: 0.5rem;">
                    <button type="button" class="btn-delete-row" style="color: var(--danger); border: none; background: none; cursor: pointer; padding: 0.5rem; opacity: 0.5;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </td>
            `;
            dataRows.appendChild(tr);
            rowCount++;
            
            const emptyMsg = dataRows.querySelector('td[colspan="5"]');
            if (emptyMsg) emptyMsg.parentElement.remove();
        });

        dataRows.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.btn-delete-row');
            if (deleteBtn) {
                deleteBtn.closest('tr').remove();
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

        dataRows.addEventListener('input', function(e) {
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
    });
</script>
@endpush
@endsection
