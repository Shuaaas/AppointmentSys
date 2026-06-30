<div class="overlay" id="overlay-del">
    <div class="modal" style="max-width:420px">
        <div class="modal-head">
            <span class="modal-title">Delete record</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-del')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon"><i class="ti ti-alert-triangle" style="font-size:44px;color:var(--red)" aria-hidden="true"></i></div>
            <p class="confirm-msg">Delete the record for <strong id="del-name"></strong>?</p>
            <p class="confirm-sub">This action moves the record to Trash. It can be restored later or permanently deleted from there.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('overlay-del')">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                <i class="ti ti-trash" style="font-size:13px" aria-hidden="true"></i> Yes, delete
            </button>
        </div>
    </div>
</div>