@extends('layouts.app')

@section('content')
<div class="mb-3">
    <a href="{{ route('config.engineer_mapping') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to categories
    </a>
</div>

<h2 class="mb-4">{{ $category->name }}</h2>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($categoryRoleId === null)
<div class="alert alert-warning">This category has no assign_role_ids. Set it in Manage Categories (e.g. 1 for Software, 2 for Hardware).</div>
@else
<p class="text-muted mb-3">Check engineers for this category (role_id = {{ $categoryRoleId }}). Uncheck to set role_id to null.</p>

<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0">{{ $category->name }} — assign_role_ids: {{ $category->assign_role_ids }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('config.engineer_mapping.store') }}" method="POST">
            @csrf
            <input type="hidden" name="category_id" value="{{ $category->id }}">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">Assign</th>
                            <th>Name</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($engineers as $index => $engineer)
                        @php $checked = (int) $engineer->role_id === (int) $categoryRoleId; @endphp
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="user_ids[]" value="{{ $engineer->id }}"
                                    {{ $checked ? 'checked' : '' }} class="form-check-input">
                            </td>
                            <td>{{ $engineer->name }}</td>
                            <td>{{ $engineer->role_name ?? $engineer->role }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No engineers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($engineers->isNotEmpty())
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            @endif
        </form>
    </div>
</div>
@endif
@endsection