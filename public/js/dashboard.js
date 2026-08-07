(function () {
    function initDashboardApprovalModal() {
        const modal = document.getElementById('approval-confirm-modal');
        if (!modal) return;

        const title = document.getElementById('approval-confirm-title');
        const text = document.getElementById('approval-confirm-text');
        const cancelBtn = document.getElementById('approval-cancel-btn');
        const confirmBtn = document.getElementById('approval-confirm-btn');
        let pendingForm = null;

        document.querySelectorAll('.approval-form').forEach(function (form) {
            const trigger = form.querySelector('.approval-trigger');
            if (!trigger) return;

            trigger.addEventListener('click', function () {
                pendingForm = form;
                const userName = form.dataset.userName || 'this account';
                const action = form.dataset.action === 'reject' ? 'reject' : 'approve';

                if (title) title.textContent = action === 'reject' ? 'Reject account?' : 'Approve account?';
                if (text) {
                    text.textContent = action === 'reject'
                        ? 'Are you sure you want to reject ' + userName + '?'
                        : 'Are you sure you want to approve ' + userName + '?';
                }
                if (confirmBtn) {
                    confirmBtn.textContent = action === 'reject' ? 'Reject' : 'Approve';
                    confirmBtn.className = action === 'reject' ? 'btn btn-danger' : 'btn btn-primary';
                }
                if (modal) modal.style.display = 'flex';
            });
        });

        function closeModal() {
            if (modal) modal.style.display = 'none';
            pendingForm = null;
        }

        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (pendingForm) {
                    pendingForm.submit();
                }
            });
        }
    }

    function initializeDashboardApprovalModalSafely() {
        window.requestAnimationFrame(function () {
            initDashboardApprovalModal();
        });
    }

    document.addEventListener('DOMContentLoaded', initializeDashboardApprovalModalSafely);
    document.addEventListener('hr:page:load', initializeDashboardApprovalModalSafely);
    if (document.readyState !== 'loading') {
        initializeDashboardApprovalModalSafely();
    }
})();
