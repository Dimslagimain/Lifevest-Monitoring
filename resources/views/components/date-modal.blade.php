<!-- Set Date Modal Premium -->
<div class="modal-overlay-premium" id="dateModal">
    <div class="modal-content-premium">
        <div class="modal-header-premium">
            <h2 id="modalTitle">Set Expiry Date</h2>
            <button class="modal-close-premium" id="modalClose">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding: var(--spacing-lg);">
            <div style="background: rgba(var(--primary-rgb), 0.08); border: 1px solid rgba(var(--primary-rgb), 0.15); border-radius: 10px; padding: 1rem; margin-bottom: 2rem; display: flex; align-items: flex-start; gap: 0.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="margin-top: 2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <p id="modalInfo" style="margin: 0; font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5;"></p>
            </div>

            <div class="form-group-premium">
                <label>Vested Expiry Date</label>
                <input type="date" id="dateInput" class="input-premium">
            </div>

            <div style="margin-top: 2.5rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <button type="button" class="btn btn-secondary" id="btnCancel" style="min-width: 100px;">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnApplyDate" style="min-width: 160px; font-weight: 700;">Update Selection</button>
            </div>
        </div>
    </div>
</div>