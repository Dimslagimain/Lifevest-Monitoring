@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 2rem auto; padding: 0 1rem;">
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

    {{-- Uncertainty Legend --}}
    <div id="uncertainty-banner" style="display: none; background: linear-gradient(135deg, #78350f15, #92400e10); border: 1px solid #d97706; border-radius: 12px; padding: 0.75rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.3rem;">⚠️</span>
        <div>
            <span style="color: #d97706; font-weight: 700; font-size: 0.9rem;">Perhatian: </span>
            <span style="color: var(--text-muted); font-size: 0.85rem;">Tanggal yang ditandai <span style="background: #fbbf2440; color: #d97706; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700;">kuning ⚠</span> kemungkinan salah baca oleh AI (tulisan tangan tidak jelas). Silakan periksa dan koreksi manual sebelum export.</span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem;">
        <!-- Data Table -->
        <div style="background: var(--bg-card); border-radius: 20px; border: 1px solid var(--border-subtle); overflow: hidden; box-shadow: var(--shadow-md);">
            <form id="export-form" action="{{ route('superadmin.pdf-scan.export') }}" method="POST">
                @csrf
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-dark); border-bottom: 1px solid var(--border-subtle);">
                            <th style="padding: 1.25rem 1.5rem; width: 25%; font-weight: 700; color: var(--text-primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">Registration</th>
                            <th style="padding: 1.25rem 1.5rem; width: 35%; font-weight: 700; color: var(--text-primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">Seat ID</th>
                            <th style="padding: 1.25rem 1.5rem; width: 35%; font-weight: 700; color: var(--text-primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">Expiry Date</th>
                            <th style="padding: 1.25rem 1.5rem; width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="data-rows">
                        @forelse($extractedData as $index => $item)
                            <tr style="border-bottom: 1px solid var(--border-subtle); transition: background 0.2s;">
                                <td style="padding: 0.8rem 1rem;">
                                    <input type="text" name="data[{{ $index }}][registration]" value="{{ $item['registration'] }}" class="input-premium row-registration" style="width: 100%; padding: 0.7rem 1rem; border-radius: 8px;">
                                </td>
                                <td style="padding: 0.8rem 1rem;">
                                    <input type="text" name="data[{{ $index }}][seat_id]" value="{{ $item['seat_id'] }}" class="input-premium" style="width: 100%; padding: 0.7rem 1rem; border-radius: 8px;">
                                </td>
                                <td style="padding: 0.8rem 1rem;">
                                    <input type="text" name="data[{{ $index }}][expiry_date]" value="{{ $item['expiry_date'] }}" class="input-premium expiry-date-input" style="width: 100%; padding: 0.7rem 1rem; border-radius: 8px;">
                                </td>
                                <td style="padding: 1rem 1.5rem;">
                                    <button type="button" class="btn-delete-row" style="color: var(--danger); border: none; background: none; cursor: pointer; padding: 0.5rem;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 4rem 1.5rem; text-align: center; color: var(--text-muted);">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                                    Tidak ada data yang terdeteksi secara otomatis. Silakan tambah baris manual atau ulangi scan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
            <div style="padding: 1.5rem; background: var(--bg-dark); border-top: 1px solid var(--border-subtle);">
                <button type="button" id="add-row" class="btn btn-secondary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Baris Baru
                </button>
            </div>
        </div>

        <!-- Raw Text Preview -->
        <div>
            <div style="background: var(--bg-card); border-radius: 20px; border: 1px solid var(--border-subtle); padding: 1.5rem; box-shadow: var(--shadow-sm); position: sticky; top: 2rem;">
                <h3 style="margin: 0 0 1rem 0; font-size: 1rem; font-weight: 700; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Raw OCR Output
                </h3>
                <div style="background: var(--bg-dark); padding: 1rem; border-radius: 12px; font-family: monospace; font-size: 0.85rem; color: var(--text-muted); height: 500px; overflow-y: auto; line-height: 1.6; white-space: pre-wrap;">
                    {{ $rawText }}
                </div>
                <p style="margin: 1rem 0 0 0; font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Gunakan teks asli di atas sebagai referensi jika hasil deteksi di tabel sebelah kiri kurang akurat.</p>
            </div>
        </div>
    </div>

    <!-- Floating Quick Navigation -->
    <div style="position: fixed; bottom: 2.5rem; right: 2.5rem; display: flex; flex-direction: column; gap: 0.75rem; z-index: 50;">
        <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Lompat ke Atas" style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle); cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.2s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--bg-card)'; this.style.color='var(--text-primary)'; this.style.borderColor='var(--border-subtle)';">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
        </button>
        <button type="button" onclick="window.scrollTo({top: document.documentElement.scrollHeight / 2 - window.innerHeight / 2, behavior: 'smooth'})" title="Lompat ke Tengah" style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle); cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.2s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--bg-card)'; this.style.color='var(--text-primary)'; this.style.borderColor='var(--border-subtle)';">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v5M12 17v5M7 12H2M22 12h-5"/></svg>
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
                <td style="padding: 1rem 1.5rem;">
                    <input type="text" name="data[${rowCount}][registration]" value="${masterRegInput.value}" class="input-premium row-registration" style="width: 100%; padding: 0.6rem 0.8rem; border-radius: 8px;">
                </td>
                <td style="padding: 1rem 1.5rem;">
                    <input type="text" name="data[${rowCount}][seat_id]" placeholder="21A, pax-1, dll" class="input-premium" style="width: 100%; padding: 0.6rem 0.8rem; border-radius: 8px;">
                </td>
                <td style="padding: 1rem 1.5rem;">
                    <input type="text" name="data[${rowCount}][expiry_date]" placeholder="JAN 2030" class="input-premium" style="width: 100%; padding: 0.6rem 0.8rem; border-radius: 8px;">
                </td>
                <td style="padding: 1rem 1.5rem;">
                    <button type="button" class="btn-delete-row" style="color: var(--danger); border: none; background: none; cursor: pointer; padding: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </td>
            `;
            dataRows.appendChild(tr);
            rowCount++;
            
            // Check if we need to remove "No data" message
            const emptyMsg = dataRows.querySelector('td[colspan="4"]');
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
                    input.style.background = 'linear-gradient(135deg, #fbbf2420, #f59e0b15)';
                    input.style.border = '2px solid #d97706';
                    input.style.color = '#d97706';
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

        // Run on page load
        markUncertainDates();

        // Re-check when user edits a date
        dataRows.addEventListener('input', function(e) {
            if (e.target.classList.contains('expiry-date-input')) {
                markUncertainDates();
            }
        });

        // Strip '?' before form submit so it doesn't pollute the Excel
        document.getElementById('export-form').addEventListener('submit', function() {
            document.querySelectorAll('.expiry-date-input').forEach(input => {
                input.value = input.value.replace(/\?/g, '').trim();
            });
        });
    });
</script>
@endpush
@endsection
