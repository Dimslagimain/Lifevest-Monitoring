@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <div class="header-section" style="background: var(--bg-card); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title" style="margin: 0; font-weight: 800; letter-spacing: -0.03em;">Bulk Import Data</h1>
            <p class="page-subtitle" style="margin-top: 0.5rem; opacity: 0.8;">Unggah file Excel atau CSV untuk memasukkan data dalam jumlah besar dengan cepat dan mudah.</p>
        </div>
        <div class="header-icon" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary-color); padding: 1rem; border-radius: 50%;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-top: 1.5rem; background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 1rem; border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.2); display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger" style="margin-top: 1.5rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="card" style="margin-top: 2rem; max-width: 700px; margin-left: auto; margin-right: auto; overflow: hidden; padding: 2.5rem; background: var(--bg-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid var(--border-subtle);">
        <form action="{{ route('superadmin.bulk-import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group-premium" style="margin-bottom: 2rem;">
                <label style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem; display: block; margin-bottom: 0.75rem;">1. Pilih Tipe Data</label>
                <div style="position: relative;">
                    <select name="import_type" id="import_type" class="input-premium select-premium" style="width: 100%; appearance: none; cursor: pointer; padding-right: 2.5rem;" required>
                        <option value="" disabled selected>-- Tentukan entitas yang akan di-import --</option>
                        <option value="aircraft">✈️ Aircraft (Armada Pesawat)</option>
                        <option value="seat">💺 Seat / Life Vest (Data Expiry Date)</option>
                        <option value="user">👤 User Account (Akun Pengguna)</option>
                    </select>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-muted);"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
            </div>

            <div class="form-group-premium" style="margin-bottom: 2.5rem;">
                <label style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem; display: block; margin-bottom: 0.75rem;">2. Unggah File (Excel / CSV)</label>
                
                <div class="upload-area" id="upload-area" style="border: 2px dashed var(--border-color); border-radius: var(--radius-md); padding: 3rem 2rem; text-align: center; cursor: pointer; transition: all 0.2s ease; background: rgba(var(--primary-rgb), 0.02);">
                    <input type="file" name="file" id="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" style="display: none;" required>
                    
                    <div id="upload-content">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.8;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/><path d="M13 8h4"/><path d="M13 12h4"/><path d="M13 16h4"/></svg>
                        <h3 style="margin: 0 0 0.5rem 0; font-weight: 700; font-size: 1.1rem; color: var(--text-primary);">Klik untuk memilih file</h3>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">atau seret dan lepas file Anda ke area ini.</p>
                        <p style="margin: 0.5rem 0 0 0; color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">Maksimal ukuran file: 10MB (.xlsx, .csv)</p>
                    </div>

                    <div id="file-info" style="display: none; align-items: center; justify-content: center; gap: 1rem;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <div style="text-align: left;">
                            <h4 id="file-name" style="margin: 0; font-weight: 700; color: var(--text-primary);">filename.xlsx</h4>
                            <p id="file-size" style="margin: 0.25rem 0 0 0; color: var(--text-muted); font-size: 0.8rem;">1.2 MB</p>
                        </div>
                        <button type="button" id="remove-file" style="background: none; border: none; color: var(--danger); cursor: pointer; padding: 0.5rem; margin-left: 1rem; border-radius: 50%; display: flex; align-items: center; justify-content: center;" title="Remove File">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid var(--border-subtle);">
                <div id="template-container" style="opacity: 0.5; pointer-events: none; transition: all 0.3s ease;">
                    <a href="#" id="download-template-btn" class="btn btn-secondary" style="display: inline-flex; align-items: center; font-weight: 600; padding: 0.75rem 1.25rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download Template
                    </a>
                </div>
                
                <button type="submit" id="submit-btn" class="btn btn-primary" style="display: inline-flex; align-items: center; padding: 0.75rem 2rem; font-weight: 700; opacity: 0.5; pointer-events: none; transition: all 0.3s ease;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/><polyline points="16 16 12 12 8 16"/></svg>
                    Mulai Import Data
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .upload-area:hover {
        border-color: var(--primary-color) !important;
        background: rgba(var(--primary-rgb), 0.05) !important;
    }
    .upload-area.dragover {
        border-color: var(--primary-color) !important;
        background: rgba(var(--primary-rgb), 0.1) !important;
        transform: scale(1.02);
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
