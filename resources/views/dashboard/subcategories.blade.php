@extends('layouts.app')

@section('content')
<div class="container py-5 text-center bg-light">
    <h3 class="text-secondary mb-4">
        Select a subcategory of {{ $category->name }}
    </h3>

    <div class="row justify-content-center">
        @forelse($subCategories as $sub)
        <div class="col-12 col-sm-6 col-md-3 mb-4">
            <a href="{{ route('tickets.create', [
                        'category' => $category->id,
                        'sub_category' => $sub->id
                    ]) }}"
                class="text-decoration-none">
                <div class="card h-100 border-0 category-card">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <h5 class="font-weight-bold mb-2">{{ $sub->name }}</h5>
                        <small class="text-muted">Click to create a ticket</small>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                No subcategories found for this category.
            </div>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary mr-2">
            ⬅ Back to Categories
        </a>
        <a href="{{ route('tickets.index') }}" class="btn btn-outline-primary">
            🏠 Tickets List
        </a>
    </div>
</div>

<style>
    .category-card {
        border-radius: 14px;
        padding: 16px;
        background: #5DADE2;
        /* Light Flat Blue */
        border: none;
        transition: 0.25s ease;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
        filter: brightness(1.03);
    }
</style>
@endsection