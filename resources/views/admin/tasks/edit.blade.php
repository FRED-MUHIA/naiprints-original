@extends('admin.layout')

@section('title', 'Edit Task')

@section('content')
    <form method="POST" action="{{ route('admin.tasks.update', $task) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.tasks._form', ['task' => $task])

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Update Task</button>
            <a href="{{ route('admin.tasks.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
