<div class="overlay" id="overlay-download">
    <div class="modal" style="max-width:420px">
        <div class="modal-head">
            <span class="modal-title" id="download-modal-title">Download not available</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-download')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon"><i class="ti ti-alert-triangle" style="font-size:44px;color:var(--red)" aria-hidden="true"></i></div>
            <p class="confirm-msg" id="download-modal-msg"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-blue" onclick="closeModal('overlay-download')">OK</button>
        </div>
    </div>
</div>

<div class="overlay" id="overlay-download-confirm">
    <div class="modal" style="max-width:520px">
        <div class="modal-head">
            <span class="modal-title">Download ZIP</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-download-confirm')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="confirm-msg">You are about to download <strong id="download-count">0</strong> appointment(s). Review the list below to confirm they are correct.</p>
            <ul class="print-confirm-list" id="download-confirm-list"></ul>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" style="min-height:44px" onclick="closeModal('overlay-download-confirm')">
                <i class="ti ti-x" aria-hidden="true"></i> Cancel
            </button>
            <button type="button" class="btn btn-blue" style="min-height:44px" onclick="confirmBulkDownload()">
                <i class="ti ti-download" aria-hidden="true"></i> Download now
            </button>
        </div>
    </div>
</div>
