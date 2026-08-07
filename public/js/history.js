/* ============================================================
   PAMS — History Page JS
   Used by: history/index.blade.php
   ============================================================ */
(function () {
    const fromEl = document.getElementById('history-from');
    const toEl   = document.getElementById('history-to');
    if (!fromEl || !toEl) return;

    fromEl.addEventListener('change', function () { toEl.min = this.value; });
    toEl.addEventListener('change', function () { fromEl.max = this.value; });
})();
