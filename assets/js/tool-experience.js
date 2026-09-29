(() => {
    'use strict';
    const app = document.querySelector('.tool-workspace');
    if (!app) return;
    const dialog = document.getElementById('te-payment'),
        message = document.getElementById('te-message'),
        checkout = document.getElementById('te-checkout'),
        recheck = document.getElementById('te-recheck');
    let waiting = null,
        checking = false;
    async function status() {
        const response = await fetch(
            '/api/tool-access?tool=' + encodeURIComponent(app.dataset.tool),
            {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { Accept: 'application/json' },
            },
        );
        if (!response.ok) throw Error('Access could not be checked. Please try again.');
        const data = await response.json();
        if (typeof data.allowed !== 'boolean')
            throw Error('Unexpected response. Refresh and try again.');
        window.ENOUGHEDU_CSRF = data.csrf;
        return data;
    }
    function permitted(data) {
        return (
            data.allowed &&
            (!['resume', 'cover', 'linkedin'].includes(app.dataset.type) || data.login)
        );
    }
    function explain(data) {
        message.textContent = data.free
            ? 'This AI tool is free. Sign in to generate your result.'
            : data.login
              ? `Unlock this tool for ₹${data.price}, or use an eligible plan. Your inputs remain here while you pay.`
              : `Sign in to continue. This premium tool costs ₹${data.price}, or is included with eligible plans.`;
        checkout.href = data.login
            ? '/checkout?tool=' + encodeURIComponent(app.dataset.tool)
            : '/login';
        checkout.textContent = data.login ? 'Continue to secure checkout' : 'Sign in in a new tab';
    }
    async function authorize() {
        if (checking || waiting) return false;
        checking = true;
        try {
            const data = await status();
            if (permitted(data)) return true;
            explain(data);
            dialog.showModal();
            return await new Promise((resolve) => (waiting = resolve));
        } catch (error) {
            message.textContent = error.message;
            checkout.hidden = true;
            dialog.showModal();
            return false;
        } finally {
            checking = false;
        }
    }
    dialog.addEventListener('close', () => {
        checkout.hidden = false;
        if (waiting) {
            waiting(false);
            waiting = null;
        }
    });
    dialog
        .querySelectorAll('[data-te-close]')
        .forEach((button) => button.addEventListener('click', () => dialog.close()));
    recheck.addEventListener('click', async () => {
        recheck.disabled = true;
        try {
            const data = await status();
            if (permitted(data)) {
                if (waiting) {
                    waiting(true);
                    waiting = null;
                }
                dialog.close();
                message.textContent = 'Access confirmed. Click the final action again if needed.';
            } else {
                explain(data);
                message.textContent += ' Access has not been confirmed yet.';
            }
        } catch (error) {
            message.textContent = error.message;
        } finally {
            recheck.disabled = false;
        }
    });
    window.EnoughEduTools = { authorize, formulasAllowed: false };
})();
