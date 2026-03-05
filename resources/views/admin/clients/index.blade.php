@extends('admin.layout')

@section('title', 'Clients')

@section('content')
    <div class="admin-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="admin-search">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search clients..." class="admin-input">
                </div>
                <button type="submit" class="admin-btn-outline">Search</button>
            </form>
            <a href="{{ route('admin.clients.create') }}" class="admin-btn-primary">Add Client</a>
        </div>
    </div>

    <div class="admin-panel overflow-hidden">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Company</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td class="font-semibold text-slate-700">{{ $client->name }}</td>
                        <td>{{ $client->company_name ?? '-' }}</td>
                        <td>{{ $client->phone ?? '-' }}</td>
                        <td>{{ $client->email ?? '-' }}</td>
                        <td>{{ $client->address ?? '-' }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.clients.edit', $client) }}" class="admin-btn-outline">Edit</a>
                                <form method="POST" action="{{ route('admin.clients.destroy', $client) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn-outline" onclick="return confirm('Delete this client?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty">No clients yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $clients->links() }}
@endsection
