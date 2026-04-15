@once
    @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
        <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
        <script>
            (function () {
                const editors = document.querySelectorAll('[data-blog-editor]');
                if (!editors.length) {
                    return;
                }

                const escapeHtml = (value) => String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');

                const insertIntoTextarea = (textarea, value) => {
                    const start = textarea.selectionStart ?? textarea.value.length;
                    const end = textarea.selectionEnd ?? textarea.value.length;
                    textarea.value = textarea.value.slice(0, start) + value + textarea.value.slice(end);
                    const cursor = start + value.length;
                    textarea.selectionStart = cursor;
                    textarea.selectionEnd = cursor;
                    textarea.focus();
                };

                editors.forEach((editorRoot) => {
                    const textarea = editorRoot.querySelector('[data-blog-editor-textarea]');
                    const toolbar = editorRoot.querySelector('[data-blog-editor-toolbar]');
                    const visualArea = editorRoot.querySelector('[data-blog-editor-visual]');
                    const visualButton = editorRoot.querySelector('[data-editor-mode="visual"]');
                    const codeButton = editorRoot.querySelector('[data-editor-mode="code"]');
                    const addMediaButton = editorRoot.querySelector('[data-editor-action="add-media"]');
                    const addFormButton = editorRoot.querySelector('[data-editor-action="add-form"]');
                    const addContactFormButton = editorRoot.querySelector('[data-editor-action="add-contact-form"]');
                    const form = editorRoot.closest('form');

                    if (!textarea) {
                        return;
                    }

                    let mode = 'code';
                    let quill = null;

                    const setModeStyles = () => {
                        if (!visualButton || !codeButton) {
                            return;
                        }
                        visualButton.classList.toggle('bg-white', mode === 'visual');
                        visualButton.classList.toggle('bg-slate-100', mode !== 'visual');
                        codeButton.classList.toggle('bg-white', mode === 'code');
                        codeButton.classList.toggle('bg-slate-100', mode !== 'code');
                    };

                    const toVisual = () => {
                        if (!quill || !toolbar || !visualArea) {
                            return;
                        }
                        quill.clipboard.dangerouslyPasteHTML(textarea.value || '');
                        textarea.classList.add('hidden');
                        toolbar.classList.remove('hidden');
                        visualArea.classList.remove('hidden');
                        mode = 'visual';
                        setModeStyles();
                    };

                    const toCode = () => {
                        if (!quill || !toolbar || !visualArea) {
                            return;
                        }
                        const html = quill.root.innerHTML.trim();
                        textarea.value = html === '<p><br></p>' ? '' : html;
                        textarea.classList.remove('hidden');
                        toolbar.classList.add('hidden');
                        visualArea.classList.add('hidden');
                        mode = 'code';
                        setModeStyles();
                    };

                    const insertContent = (html) => {
                        if (mode === 'visual' && quill) {
                            const range = quill.getSelection(true);
                            const index = range ? range.index : quill.getLength();
                            quill.clipboard.dangerouslyPasteHTML(index, html, 'user');
                            return;
                        }
                        insertIntoTextarea(textarea, html);
                    };

                    if (typeof window.Quill !== 'undefined' && toolbar && visualArea) {
                        quill = new window.Quill(visualArea, {
                            theme: 'snow',
                            modules: {
                                toolbar: toolbar,
                            },
                        });
                        quill.root.innerHTML = textarea.value || '';
                        toVisual();
                    } else {
                        mode = 'code';
                        setModeStyles();
                    }

                    visualButton?.addEventListener('click', toVisual);
                    codeButton?.addEventListener('click', toCode);

                    addMediaButton?.addEventListener('click', () => {
                        const mediaUrl = window.prompt('Enter media URL (image or file):');
                        if (!mediaUrl) {
                            return;
                        }
                        const safeUrl = escapeHtml(mediaUrl.trim());
                        if (mode === 'visual' && quill) {
                            const range = quill.getSelection(true);
                            const index = range ? range.index : quill.getLength();
                            if (/\.(png|jpe?g|gif|webp|svg|avif)$/i.test(safeUrl)) {
                                quill.insertEmbed(index, 'image', safeUrl, 'user');
                            } else {
                                quill.insertText(index, safeUrl, { link: safeUrl }, 'user');
                            }
                            return;
                        }
                        insertIntoTextarea(textarea, safeUrl);
                    });

                    addFormButton?.addEventListener('click', () => {
                        const formId = window.prompt('Enter form ID:', 'your-form-id');
                        if (!formId) {
                            return;
                        }
                        insertContent('<p>[form id="' + escapeHtml(formId.trim()) + '"]</p>');
                    });

                    addContactFormButton?.addEventListener('click', () => {
                        const formId = window.prompt('Enter contact form ID:', 'contact-form');
                        if (!formId) {
                            return;
                        }
                        insertContent('<p>[contact-form id="' + escapeHtml(formId.trim()) + '"]</p>');
                    });

                    form?.addEventListener('submit', () => {
                        if (mode === 'visual') {
                            toCode();
                        }
                    });
                });
            })();
        </script>
    @endpush
@endonce

