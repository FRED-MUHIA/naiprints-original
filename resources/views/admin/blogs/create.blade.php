@extends('admin.layout')

@section('title', 'Create Blog Post')

@section('content')
    <form method="POST" action="{{ route('admin.blogs.store') }}" class="space-y-8" enctype="multipart/form-data">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Post Details</h2>
                    <div class="mt-4 grid gap-5">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Slug (optional)</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-generated-from-title" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Category</label>
                            <select name="blog_category_id" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="">Uncategorized</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('blog_category_id') === (string) $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Excerpt</label>
                            <textarea name="excerpt" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">{{ old('excerpt') }}</textarea>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Content</label>
                            <div class="mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-3 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" data-editor-action="add-media" class="admin-btn-outline !rounded-xl !px-3 !py-1.5 !text-xs">
                                            Add Media
                                        </button>
                                        <button type="button" data-editor-action="add-form" class="admin-btn-outline !rounded-xl !px-3 !py-1.5 !text-xs">
                                            Add Form
                                        </button>
                                        <button type="button" data-editor-action="add-contact-form" class="admin-btn-outline !rounded-xl !px-3 !py-1.5 !text-xs">
                                            Add Contact Form
                                        </button>
                                    </div>
                                    <div class="inline-flex overflow-hidden rounded-xl border border-slate-300">
                                        <button type="button" data-editor-mode="visual" class="bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">
                                            Visual
                                        </button>
                                        <button type="button" data-editor-mode="code" class="border-l border-slate-300 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                            Code
                                        </button>
                                    </div>
                                </div>
                                <textarea id="blog-content-editor" name="content" rows="16" class="w-full px-4 py-3 text-sm leading-7 focus:outline-none" required>{{ old('content') }}</textarea>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Use rich formatting, links, media, and tables. Switch to Code for raw HTML editing.</p>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-6 py-6">
                        <p class="text-2xl font-semibold uppercase tracking-[0.32em] text-slate-900">SEO READY</p>
                        <p class="mt-4 text-sm leading-relaxed text-slate-700">
                            Each post supports custom meta title, meta description, category organization, and clean slug URLs for better search visibility.
                        </p>
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold">SEO Meta</h2>
                    <div class="mt-4 grid gap-5">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Meta title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Meta description</label>
                            <input type="text" name="meta_description" value="{{ old('meta_description') }}" maxlength="255" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Publish</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Status</label>
                            <select name="status" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                                <option value="published" @selected(old('status') === 'published')>Published</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Published at</label>
                            <input type="datetime-local"
                                   name="published_at"
                                   value="{{ old('published_at') }}"
                                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                            <p class="mt-2 text-xs text-slate-500">Leave blank to publish immediately when status is Published.</p>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Cover Image</h2>
                    <div class="mt-4 space-y-3">
                        <input type="file" name="cover_image" accept="image/*" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        <p class="text-xs text-slate-500">Recommended ratio: 16:10. Max size: 2MB.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="admin-btn-primary">Save blog post</button>
            <a href="{{ route('admin.blogs.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        (function () {
            const textarea = document.getElementById('blog-content-editor');
            if (!textarea || typeof window.tinymce === 'undefined') {
                return;
            }

            const visualButton = document.querySelector('[data-editor-mode="visual"]');
            const codeButton = document.querySelector('[data-editor-mode="code"]');
            const addMediaButton = document.querySelector('[data-editor-action="add-media"]');
            const addFormButton = document.querySelector('[data-editor-action="add-form"]');
            const addContactFormButton = document.querySelector('[data-editor-action="add-contact-form"]');
            const form = textarea.closest('form');
            let mode = 'visual';
            let editorInstance = null;

            const escapeHtml = (value) => String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const insertIntoTextarea = (value) => {
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                const current = textarea.value;
                textarea.value = current.slice(0, start) + value + current.slice(end);
                const cursor = start + value.length;
                textarea.selectionStart = cursor;
                textarea.selectionEnd = cursor;
                textarea.focus();
            };

            const insertContent = (value) => {
                if (mode === 'visual' && editorInstance) {
                    editorInstance.insertContent(value);
                    return;
                }
                insertIntoTextarea(value);
            };

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

            window.tinymce.init({
                target: textarea,
                menubar: false,
                branding: false,
                promotion: false,
                height: 520,
                plugins: 'advlist autolink lists link image media table code fullscreen wordcount',
                toolbar: 'blocks | bold italic underline strikethrough | bullist numlist blockquote | alignleft aligncenter alignright | link image media table | undo redo | code fullscreen',
                block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
                content_style: 'body { font-family: Nunito Sans, sans-serif; font-size: 15px; line-height: 1.7; }',
                setup(editor) {
                    editorInstance = editor;
                    editor.on('init', () => {
                        textarea.classList.add('hidden');
                        mode = 'visual';
                        syncModeButtons();
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
                    insertContent('<img src="' + safeUrl + '" alt="' + escapeHtml(altText) + '" />');
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
        })();
    </script>
@endpush
