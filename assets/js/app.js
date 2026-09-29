const root = document.documentElement;
const saved = localStorage.getItem('enoughedu-theme');
if (saved) root.dataset.theme = saved;
document.addEventListener('DOMContentLoaded', () => {
    const theme = document.querySelector('[data-theme-toggle]');
    if (theme)
        theme.addEventListener('click', () => {
            const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            root.dataset.theme = next;
            localStorage.setItem('enoughedu-theme', next);
        });
    addEventListener('scroll', () =>
        document.querySelector('.nav')?.classList.toggle('scrolled', scrollY > 12),
    );
    document
        .querySelectorAll('.faq-q')
        .forEach((b) =>
            b.addEventListener('click', () => b.closest('.faq-item').classList.toggle('open')),
        );
    const io = new IntersectionObserver(
        (es) =>
            es.forEach((e) => {
                if (e.isIntersecting) e.target.classList.add('visible');
            }),
        { threshold: 0.12 },
    );
    document.querySelectorAll('.reveal').forEach((x) => io.observe(x));
    document.querySelectorAll('[data-counter]').forEach((el) => {
        let done = false;
        const co = new IntersectionObserver((es) => {
            if (es[0].isIntersecting && !done) {
                done = true;
                const n = +el.dataset.counter,
                    d = 1100,
                    start = performance.now();
                const run = (t) => {
                    const p = Math.min((t - start) / d, 1);
                    el.textContent =
                        Math.floor(n * (1 - Math.pow(1 - p, 3))).toLocaleString('en-IN') +
                        (el.dataset.suffix || '');
                    if (p < 1) requestAnimationFrame(run);
                };
                requestAnimationFrame(run);
            }
        });
        co.observe(el);
    });
    document
        .querySelectorAll('[data-modal-open]')
        .forEach(
            (b) =>
                (b.onclick = () =>
                    document.getElementById(b.dataset.modalOpen)?.classList.add('open')),
        );
    document
        .querySelectorAll('[data-modal-close]')
        .forEach((b) => (b.onclick = () => b.closest('.modal')?.classList.remove('open')));
});
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-mobile-menu-toggle]'),
        menu = document.querySelector('[data-mobile-nav]');
    if (!toggle || !menu) return;
    const close = () => {
        menu.hidden = true;
        menu.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open navigation');
    };
    toggle.addEventListener('click', () => {
        const opening = menu.hidden;
        menu.hidden = !opening;
        menu.classList.toggle('open', opening);
        toggle.setAttribute('aria-expanded', String(opening));
        toggle.setAttribute('aria-label', opening ? 'Close navigation' : 'Open navigation');
    });
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
    addEventListener('resize', () => {
        if (innerWidth > 950) close();
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-checkout-form]');
    if (!form) return;
    const input = form.querySelector('[data-coupon-input]'),
        apply = form.querySelector('[data-coupon-validate]'),
        feedback = form.querySelector('[data-coupon-feedback]'),
        submit = form.querySelector('[data-checkout-submit]'),
        subtotal = document.querySelector('[data-summary-subtotal]'),
        coupon = document.querySelector('[data-summary-coupon]'),
        total = document.querySelector('[data-summary-total]'),
        originalTotal = total.textContent;
    let validated = '';
    const normalized = () => input.value.trim().toUpperCase();
    const reset = () => {
        validated = '';
        form.dataset.couponValidated = 'false';
        coupon.textContent = 'Not applied';
        coupon.classList.remove('coupon-saving');
        total.textContent = originalTotal;
        feedback.textContent = input.value.trim()
            ? 'Coupon changed. Select Apply coupon to update the amount.'
            : 'Enter a code and select Apply coupon to see your final amount.';
        feedback.className = 'coupon-feedback muted';
    };
    input.addEventListener('input', reset);
    apply.addEventListener('click', async () => {
        apply.disabled = true;
        submit.disabled = true;
        apply.textContent = 'Checking…';
        feedback.textContent = 'Checking this coupon securely…';
        feedback.className = 'coupon-feedback muted';
        try {
            const response = await fetch('/api/coupon/validate', {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            if (!response.ok || !data.valid)
                throw new Error(data.message || 'This coupon could not be applied.');
            validated = normalized();
            form.dataset.couponValidated = 'true';
            subtotal.textContent = data.subtotal_formatted;
            coupon.textContent =
                data.discount > 0 ? `${data.discount_formatted} · ${data.code}` : 'Not applied';
            coupon.classList.toggle('coupon-saving', data.discount > 0);
            total.textContent = data.total_formatted;
            feedback.textContent = data.message;
            feedback.className = 'coupon-feedback success';
        } catch (error) {
            validated = '';
            form.dataset.couponValidated = 'false';
            coupon.textContent = 'Not applied';
            coupon.classList.remove('coupon-saving');
            total.textContent = originalTotal;
            feedback.textContent =
                error.message || 'Coupon validation is temporarily unavailable. Please try again.';
            feedback.className = 'coupon-feedback error';
        } finally {
            apply.disabled = false;
            submit.disabled = false;
            apply.textContent = 'Apply coupon';
        }
    });
    form.addEventListener('submit', (event) => {
        const code = normalized();
        if (code !== '' && validated !== code) {
            event.preventDefault();
            feedback.textContent =
                'Select Apply coupon and review the updated amount before continuing.';
            feedback.className = 'coupon-feedback error';
            input.focus();
            return;
        }
        submit.disabled = true;
        submit.textContent = 'Opening secure checkout…';
    });
});
function calculateCGPA() {
    const inputs = [...document.querySelectorAll('[data-grade]')];
    let points = 0,
        credits = 0;
    inputs.forEach((row) => {
        const g = parseFloat(row.querySelector('[name*=grade]')?.value || 0),
            c = parseFloat(row.querySelector('[name*=credit]')?.value || 0);
        points += g * c;
        credits += c;
    });
    const out = document.getElementById('calc-result');
    if (out) out.textContent = credits ? (points / credits).toFixed(2) : '0.00';
}
function calculateAttendance() {
    const held = +document.getElementById('held')?.value || 0,
        attended = +document.getElementById('attended')?.value || 0,
        target = +document.getElementById('target')?.value || 75,
        pct = held ? (attended / held) * 100 : 0;
    let note = `${pct.toFixed(1)}% attendance`;
    if (pct < target) {
        const needed = Math.ceil((target * held - 100 * attended) / (100 - target));
        note += ` · Attend the next ${Math.max(0, needed)} classes`;
    } else {
        const skip = Math.floor((100 * attended - target * held) / target);
        note += ` · You can miss ${Math.max(0, skip)} classes`;
    }
    document.getElementById('attendance-result').textContent = note;
}
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-onboarding-form]');
    if (!form) return;
    const steps = [...form.querySelectorAll('[data-onboarding-step]')],
        progress = document.querySelector('[data-onboarding-progress]'),
        counter = document.querySelector('[data-onboarding-count]');
    let current = 0;
    const show = (index) => {
        current = Math.max(0, Math.min(index, steps.length - 1));
        steps.forEach((step, i) => {
            const visible = i === current;
            step.hidden = !visible;
            step.classList.toggle('active', visible);
            step.setAttribute('aria-hidden', visible ? 'false' : 'true');
        });
        if (progress) progress.style.width = `${((current + 1) / steps.length) * 100}%`;
        if (counter) counter.textContent = `Step ${current + 1} of ${steps.length}`;
        requestAnimationFrame(() => {
            steps[current]
                .querySelector('input:not([type=hidden]):not([type=radio]),select,input:checked')
                ?.focus({ preventScroll: true });
        });
        scrollTo({ top: 0, behavior: 'smooth' });
    };
    const validStep = () => {
        const institutionId = steps[current].querySelector('[data-institution-id]');
        if (institutionId && !institutionId.value) {
            const search = steps[current].querySelector('[data-institution-search]');
            if (search) {
                search.setCustomValidity('Choose your college or university from the suggestions.');
                search.reportValidity();
                search.setCustomValidity('');
                search.focus();
            }
            return false;
        }
        const controls = [...steps[current].querySelectorAll('input,select,textarea')];
        for (const control of controls) {
            if (!control.checkValidity()) {
                control.reportValidity();
                return false;
            }
        }
        return true;
    };
    form.querySelectorAll('[data-onboarding-next]').forEach((button) =>
        button.addEventListener('click', () => {
            if (validStep()) show(current + 1);
        }),
    );
    form.querySelectorAll('[data-onboarding-back]').forEach((button) =>
        button.addEventListener('click', () => show(current - 1)),
    );
    const range = form.querySelector('[data-hours-range]'),
        output = form.querySelector('[data-hours-output]');
    if (range && output) range.addEventListener('input', () => (output.textContent = range.value));
    form.addEventListener('keydown', (event) => {
        if (
            event.key === 'Enter' &&
            event.target.matches('input:not([type=radio]):not([type=range]),select') &&
            current < steps.length - 1
        ) {
            event.preventDefault();
            if (validStep()) show(current + 1);
        }
    });
    form.addEventListener('submit', (event) => {
        if (!validStep()) {
            event.preventDefault();
            return;
        }
        form.classList.add('submitting');
        const submit = form.querySelector('button[type=submit]');
        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Building your dashboard…';
        }
    });
    show(0);
});
document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('[data-institution-search]'),
        idField = document.querySelector('[data-institution-id]'),
        results = document.querySelector('[data-institution-results]'),
        help = document.querySelector('[data-institution-help]');
    if (!input || !idField || !results) return;
    input.setCustomValidity(
        idField.value ? '' : 'Please select your college or university from the suggestions.',
    );
    let timer,
        controller,
        items = [],
        active = -1;
    const close = () => {
        results.hidden = true;
        active = -1;
    };
    const choose = (item) => {
        input.value = item.name;
        idField.value = item.id;
        input.setCustomValidity('');
        help.textContent = item.location
            ? `Selected · ${item.location}, India`
            : 'Selected · India';
        help.classList.add('selected');
        close();
    };
    const highlight = () =>
        results
            .querySelectorAll('button')
            .forEach((button, index) => button.classList.toggle('active', index === active));
    const render = (rows) => {
        items = rows;
        results.replaceChildren();
        if (!rows.length) {
            const empty = document.createElement('div');
            empty.className = 'institution-empty';
            empty.textContent =
                'No matching Indian institution found. Try the official or full name.';
            results.append(empty);
        } else
            rows.forEach((item, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.setAttribute('role', 'option');
                const name = document.createElement('b');
                name.textContent = item.name;
                const location = document.createElement('small');
                location.textContent = item.location ? `${item.location}, India` : 'India';
                button.append(name, location);
                button.addEventListener('click', () => choose(item));
                results.append(button);
            });
        results.hidden = false;
    };
    const search = async () => {
        const query = input.value.trim();
        idField.value = '';
        input.setCustomValidity('Please select your college or university from the suggestions.');
        help.classList.remove('selected');
        if (query.length < 2) {
            close();
            help.textContent = 'Type at least 2 letters, then choose one result.';
            return;
        }
        help.textContent = 'Searching colleges and universities across India…';
        controller?.abort();
        controller = new AbortController();
        try {
            const response = await fetch(`/api/institutions?q=${encodeURIComponent(query)}`, {
                signal: controller.signal,
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            render(Array.isArray(data.results) ? data.results : []);
            help.textContent =
                data.message || 'Choose the exact institution from the suggestions below.';
        } catch (error) {
            if (error.name !== 'AbortError') {
                render([]);
                help.textContent =
                    'The institution directory is temporarily unavailable. Try again shortly.';
            }
        }
    };
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(search, 280);
    });
    input.addEventListener('keydown', (event) => {
        const buttons = [...results.querySelectorAll('button')];
        if (results.hidden || !buttons.length) return;
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            active = Math.min(active + 1, buttons.length - 1);
            highlight();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            active = Math.max(active - 1, 0);
            highlight();
        } else if (event.key === 'Enter' && active >= 0) {
            event.preventDefault();
            choose(items[active]);
        } else if (event.key === 'Escape') close();
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.institution-field')) close();
    });
});
