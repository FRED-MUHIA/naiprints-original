@php
    $items = old('items');
    if (!is_array($items)) {
        $items = isset($quotation)
            ? $quotation->items->map(fn ($item) => [
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->toArray()
            : [
                ['name' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0],
            ];
    }
@endphp

<div class="space-y-6" data-invoice-builder data-client-autofill>
    <div class="admin-card space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Client</p>
                <h2 class="mt-2 text-xl font-semibold">Client Details</h2>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Select Existing Client
                <select name="client_id" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-client-select>
                    <option value="">-- Add New Client --</option>
                    @foreach ($clients as $clientOption)
                        <option value="{{ $clientOption->id }}"
                            data-client-name="{{ $clientOption->name }}"
                            data-client-company-name="{{ $clientOption->company_name }}"
                            data-client-phone="{{ $clientOption->phone }}"
                            data-client-email="{{ $clientOption->email }}"
                            data-client-address="{{ $clientOption->address }}"
                            @selected(old('client_id', $quotation->client_id ?? '') == $clientOption->id)>
                            {{ $clientOption->display_name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <div class="text-xs text-slate-500">
                If you do not see the client, fill in the new client details below.
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Client Name
                <input type="text" name="client_new[name]" value="{{ old('client_new.name') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-client-field="name">
            </label>
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Company Name
                <input type="text" name="client_new[company_name]" value="{{ old('client_new.company_name') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-client-field="company_name">
            </label>
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Phone
                <input type="text" name="client_new[phone]" value="{{ old('client_new.phone') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-client-field="phone">
            </label>
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Email
                <input type="email" name="client_new[email]" value="{{ old('client_new.email') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-client-field="email">
            </label>
            <label class="space-y-2 text-sm font-semibold text-slate-600 md:col-span-2">
                Address
                <input type="text" name="client_new[address]" value="{{ old('client_new.address') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-client-field="address">
            </label>
        </div>
    </div>

    <div class="admin-card space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Quotation</p>
                <h2 class="mt-2 text-xl font-semibold">Quotation Details</h2>
            </div>
            <div class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">
                {{ $quotation->number ?? $nextNumber ?? 'Auto-generated' }}
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-4">
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Quotation Date
                <input type="date" name="issue_date" value="{{ old('issue_date', optional($quotation->issue_date ?? now())->format('Y-m-d')) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
            </label>
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Valid Until
                <input type="date" name="valid_until" value="{{ old('valid_until', optional($quotation->valid_until ?? now()->addDays(7))->format('Y-m-d')) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
            </label>
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Status
                <select name="status" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
                    <option value="draft" @selected(old('status', $quotation->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="sent" @selected(old('status', $quotation->status ?? 'draft') === 'sent')>Sent</option>
                    <option value="accepted" @selected(old('status', $quotation->status ?? 'draft') === 'accepted')>Accepted</option>
                    <option value="rejected" @selected(old('status', $quotation->status ?? 'draft') === 'rejected')>Rejected</option>
                </select>
            </label>
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Currency
                <input type="text" value="KES" class="admin-input border border-slate-200 rounded-xl px-4 py-2 bg-slate-50" disabled>
            </label>
        </div>
    </div>

    <div class="admin-card space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Items</p>
                <h2 class="mt-2 text-xl font-semibold">Quotation Items</h2>
            </div>
            <button type="button" class="admin-btn-outline" data-invoice-add>Add Item</button>
        </div>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 24%;">Item / Service</th>
                        <th>Description</th>
                        <th style="width: 12%;">Qty</th>
                        <th style="width: 14%;">Unit Price</th>
                        <th style="width: 14%;">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody data-invoice-items>
                    @foreach ($items as $index => $item)
                        <tr data-invoice-row>
                            <td>
                                <input type="text" name="items[{{ $index }}][name]" value="{{ old("items.$index.name", $item['name'] ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full" required>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][description]" value="{{ old("items.$index.description", $item['description'] ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0.01" name="items[{{ $index }}][quantity]" value="{{ old("items.$index.quantity", $item['quantity'] ?? 1) }}" class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full" data-invoice-qty>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" value="{{ old("items.$index.unit_price", $item['unit_price'] ?? 0) }}" class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full" data-invoice-price>
                            </td>
                            <td>
                                <input type="text" readonly class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full bg-slate-50" data-invoice-total>
                            </td>
                            <td class="text-right">
                                <button type="button" class="admin-btn-outline" data-invoice-remove>Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <div class="space-y-4">
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Notes
                <textarea name="notes" rows="4" class="admin-input border border-slate-200 rounded-xl px-4 py-2">{{ old('notes', $quotation->notes ?? '') }}</textarea>
            </label>
            <label class="space-y-2 text-sm font-semibold text-slate-600">
                Terms
                <textarea name="terms" rows="3" class="admin-input border border-slate-200 rounded-xl px-4 py-2">{{ old('terms', $quotation->terms ?? '') }}</textarea>
            </label>
        </div>
        <div class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Subtotal</span>
                <strong data-invoice-subtotal>KES 0.00</strong>
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-slate-500">
                    <input type="checkbox" name="apply_tax" value="1" data-invoice-tax-toggle
                        @checked(old('apply_tax', ($quotation->tax_rate ?? null) !== null))>
                    Apply Tax
                </label>
                <input type="number" step="0.01" min="0" name="tax_rate" value="{{ old('tax_rate', $quotation->tax_rate ?? '') }}"
                    class="admin-input border border-slate-200 rounded-xl px-3 py-1.5 w-24 text-right" data-invoice-tax-rate>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Tax Amount</span>
                <strong data-invoice-tax-amount>KES 0.00</strong>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Discount</span>
                <input type="number" step="0.01" min="0" name="discount_amount" value="{{ old('discount_amount', $quotation->discount_amount ?? 0) }}"
                    class="admin-input border border-slate-200 rounded-xl px-3 py-1.5 w-24 text-right" data-invoice-discount>
            </div>
            <div class="flex items-center justify-between border-t border-slate-200 pt-4">
                <span class="text-sm font-semibold text-slate-600">Grand Total</span>
                <strong class="text-lg text-slate-900" data-invoice-grand-total>KES 0.00</strong>
            </div>
        </div>
    </div>

    <template data-invoice-template>
        <tr data-invoice-row>
            <td>
                <input type="text" name="items[__INDEX__][name]" class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full" required>
            </td>
            <td>
                <input type="text" name="items[__INDEX__][description]" class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full">
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full" data-invoice-qty>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" value="0" class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full" data-invoice-price>
            </td>
            <td>
                <input type="text" readonly class="admin-input border border-slate-200 rounded-xl px-3 py-2 w-full bg-slate-50" data-invoice-total>
            </td>
            <td class="text-right">
                <button type="button" class="admin-btn-outline" data-invoice-remove>Remove</button>
            </td>
        </tr>
    </template>
</div>
