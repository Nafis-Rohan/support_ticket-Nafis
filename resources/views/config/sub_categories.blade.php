@extends('layouts.app')

@section('content')
<h2 class="mb-4">Manage Sub Categories</h2>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Add new sub category --}}
<div class="card p-3 mb-4">
    <h6 class="mb-3">Add new sub category</h6>
    <form action="{{ route('config.sub_categories.store') }}" method="POST">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-0">Under category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">— Select category —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0">Sub category name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter sub category name" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Add Sub Category</button>
            </div>
        </div>
    </form>
</div>

{{-- Current Sub Categories --}}
<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0">Current Sub Categories</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Sub category</th>
                        <th>Category</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subCategories as $index => $sub)
                    <tr>
                        <td>{{ $subCategories->firstItem() + $index }}</td>
                        <td>{{ $sub->sub_category_name }}</td>
                        <td>{{ $sub->category_name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('config.sub_categories.edit', $sub->id) }}" class="btn btn-warning btn-sm me-1">Edit</a>
                            <a href="{{ route('config.sub_categories.destroy', $sub->id) }}"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this sub category?')">Delete</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No sub categories found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-center py-3">
            {{ $subCategories->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@endsection