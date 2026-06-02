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

                        <!-- File Size Error (hidden by default) -->
                        <div id="file-error" style="display: none; margin-top: 1rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); padding: 0.75rem 1rem; border-radius: 10px; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                <span id="file-error-msg" style="color: #ef4444; font-weight: 600; font-size: 0.85rem;"></span>
                            </div>
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

<!-- Loading Overlay -->
<div id="scan-loading-overlay" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(10, 10, 20, 0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); justify-content: center; align-items: center; flex-direction: column; gap: 2rem;">
    <!-- Scanner Animation -->
    <div style="position: relative; width: 120px; height: 120px;">
        <!-- Outer ring -->
        <div id="scan-ring" style="position: absolute; inset: 0; border-radius: 50%; border: 3px solid transparent; border-top-color: var(--primary); animation: spin 1.2s linear infinite;"></div>
        <!-- Inner ring -->
        <div style="position: absolute; inset: 12px; border-radius: 50%; border: 3px solid transparent; border-bottom-color: rgba(var(--primary-rgb), 0.5); animation: spin 1.8s linear infinite reverse;"></div>
        <!-- Center icon -->
        <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
            <svg id="scan-center-icon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="animation: pulse 2s ease-in-out infinite;">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
    </div>

    <!-- Title -->
    <div style="text-align: center;">
        <h2 style="margin: 0 0 0.5rem; font-size: 1.6rem; font-weight: 800; color: white; letter-spacing: -0.02em;">Mengekstrak Data...</h2>
        <p id="scan-subtitle" style="margin: 0; color: rgba(255,255,255,0.5); font-size: 0.95rem;">Mohon tunggu, AI sedang membaca dokumen Anda</p>
    </div>

    <!-- Progress Steps -->
    <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 340px; max-width: 90vw;">
        <div class="scan-step" data-step="0" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); transition: all 0.4s ease;">
            <div class="step-icon" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(var(--primary-rgb), 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.4s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            </div>
            <div>
                <div class="step-label" style="font-size: 0.85rem; font-weight: 700; color: rgba(255,255,255,0.7); transition: color 0.3s;">Mengunggah file...</div>
                <div class="step-desc" style="font-size: 0.75rem; color: rgba(255,255,255,0.35);">Mengirim file ke server</div>
            </div>
        </div>
        <div class="scan-step" data-step="1" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); transition: all 0.4s ease;">
            <div class="step-icon" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.4s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            </div>
            <div>
                <div class="step-label" style="font-size: 0.85rem; font-weight: 700; color: rgba(255,255,255,0.35); transition: color 0.3s;">Konversi ke gambar</div>
                <div class="step-desc" style="font-size: 0.75rem; color: rgba(255,255,255,0.2);">PDF → gambar per halaman</div>
            </div>
        </div>
        <div class="scan-step" data-step="2" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); transition: all 0.4s ease;">
            <div class="step-icon" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.4s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            </div>
            <div>
                <div class="step-label" style="font-size: 0.85rem; font-weight: 700; color: rgba(255,255,255,0.35); transition: color 0.3s;">AI menganalisis dokumen</div>
                <div class="step-desc" style="font-size: 0.75rem; color: rgba(255,255,255,0.2);">Membaca seat ID & tanggal expiry</div>
            </div>
        </div>
        <div class="scan-step" data-step="3" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); transition: all 0.4s ease;">
            <div class="step-icon" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.4s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <div>
                <div class="step-label" style="font-size: 0.85rem; font-weight: 700; color: rgba(255,255,255,0.35); transition: color 0.3s;">Menyusun hasil</div>
                <div class="step-desc" style="font-size: 0.75rem; color: rgba(255,255,255,0.2);">Memformat data untuk review</div>
            </div>
        </div>
    </div>

    <!-- Elapsed Time -->
    <div style="text-align: center; margin-top: 0.5rem;">
        <span id="scan-elapsed" style="font-size: 0.8rem; color: rgba(255,255,255,0.3); font-variant-numeric: tabular-nums;">00:00</span>
        <p style="margin: 0.5rem 0 0; font-size: 0.75rem; color: rgba(255,255,255,0.2);">Proses biasanya memakan waktu 30-120 detik</p>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(0.92); }
}
.scan-step.active {
    background: rgba(var(--primary-rgb), 0.1) !important;
    border-color: rgba(var(--primary-rgb), 0.25) !important;
}
.scan-step.active .step-icon {
    background: rgba(var(--primary-rgb), 0.2) !important;
}
.scan-step.active .step-icon svg {
    stroke: var(--primary) !important;
}
.scan-step.active .step-label {
    color: white !important;
}
.scan-step.active .step-desc {
    color: rgba(255,255,255,0.45) !important;
}
.scan-step.done .step-icon {
    background: rgba(16, 185, 129, 0.15) !important;
}
.scan-step.done .step-icon svg {
    stroke: #10b981 !important;
}
.scan-step.done .step-label {
    color: rgba(255,255,255,0.5) !important;
}
</style>

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
            fileError.style.display = 'none';
            submitBtn.style.opacity = '0.5';
            submitBtn.style.pointerEvents = 'none';
        });

        const fileError = document.getElementById('file-error');
        const fileErrorMsg = document.getElementById('file-error-msg');
        const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB
        const ALLOWED_TYPES = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        const ALLOWED_EXTENSIONS = ['.pdf', '.jpg', '.jpeg', '.png'];

        function showFileInfo(file) {
            // Reset error
            fileError.style.display = 'none';

            // Validate file size
            if (file.size > MAX_FILE_SIZE) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                fileErrorMsg.textContent = `File terlalu besar (${sizeMB} MB). Maksimum 20 MB.`;
                fileError.style.display = 'block';
                fileName.textContent = file.name;
                fileSize.textContent = sizeMB + ' MB';
                uploadContent.style.display = 'none';
                fileInfo.style.display = 'flex';
                submitBtn.style.opacity = '0.5';
                submitBtn.style.pointerEvents = 'none';
                return;
            }

            // Validate file type
            const ext = '.' + file.name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXTENSIONS.includes(ext)) {
                fileErrorMsg.textContent = `Format file "${ext}" tidak didukung. Gunakan PDF, JPG, atau PNG.`;
                fileError.style.display = 'block';
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                uploadContent.style.display = 'none';
                fileInfo.style.display = 'flex';
                submitBtn.style.opacity = '0.5';
                submitBtn.style.pointerEvents = 'none';
                return;
            }

            fileName.textContent = file.name;
            fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            uploadContent.style.display = 'none';
            fileInfo.style.display = 'flex';
            submitBtn.style.opacity = '1';
            submitBtn.style.pointerEvents = 'auto';
        }

        // === LOADING OVERLAY LOGIC ===
        const form = document.querySelector('form[action*="pdf-scan"]');
        const overlay = document.getElementById('scan-loading-overlay');
        const elapsedEl = document.getElementById('scan-elapsed');
        const subtitleEl = document.getElementById('scan-subtitle');
        const steps = document.querySelectorAll('.scan-step');

        const subtitleMessages = [
            'Mengunggah file ke server...',
            'Mengkonversi halaman PDF menjadi gambar...',
            'AI sedang membaca & menganalisis dokumen Anda...',
            'Hampir selesai, menyusun data...',
        ];

        let currentStep = 0;
        let startTime;
        let timerInterval;

        function activateStep(idx) {
            steps.forEach((step, i) => {
                step.classList.remove('active', 'done');
                if (i < idx) step.classList.add('done');
                if (i === idx) step.classList.add('active');
            });
            if (subtitleMessages[idx]) {
                subtitleEl.textContent = subtitleMessages[idx];
            }
            currentStep = idx;
        }

        function updateElapsed() {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const mins = String(Math.floor(elapsed / 60)).padStart(2, '0');
            const secs = String(elapsed % 60).padStart(2, '0');
            elapsedEl.textContent = `${mins}:${secs}`;
        }

        if (form) {
            form.addEventListener('submit', function() {
                // Show overlay
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                // Disable submit button to prevent double submit
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.pointerEvents = 'none';

                // Start timer
                startTime = Date.now();
                timerInterval = setInterval(updateElapsed, 1000);

                // Activate first step immediately
                activateStep(0);

                // Auto-advance steps on realistic intervals
                setTimeout(() => activateStep(1), 3000);   // 3s: converting PDF
                setTimeout(() => activateStep(2), 10000);  // 10s: AI analyzing
                setTimeout(() => activateStep(3), 60000);  // 60s: formatting results
            });
        }
    });
</script>
@endpush
@endsection
