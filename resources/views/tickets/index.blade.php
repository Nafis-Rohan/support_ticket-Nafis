@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-light text-grey d-flex justify-content-between align-items-center">
        <h5 class="mb-0">🎫 All Tickets</h5>
        <a href="{{ route('tickets.create') }}" class="btn btn-info btn-sm">
            + Add Ticket
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>SL</th>
                        <th>Branch</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Sub-Category</th>
                        @php $isAdmin = auth()->user()->role === 1; @endphp
                        @if($isAdmin)
                        <th>Priority</th>
                        @endif
                        <th>Status</th>
                        <th>Date</th>
                        <th>Solved By</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $index => $ticket)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $ticket->br_name }}</td>
                            <td>{{ $ticket->subject }}</td>

                            {{-- Category and Sub-Category--}}
                            <td>{{ $ticket->category_name ?? 'N/A' }}</td>
                            <td>{{ $ticket->sub_category_name ?? 'N/A' }}</td>

                            @php $isAdmin = auth()->user()->role === 1; @endphp
                            @if($isAdmin)
                            {{-- Priority --}}
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
                            @endif

                            {{-- Status --}}
                            <td class="text-center">
                                @if ($ticket->status == 0)
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif ($ticket->status == 1)
                                    <span class="badge bg-info">Processing</span>
                                @else
                                    <span class="badge bg-success">Solved</span>
                                @endif
                            </td>

                            {{-- Created Date --}}
                            <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y') }}</td>

                            {{-- Solved By --}}
                            <td>{{ $ticket->solved_by_name ?? '—' }}</td>
                            
                            <td>
                                {{ $ticket->assigned_to_name ?? 'Unassigned' }}
                            </td>


                            {{-- Actions --}}
                            <td class="text-center">
                                <a href="{{ route('tickets.show', $ticket->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
