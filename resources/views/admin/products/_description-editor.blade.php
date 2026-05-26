@once
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.9/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            (function () {
                const editors = document.querySelectorAll('[data-product-description-editor]');
                if (!editors.length || typeof window.tinymce === 'undefined') {
                    return;
                }

                editors.forEach((editorRoot, index) => {
                    const textarea = editorRoot.querySelector('[data-product-description-textarea]');
                    const visualButton = editorRoot.querySelector('[data-editor-mode="visual"]');
                    const codeButton = editorRoot.querySelector('[data-editor-mode="code"]');
                    const form = editorRoot.closest('form');

                    if (!textarea) {
                        return;
                    }

                    if (!textarea.id) {
                        textarea.id = 'product-description-editor-' + (index + 1);
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
                        editorInstance.getContainer()?.classList.add('hidden');
                        textarea.classList.remove('hidden');
                        mode = 'code';
                        syncModeButtons();
                    };

                    const switchToVisual = () => {
                        if (!editorInstance) {
                            return;
                        }

                        editorInstance.setContent(textarea.value || '');
                        editorInstance.getContainer()?.classList.remove('hidden');
                        textarea.classList.add('hidden');
                        mode = 'visual';
                        syncModeButtons();
                    };

                    window.tinymce.init({
                        target: textarea,
                        menubar: false,
                        branding: false,
                        statusbar: true,
                        height: 360,
                        convert_urls: false,
                        entity_encoding: 'raw',
                        plugins: 'advlist autolink lists link table code fullscreen paste',
                        toolbar: 'blocks | bold italic underline strikethrough | bullist numlist blockquote | alignleft aligncenter alignright | link table | removeformat | undo redo | code fullscreen',
                        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
                        content_style: 'body { font-family: Nunito Sans, sans-serif; font-size: 15px; line-height: 1.7; color: #334155; } ul, ol { padding-left: 1.5rem; }',
                        setup(editor) {
                            editorInstance = editor;

                            editor.on('init', () => {
                                mode = 'visual';
                                textarea.classList.add('hidden');
                                syncModeButtons();
                            });

                            editor.on('change input undo redo', () => {
                                editor.save();
                            });
                        },
                    });

                    visualButton?.addEventListener('click', switchToVisual);
                    codeButton?.addEventListener('click', switchToCode);

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
