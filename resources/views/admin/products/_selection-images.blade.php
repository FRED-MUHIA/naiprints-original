@php
    $selectionImages = isset($product)
        ? $product->selectionImages->values()
        : collect();
    $selectionCategoryIds = collect($selectedCategoryIds ?? $oldCategoryIds ?? [])
        ->push(old('category_id', $primaryCategoryId ?? $oldPrimaryCategory ?? null))
        ->filter(fn ($categoryId) => is_numeric($categoryId))
        ->map(fn ($categoryId) => (int) $categoryId)
        ->unique();
    $isMaasaiSelectionCategory = $categories
        ->whereIn('id', $selectionCategoryIds->all())
        ->contains(function ($category) {
            $categoryText = strtolower(trim(($category->slug ?? '') . ' ' . ($category->name ?? '')));
            return str_contains($categoryText, 'maasai');
        });
    $newSelectionRows = old('new_selection_images', []);
@endphp

<div id="selection-images"
     class="rounded-2xl border border-slate-200 bg-slate-50 p-4 {{ $isMaasaiSelectionCategory ? '' : 'hidden' }}"
     data-selection-image-preferences>
    <h2 class="text-lg font-semibold">Selection images</h2>
    <p class="mt-2 text-sm text-slate-500">Upload visual choices customers can select on the product page before ordering.</p>

    @if ($selectionImages->isNotEmpty())
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($selectionImages as $image)
                <div class="rounded-2xl border border-slate-200 p-3">
                    <div class="product-image-gallery overflow-hidden rounded-xl">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}" alt="{{ $image->label }}" class="h-full w-full object-cover">
                    </div>
                    <div class="mt-3 grid gap-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Label</label>
                        <input type="text" name="selection_images[{{ $image->id }}][label]" value="{{ old('selection_images.' . $image->id . '.label', $image->label) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">

                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Position</label>
                        <input type="number" min="0" name="selection_images[{{ $image->id }}][position]" value="{{ old('selection_images.' . $image->id . '.position', $image->position) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">

                        <label class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                            <input type="checkbox" name="selection_images[{{ $image->id }}][remove]" value="1" class="h-4 w-4 rounded border-slate-300">
                            Remove this choice
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-5 grid gap-3">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Add choices</p>
        @for ($selectionIndex = 0; $selectionIndex < 6; $selectionIndex++)
            @php
                $selectionRow = $newSelectionRows[$selectionIndex] ?? [];
            @endphp
            <div class="grid gap-3 rounded-2xl border border-slate-200 p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)]">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Choice label</label>
                    <input type="text" name="new_selection_images[{{ $selectionIndex }}][label]" value="{{ $selectionRow['label'] ?? '' }}" placeholder="e.g. Red shuka" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Choice image</label>
                    <input type="file" name="new_selection_images[{{ $selectionIndex }}][image]" accept="image/*" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                </div>
            </div>
        @endfor
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const initSelectionImagePreferences = () => {
                const card = document.querySelector('[data-selection-image-preferences]');
                const primaryCategory = document.querySelector('[data-primary-category-select]');
                const categorySelects = Array.from(document.querySelectorAll('[data-product-category-select]'));
                if (!card || !primaryCategory) {
                    return;
                }

                const selectHasMaasai = (select) =>
                    Array.from(select.selectedOptions || []).some((option) => option.dataset?.isMaasai === '1');

                const updateCard = () => {
                    const selected = primaryCategory.options[primaryCategory.selectedIndex];
                    const hasMaasai = selected?.dataset?.isMaasai === '1'
                        || categorySelects.some(selectHasMaasai);
                    card.classList.toggle('hidden', !hasMaasai);
                };

                primaryCategory.addEventListener('change', updateCard);
                categorySelects.forEach((select) => select.addEventListener('change', updateCard));
                updateCard();
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSelectionImagePreferences);
            } else {
                initSelectionImagePreferences();
            }
        })();
    </script>
@endpush
