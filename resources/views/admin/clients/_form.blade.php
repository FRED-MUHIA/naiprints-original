<div class="admin-card space-y-5">
    <div class="grid gap-5 md:grid-cols-2">
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Client Name
            <input type="text" name="name" value="{{ old('name', $client->name ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Company Name
            <input type="text" name="company_name" value="{{ old('company_name', $client->company_name ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Phone Number
            <input type="text" name="phone" value="{{ old('phone', $client->phone ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Email
            <input type="email" name="email" value="{{ old('email', $client->email ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
        </label>
    </div>
    <label class="space-y-2 text-sm font-semibold text-slate-600">
        Address
        <input type="text" name="address" value="{{ old('address', $client->address ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
    </label>
    <label class="space-y-2 text-sm font-semibold text-slate-600">
        Notes
        <textarea name="notes" rows="4" class="admin-input border border-slate-200 rounded-xl px-4 py-2">{{ old('notes', $client->notes ?? '') }}</textarea>
    </label>
</div>
