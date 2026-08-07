/* ============================================================
   PAMS — Reset Password Modal JS
   Used by: admin/passwords/index.blade.php
   Features: open/close reset password modal
   ============================================================
   FLAG 1 RESOLUTION: Instead of inline onclick="openResetModal(id, name)",
   the button now carries data-user-id and data-user-name attributes,
   and this file wires the click via event delegation.
   ============================================================ */

(function () {
    function openResetModal(id, name) {
        const overlay = document.getElementById('reset-overlay');
        const form = document.getElementById('reset-form');
        const baseUrl = overlay.dataset.baseUrl || '/admin/passwords';
        form.action = baseUrl + '/' + id;
        document.getElementById('reset-subtext').textContent = 'Set a new password for ' + name + '.';
        form.reset();
        overlay.classList.add('show');
    }

    function closeResetModal() {
        document.getElementById('reset-overlay').classList.remove('show');
    }

    function init() {
        // Event delegation: handles Reset buttons added by Blade loop
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-user-id][data-user-name]');
            if (btn && btn.closest('#reset-overlay') === null) {
                openResetModal(btn.dataset.userId, btn.dataset.userName);
            }

            // Close buttons: class="modal-close" inside #reset-overlay
            const closeBtn = e.target.closest('#reset-overlay .modal-close');
            if (closeBtn) closeResetModal();

            // Cancel button by id
            if (e.target.id === 'reset-cancel-btn') closeResetModal();
        });

        const overlay = document.getElementById('reset-overlay');
        if (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeResetModal();
            });
        }
    }

    if (document.readyState !== 'loading') {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
    document.addEventListener('hr:page:load', init);

    // Keep global functions for backward compat with any remaining onclick refs
    window.openResetModal  = openResetModal;
    window.closeResetModal = closeResetModal;
})();
