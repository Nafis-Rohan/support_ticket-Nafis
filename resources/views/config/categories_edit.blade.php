@extends('layouts.app')

@section('content')
<h2 class="mb-4">Edit Category</h2>

<div class="card p-3">
    <form action="{{ route('categories.update', $category->id) }}" method="POST">
        @csrf
        <div class="row align-items-end">
            <div class="col-md-6">
                <label class="form-label">Category name</label>
                <input type="text" name="category_name" class="form-control" value="{{ old('category_name', $category->name) }}" required>
            </div>
            <div class="col-md-4 mt-2 mt-md-0">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
