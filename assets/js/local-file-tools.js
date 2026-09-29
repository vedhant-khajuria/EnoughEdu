(() => {
    'use strict';
    const app = document.querySelector('.tool-workspace');
    if (!app) return;
    const type = app.dataset.type;
    const supported = new Set([
        'image-converter',
        'image-resizer',
        'image-pdf',
        'pdf-merge',
        'pdf-split',
    ]);
    if (!supported.has(type)) return;

    const input = document.getElementById('local-files');
    const run = document.getElementById('tool-run');
    const reset = document.getElementById('tool-reset');
    const result = document.getElementById('tool-result');
    const copy = document.getElementById('copy-result');
    const list = document.getElementById('selected-file-list');
    const maxFileBytes = 40 * 1024 * 1024;
    const maxTotalBytes = 120 * 1024 * 1024;
    const maxFiles = 30;
    let files = [];
    let imageRatio = 0;
    let prepared = null,
        processing = false;

    const esc = (value) =>
        String(value).replace(
            /[&<>"']/g,
            (char) =>
                ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char],
        );
    const bytes = (value) =>
        value < 1024
            ? `${value} B`
            : value < 1048576
              ? `${(value / 1024).toFixed(1)} KB`
              : `${(value / 1048576).toFixed(1)} MB`;
    const safeBase = (name) =>
        (
            name
                .replace(/\.[^.]+$/, '')
                .replace(/[^a-z0-9_-]+/gi, '-')
                .replace(/^-+|-+$/g, '') || 'enoughedu-file'
        ).slice(0, 90);
    const show = (title, message, kind = '') => {
        result.innerHTML = `<div class="local-result ${kind}"><h2>${esc(title)}</h2><p>${esc(message)}</p></div>`;
    };
    const busy = (state) => {
        processing = state;
        run.disabled = state;
        input.disabled = state;
        reset.disabled = state;
        app.querySelectorAll('input,select').forEach((el) => (el.disabled = state));
        run.textContent = state ? 'Processing in your browser…' : 'Process files';
    };
    // Keep the completed file in memory. Only the Download click starts access checking.
    const download = (data, name, mime) => {
        const blob = data instanceof Blob ? data : new Blob([data], { type: mime });
        prepared = { blob, name };
        return blob.size;
    };
    const showDownload = () => {
        if (!prepared) return;
        const saved = prepared,
            button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-primary';
        button.textContent = 'Download file';
        const note = document.createElement('p');
        note.className = 'muted';
        note.textContent =
            'Your file is ready on this device. Premium access is checked when you download. Keep this tab open while signing in or paying.';
        result.append(note, button);
        button.addEventListener('click', async () => {
            if (processing || prepared !== saved) return;
            button.disabled = true;
            try {
                if (!(await window.EnoughEduTools.authorize())) return;
                if (prepared !== saved) return;
                const url = URL.createObjectURL(saved.blob),
                    link = document.createElement('a');
                link.href = url;
                link.download = saved.name;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(url), 30000);
                button.textContent = 'Download again';
            } catch (error) {
                note.textContent = error.message || 'Could not download. Please try again.';
            } finally {
                button.disabled = false;
            }
        });
    };
    const canvasBlob = (canvas, mime, quality) =>
        new Promise((resolve, reject) =>
            canvas.toBlob(
                (blob) =>
                    blob
                        ? resolve(blob)
                        : reject(new Error('This browser could not create that image format.')),
                mime,
                quality,
            ),
        );
    const readImage = (file) =>
        new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file),
                image = new Image();
            image.onload = () => {
                URL.revokeObjectURL(url);
                resolve(image);
            };
            image.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error(`${file.name} is not a readable image.`));
            };
            image.src = url;
        });
    const assertImageDimensions = (image) => {
        if (
            image.naturalWidth < 1 ||
            image.naturalHeight < 1 ||
            image.naturalWidth > 12000 ||
            image.naturalHeight > 12000 ||
            image.naturalWidth * image.naturalHeight > 80000000
        )
            throw new Error(
                'Images must be no larger than 12,000 pixels per side or 80 megapixels.',
            );
    };
    const renderList = () => {
        if (!list) return;
        list.innerHTML = files.length
            ? `<b>${files.length} selected file${files.length === 1 ? '' : 's'}</b>` +
              files
                  .map(
                      (file, index) =>
                          `<span><i>${index + 1}</i><span>${esc(file.name)}<small>${bytes(file.size)}</small></span></span>`,
                  )
                  .join('')
            : '';
    };
    const validateSelection = (selected) => {
        if (!selected.length) throw new Error('Choose at least one file.');
        if (selected.length > maxFiles)
            throw new Error(`Choose no more than ${maxFiles} files at once.`);
        if (selected.some((file) => file.size > maxFileBytes))
            throw new Error('Each file must be 40 MB or smaller.');
        if (selected.reduce((sum, file) => sum + file.size, 0) > maxTotalBytes)
            throw new Error('The selected files must total 120 MB or less.');
    };
    const outputDetails = () => {
        const mime = document.getElementById('output-format')?.value || 'image/png',
            quality = Math.max(
                0.1,
                Math.min(1, Number(document.getElementById('image-quality')?.value || 90) / 100),
            ),
            extension = { 'image/png': 'png', 'image/jpeg': 'jpg', 'image/webp': 'webp' }[mime];
        return { mime, quality, extension };
    };

    const convertImage = async (resize) => {
        validateSelection(files);
        const file = files[0];
        if (!file.type.startsWith('image/')) throw new Error('Choose a supported image file.');
        const image = await readImage(file);
        assertImageDimensions(image);
        const originalWidth = image.naturalWidth,
            originalHeight = image.naturalHeight;
        let width = originalWidth,
            height = originalHeight;
        if (resize) {
            width = Math.round(
                Number(document.getElementById('image-width').value) || originalWidth,
            );
            height = Math.round(
                Number(document.getElementById('image-height').value) || originalHeight,
            );
            if (width < 1 || height < 1 || width > 12000 || height > 12000)
                throw new Error('Width and height must be between 1 and 12,000 pixels.');
        }
        const { mime, quality, extension } = outputDetails(),
            canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const context = canvas.getContext('2d', { alpha: mime !== 'image/jpeg' });
        if (!context) throw new Error('Canvas processing is unavailable in this browser.');
        if (mime === 'image/jpeg') {
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, width, height);
        }
        context.imageSmoothingEnabled = true;
        context.imageSmoothingQuality = 'high';
        context.drawImage(image, 0, 0, width, height);
        const blob = await canvasBlob(canvas, mime, quality),
            size = download(blob, `${safeBase(file.name)}-${width}x${height}.${extension}`, mime);
        show(
            'Your file is ready',
            `${width} × ${height} ${extension.toUpperCase()} · ${bytes(size)}. The source file never left this browser.`,
            'success',
        );
    };

    const imageToPdf = async () => {
        validateSelection(files);
        if (!window.PDFLib)
            throw new Error('The local PDF engine did not load. Refresh and try again.');
        const { PDFDocument } = window.PDFLib,
            documentPdf = await PDFDocument.create(),
            sizeMode = document.getElementById('pdf-page-size').value,
            margin = Number(document.getElementById('pdf-margin').value) || 0,
            sizes = { a4: [595.28, 841.89], letter: [612, 792] };
        for (const file of files) {
            if (!file.type.startsWith('image/')) throw new Error(`${file.name} is not an image.`);
            const image = await readImage(file);
            assertImageDimensions(image);
            const canvas = document.createElement('canvas');
            canvas.width = image.naturalWidth;
            canvas.height = image.naturalHeight;
            const context = canvas.getContext('2d');
            context.drawImage(image, 0, 0);
            const png = await canvasBlob(canvas, 'image/png', 1),
                embedded = await documentPdf.embedPng(await png.arrayBuffer());
            let pageWidth, pageHeight;
            if (sizeMode === 'fit') {
                pageWidth = embedded.width + margin * 2;
                pageHeight = embedded.height + margin * 2;
            } else [pageWidth, pageHeight] = sizes[sizeMode];
            const page = documentPdf.addPage([pageWidth, pageHeight]),
                scale = Math.min(
                    (pageWidth - margin * 2) / embedded.width,
                    (pageHeight - margin * 2) / embedded.height,
                ),
                drawWidth = embedded.width * scale,
                drawHeight = embedded.height * scale;
            page.drawImage(embedded, {
                x: (pageWidth - drawWidth) / 2,
                y: (pageHeight - drawHeight) / 2,
                width: drawWidth,
                height: drawHeight,
            });
        }
        const pdf = await documentPdf.save(),
            size = download(pdf, 'EnoughEdu-images.pdf', 'application/pdf');
        show(
            'Your file is ready',
            `${files.length} image${files.length === 1 ? '' : 's'} became ${files.length} PDF page${files.length === 1 ? '' : 's'} · ${bytes(size)}. Nothing was uploaded.`,
            'success',
        );
    };

    const mergePdfs = async () => {
        validateSelection(files);
        if (files.length < 2) throw new Error('Choose at least two PDF files to merge.');
        if (!window.PDFLib)
            throw new Error('The local PDF engine did not load. Refresh and try again.');
        const { PDFDocument } = window.PDFLib,
            merged = await PDFDocument.create();
        let pageCount = 0;
        for (const file of files) {
            if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf'))
                throw new Error(`${file.name} is not a PDF.`);
            const source = await PDFDocument.load(await file.arrayBuffer());
            pageCount += source.getPageCount();
            if (pageCount > 1000) throw new Error('Merge no more than 1,000 PDF pages at once.');
            const pages = await merged.copyPages(source, source.getPageIndices());
            pages.forEach((page) => merged.addPage(page));
        }
        const pdf = await merged.save(),
            size = download(pdf, 'EnoughEdu-merged.pdf', 'application/pdf');
        show(
            'Your file is ready',
            `${files.length} PDFs and ${pageCount} pages were combined locally · ${bytes(size)}.`,
            'success',
        );
    };

    const parsePages = (value, total) => {
        const pages = [];
        for (const token of value
            .split(',')
            .map((item) => item.trim())
            .filter(Boolean)) {
            const match = token.match(/^(\d+)(?:\s*-\s*(\d+))?$/);
            if (!match) throw new Error(`“${token}” is not a valid page or range.`);
            const start = Number(match[1]),
                end = Number(match[2] || match[1]);
            if (start < 1 || end < start || end > total)
                throw new Error(`Choose pages from 1 to ${total}.`);
            for (let page = start; page <= end; page++)
                if (!pages.includes(page - 1)) pages.push(page - 1);
        }
        if (!pages.length) throw new Error('Enter at least one page number.');
        return pages;
    };
    const splitPdf = async () => {
        validateSelection(files);
        if (!window.PDFLib)
            throw new Error('The local PDF engine did not load. Refresh and try again.');
        const file = files[0];
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf'))
            throw new Error('Choose a PDF file.');
        const { PDFDocument } = window.PDFLib,
            source = await PDFDocument.load(await file.arrayBuffer()),
            indices = parsePages(document.getElementById('pdf-pages').value, source.getPageCount()),
            output = await PDFDocument.create(),
            pages = await output.copyPages(source, indices);
        pages.forEach((page) => output.addPage(page));
        const pdf = await output.save(),
            size = download(pdf, `${safeBase(file.name)}-pages.pdf`, 'application/pdf');
        show(
            'Your file is ready',
            `${indices.length} of ${source.getPageCount()} pages were copied locally · ${bytes(size)}.`,
            'success',
        );
    };

    input?.addEventListener('change', async () => {
        prepared = null;
        show('Files selected', 'Choose your settings, then click Process files.');
        files = Array.from(input.files || []);
        try {
            validateSelection(files);
            renderList();
            if ((type === 'image-resizer' || type === 'image-converter') && files[0]) {
                const image = await readImage(files[0]);
                imageRatio = image.naturalWidth / image.naturalHeight;
                if (type === 'image-resizer') {
                    document.getElementById('image-width').value = image.naturalWidth;
                    document.getElementById('image-height').value = image.naturalHeight;
                }
                show(
                    'File ready',
                    `${files[0].name} · ${image.naturalWidth} × ${image.naturalHeight} pixels.`,
                );
            }
        } catch (error) {
            files = [];
            input.value = '';
            renderList();
            show('Check your files', error.message, 'error');
        }
    });
    document.getElementById('image-quality')?.addEventListener('input', (event) => {
        document.getElementById('quality-value').textContent = `${event.target.value}%`;
    });
    document.getElementById('image-width')?.addEventListener('input', (event) => {
        if (document.getElementById('lock-aspect')?.checked && imageRatio) {
            const width = Number(event.target.value);
            if (width > 0)
                document.getElementById('image-height').value = Math.max(
                    1,
                    Math.round(width / imageRatio),
                );
        }
    });
    document.getElementById('image-height')?.addEventListener('input', (event) => {
        if (document.getElementById('lock-aspect')?.checked && imageRatio) {
            const height = Number(event.target.value);
            if (height > 0)
                document.getElementById('image-width').value = Math.max(
                    1,
                    Math.round(height * imageRatio),
                );
        }
    });
    run?.addEventListener('click', async (event) => {
        event.preventDefault();
        if (processing) return;
        prepared = null;
        busy(true);
        show('Processing files', 'Your files stay in this browser.');
        try {
            if (type === 'image-converter') await convertImage(false);
            else if (type === 'image-resizer') await convertImage(true);
            else if (type === 'image-pdf') await imageToPdf();
            else if (type === 'pdf-merge') await mergePdfs();
            else if (type === 'pdf-split') await splitPdf();
            showDownload();
        } catch (error) {
            prepared = null;
            show(
                'Could not create the file',
                error instanceof Error
                    ? error.message
                    : 'The selected file could not be processed.',
                'error',
            );
        } finally {
            busy(false);
        }
    });
    app.querySelectorAll('input:not([type=file]),select').forEach((el) =>
        el.addEventListener('change', () => {
            if (prepared) {
                prepared = null;
                show(
                    'Settings changed',
                    'Click Process files again to prepare an updated download.',
                );
            }
        }),
    );
    reset?.addEventListener('click', () => location.reload());
    if (copy) copy.hidden = true;
})();
