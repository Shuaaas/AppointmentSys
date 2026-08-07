/* ============================================================
   PAMS — Trash Page JS
   Used by: appointments/trash.blade.php
   ============================================================ */

function toggleSelectAll(source) {
    document.querySelectorAll('.select-row').forEach(cb => cb.checked = source.checked);
}

function buildForm(url) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.innerHTML = '<input type="hidden" name="_token" value="' + token + '"><input type="hidden" name="_method" value="DELETE">';
    document.body.appendChild(form);
    return form;
}

function restoreSingle(btn, name) {
    if (!confirm('Restore ' + name + '?')) return;
    buildForm(btn.dataset.restoreUrl).submit();
}

function deleteSingle(btn, name) {
    if (!confirm('Permanently delete ' + name + '? This cannot be undone.')) return;
    buildForm(btn.dataset.forceUrl).submit();
}

function bulkDelete() {
    const selected = Array.from(document.querySelectorAll('.select-row:checked')).map(cb => cb.value);
    if (selected.length === 0) { alert('Please select at least one record to delete.'); return; }
    if (!confirm('Permanently delete the selected ' + selected.length + ' record(s)? This cannot be undone.')) return;

    const bulkUrl = document.querySelector('.table-card')?.dataset?.bulkUrl;
    if (!bulkUrl) { alert('Bulk delete URL is not configured.'); return; }

    const form = buildForm(bulkUrl);
    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });
    form.submit();
}

(function () {
    const btn = document.getElementById('btn-bulk-delete');
    if (btn) btn.addEventListener('click', bulkDelete);

    const selectAllEl = document.getElementById('select-all-trash');
    if (selectAllEl) selectAllEl.addEventListener('change', function () { toggleSelectAll(this); });
})();
