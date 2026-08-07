/* ============================================================
   PAMS — Archive Page JS
   Used by: appointments/archive.blade.php
   ============================================================ */

function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

function toggleSelectAll(source) {
    document.querySelectorAll('.select-row').forEach(cb => cb.checked = source.checked);
}

(function initArchiveDateRange() {
    const fromEl = document.getElementById('archive-from');
    const toEl   = document.getElementById('archive-to');
    if (!fromEl || !toEl) return;

    fromEl.addEventListener('change', function () { toEl.min = this.value; });
    toEl.addEventListener('change', function () { fromEl.max = this.value; });
})();

function submitMonitoringExport() {
    const selected = Array.from(document.querySelectorAll('.select-row:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one record to export.');
        return;
    }
    openModal('overlay-monitoring-confirm');
}

function confirmMonitoringExport() {
    closeModal('overlay-monitoring-confirm');

    const btn = document.getElementById('btn-export-monitoring');
    const label = document.getElementById('monitoring-export-label');
    if (btn) btn.disabled = true;
    if (label) label.textContent = 'Downloading...';

    const selected = Array.from(document.querySelectorAll('.select-row:checked')).map(cb => cb.value);
    const tokenEl = document.querySelector('meta[name="csrf-token"]');
    const token = tokenEl ? tokenEl.getAttribute('content') : '';

    const formData = new FormData();
    formData.append('_token', token);
    selected.forEach(id => formData.append('ids[]', id));

    fetch(window._pamsArchiveExportUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('Server error (' + response.status + '): ' + text.slice(0, 200));
            });
        }
        const disposition = response.headers.get('Content-Disposition');
        return response.blob().then(blob => ({ blob, disposition }));
    })
    .then(({ blob, disposition }) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        let filename = 'monitoring_export.xlsx';
        if (disposition) {
            const match = disposition.match(/filename\*?=(?:UTF-8''|"?)([^";]+)/i);
            if (match && match[1]) filename = decodeURIComponent(match[1]);
        }
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
    })
    .catch(err => { alert('Export failed: ' + err.message); })
    .finally(() => {
        setTimeout(() => {
            if (btn) btn.disabled = false;
            if (label) label.textContent = 'Export Monitoring';
        }, 1000);
    });
}

// Wire the select-all checkbox
(function () {
    const selectAllEl = document.getElementById('select-all-archive');
    if (selectAllEl) selectAllEl.addEventListener('change', function () { toggleSelectAll(this); });
})();
