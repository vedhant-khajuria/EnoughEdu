(() => {
    'use strict';
    const dialog = document.getElementById('rl-membership-dialog');
    let previousFocus;
    const close = () => {
        dialog.close();
        previousFocus?.focus();
    };
    dialog
        ?.querySelectorAll('[data-rl-close]')
        .forEach((button) => button.addEventListener('click', close));
    dialog?.addEventListener('cancel', (event) => {
        event.preventDefault();
        close();
    });
    document.querySelectorAll('[data-rl-download]').forEach((button) =>
        button.addEventListener('click', async () => {
            button.disabled = true;
            try {
                const target = new URL(button.dataset.downloadUrl, location.origin);
                const response = await fetch('/resources/membership/status' + target.search, {
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) throw new Error('Cannot check access');
                const data = await response.json();
                if (data.allowed) {
                    location.assign(target.href);
                    return;
                }
                if (dialog && typeof dialog.showModal === 'function') {
                    previousFocus = button;
                    dialog.showModal();
                } else location.assign('/resources/membership');
            } catch {
                location.assign('/resources/membership');
            } finally {
                button.disabled = false;
            }
        }),
    );
    const form = document.querySelector('[data-rm-create]');
    if (!form) return;
    const feedback = document.getElementById('rm-feedback');
    const pay = document.getElementById('rm-pay');
    const summary = document.getElementById('rm-checkout-summary');
    let checkout;
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submit = form.querySelector('button[type=submit]');
        submit.disabled = true;
        feedback.textContent = 'Preparing your payment details…';
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Could not prepare checkout.');
            checkout = data;
            summary.textContent = data.trial_end
                ? `₹5 setup fee today. First monthly charge of ${data.amount_label} on ${data.trial_end}, then ${data.amount_label} each month for up to 120 payments unless cancelled. Downloads unlock after payment and autopay authorization are confirmed.`
                : `${data.amount_label} today and every month for up to 120 payments unless cancelled. Returning membership: no new trial or setup fee.`;
            summary.hidden = false;
            pay.hidden = false;
            form.hidden = true;
            feedback.textContent = 'Review the schedule above, then continue securely to Razorpay.';
        } catch (error) {
            feedback.textContent =
                error.message || 'Session expired. Refresh this page and try again.';
            submit.disabled = false;
        }
    });
    pay.addEventListener('click', () => {
        if (!checkout || typeof window.Razorpay !== 'function') {
            feedback.textContent = 'Payment checkout did not load. Refresh and try again.';
            return;
        }
        pay.disabled = true;
        const instance = new window.Razorpay({
            key: checkout.key,
            subscription_id: checkout.subscription_id,
            name: checkout.name,
            description: checkout.description,
            prefill: checkout.prefill,
            theme: { color: '#2563eb' },
            modal: {
                ondismiss: () => {
                    pay.disabled = false;
                    feedback.textContent =
                        'Checkout closed. You can resume while this authorization request is valid.';
                },
            },
            handler: async (result) => {
                const body = new FormData();
                body.set('csrf', checkout.csrf);
                // Match the application's existing CSRF field name.
                const csrfField = form.querySelector(
                    'input[type=hidden]:not([name=offer_plan]):not([name=offer_amount])',
                );
                if (csrfField) {
                    body.delete('csrf');
                    body.set(csrfField.name, csrfField.value);
                }
                body.set('membership_id', checkout.membership_id);
                body.set('razorpay_payment_id', result.razorpay_payment_id);
                body.set('razorpay_signature', result.razorpay_signature);
                feedback.textContent = 'Checking payment and autopay authorization…';
                try {
                    const response = await fetch('/resources/membership/verify', {
                        method: 'POST',
                        body,
                        headers: { Accept: 'application/json' },
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error);
                    feedback.textContent = data.message;
                    pay.hidden = true;
                    const link = document.createElement('a');
                    link.href = '/resources/membership';
                    link.className = 'card-link';
                    link.textContent = ' Refresh membership status';
                    feedback.append(link);
                } catch (error) {
                    feedback.textContent =
                        error.message ||
                        'Confirmation is pending. Refresh this page before trying another payment.';
                    pay.hidden = true;
                }
            },
        });
        instance.on('payment.failed', () => {
            feedback.textContent =
                'Payment did not complete. Check your bank and membership status before retrying.';
            pay.disabled = false;
        });
        instance.open();
    });
})();
