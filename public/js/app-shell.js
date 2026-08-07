/* ── Auto-submit utility ──────────────────────────────────────
   Any element with class "js-autosubmit" will submit its closest
   parent <form> when its value changes.
   Replaces all onchange="this.form.submit()" inline handlers.
   ─────────────────────────────────────────────────────────── */
document.addEventListener('change', function (e) {
    if (e.target.matches('.js-autosubmit')) {
        e.target.closest('form').submit();
    }
});

(function () {
    const sidebar = document.getElementById('hrSidebar');
    const toggleBtn = document.getElementById('hrSidebarToggleBtn');
    const STORAGE_KEY = 'hrSidebarCollapsed';

    if (!sidebar || !toggleBtn) return;

    const MOBILE_BREAKPOINT = 900;
    let desktopCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';

    function isMobile() {
        return window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT}px)`).matches;
    }

    const toggleIcon = toggleBtn.querySelector('i');

    function updateToggleIcon() {
        if (!toggleIcon) return;
        if (isMobile()) {
            toggleIcon.className = sidebar.classList.contains('visible') ? 'ti ti-x' : 'ti ti-menu';
        } else {
            toggleIcon.className = desktopCollapsed ? 'ti ti-layout-sidebar-right' : 'ti ti-menu';
        }
    }

    function updateToggleState() {
        if (isMobile()) {
            toggleBtn.classList.toggle('active', sidebar.classList.contains('visible'));
        } else {
            toggleBtn.classList.toggle('active', desktopCollapsed);
        }
    }

    function applyState() {
        if (isMobile()) {
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('visible');
            toggleBtn.setAttribute('aria-label', 'Open sidebar');
        } else {
            sidebar.classList.toggle('collapsed', desktopCollapsed);
            sidebar.classList.remove('visible');
            toggleBtn.setAttribute('aria-label', desktopCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
        }
        updateToggleIcon();
        updateToggleState();
        sidebar.classList.toggle('logo-only', desktopCollapsed && !isMobile());
    }

    applyState();

    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (isMobile()) {
            const visible = sidebar.classList.toggle('visible');
            toggleBtn.setAttribute('aria-label', visible ? 'Close sidebar' : 'Open sidebar');
            updateToggleIcon();
            updateToggleState();
        } else {
            desktopCollapsed = !desktopCollapsed;
            localStorage.setItem(STORAGE_KEY, desktopCollapsed ? 'true' : 'false');
            applyState();
        }
    });

    document.addEventListener('click', function (e) {
        if (isMobile() && sidebar.classList.contains('visible')) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('visible');
                toggleBtn.setAttribute('aria-label', 'Open sidebar');
                updateToggleIcon();
                updateToggleState();
            }
        }
    });

    window.addEventListener('resize', applyState);
})();

(function () {
    const fullscreenBtn = document.getElementById('hrFullscreenBtn');
    if (!fullscreenBtn) return;

    const icon = fullscreenBtn.querySelector('i');
    const FS_KEY = 'hrFullscreenActive';

    function updateIcon() {
        if (!icon) return;
        icon.className = document.fullscreenElement ? 'ti ti-arrows-minimize' : 'ti ti-arrows-maximize';
    }

    function setActive(on) {
        try { localStorage.setItem(FS_KEY, on ? '1' : '0'); } catch (e) {}
    }

    function isActive() {
        try { return localStorage.getItem(FS_KEY) === '1'; } catch (e) { return false; }
    }

    function requestFs() {
        const el = document.documentElement;
        if (el.requestFullscreen) el.requestFullscreen().catch(function () {});
    }

    fullscreenBtn.addEventListener('click', function () {
        if (!document.fullscreenElement) {
            requestFs();
            setActive(true);
        } else {
            document.exitFullscreen();
            setActive(false);
        }
    });

    function restore() {
        if (document.fullscreenElement) return;
        if (isActive()) requestFs();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restore);
    } else {
        restore();
    }

    document.addEventListener('fullscreenchange', function () {
        if (!document.fullscreenElement) setActive(false);
        updateIcon();
    });
    updateIcon();
})();

(function () {
    const el = document.getElementById('hrHeaderDateTime');
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

/* ── AJAX page navigation ─────────────────
   Loads sidebar/header links without a full page reload so the
   document (and its fullscreen state) stays alive until the user
   explicitly exits fullscreen. Per-page scripts are re-injected and
   re-initialised via a synthetic DOMContentLoaded. */
(function () {
    if (typeof window.axios === 'undefined') return;

    function showNavLoader() {
        const o = document.getElementById('deped-page-loader');
        if (o) o.classList.add('is-active');
    }

    function hideNavLoader() {
        const o = document.getElementById('deped-page-loader');
        if (!o) return;
        const bar = o.querySelector('.loader-progress-bar');
        if (bar) { bar.style.transition = 'width 0.15s ease-out'; bar.style.width = '100%'; }
        setTimeout(function () {
            o.classList.remove('is-active');
            if (bar) setTimeout(function () { bar.style.transition = ''; bar.style.width = ''; }, 150);
        }, 140);
    }

    function pathOf(href) {
        try { return new URL(href, window.location.href); }
        catch (e) { return null; }
    }

    function isNavLink(link) {
        if (!link || link.tagName !== 'A') return false;
        if (link.hasAttribute('data-no-loader')) return false;
        if (link.target && link.target !== '_self') return false;
        if (link.hasAttribute('download')) return false;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
            href.startsWith('mailto:') || href.startsWith('tel:')) return false;
        const url = pathOf(link.href);
        if (!url || url.origin !== window.location.origin) return false;
        return true;
    }

    function setActiveNav(url) {
        const u = pathOf(url);
        if (!u) return;
        const links = Array.from(document.querySelectorAll('.nav-link'));
        let exact = false;
        links.forEach(function (a) {
            const ap = pathOf(a.href);
            if (ap && ap.pathname === u.pathname) exact = true;
        });
        links.forEach(function (a) {
            const ap = pathOf(a.href);
            if (!ap) { a.classList.remove('active'); return; }
            const isActive = exact
                ? ap.pathname === u.pathname
                : u.pathname === ap.pathname || u.pathname.startsWith(ap.pathname + '/');
            a.classList.toggle('active', isActive);
        });
    }

    function reinsertScripts(container, sourceContainer) {
        if (!container || !sourceContainer) return;

        container.querySelectorAll('script').forEach(function (script) {
            script.remove();
        });

        sourceContainer.querySelectorAll('script').forEach(function (old) {
            const s = document.createElement('script');
            if (old.src) {
                s.src = old.src;
                s.async = false;
            } else {
                s.textContent = old.textContent;
            }
            container.appendChild(s);
        });
    }

    function reinitScripts(doc) {
        var newCsrfMeta = doc.querySelector('meta[name="csrf-token"]');
        var curCsrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (newCsrfMeta && curCsrfMeta) {
            var freshToken = newCsrfMeta.getAttribute('content');
            curCsrfMeta.setAttribute('content', freshToken);
            if (window.axios) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = freshToken;
            }
        }

        var curModals = document.getElementById('hr-modals');
        var nextModals = doc.getElementById('hr-modals');
        if (curModals && nextModals) {
            curModals.innerHTML = nextModals.innerHTML;
            reinsertScripts(curModals, nextModals);
        }

        var cur = document.getElementById('hr-scripts');
        var next = doc.getElementById('hr-scripts');
        if (cur && next) {
            reinsertScripts(cur, next);
        }

        var curMain = document.querySelector('main.content') || document.querySelector('main');
        var nextMain = doc.querySelector('main.content') || doc.querySelector('main');
        if (curMain && nextMain) {
            reinsertScripts(curMain, nextMain);
        }
    }

    function triggerPageLoadEvents() {
        document.dispatchEvent(new Event('hr:page:load'));
    }

    /* Sync <head> <link rel="stylesheet"> tags from the incoming doc.
       - Adds sheets that the new page needs but current page doesn't have.
       - Removes sheets the current page has but the new page doesn't need.
       Core app stylesheets (page-loader.css, app.css, bootstrap-icons, tabler)
       are always kept; only per-page injected sheets (e.g. auth.css) are diffed. */
    function syncHeadStyles(doc) {
        // Collect hrefs that are "core" and should never be removed
        var corePatterns = ['app.css', 'page-loader.css', 'bootstrap-icons', 'tabler-icons'];
        function isCore(href) {
            if (!href) return true;
            return corePatterns.some(function (p) { return href.indexOf(p) !== -1; });
        }

        // Gather per-page sheets from the incoming document
        var nextSheets = {};
        doc.querySelectorAll('link[rel="stylesheet"]').forEach(function (el) {
            var href = el.getAttribute('href');
            if (href && !isCore(href)) nextSheets[href] = true;
        });

        // Remove per-page sheets that the new page does NOT need
        document.querySelectorAll('link[rel="stylesheet"]').forEach(function (el) {
            var href = el.getAttribute('href');
            if (!isCore(href) && href && !nextSheets[href]) {
                el.parentNode.removeChild(el);
            }
        });

        // Add per-page sheets that the new page needs but aren't loaded yet
        Object.keys(nextSheets).forEach(function (href) {
            var already = document.querySelector('link[rel="stylesheet"][href="' + href + '"]');
            if (!already) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            }
        });
    }

    /* Sync the <body> class from the incoming doc so scoped CSS
       rules (e.g. .add-user-page-host) apply/disappear correctly. */
    function syncBodyClass(doc) {
        var newClass = (doc.body && doc.body.className) ? doc.body.className : '';
        document.body.className = newClass;
    }

    let navToken = 0;

    function loadPage(url, push) {
        const token = ++navToken;
        showNavLoader();

        window.axios.get(url)
            .then(function (res) {
                if (token !== navToken) return;
                const doc = new DOMParser().parseFromString(res.data, 'text/html');

                // Sync per-page stylesheets and body class BEFORE swapping content
                syncHeadStyles(doc);
                syncBodyClass(doc);

                const newMain = doc.querySelector('main.content') || doc.querySelector('main');
                const curMain = document.querySelector('main.content') || document.querySelector('main');
                if (newMain && curMain) curMain.innerHTML = newMain.innerHTML;

                reinitScripts(doc);

                triggerPageLoadEvents();

                document.title = doc.title;
                const newTitle = doc.querySelector('.hr-page-title');
                const curTitle = document.querySelector('.hr-page-title');
                if (newTitle && curTitle) curTitle.textContent = newTitle.textContent;

                setActiveNav(url);
                if (push !== false) history.pushState({ url: url }, '', url);
            })
            .catch(function () {
                if (token === navToken) window.location.href = url;
            })
            .finally(function () {
                if (token === navToken) hideNavLoader();
            });
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 ||
            e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const link = e.target.closest('a');
        if (!isNavLink(link)) return;
        const url = pathOf(link.href);
        if (url.pathname === window.location.pathname && url.search === window.location.search) return;
        e.preventDefault();
        loadPage(link.href, true);
    });

    window.addEventListener('popstate', function () {
        loadPage(window.location.href, false);
    });
})();
