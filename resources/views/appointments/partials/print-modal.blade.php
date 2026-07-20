<div class="overlay" id="overlay-print">
    <div class="modal" style="max-width:520px">
        <div class="modal-head">
            <span class="modal-title">Print selected records</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-print')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="confirm-msg">You are about to print <strong id="print-count">0</strong> record(s). Review the list below to confirm they are correct.</p>
            <ul class="print-confirm-list" id="print-confirm-list"></ul>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" style="min-height:44px" onclick="closeModal('overlay-print')">
                <i class="ti ti-x" aria-hidden="true"></i> Cancel
            </button>
            <button type="button" class="btn btn-primary" style="min-height:44px" onclick="printSelectedRecords()">
                <i class="ti ti-printer" aria-hidden="true"></i> Print now
            </button>
        </div>
    </div>
</div>
