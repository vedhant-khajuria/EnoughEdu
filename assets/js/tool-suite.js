(() => {
    'use strict';
    const app = document.querySelector('.tool-workspace');
    if (!app) return;
    const slug = app.dataset.tool,
        type = app.dataset.type,
        result = document.getElementById('tool-result'),
        run = document.getElementById('tool-run'),
        reset = document.getElementById('tool-reset'),
        copy = document.getElementById('copy-result');
    const val = (id) => document.getElementById(id)?.value?.trim() || '';
    const num = (id) => Number(val(id));
    const esc = (s) =>
        String(s).replace(
            /[&<>"']/g,
            (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c],
        );
    const show = (title, body, plain = '') => {
        result.innerHTML = `<h2 style="margin:18px 0">${esc(title)}</h2><div class="tool-result-content">${body}</div>`;
        result.dataset.plain = plain || result.innerText;
        copy.hidden = false;
    };
    const grades = () => {
        let weighted = 0,
            credits = 0;
        document.querySelectorAll('#grade-rows .tool-row').forEach((r) => {
            const g = Number(r.querySelector('[name=grade]').value),
                c = Number(r.querySelector('[name=credit]').value);
            if (Number.isFinite(g) && Number.isFinite(c) && c > 0) {
                weighted += g * c;
                credits += c;
            }
        });
        if (!credits)
            return show(
                'Add your grades',
                '<p>Enter at least one grade point and credit value.</p>',
            );
        const score = weighted / credits;
        show(
            score.toFixed(2),
            `<p>Weighted across <b>${credits} credits</b>.</p><p>${score >= 8.5 ? 'Excellent performance.' : score >= 7 ? 'You are on a solid track.' : 'Use the GPA planner to build a recovery target.'}</p>`,
            score.toFixed(2),
        );
    };
    const percentage = () => {
        const a = num('marks-obtained'),
            b = num('marks-total');
        if (!(b > 0) || a < 0)
            return show('Check the values', '<p>Total marks must be greater than zero.</p>');
        const p = (a / b) * 100,
            grade =
                p >= 90
                    ? 'A+'
                    : p >= 80
                      ? 'A'
                      : p >= 70
                        ? 'B'
                        : p >= 60
                          ? 'C'
                          : p >= 50
                            ? 'D'
                            : 'Needs improvement';
        show(
            `${p.toFixed(2)}%`,
            `<p>Grade band: <b>${grade}</b></p><p>${a} out of ${b} marks.</p>`,
            `${p.toFixed(2)}% — ${grade}`,
        );
    };
    const attendance = () => {
        const held = num('held'),
            att = num('attended'),
            target = num('attendance-target');
        if (!(held > 0) || att < 0 || att > held || target <= 0 || target >= 100)
            return show(
                'Check the values',
                '<p>Attended classes cannot exceed classes held, and target must be below 100%.</p>',
            );
        const pct = (att / held) * 100;
        if (pct < target) {
            const need = Math.ceil((target * held - 100 * att) / (100 - target));
            show(
                `${pct.toFixed(1)}%`,
                `<p>Attend the next <b>${Math.max(0, need)} classes</b> continuously to reach ${target}%.</p>`,
                `${pct.toFixed(1)}%. Attend next ${need} classes.`,
            );
        } else {
            const miss = Math.floor((100 * att - target * held) / target);
            show(
                `${pct.toFixed(1)}%`,
                `<p>You can miss up to <b>${Math.max(0, miss)} classes</b> while staying at or above ${target}%.</p>`,
                `${pct.toFixed(1)}%. You can miss ${miss} classes.`,
            );
        }
    };
    const gpa = () => {
        const v = num('gpa-value'),
            scale = num('gpa-scale');
        if (v < 0 || v > scale)
            return show('Check the value', `<p>Enter a number between 0 and ${scale}.</p>`);
        let ten, pct, four;
        if (scale === 10) {
            ten = v;
            pct = Math.min(100, v * 9.5);
            four = Math.min(4, v / 2.5);
        } else if (scale === 4) {
            four = v;
            ten = v * 2.5;
            pct = Math.min(100, (v / 4) * 100);
        } else {
            pct = v;
            ten = Math.min(10, v / 9.5);
            four = Math.min(4, v / 25);
        }
        show(
            `${ten.toFixed(2)} / 10`,
            `<p><b>${four.toFixed(2)} / 4</b><br><b>${pct.toFixed(2)}%</b></p><small>Conversions are estimates; universities may use different rules.</small>`,
            `${ten.toFixed(2)}/10 · ${four.toFixed(2)}/4 · ${pct.toFixed(2)}%`,
        );
    };
    const marks = () => {
        const i = num('internal'),
            im = num('internal-max'),
            e = num('external'),
            em = num('external-max');
        if (!(im > 0 && em > 0) || i < 0 || e < 0 || i > im || e > em)
            return show('Check the marks', '<p>Marks cannot exceed their maximum.</p>');
        const total = i + e,
            max = im + em,
            pct = (total / max) * 100;
        show(
            `${total.toFixed(1)} / ${max}`,
            `<p>Predicted percentage: <b>${pct.toFixed(2)}%</b></p><p>Estimated grade: <b>${pct >= 90 ? 'A+' : pct >= 80 ? 'A' : pct >= 70 ? 'B' : pct >= 60 ? 'C' : pct >= 50 ? 'D' : 'F'}</b></p>`,
            `${total}/${max} (${pct.toFixed(2)}%)`,
        );
    };
    const backlog = () => {
        const cg = num('current-cgpa'),
            done = num('completed-credits'),
            bc = num('backlog-credits'),
            bg = num('backlog-grade');
        if (cg < 0 || cg > 10 || done <= 0 || bc <= 0 || bg < 0 || bg > 10)
            return show(
                'Check the values',
                '<p>CGPA and grade points must be between 0 and 10.</p>',
            );
        const projected = (cg * done + bg * bc) / (done + bc),
            change = projected - cg;
        show(
            projected.toFixed(2),
            `<p>Projected CGPA after clearing the course.</p><p>Change: <b>${change >= 0 ? '+' : ''}${change.toFixed(3)}</b></p>`,
            `${projected.toFixed(2)} projected CGPA`,
        );
    };
    let generatedResume = null,
        resumeProfile = null,
        generatedCareer = null,
        careerProfile = null,
        careerMode = '';
    const careerDetails = () => ({
        name: val('profile-name'),
        email: val('profile-email'),
        phone: val('profile-phone'),
        location: val('profile-location'),
        linkedin: val('profile-linkedin'),
        portfolio: val('profile-portfolio'),
        branch: val('profile-branch'),
        education: val('profile-education'),
        skills: val('profile-skills'),
        projects: val('profile-projects'),
        target: val('profile-target'),
        job_description: val('profile-job'),
        company: val('profile-company'),
        hiring_manager: val('profile-manager'),
        tone: val('profile-tone'),
    });
    const profile = async (kind) => {
        const details = careerDetails(),
            pdfButton = document.getElementById('download-career-pdf'),
            invalid =
                !details.name ||
                !details.target ||
                (!details.skills && !details.projects) ||
                (kind === 'resume' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(details.email)) ||
                (kind === 'cover' && (!details.company || !details.job_description));
        if (invalid)
            return show(
                'Complete the important fields',
                '<p>Add every required field, including truthful skills or experience.</p>',
            );
        if (!(await window.EnoughEduTools.authorize())) return;
        run.disabled = true;
        run.textContent = 'Generating…';
        copy.hidden = true;
        if (pdfButton) pdfButton.hidden = true;
        generatedCareer = null;
        careerProfile = null;
        careerMode = '';
        try {
            const form = new URLSearchParams({
                    csrf: window.ENOUGHEDU_CSRF || '',
                    mode: kind,
                    details: JSON.stringify(details),
                }),
                response = await fetch('/api/career-ai', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        Accept: 'application/json',
                    },
                    body: form,
                }),
                json = await response.json();
            if (!response.ok) throw new Error(json.error || 'Generation failed.');
            const d = json.data || {};
            generatedCareer = d;
            careerProfile = details;
            careerMode = kind;
            let text = '',
                body = '';
            if (kind === 'resume') {
                generatedResume = d;
                resumeProfile = details;
                text = `${details.name}\n${[details.email, details.phone, details.location].filter(Boolean).join(' | ')}\n\n${d.headline || ''}\n\nPROFESSIONAL SUMMARY\n${d.summary || ''}\n\nSKILLS\n${(d.skills || []).join(' | ')}\n\nPROJECTS & EXPERIENCE\n${(d.experience_bullets || []).map((x) => '• ' + x).join('\n')}\n\nEDUCATION\n${d.education || details.education || details.branch || ''}`;
            } else if (kind === 'linkedin')
                text = `HEADLINE\n${d.headline || ''}\n\nABOUT\n${d.about || ''}\n\nSKILLS\n${(d.skills || []).join(' · ')}`;
            else text = d.letter || '';
            body = `<pre class="generated-text">${esc(text)}</pre><small>AI can make mistakes. Verify every fact before using this content.</small>`;
            show(
                kind === 'resume'
                    ? 'ATS-friendly resume'
                    : kind === 'linkedin'
                      ? 'LinkedIn profile'
                      : 'Cover letter',
                body,
                text,
            );
            if (pdfButton) pdfButton.hidden = false;
        } catch (error) {
            show('Could not generate', `<p>${esc(error.message)}</p>`);
        } finally {
            run.disabled = false;
            run.textContent = 'Generate with AI';
        }
    };
    const ats = () => {
        const resume = val('resume-text'),
            job = val('job-description');
        if (resume.length < 80 || job.length < 30)
            return show(
                'More text needed',
                '<p>Paste both the job description and your resume text.</p>',
            );
        const stop = new Set(
            'the and for with that this from your you are our will have has into using use job role work'.split(
                ' ',
            ),
        );
        const words = job.toLowerCase().match(/[a-z][a-z+#.]{2,}/g) || [];
        const freq = {};
        words.forEach((w) => {
            if (!stop.has(w)) freq[w] = (freq[w] || 0) + 1;
        });
        const keys = Object.keys(freq)
                .sort((a, b) => freq[b] - freq[a])
                .slice(0, 18),
            lower = resume.toLowerCase(),
            matched = keys.filter((k) => lower.includes(k)),
            missing = keys.filter((k) => !lower.includes(k)),
            sections = ['education', 'skills', 'experience', 'project'].filter((s) =>
                lower.includes(s),
            ),
            metrics = /\b\d+(?:%|x|\+)?\b/.test(resume);
        const score = Math.min(
            100,
            Math.round(
                (matched.length / Math.max(1, keys.length)) * 60 +
                    sections.length * 8 +
                    (metrics ? 8 : 0),
            ),
        );
        show(
            `${score}/100 ATS score`,
            `<p><b>Matched keywords:</b> ${esc(matched.join(', ') || 'None')}</p><p><b>Consider adding:</b> ${esc(missing.slice(0, 10).join(', ') || 'Strong match')}</p><p><b>Sections found:</b> ${sections.length}/4 · Measurable outcomes: ${metrics ? 'Yes' : 'Add numbers'}</p>`,
            `${score}/100. Missing: ${missing.join(', ')}`,
        );
    };
    const cleanText = (text, mode) => {
        let s = text
            .replace(/\s+/g, ' ')
            .trim()
            .replace(/\s+([,.!?;:])/g, '$1')
            .replace(/([.!?])\s*([a-z])/g, (m, p, c) => p + ' ' + c.toUpperCase())
            .replace(/\bi\b/g, 'I')
            .replace(/\bdoesnt\b/gi, "doesn't")
            .replace(/\bcant\b/gi, "can't")
            .replace(/\bwont\b/gi, "won't")
            .replace(/\btheir is\b/gi, 'there is')
            .replace(/\balot\b/gi, 'a lot');
        if (mode !== 'grammar') {
            const swaps = {
                important: 'essential',
                help: 'support',
                show: 'demonstrate',
                use: 'apply',
                make: 'create',
                good: 'effective',
                many: 'numerous',
                because: 'since',
                also: 'additionally',
                but: 'however',
            };
            s = s
                .split(/(\W+)/)
                .map((w) => swaps[w.toLowerCase()] || w)
                .join('');
            if (mode === 'rewrite') {
                const sentences = s.split(/(?<=[.!?])\s+/);
                s = sentences
                    .map((x, i) => (i % 2 ? x.replace(/^([^,]{3,30}),\s*/, '') : x))
                    .join(' ');
            }
        }
        return s.charAt(0).toUpperCase() + s.slice(1);
    };
    const writing = (mode) => {
        const input = val('writing-input');
        if (input.length < 10)
            return show('Add more text', '<p>Enter at least one complete sentence.</p>');
        const out = cleanText(input, mode);
        show(
            mode === 'grammar'
                ? 'Corrected text'
                : mode === 'rewrite'
                  ? 'Rewritten draft'
                  : 'Paraphrased text',
            `<pre class="generated-text">${esc(out)}</pre><small>Review factual meaning and cite original sources. Rewriting does not certify plagiarism-free status.</small>`,
            out,
        );
    };
    const citation = () => {
        const author = val('citation-author') || 'Unknown author',
            title = val('citation-title') || 'Untitled',
            publisher = val('citation-publisher') || '',
            year = val('citation-year') || 'n.d.',
            url = val('citation-url'),
            style = val('citation-style');
        let out =
            style === 'APA'
                ? `${author}. (${year}). ${title}. ${publisher}. ${url}`
                : style === 'MLA'
                  ? `${author}. “${title}.” ${publisher}, ${year}, ${url}.`
                  : `${author}, “${title},” ${publisher}, ${year}. [Online]. Available: ${url}`;
        show(`${style} citation`, `<pre class="generated-text">${esc(out)}</pre>`, out);
    };
    const trackerKey = `enoughedu-${app.dataset.user || 'anonymous'}-${slug}`;
    const loadItems = () => {
        try {
            const stored = JSON.parse(localStorage.getItem(trackerKey) || '[]');
            return Array.isArray(stored) ? stored : [];
        } catch {
            return [];
        }
    };
    const renderItems = () => {
        const box = document.getElementById('tracker-items');
        if (!box) return;
        const items = loadItems();
        box.innerHTML = items.length
            ? items
                  .map(
                      (x, i) =>
                          `<label class="tracker-item"><input type="checkbox" data-check="${i}" ${x.done ? 'checked' : ''}><span><b>${esc(x.title)}</b><small>${esc(x.date || 'No date')} · ${esc(x.priority)}</small></span><button type="button" data-delete="${i}" aria-label="Delete">×</button></label>`,
                  )
                  .join('')
            : '<p class="muted">No items yet.</p>';
        box.querySelectorAll('[data-check]').forEach(
            (el) =>
                (el.onchange = () => {
                    const a = loadItems();
                    a[el.dataset.check].done = el.checked;
                    localStorage.setItem(trackerKey, JSON.stringify(a));
                    renderItems();
                }),
        );
        box.querySelectorAll('[data-delete]').forEach(
            (el) =>
                (el.onclick = () => {
                    const a = loadItems();
                    a.splice(el.dataset.delete, 1);
                    localStorage.setItem(trackerKey, JSON.stringify(a));
                    renderItems();
                }),
        );
    };
    const tracker = () => {
        const title = val('tracker-title');
        if (!title) return show('Add a task', '<p>Enter a task or milestone first.</p>');
        const items = loadItems();
        items.push({
            title,
            date: val('tracker-date'),
            priority: val('tracker-priority'),
            done: false,
        });
        localStorage.setItem(trackerKey, JSON.stringify(items));
        document.getElementById('tracker-title').value = '';
        renderItems();
        show(
            'Saved locally',
            `<p>${items.length} item${items.length === 1 ? '' : 's'} in this tracker.</p>`,
        );
    };
    let timer = null,
        seconds = 1500,
        running = false;
    const drawTimer = () => {
        const d = document.getElementById('timer-display');
        if (d)
            d.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    };
    const pomodoro = () => {
        if (running) {
            clearInterval(timer);
            running = false;
            run.textContent = 'Resume timer';
            return;
        }
        if (seconds <= 0) seconds = Math.max(1, num('focus-minutes')) * 60;
        running = true;
        run.textContent = 'Pause timer';
        timer = setInterval(() => {
            seconds--;
            drawTimer();
            if (seconds <= 0) {
                clearInterval(timer);
                running = false;
                run.textContent = 'Start break';
                seconds = Math.max(1, num('break-minutes')) * 60;
                drawTimer();
            }
        }, 1000);
    };
    let countdownTimer = null;
    const countdown = () => {
        const date = new Date(val('exam-date'));
        if (Number.isNaN(date.getTime()))
            return show('Choose the exam date', '<p>Select a valid future date and time.</p>');
        const draw = () => {
            let diff = date - Date.now();
            if (diff <= 0) {
                document.getElementById('countdown-grid').innerHTML =
                    '<strong>The exam time has arrived.</strong>';
                clearInterval(countdownTimer);
                return;
            }
            const days = Math.floor(diff / 864e5);
            diff %= 864e5;
            const h = Math.floor(diff / 36e5);
            diff %= 36e5;
            const m = Math.floor(diff / 6e4);
            const s = Math.floor((diff % 6e4) / 1000);
            document.getElementById('countdown-grid').innerHTML =
                `<span><b>${days}</b>days</span><span><b>${h}</b>hours</span><span><b>${m}</b>minutes</span><span><b>${s}</b>seconds</span>`;
        };
        draw();
        clearInterval(countdownTimer);
        countdownTimer = setInterval(draw, 1000);
        show(val('exam-name') || 'Exam countdown', '<p>Your live countdown is running.</p>');
    };
    const units = window.ENOUGHEDU_UNITS || {};
    const fillUnits = () => {
        const category = document.getElementById('unit-category');
        if (!category.options.length)
            category.innerHTML = Object.entries(units)
                .map(([key, item]) => `<option value="${esc(key)}">${esc(item.label)}</option>`)
                .join('');
        const group = units[category.value] || Object.values(units)[0],
            entries = Object.entries(group?.units || {});
        ['unit-from', 'unit-to'].forEach(
            (id, i) =>
                (document.getElementById(id).innerHTML = entries
                    .map(
                        ([key, item], n) =>
                            `<option value="${esc(key)}" ${n === i ? 'selected' : ''}>${esc(item.label)}</option>`,
                    )
                    .join('')),
        );
        const total = Object.values(units).reduce(
                (sum, item) => sum + Object.keys(item.units).length,
                0,
            ),
            count = document.getElementById('unit-library-count');
        if (count)
            count.textContent = `${Object.keys(units).length} quantities · ${total} units available`;
    };
    const convertUnits = () => {
        const group = units[val('unit-category')],
            v = num('unit-value'),
            from = group?.units[val('unit-from')],
            to = group?.units[val('unit-to')];
        if (!from || !to || !Number.isFinite(v))
            return show('Check the values', '<p>Choose valid units and enter a number.</p>');
        const base = (v + from.offset) * from.factor,
            out = base / to.factor - to.offset,
            display = Number(out.toPrecision(12)),
            fromLabel = from.label,
            toLabel = to.label;
        show(
            `${display} ${toLabel}`,
            `<p>${v} ${esc(fromLabel)} equals <b>${display} ${esc(toLabel)}</b>.</p>`,
            `${v} ${fromLabel} = ${display} ${toLabel}`,
        );
    };
    const formulaData = window.ENOUGHEDU_FORMULAS || [];
    const formulas = () => {
        if (!window.EnoughEduTools.formulasAllowed) {
            document.getElementById('formula-results').textContent =
                'Choose a discipline or search term, then click Calculate / Generate to view your results.';
            return;
        }
        const q = val('formula-search').toLowerCase(),
            category = val('formula-category'),
            rows = formulaData.filter(
                (x) =>
                    (!category || x.category === category) &&
                    `${x.name} ${x.formula} ${x.keywords} ${x.variables}`.toLowerCase().includes(q),
            ),
            visible = rows.slice(0, 80);
        document.getElementById('formula-results').innerHTML =
            visible
                .map(
                    (x) =>
                        `<article class="formula-card"><small>${esc(x.category)}</small><b>${esc(x.name)}</b><code>${esc(x.formula)}</code><span>${esc(x.variables || '')}</span></article>`,
                )
                .join('') ||
            '<p>No matching formula. Try a symbol, law name, variable or discipline.</p>';
        document.getElementById('formula-count').textContent =
            `${rows.length} matching formula${rows.length === 1 ? '' : 's'}${rows.length > 80 ? ' · showing first 80' : ''}`;
    };
    const scientific = () => {
        let x = val('scientific-expression').toLowerCase();
        if (
            !/^[0-9+\-*/().,^\s_a-z]+$/.test(x) ||
            /[a-z_]/.test(x.replace(/\b(sin|cos|tan|sqrt|log|ln|abs|pi|e)\b/g, ''))
        )
            return show(
                'Invalid expression',
                '<p>Use numbers and supported mathematical functions only.</p>',
            );
        x = x
            .replace(/\^/g, '**')
            .replace(/\bpi\b/g, 'Math.PI')
            .replace(/\be\b/g, 'Math.E')
            .replace(/\bsqrt\b/g, 'Math.sqrt')
            .replace(/\blog\b/g, 'Math.log10')
            .replace(/\bln\b/g, 'Math.log')
            .replace(/\babs\b/g, 'Math.abs')
            .replace(
                /\b(sin|cos|tan)\s*\(([^()]*)\)/g,
                (_, fn, arg) => `Math.${fn}((${arg})*Math.PI/180)`,
            );
        try {
            const out = Function(`"use strict";return (${x})`)();
            if (!Number.isFinite(out)) throw 0;
            show(
                String(Number(out.toPrecision(12))),
                '<p>Calculated successfully.</p>',
                String(out),
            );
        } catch {
            show(
                'Could not calculate',
                '<p>Check brackets and use supported functions: sin, cos, tan, sqrt, log, ln and abs.</p>',
            );
        }
    };
    const gpaPlan = () => {
        const c = num('plan-current'),
            done = num('plan-completed'),
            target = num('plan-target'),
            next = num('plan-next');
        if (c < 0 || c > 10 || target < 0 || target > 10 || done <= 0 || next <= 0)
            return show('Check the values', '<p>CGPA values must be between 0 and 10.</p>');
        const need = (target * (done + next) - c * done) / next;
        show(
            `${need.toFixed(2)} SGPA`,
            `<p>${need > 10 ? 'The target is not achievable in one semester. Consider a longer plan.' : need < 0 ? 'You have already secured this target.' : `Aim for at least <b>${need.toFixed(2)}</b> next semester.`}</p>`,
            `${need.toFixed(2)} required SGPA`,
        );
    };
    const careerPdf = document.getElementById('download-career-pdf');
    careerPdf?.addEventListener('click', async (e) => {
        if (!generatedCareer || !careerProfile || !careerMode) return;
        const endpoints = {
                resume: '/tools/resume-builder/pdf',
                linkedin: '/tools/linkedin-generator/pdf',
                cover: '/tools/cover-letter-generator/pdf',
            },
            labels = { resume: 'Resume', linkedin: 'LinkedIn-Profile', cover: 'Cover-Letter' };
        e.currentTarget.disabled = true;
        e.currentTarget.textContent = 'Preparing PDF…';
        try {
            const form = new URLSearchParams({
                csrf: window.ENOUGHEDU_CSRF || '',
                profile: JSON.stringify(careerProfile),
            });
            form.set(
                careerMode === 'resume' ? 'resume' : 'content',
                JSON.stringify(generatedCareer),
            );
            const response = await fetch(endpoints[careerMode], {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body: form,
            });
            if (!response.ok)
                throw new Error((await response.text()) || 'PDF could not be created.');
            const blob = await response.blob(),
                url = URL.createObjectURL(blob),
                link = document.createElement('a');
            link.href = url;
            link.download = `${(careerProfile.name || 'Student').replace(/[^a-z0-9]+/gi, '-')}-EnoughEdu-${labels[careerMode]}.pdf`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            setTimeout(() => URL.revokeObjectURL(url), 1000);
        } catch (error) {
            show('PDF could not be created', `<p>${esc(error.message)}</p>`);
        } finally {
            e.currentTarget.disabled = false;
            e.currentTarget.textContent = 'Download PDF';
        }
    });
    const handlers = {
        grades,
        percentage,
        attendance,
        gpa,
        marks,
        backlog,
        resume: () => profile('resume'),
        linkedin: () => profile('linkedin'),
        cover: () => profile('cover'),
        ats,
        rewrite: () => writing('rewrite'),
        grammar: () => writing('grammar'),
        paraphrase: () => writing('paraphrase'),
        citation,
        tracker,
        pomodoro,
        countdown,
        units: convertUnits,
        formulas,
        scientific,
        'gpa-plan': gpaPlan,
    };
    run?.addEventListener('click', async (e) => {
        e.preventDefault();
        if (['resume', 'linkedin', 'cover'].includes(type)) {
            await handlers[type]?.();
            return;
        }
        if (await window.EnoughEduTools.authorize()) {
            window.EnoughEduTools.formulasAllowed = true;
            handlers[type]?.();
        }
    });
    reset?.addEventListener('click', () => {
        if (type === 'tracker') {
            localStorage.removeItem(trackerKey);
            renderItems();
        } else location.reload();
    });
    copy?.addEventListener('click', async () => {
        await navigator.clipboard.writeText(result.dataset.plain || result.innerText);
        copy.textContent = 'Copied';
        setTimeout(() => (copy.textContent = 'Copy result'), 1200);
    });
    if (type === 'tracker') renderItems();
    if (type === 'units') {
        fillUnits();
        document.getElementById('unit-category').onchange = fillUnits;
    }
    if (type === 'formulas') {
        const categories = [...new Set(formulaData.map((x) => x.category))].sort(),
            selector = document.getElementById('formula-category'),
            suggestions = document.getElementById('formula-suggestions');
        selector.innerHTML =
            '<option value="">All disciplines</option>' +
            categories.map((x) => `<option>${esc(x)}</option>`).join('');
        suggestions.innerHTML = formulaData
            .map((x) => `<option value="${esc(x.name)}">${esc(x.category)}</option>`)
            .join('');
        document.getElementById('formula-search').addEventListener('input', formulas);
        selector.addEventListener('change', formulas);
        formulas();
    }
    if (type === 'countdown') {
        const d = new Date(Date.now() + 7 * 864e5);
        document.getElementById('exam-date').value = d.toISOString().slice(0, 16);
    }
    drawTimer();
})();
