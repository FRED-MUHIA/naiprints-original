@extends('admin.layout')

@section('title', 'Assign Task')

@section('content')
    <form method="POST" action="{{ route('admin.tasks.store') }}" class="space-y-6">
        @csrf
        @include('admin.tasks._form', ['task' => $task])

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Save Task</button>
            <a href="{{ route('admin.tasks.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
