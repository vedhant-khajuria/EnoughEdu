(() => {
    'use strict';
    const dialog = document.getElementById('app-download-prompt');
    if (!dialog || typeof dialog.showModal !== 'function') return;
    // Avoid inviting visitors who already opened an installed web app or Android WebView.
    if (
        matchMedia('(display-mode: standalone)').matches ||
        matchMedia('(display-mode: fullscreen)').matches ||
        /; wv\)/i.test(navigator.userAgent) ||
        document.referrer.startsWith('android-app://')
    )
        return;
    const key = 'enoughedu-app-invitation-v1';
    let state;
    try {
        state = JSON.parse(sessionStorage.getItem(key) || '{}');
    } catch {
        return;
    }
    if (!state || typeof state !== 'object') state = {};
    if (state.shown) return;
    let elapsed = Number.isFinite(state.elapsed) ? Math.max(0, Math.min(120000, state.elapsed)) : 0;
    let last = performance.now();
    let wasVisible = !document.hidden;
    let timer;
    let previousFocus;
    const save = () => {
        try {
            sessionStorage.setItem(key, JSON.stringify({ elapsed, shown: Boolean(state.shown) }));
        } catch {
            clearInterval(timer);
        }
    };
    const close = () => {
        dialog.close();
        previousFocus?.focus();
    };
    const tick = () => {
        const now = performance.now();
        if (wasVisible) elapsed = Math.min(120000, elapsed + now - last);
        last = now;
        wasVisible = !document.hidden;
        if (
            !state.shown &&
            elapsed >= 120000 &&
            !document.hidden &&
            !document.querySelector('dialog[open], .modal.open')
        ) {
            previousFocus = document.activeElement;
            dialog.showModal();
            state.shown = true;
            clearInterval(timer);
        }
        save();
    };
    dialog
        .querySelectorAll('[data-app-dismiss]')
        .forEach((button) => button.addEventListener('click', close));
    dialog.querySelector('[data-app-continue]').addEventListener('click', close);
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            const r = dialog.getBoundingClientRect();
            if (
                event.clientX < r.left ||
                event.clientX > r.right ||
                event.clientY < r.top ||
                event.clientY > r.bottom
            )
                close();
        }
    });
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        close();
    });
    document.addEventListener('visibilitychange', tick);
    addEventListener('pagehide', tick);
    timer = setInterval(tick, 1000);
})();
