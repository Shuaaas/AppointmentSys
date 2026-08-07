<div class="overlay" id="overlay-others">
    <div class="modal modal--narrow">
        <div class="modal-head">
            <span class="modal-title">Enter Name</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-others')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon"><i class="ti ti-user others-modal-icon" aria-hidden="true"></i></div>
            <p class="others-modal-desc">Please enter the full name of the person who prepared this document.</p>
            <input type="text" id="others-name-input" placeholder="e.g. Juan D. Dela Cruz" class="others-name-input">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('overlay-others')">Cancel</button>
            <button type="button" class="btn btn-blue" id="others-save-btn" onclick="saveOthersName()" disabled>Save</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/appointments-others-modal.js') }}"></script>
