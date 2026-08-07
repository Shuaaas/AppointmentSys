/* ============================================================
   PAMS — User Management JS
   Used by: admin/users/index.blade.php
   Features: deactivate/reactivate confirmation modal,
             position title inline save
   ============================================================ */

function initUserDeactivationModal() {
    const modal = document.getElementById('deactivate-confirm-modal');
    const title = document.getElementById('deactivate-confirm-title');
    const text = document.getElementById('deactivate-confirm-text');
    const cancelBtn = document.getElementById('deactivate-cancel-btn');
    const confirmBtn = document.getElementById('deactivate-confirm-btn');
    let pendingForm = null;

    document.querySelectorAll('.deactivate-user-form').forEach(function (form) {
        form.querySelector('.deactivate-trigger').addEventListener('click', function () {
            pendingForm = form;
            const userName = form.dataset.userName || 'this account';
            const action = form.dataset.action === 'reactivate' ? 'reactivate' : 'deactivate';

            title.textContent = action === 'reactivate' ? 'Reactivate account?' : 'Deactivate account?';
            text.textContent = action === 'reactivate'
                ? 'Are you sure you want to reactivate ' + userName + '?'
                : 'Are you sure you want to deactivate ' + userName + '?';
            confirmBtn.textContent = action === 'reactivate' ? 'Reactivate' : 'Deactivate';
            confirmBtn.className = action === 'reactivate' ? 'btn btn-success' : 'btn btn-danger';
            modal.style.display = 'flex';
        });
    });

    function closeModal() {
        modal.style.display = 'none';
        pendingForm = null;
    }

    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    confirmBtn.addEventListener('click', function () {
        if (pendingForm) {
            pendingForm.submit();
        }
    });
}

function initializeUserDeactivationModalSafely() {
    window.requestAnimationFrame(function () {
        initUserDeactivationModal();
    });
}

document.addEventListener('DOMContentLoaded', initializeUserDeactivationModalSafely);
document.addEventListener('hr:page:load', initializeUserDeactivationModalSafely);
if (document.readyState !== 'loading') {
    initializeUserDeactivationModalSafely();
}

function initPositionInputs() {
    const inputs = document.querySelectorAll('.position-input');
    let timeout = null;

    inputs.forEach(function (input) {
        input.addEventListener('blur', function () {
            if (timeout) clearTimeout(timeout);
            savePosition(this);
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (timeout) clearTimeout(timeout);
                savePosition(this);
            }
        });

        input.addEventListener('input', function () {
            if (timeout) clearTimeout(timeout);
            timeout = setTimeout(() => savePosition(this), 1500);
        });
    });
}

function savePosition(input) {
    const userId = input.dataset.userId;
    const value = input.value.trim();
    const originalValue = input.value;

    fetch('/admin/users/' + userId + '/position', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ position_title: value }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.style.borderColor = 'var(--green)';
            setTimeout(() => {
                input.style.borderColor = '';
            }, 1500);
        } else {
            input.value = originalValue;
            input.style.borderColor = 'var(--red)';
        }
    })
    .catch(() => {
        input.value = originalValue;
        input.style.borderColor = 'var(--red)';
    });
}

document.addEventListener('hr:page:load', initPositionInputs);
if (document.readyState !== 'loading') {
    initPositionInputs();
} else {
    document.addEventListener('DOMContentLoaded', initPositionInputs);
}
