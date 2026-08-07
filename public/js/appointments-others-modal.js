/* ============================================================
   PAMS — "Others" Name Modal JS
   Used by: appointments/partials/others-modal.blade.php
   ============================================================ */

function openOthersModal(targetFieldId) {
    const input = document.getElementById('others-name-input');
    const saveBtn = document.getElementById('others-save-btn');

    input.value = '';
    saveBtn.disabled = true;

    input.oninput = function () {
        saveBtn.disabled = this.value.trim().length === 0;
    };

    document.getElementById('overlay-others').dataset.targetField = targetFieldId;
    document.getElementById('overlay-others').classList.add('show');

    // Defer focus so the overlay transition completes first
    requestAnimationFrame(function () { input.focus(); });
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
