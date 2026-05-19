@extends('layouts.app')

@section('content')
<div style="max-width: 800px; margin: 1rem auto; padding: 0 1rem;">
    <!-- Modern Header -->
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.2), rgba(var(--primary-rgb), 0.05)); border: 1px solid rgba(var(--primary-rgb), 0.1); margin-bottom: 1rem; box-shadow: 0 8px 16px -4px rgba(var(--primary-rgb), 0.1);">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 7l-7 5 7 5V7z"></path><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
        </div>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.03em; margin: 0 0 0.5rem 0;">Smart PDF Scanner</h1>
        <p style="font-size: 1.05rem; color: var(--text-muted); max-width: 600px; margin: 0 auto; line-height: 1.5;">Unggah dokumen LOPA atau daftar periksa Anda untuk mengekstrak data Life Vest secara otomatis menggunakan teknologi AI.</p>
    </div>

    @if(session('error'))
        <div style="margin-bottom: 2rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1rem 1.5rem; border-radius: 12px; display: flex; align-items: center; gap: 1rem;">
            <div style="background: #ef4444; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <p style="margin: 0; color: #ef4444; font-weight: 600;">{{ session('error') }}</p>
        </div>
    @endif

    <div style="background: var(--bg-card); border-radius: 24px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg); padding: 2rem; position: relative; overflow: hidden;">
        <form action="{{ route('superadmin.pdf-scan.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem;">1</div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Unggah File PDF atau Gambar</h3>
                </div>
                
                <div style="padding-left: 2.5rem;">
                    <div class="upload-area" id="upload-area" style="border: 2px dashed var(--border); border-radius: 16px; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s ease; background: var(--bg-dark); position: relative;">
                        <input type="file" name="file" id="file" accept=".pdf,image/*" style="display: none;" required>
                        
                        <div id="upload-content">
                            <div style="background: var(--primary-glow); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            </div>
                            <h3 style="margin: 0 0 0.5rem 0; font-weight: 700; font-size: 1.2rem; color: var(--text-primary);">Pilih File PDF atau Foto Scan</h3>
                            <p style="margin: 0 0 1.5rem 0; color: var(--text-muted); font-size: 0.95rem;">Mendukung format PDF, JPG, PNG (Maks. 20MB)</p>
                            <button type="button" class="btn btn-secondary" style="pointer-events: none; padding: 0.75rem 2rem; border-radius: 8px;">Pilih File...</button>
                        </div>

                        <div id="file-info" style="display: none; align-items: center; justify-content: space-between; background: var(--bg-card); border: 1px solid var(--border-subtle); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
                            <div style="display: flex; align-items: center; gap: 1.25rem;">
                                <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                </div>
                                <div style="text-align: left;">
                                    <h4 id="file-name" style="margin: 0 0 0.25rem 0; font-weight: 700; color: var(--text-primary); font-size: 1.05rem;">filename.jpg</h4>
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

            <div style="padding-left: 2.5rem; margin-top: 1.5rem; border-top: 1px solid var(--border-subtle); padding-top: 1.5rem;">
                <button type="submit" id="submit-btn" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.75rem; padding: 1.25rem; font-size: 1.1rem; font-weight: 700; border-radius: 12px; opacity: 0.5; pointer-events: none;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    Mulai Ekstraksi Data (OCR)
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('file');
        const uploadContent = document.getElementById('upload-content');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const removeFileBtn = document.getElementById('remove-file');
        const submitBtn = document.getElementById('submit-btn');

        uploadArea.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                showFileInfo(this.files[0]);
            }
        });

        // === DRAG AND DROP SUPPORT ===
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = 'var(--primary)';
                uploadArea.style.background = 'rgba(59, 130, 246, 0.05)'; // subtle blue tint
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = 'var(--border)';
                uploadArea.style.background = 'var(--bg-dark)';
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

        removeFileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.value = '';
            uploadContent.style.display = 'block';
            fileInfo.style.display = 'none';
            submitBtn.style.opacity = '0.5';
            submitBtn.style.pointerEvents = 'none';
        });

        function showFileInfo(file) {
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            uploadContent.style.display = 'none';
            fileInfo.style.display = 'flex';
            submitBtn.style.opacity = '1';
            submitBtn.style.pointerEvents = 'auto';
        }
    });
</script>
@endpush
@endsection
