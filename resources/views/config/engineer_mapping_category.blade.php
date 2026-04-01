@extends('layouts.app')

@section('content')
<div class="mb-3">
    <a href="{{ route('config.engineer_mapping') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to categories
    </a>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
    <h2 class="mb-0">{{ $category->name }}</h2>
    @if($categoryRoleId !== null)
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addEngineerModal">
            <i class="fas fa-plus me-1"></i> Add Engineer
        </button>
    @endif
</div>

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
<div class="mb-3">
    <strong>Assigned Engineers:</strong>
    <div class="table-responsive mt-2">
        <table class="table table-sm table-bordered align-middle mb-0" style="max-width: 700px;">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;" class="text-center">SL</th>
                    <th>Name</th>
                    <th style="width: 140px;">Role</th>
                    <th style="width: 90px;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($assignedEngineers) && $assignedEngineers->isNotEmpty())
                    @foreach($assignedEngineers as $i => $ae)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $ae->name }}</td>
                            <td>{{ $ae->role_name ?? $ae->role }}</td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('config.engineer_mapping.remove_engineer', $category->id) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $ae->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Remove">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">None</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endif

@if($categoryRoleId !== null)
<!-- Add Engineer Modal -->
<div class="modal fade" id="addEngineerModal" tabindex="-1" aria-labelledby="addEngineerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('config.engineer_mapping.add_engineer', $category->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addEngineerModalLabel">Add Engineer to {{ $category->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Engineer</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Select Engineer --</option>
                        @foreach(($availableEngineers ?? collect()) as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->role_name ?? $e->role }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">
                        Only eligible users (Admin/Engineer) are shown.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection