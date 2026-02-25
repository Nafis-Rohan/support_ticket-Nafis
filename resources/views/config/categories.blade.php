@extends('layouts.app')

@section('content')
<h2 class="mb-4">Manage Categories</h2>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Add category (above current categories) -->
<div class="card p-3 mb-4">
    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <div class="row align-items-end">
            <div class="col">
                <input type="text" name="category_name" class="form-control" placeholder="Add new category" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </div>
    </form>
</div>

<!-- Current categories -->
<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0">Current categories</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Category name</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-warning btn-sm me-1">Edit</a>
                            <a href="{{ route('categories.destroy', $category->id) }}"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this category?')">Delete</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No categories yet. Add one above.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection