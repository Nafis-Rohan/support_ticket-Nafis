@extends('layouts.app')

@section('content')
<h2 class="mb-4">Engineer Mapping</h2>

<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0">Categories</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($categories as $cat)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="{{ route('config.engineer_mapping.category', $cat->id) }}" class="text-decoration-none">
                    <div class="card border shadow-sm h-100 text-center py-4 hover-shadow">
                        <div class="card-body py-3">
                            <h6 class="mb-0 text-dark">{{ $cat->name }}</h6>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @if($categories->isEmpty())
        <p class="text-muted mb-0">No categories found.</p>
        @endif
    </div>
</div>
@endsection
