/* ============================================================
   PAMS — Auth page clock
   Used by: auth/login.blade.php
   Drives: #authHeaderDateTime live clock in the auth header
   ============================================================ */
(function () {
    const el = document.getElementById('authHeaderDateTime');
    if (!el) return;

    function update() {
        const now = new Date();
        const datePart = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
        const timePart = now.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
        el.textContent = datePart + ' at ' + timePart;
    }

    update();
    setInterval(update, 1000);
})();
