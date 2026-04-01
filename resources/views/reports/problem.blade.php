@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0">Problem-wise Report</h5>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('reports.problem') }}" class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id" id="category_id" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (int) $selectedCategoryId === (int) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="sub_category_id" class="form-label">Sub Category</label>
                <select name="sub_category_id" id="sub_category_id" class="form-select">
                    <option value="">-- All Sub Categories --</option>
                    @foreach($subCategories as $subCategory)
                        <option value="{{ $subCategory->id }}" {{ (int) $selectedSubCategoryId === (int) $subCategory->id ? 'selected' : '' }}>
                            {{ $subCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        @if($selectedCategoryId && $tickets->count())
            <div class="table-responsive">
                <table id="problemWiseTable" class="table table-bordered table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Ticket ID</th>
                            <th>Branch</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Sub Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Solved By</th>
                            <th>Assigned To</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td>#{{ $ticket->id }}</td>
                                <td>{{ $ticket->branch_name ?? 'N/A' }}</td>
                                <td>{{ $ticket->subject }}</td>
                                <td>{{ $ticket->category_name ?? 'N/A' }}</td>
                                <td>{{ $ticket->sub_category_name ?? 'N/A' }}</td>
                                <td>{{ $ticket->priority_name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    @if ($ticket->status == 0)
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif ($ticket->status == 1)
                                        <span class="badge bg-info text-dark">Processing</span>
                                    @else
                                        <span class="badge bg-success">Solved</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->solved_by_name ?? '—' }}</td>
                                <td>{{ $ticket->assigned_to_name ?? 'Unassigned' }}</td>
                                <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($selectedCategoryId)
            <div class="alert alert-info mb-0">
                No tickets found for selected filters.
            </div>
        @else
            <p class="text-muted mb-0">Select Category/Sub Category and click Search to view report.</p>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        const $category = $('#category_id');
        const $subCategory = $('#sub_category_id');

        $category.on('change', function () {
            const categoryId = $(this).val();
            $subCategory.empty().append('<option value="">-- All Sub Categories --</option>');

            if (!categoryId) {
                return;
            }

            $.get("{{ url('/sub-categories') }}/" + categoryId, function (data) {
                data.forEach(function (item) {
                    $subCategory.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
            });
        });

        if ($('#problemWiseTable').length) {
            $('#problemWiseTable').DataTable({
                pageLength: 25,
                order: [[0, 'desc']]
            });
        }
    });
</script>
@endsection
