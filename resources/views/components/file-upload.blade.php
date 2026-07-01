@props([
    'accept' => '.pdf,image/*',
    'maxSize' => 20,
    'label' => 'Choose File',
    'buttonText' => 'Pilih File...',
    'name' => 'file',
    'required' => true,
    'hint' => ''
])

<div class="upload-area" id="upload-area"
    style="border: 2px dashed var(--border); border-radius: 12px; padding: 1.25rem; text-align: center; cursor: pointer; transition: all 0.2s ease; background: var(--bg-dark); position: relative;">
    <input type="file" name="{{ $name }}" id="file" accept="{{ $accept }}" style="display: none;" {{ $required ? 'required' : '' }}>

    <div id="upload-content">
        <div style="background: var(--primary-glow); width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
        <h3 style="margin: 0 0 0.25rem 0; font-weight: 700; font-size: 1rem; color: var(--text-primary);">{{ $label }}</h3>
        @if($hint)
            <p style="margin: 0 0 1rem 0; color: var(--text-muted); font-size: 0.88rem;">{{ $hint }}</p>
        @endif
        <button type="button" class="btn btn-secondary" style="pointer-events: none; padding: 0.75rem 2rem; border-radius: 8px;">{{ $buttonText }}</button>
    </div>

    <div id="file-info"
        style="display: none; align-items: center; justify-content: space-between; background: var(--bg-card); border: 1px solid var(--border-subtle); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="background: var(--primary-glow); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2-2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
            </div>
            <div>
                <p id="file-name" style="margin: 0; font-weight: 600; font-size: 0.9rem; color: var(--text-primary);"></p>
                <p id="file-size" style="margin: 0.2rem 0 0; font-size: 0.8rem; color: var(--text-muted);"></p>
            </div>
        </div>
        <button type="button" id="remove-file" style="background: none; border: none; color: var(--danger); cursor: pointer; padding: 0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const uploadArea = document.getElementById('upload-area');
                const fileInput = document.getElementById('file');
                const uploadContent = document.getElementById('upload-content');
                const fileInfo = document.getElementById('file-info');
                const fileName = document.getElementById('file-name');
                const fileSize = document.getElementById('file-size');
                const removeBtn = document.getElementById('remove-file');

                if (!uploadArea) return;

                uploadArea.addEventListener('click', function (e) {
                    if (e.target !== removeBtn && !removeBtn?.contains(e.target)) {
                        fileInput?.click();
                    }
                });

                uploadArea.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    this.style.borderColor = 'var(--primary)';
                    this.style.background = 'rgba(var(--primary-rgb), 0.05)';
                });

                uploadArea.addEventListener('dragleave', function (e) {
                    e.preventDefault();
                    this.style.borderColor = 'var(--border)';
                    this.style.background = 'var(--bg-dark)';
                });

                uploadArea.addEventListener('drop', function (e) {
                    e.preventDefault();
                    this.style.borderColor = 'var(--border)';
                    this.style.background = 'var(--bg-dark)';
                    if (e.dataTransfer.files.length > 0 && fileInput) {
                        fileInput.files = e.dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change'));
                    }
                });

                if (fileInput) {
                    fileInput.addEventListener('change', function () {
                        if (this.files.length > 0) {
                            const file = this.files[0];
                            const maxBytes = {{ $maxSize }} * 1024 * 1024;
                            if (file.size > maxBytes) {
                                alert('File too large. Maximum size is {{ $maxSize }}MB.');
                                this.value = '';
                                return;
                            }
                            if (fileName) fileName.textContent = file.name;
                            if (fileSize) fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                            if (uploadContent) uploadContent.style.display = 'none';
                            if (fileInfo) fileInfo.style.display = 'flex';
                        }
                    });
                }

                if (removeBtn) {
                    removeBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (fileInput) {
                            fileInput.value = '';
                            fileInput.dispatchEvent(new Event('change'));
                        }
                        if (uploadContent) uploadContent.style.display = '';
                        if (fileInfo) fileInfo.style.display = 'none';
                    });
                }
            });
        </script>
    @endpush
@endonce
