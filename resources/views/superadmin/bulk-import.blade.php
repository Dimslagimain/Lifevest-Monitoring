@extends('layouts.app')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 0 1rem;">
    <!-- Modern Header -->
        <div style="text-align: center; margin-bottom: 0.5rem;">
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.03em; margin: 0 0 0.25rem 0;">
                Bulk Import Data
            </h1>

            <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 600px; margin: 0 auto; line-height: 1.4;">
                Unggah file spreadsheet untuk memproses penambahan data atau pembaruan massal secara otomatis.
            </p>
        </div>

    @if(session('success'))
        <div style="margin-bottom: 2rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 1rem 1.5rem; border-radius: 12px; display: flex; align-items: center; gap: 1rem;">
            <div style="background: #10b981; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <p style="margin: 0; color: #10b981; font-weight: 600;">{{ session('success') }}</p>
        </div>
    @endif
    
    @if(session('error'))
        <div style="margin-bottom: 2rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1rem 1.5rem; border-radius: 12px; display: flex; align-items: center; gap: 1rem;">
            <div style="background: #ef4444; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <p style="margin: 0; color: #ef4444; font-weight: 600;">{{ session('error') }}</p>
        </div>
    @endif

    <div style="background: var(--bg-card); border-radius: 20px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg); padding: 1.25rem; position: relative; overflow: hidden;">
        <!-- Decorative blob background -->
        <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>

        <form action="{{ route('superadmin.bulk-import.process') }}" method="POST" enctype="multipart/form-data" style="position: relative; z-index: 1;">
            @csrf
            
            <div style="margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">1</div>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-primary);">Pilih Kategori Data</h3>
                </div>
                
                <div style="padding-left: 2.25rem;">
                    <div style="position: relative;">
                        <select name="import_type" id="import_type" class="input-premium" style="width: 100%; appearance: none; cursor: pointer; padding: 0.5rem 1rem; font-size: 0.9rem; font-weight: 500; border-radius: 10px; border: 2px solid var(--border);" required>
                            <option value="" disabled selected>Tentukan entitas yang akan di-import</option>
                            <option value="aircraft">Aircraft (Armada Pesawat)</option>
                            <option value="seat">Life Vest (Data Expiry Date)</option>
                            <option value="user">User Account (Akun Pengguna)</option>
                        </select>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; right: 1.25rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-muted);"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    
                    <div id="template-container" style="margin-top: 1rem; opacity: 0.5; pointer-events: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.75rem; background: var(--bg-dark); padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border-subtle);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <span style="font-size: 0.85rem; color: var(--text-muted); flex-grow: 1;">Belum punya formatnya? Unduh template Excel resmi kami.</span>
                        <a href="#" id="download-template-btn" style="display: inline-flex; align-items: center; font-weight: 700; font-size: 0.85rem; color: var(--primary); text-decoration: none; padding: 0.5rem 1rem; background: var(--primary-glow); border-radius: 6px; transition: background 0.2s;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Unduh Template
                        </a>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">2</div>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-primary);">Unggah File Spreadsheet</h3>
                </div>
                
                <div style="padding-left: 2.25rem;">
                    <div class="upload-area" id="upload-area" style="border: 2px dashed var(--border); border-radius: 12px; padding: 1.25rem; text-align: center; cursor: pointer; transition: all 0.2s ease; background: var(--bg-dark); position: relative;">
                        <input type="file" name="file" id="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" style="display: none;" required>
                        
                        <div id="upload-content">
                            <div style="background: var(--primary-glow); width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/><path d="M12 18v-6"/><path d="M9 15l3-3 3 3"/></svg>
                            </div>
                            <h3 style="margin: 0 0 0.25rem 0; font-weight: 700; font-size: 1.1rem; color: var(--text-primary);">Tarik & Lepas file Anda di sini</h3>
                            <p style="margin: 0 0 0.75rem 0; color: var(--text-muted); font-size: 0.9rem;">atau</p>
                            <button type="button" class="btn btn-secondary" style="pointer-events: none; padding: 0.5rem 1.5rem; border-radius: 8px;">Telusuri File...</button>
                            <p style="margin: 1rem 0 0 0; color: var(--text-muted); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Mendukung .XLSX dan .CSV (Maks. 10MB)</p>
                        </div>

                        <div id="file-info" style="display: none; align-items: center; justify-content: space-between; background: var(--bg-card); border: 1px solid var(--border-subtle); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
                            <div style="display: flex; align-items: center; gap: 1.25rem;">
                                <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                                </div>
                                <div style="text-align: left;">
                                    <h4 id="file-name" style="margin: 0 0 0.25rem 0; font-weight: 700; color: var(--text-primary); font-size: 1.05rem;">filename.xlsx</h4>
                                    <p id="file-size" style="margin: 0; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">1.2 MB</p>
                                </div>
                            </div>
                            <button type="button" id="remove-file" class="btn-icon" style="color: var(--danger); border-color: transparent;" title="Ganti File">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div style="padding-left: 2.25rem; margin-top: 0.75rem; border-top: 1px solid var(--border-subtle); padding-top: 0.75rem;">
                <button type="submit" id="submit-btn" class="btn btn-primary confirm-submit" 
                    data-confirm-title="Mulai Import Data?"
                    data-confirm-text="Pastikan format file sudah sesuai dengan template yang diunduh."
                    data-confirm-icon="warning"
                    data-confirm-button-text="Ya, Mulai Import"
                    data-confirm-variant="primary"
                    style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 1rem; font-size: 1.05rem; font-weight: 700; border-radius: 10px; opacity: 0.5; pointer-events: none;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Mulai Proses Import Data
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .upload-area:hover {
        border-color: var(--primary) !important;
        background: rgba(var(--primary-rgb), 0.05) !important;
    }
    .upload-area.dragover {
        border-color: var(--primary) !important;
        background: rgba(var(--primary-rgb), 0.1) !important;
        transform: scale(1.02);
    }
    /* Make the select placeholder text gray */
    select:invalid {
        color: var(--text-muted);
    }
    select option[value=""][disabled] {
        display: none;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('import_type');
        const templateContainer = document.getElementById('template-container');
        const templateBtn = document.getElementById('download-template-btn');
        const submitBtn = document.getElementById('submit-btn');
        
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('file');
        const uploadContent = document.getElementById('upload-content');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const removeFileBtn = document.getElementById('remove-file');

        // Handle Type Selection
        typeSelect.addEventListener('change', function() {
            if (this.value) {
                templateContainer.style.opacity = '1';
                templateContainer.style.pointerEvents = 'auto';
                templateBtn.href = "{{ url('/superadmin/bulk-import/template') }}/" + this.value;
                checkSubmitStatus();
            }
        });

        // Handle File Input Click
        uploadArea.addEventListener('click', function(e) {
            if (e.target !== removeFileBtn && !removeFileBtn.contains(e.target)) {
                fileInput.click();
            }
        });

        // Handle File Selection
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                showFileInfo(file);
            }
        });

        // Handle Remove File
        removeFileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            fileInput.value = '';
            uploadContent.style.display = 'block';
            fileInfo.style.display = 'none';
            checkSubmitStatus();
        });

        // Drag and Drop Logic
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('dragover');
            }, false);
        });

        uploadArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files && files[0]) {
                fileInput.files = files;
                showFileInfo(files[0]);
            }
        }, false);

        function showFileInfo(file) {
            fileName.textContent = file.name;
            
            // Convert to MB or KB
            let size = file.size;
            if (size > 1024 * 1024) {
                fileSize.textContent = (size / (1024 * 1024)).toFixed(2) + ' MB';
            } else {
                fileSize.textContent = (size / 1024).toFixed(2) + ' KB';
            }

            uploadContent.style.display = 'none';
            fileInfo.style.display = 'flex';
            checkSubmitStatus();
        }

        function checkSubmitStatus() {
            if (typeSelect.value && fileInput.files.length > 0) {
                submitBtn.style.opacity = '1';
                submitBtn.style.pointerEvents = 'auto';
            } else {
                submitBtn.style.opacity = '0.5';
                submitBtn.style.pointerEvents = 'none';
            }
        }
    });
</script>
@endpush
@endsection
