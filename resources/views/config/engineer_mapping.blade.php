@extends('layouts.app')

@section('content')
<h2 class="mb-4">Engineer Mapping (Sub-category wise)</h2>

<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0">Choose category</h6>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            @foreach(($categories ?? collect()) as $category)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <a
                        href="{{ route('config.engineer_mapping', ['category_id' => $category->id]) }}"
                        class="text-decoration-none"
                    >
                        <div class="card h-100 border shadow-sm {{ (int) ($selectedCategoryId ?? 0) === (int) $category->id ? 'border-primary' : '' }}">
                            <div class="card-body py-3 text-center">
                                <h6 class="mb-0 text-dark">{{ $category->name }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if((int) ($selectedCategoryId ?? 0) === 0)
            <div class="alert alert-info mb-0">
                Please select a category card to view its sub-categories.
            </div>
        @else
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">
            <h6 class="mb-0">
                Sub-categories of: <span class="text-primary">{{ $selectedCategory->name ?? 'Selected Category' }}</span>
            </h6>
            <a href="{{ route('config.engineer_mapping') }}" class="btn btn-outline-secondary btn-sm">Back to all categories</a>
        </div>

        <form method="GET" action="{{ route('config.engineer_mapping') }}" class="row g-2 align-items-end mb-4">
            <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
            <div class="col-12 col-md-6 col-lg-4">
                <label for="search" class="form-label mb-1">Search sub-category</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    placeholder="Type sub-category name..."
                    value="{{ $search ?? '' }}"
                >
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </div>
            @if(!empty($search))
                <div class="col-auto">
                    <a href="{{ route('config.engineer_mapping', ['category_id' => $selectedCategoryId]) }}" class="btn btn-outline-secondary btn-sm">Clear Search</a>
                </div>
            @endif
        </form>

        <div class="row g-3">
            @foreach($subCategories as $cat)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="{{ route('config.engineer_mapping.category', $cat->id) }}" class="text-decoration-none">
                    <div class="card border shadow-sm h-100 text-center py-4 hover-shadow">
                        <div class="card-body py-3">
                            <h6 class="mb-1 text-dark">{{ $cat->name }}</h6>
                            <div class="small text-muted">{{ $cat->category_name ?? 'No category' }}</div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @if($subCategories->isEmpty())
        <p class="text-muted mb-0">No sub-categories found for this category.</p>
        @endif
        @endif
    </div>
</div>
@endsection
