/* ============================================================
   PAMS — Print auto-trigger
   Used by: appointments/print.blade.php (standalone print page)
   Replaces: <body onload="window.print()">
   ============================================================ */
window.addEventListener('load', function () { window.print(); });
