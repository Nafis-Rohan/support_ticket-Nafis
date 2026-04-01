@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">📂 Category Tickets</h5>
    </div>

    <div class="card-body">
        {{-- Category filter (only mapped categories) --}}
        <form method="GET" action="{{ route('tickets.by_category') }}" class="row g-2 mb-3 align-items-end">
            <div class="col-md-4">
                <label for="category_id" class="form-label mb-1 fw-semibold">Category</label>
                <select name="category_id" id="category_id" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (int) $selectedCategoryId === (int) $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary px-4">
                    Search
                </button>
            </div>
        </form>

        @if($selectedCategoryId && $tickets->count())
            <div class="table-responsive">
                <table id="categoryTicketsTable" class="table table-bordered table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th class="d-none">TicketId</th>
                            <th>SL</th>
                            <th>Branch</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Sub-Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Solved By</th>
                            <th>Assigned To</th>
                            @if(auth()->user()->role == 2)
                                <th>Message</th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $index => $ticket)
                            <tr>
                                <td class="d-none">{{ (int) $ticket->id }}</td>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $ticket->br_name }}</td>
                                <td>{{ $ticket->subject }}</td>
                                <td>{{ $ticket->category_name ?? 'N/A' }}</td>
                                <td>{{ $ticket->sub_category_name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    @if($ticket->priority_name == 'High')
                                        <span class="badge bg-success">High</span>
                                    @elseif($ticket->priority_name == 'Medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @elseif($ticket->priority_name == 'Low')
                                        <span class="badge bg-info">Low</span>
                                    @elseif($ticket->priority_name == 'Urgent')
                                        <span class="badge bg-danger">Urgent</span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($ticket->status == 0)
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif ($ticket->status == 1)
                                        <span class="badge bg-info">Processing</span>
                                    @else
                                        <span class="badge bg-success">Solved</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y') }}</td>
                                <td>{{ $ticket->solved_by_name ?? '—' }}</td>
                                <td>{{ $ticket->assigned_to_name ?? 'Unassigned' }}</td>
                                @if(auth()->user()->role == 2)
                                    @php
                                        $note = trim((string) ($ticket->handoff_note ?? ''));
                                        $showNote = ((int) ($ticket->assigned_to ?? 0) === (int) auth()->id()) && $note !== '';
                                    @endphp
                                    <td class="small text-start">
                                        {{ $showNote ? \Illuminate\Support\Str::limit($note, 60) : '—' }}
                                    </td>
                                @endif
                                <td class="text-center">
                                    <a href="{{ route('tickets.show', $ticket->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($selectedCategoryId)
            <p class="text-muted mb-0">No tickets found for this category.</p>
        @else
            <p class="text-muted mb-0">Select a category and click Search to see tickets.</p>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#categoryTicketsTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']], // hidden TicketId column
            columnDefs: [
                { targets: [0], visible: false, searchable: false }
            ]
        });
    });
</script>
@endsection