<div class="overlay" id="overlay-others">
    <div class="modal" style="max-width:400px">
        <div class="modal-head">
            <span class="modal-title">Enter Name</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-others')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon"><i class="ti ti-user" style="font-size:40px;color:var(--hr-primary)" aria-hidden="true"></i></div>
            <p style="margin:16px 0 12px;font-size:0.9rem;color:var(--text-secondary)">Please enter the full name of the person who prepared this document.</p>
            <input type="text" id="others-name-input" placeholder="e.g. Juan D. Dela Cruz" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:0.9rem;">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('overlay-others')">Cancel</button>
            <button type="button" class="btn btn-blue" id="others-save-btn" onclick="saveOthersName()" disabled>Save</button>
        </div>
    </div>
</div>

<script>
function openOthersModal(targetFieldId) {
    const input = document.getElementById('others-name-input');
    const saveBtn = document.getElementById('others-save-btn');

    input.value = '';
    input.focus();

    saveBtn.disabled = true;

    input.oninput = function () {
        saveBtn.disabled = this.value.trim().length === 0;
    };

    document.getElementById('overlay-others').dataset.targetField = targetFieldId;
    document.getElementById('overlay-others').classList.add('show');
}

function saveOthersName() {
    const input = document.getElementById('others-name-input');
    const targetFieldId = document.getElementById('overlay-others').dataset.targetField;
    const value = input.value.trim();

    if (!value || !targetFieldId) return;

    const target = document.getElementById(targetFieldId);
    if (!target) {
        closeModal('overlay-others');
        return;
    }

    if (target.tagName === 'SELECT') {
        let option = target.querySelector('option[value="' + value + '"]');
        if (!option) {
            option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            target.appendChild(option);
        }
        target.value = value;
    } else {
        target.value = value;
    }

    closeModal('overlay-others');
}
</script>
