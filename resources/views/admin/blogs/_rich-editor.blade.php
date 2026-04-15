@once
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.9/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            (function () {
                const editors = document.querySelectorAll('[data-blog-editor]');
                if (!editors.length || typeof window.tinymce === 'undefined') {
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
                    const current = textarea.value;
                    textarea.value = current.slice(0, start) + value + current.slice(end);
                    const cursor = start + value.length;
                    textarea.selectionStart = cursor;
                    textarea.selectionEnd = cursor;
                    textarea.focus();
                };

                editors.forEach((editorRoot, index) => {
                    const textarea = editorRoot.querySelector('[data-blog-editor-textarea]');
                    const visualButton = editorRoot.querySelector('[data-editor-mode="visual"]');
                    const codeButton = editorRoot.querySelector('[data-editor-mode="code"]');
                    const addMediaButton = editorRoot.querySelector('[data-editor-action="add-media"]');
                    const addFormButton = editorRoot.querySelector('[data-editor-action="add-form"]');
                    const addContactFormButton = editorRoot.querySelector('[data-editor-action="add-contact-form"]');
                    const form = editorRoot.closest('form');
                    const csrfToken = form?.querySelector('input[name="_token"]')?.value || '';

                    if (!textarea) {
                        return;
                    }

                    if (!textarea.id) {
                        textarea.id = 'blog-content-editor-' + (index + 1);
                    }

                    let mode = 'visual';
                    let editorInstance = null;

                    const syncModeButtons = () => {
                        if (!visualButton || !codeButton) {
                            return;
                        }
                        visualButton.classList.toggle('bg-white', mode === 'visual');
                        visualButton.classList.toggle('bg-slate-100', mode !== 'visual');
                        codeButton.classList.toggle('bg-white', mode === 'code');
                        codeButton.classList.toggle('bg-slate-100', mode !== 'code');
                    };

                    const switchToCode = () => {
                        if (!editorInstance) {
                            return;
                        }
                        editorInstance.save();
                        const editorContainer = editorInstance.getContainer();
                        if (editorContainer) {
                            editorContainer.classList.add('hidden');
                        }
                        textarea.classList.remove('hidden');
                        mode = 'code';
                        syncModeButtons();
                    };

                    const switchToVisual = () => {
                        if (!editorInstance) {
                            return;
                        }
                        editorInstance.setContent(textarea.value || '');
                        const editorContainer = editorInstance.getContainer();
                        if (editorContainer) {
                            editorContainer.classList.remove('hidden');
                        }
                        textarea.classList.add('hidden');
                        mode = 'visual';
                        syncModeButtons();
                    };

                    const insertContent = (value) => {
                        if (mode === 'visual' && editorInstance) {
                            editorInstance.insertContent(value);
                            return;
                        }
                        insertIntoTextarea(textarea, value);
                    };

                    const uploadImageBlob = async (blob) => {
                        if (!csrfToken) {
                            throw new Error('Missing CSRF token.');
                        }
                        const formData = new FormData();
                        formData.append('_token', csrfToken);
                        formData.append('file', blob, blob.name || ('pasted-image-' + Date.now() + '.png'));

                        const response = await fetch('{{ route('admin.blogs.editor.upload-image') }}', {
                            method: 'POST',
                            body: formData,
                            credentials: 'same-origin',
                        });
                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok || !payload.location) {
                            throw new Error(payload.message || 'Image upload failed.');
                        }

                        return payload.location;
                    };

                    const normalizePastedHtml = (html) => {
                        const wrapper = document.createElement('div');
                        wrapper.innerHTML = html;
                        wrapper.querySelectorAll('img').forEach((img) => {
                            const attrs = ['src', 'data-src', 'data-lazy-src', 'data-original', 'data-srcset', 'srcset'];
                            let chosen = '';

                            for (const attr of attrs) {
                                let value = (img.getAttribute(attr) || '').trim();
                                if (!value) {
                                    continue;
                                }
                                if (attr.includes('srcset')) {
                                    value = value.split(',')[0]?.trim() || '';
                                    value = value.split(/\s+/)[0] || '';
                                }
                                if (!value) {
                                    continue;
                                }
                                if (attr === 'src') {
                                    const lower = value.toLowerCase();
                                    const isPlaceholder = lower === 'about:blank'
                                        || lower === '#'
                                        || lower.startsWith('data:image/gif;base64,r0lgod');
                                    if (!isPlaceholder) {
                                        chosen = value;
                                        break;
                                    }
                                    if (!chosen) {
                                        chosen = value;
                                    }
                                } else if (!chosen || chosen.toLowerCase().startsWith('data:image/gif;base64,r0lgod')) {
                                    chosen = value;
                                }
                            }

                            if (chosen) {
                                img.setAttribute('src', chosen);
                            }
                        });

                        return wrapper.innerHTML;
                    };

                    window.tinymce.init({
                        target: textarea,
                        menubar: false,
                        branding: false,
                        statusbar: true,
                        height: 520,
                        convert_urls: false,
                        entity_encoding: 'raw',
                        verify_html: false,
                        cleanup: false,
                        paste_as_text: false,
                        paste_data_images: true,
                        paste_webkit_styles: 'all',
                        paste_retain_style_properties: 'all',
                        valid_elements: '*[*]',
                        extended_valid_elements: '*[*]',
                        plugins: 'advlist autolink lists link image media table code fullscreen paste',
                        toolbar: 'blocks | bold italic underline strikethrough | numlist bullist blockquote | alignleft aligncenter alignright | link image media table | removeformat | undo redo | code fullscreen',
                        block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
                        content_style: 'body { font-family: Nunito Sans, sans-serif; font-size: 15px; line-height: 1.7; }',
                        images_upload_handler: function (blobInfo, success, failure) {
                            uploadImageBlob(blobInfo.blob())
                                .then((location) => success(location))
                                .catch((error) => failure(error.message || 'Image upload failed'));
                        },
                        paste_preprocess: function (plugin, args) {
                            args.content = normalizePastedHtml(args.content || '');
                        },
                        setup(editor) {
                            editorInstance = editor;

                            editor.on('init', () => {
                                mode = 'visual';
                                syncModeButtons();
                                textarea.classList.add('hidden');
                            });

                            editor.on('change input undo redo', () => {
                                editor.save();
                            });
                        },
                    });

                    visualButton?.addEventListener('click', switchToVisual);
                    codeButton?.addEventListener('click', switchToCode);

                    addMediaButton?.addEventListener('click', () => {
                        const mediaUrl = window.prompt('Enter media URL (image or file):');
                        if (!mediaUrl) {
                            return;
                        }
                        const safeUrl = escapeHtml(mediaUrl.trim());
                        const isImage = /\.(png|jpe?g|gif|webp|svg|avif)$/i.test(safeUrl);

                        if (isImage) {
                            const altText = window.prompt('Alt text (optional):', '') || '';
                            const payload = new FormData();
                            payload.append('_token', csrfToken);
                            payload.append('url', safeUrl);
                            fetch('{{ route('admin.blogs.editor.upload-image-url') }}', {
                                method: 'POST',
                                body: payload,
                                credentials: 'same-origin',
                            })
                                .then(async (response) => {
                                    const data = await response.json().catch(() => ({}));
                                    if (!response.ok || !data.location) {
                                        throw new Error(data.message || 'Could not localize image URL.');
                                    }
                                    insertContent('<img src="' + escapeHtml(data.location) + '" alt="' + escapeHtml(altText) + '" />');
                                })
                                .catch(() => {
                                    insertContent('<img src="' + safeUrl + '" alt="' + escapeHtml(altText) + '" />');
                                });
                            return;
                        }

                        insertContent('<a href="' + safeUrl + '" target="_blank" rel="noopener noreferrer">' + safeUrl + '</a>');
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
                        if (mode === 'visual' && editorInstance) {
                            editorInstance.save();
                        }
                    });
                });
            })();
        </script>
    @endpush
@endonce
