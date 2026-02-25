@extends('layouts.app')

@section('content')
<h2 class="mb-4">Edit Sub Category</h2>

<div class="card p-3">
    <form action="{{ route('config.sub_categories.update', $subCategory->id) }}" method="POST">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Sub category name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $subCategory->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Under category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">— Select category —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $subCategory->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('config.sub_categories') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
