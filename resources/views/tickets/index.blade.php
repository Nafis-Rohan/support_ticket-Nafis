@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-light text-grey d-flex justify-content-between align-items-center">
        <h5 class="mb-0">🎫 {{ $pageTitle ?? 'All Tickets' }}</h5>
        <a href="{{ route('tickets.create') }}" class="btn btn-info btn-sm">
            + Add Ticket
        </a>
    </div>

    <div class="card-body">
        @php
            $isBranch = (int) auth()->user()->role === 3;
            $isEngineer = (int) auth()->user()->role === 2;
            $isOpenUnsolvedList = request()->routeIs('tickets.open', 'tickets.engineer_open');
            $openFilter = $openTicketFilter ?? 'today';
            $branchFilter = $branchTicketFilter ?? 'solved';
            // DataTables breaks if an "empty" row uses a single colspan cell (column count mismatch).
            // Use empty tbody + language.emptyTable instead.
            $dataTablesEmptyMsg = ($isOpenUnsolvedList && $openFilter === 'today')
                ? 'No tickets issued yet.'
                : 'No tickets found.';
        @endphp

        @if($isBranch && request()->routeIs('tickets.index'))
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="text-muted small fw-semibold me-1">Filter:</span>
            <a href="{{ route('tickets.index', ['status' => 'solved']) }}"
               class="btn btn-sm {{ $branchFilter === 'solved' ? 'btn-primary' : 'btn-outline-primary' }}">
                Solved
            </a>
            <a href="{{ route('tickets.index', ['status' => 'unsolved']) }}"
               class="btn btn-sm {{ $branchFilter === 'unsolved' ? 'btn-primary' : 'btn-outline-primary' }}">
                Unsolved
            </a>
            <a href="{{ route('tickets.index', ['status' => 'all']) }}"
               class="btn btn-sm {{ $branchFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                All
            </a>
            <span class="text-muted small ms-md-2">
                @if($branchFilter === 'solved')
                    Showing solved tickets.
                @elseif($branchFilter === 'unsolved')
                    Showing pending and processing tickets.
                @else
                    Showing all tickets.
                @endif
            </span>
        </div>
        @endif

        @if($isOpenUnsolvedList && in_array(auth()->user()->role, [1, 2]))
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="text-muted small fw-semibold me-1">Filter:</span>
            @if(auth()->user()->role === 1)
                <a href="{{ route('tickets.open', ['filter' => 'all']) }}"
                   class="btn btn-sm {{ $openFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                    All unsolved
                </a>
                <a href="{{ route('tickets.open') }}"
                   class="btn btn-sm {{ $openFilter === 'today' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Today&rsquo;s tickets
                </a>
            @else
                <a href="{{ route('tickets.engineer_open', ['filter' => 'all']) }}"
                   class="btn btn-sm {{ $openFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                    All unsolved
                </a>
                <a href="{{ route('tickets.engineer_open') }}"
                   class="btn btn-sm {{ $openFilter === 'today' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Today&rsquo;s tickets
                </a>
            @endif
            <span class="text-muted small ms-md-2">
                @if($openFilter === 'today')
                    Showing pending and processing tickets created today.
                @else
                    Showing all pending and processing tickets.
                @endif
            </span>
        </div>
        @endif

        <div class="table-responsive">
            <table id="allTicketsTable"
                class="table table-bordered table-hover table-striped align-middle"
                data-empty-message="{{ e($dataTablesEmptyMsg) }}">
                <thead class="table-light">
                    <tr class="text-center">
                        <th class="d-none">TicketId</th>
                        <th>SL</th>
                        @if(!$isBranch)
                        <th>Branch</th>
                        @endif
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Sub-Category</th>
                        @php $canSeePriority = in_array(auth()->user()->role, [1, 2]); @endphp
                        @if($canSeePriority)
                        <th>Priority</th>
                        @endif
                        <th>Status</th>
                        <th>Date</th>
                        @if(!$isBranch)
                        <th>Solved By</th>
                        <th>Assigned To</th>
                        @endif
                        @if($isBranch)
                        <th>Solved Message</th>
                        @endif
                        @if(!$isBranch && $isEngineer)
                        <th>Message</th>
                        @endif
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $index => $ticket)
                        <tr>
                            <td class="d-none">{{ (int) $ticket->id }}</td>
                            <td class="text-center">{{ $index + 1 }}</td>
                            @if(!$isBranch)
                            <td>{{ $ticket->br_name }}</td>
                            @endif
                            <td>{{ $ticket->subject }}</td>

                            {{-- Category and Sub-Category--}}
                            <td>{{ $ticket->category_name ?? 'N/A' }}</td>
                            <td>{{ $ticket->sub_category_name ?? 'N/A' }}</td>

                            @php $canSeePriority = in_array(auth()->user()->role, [1, 2]); @endphp
                            @if($canSeePriority)
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

                            @if($isBranch)
                                @php
                                    $solvedMsg = trim((string) ($ticket->solved_message ?? ''));
                                @endphp
                                <td class="small text-start">
                                    @if((int) ($ticket->status ?? 0) === 2 && $solvedMsg !== '')
                                        {{ \Illuminate\Support\Str::limit($solvedMsg, 60) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif

                            @if(!$isBranch)
                            {{-- Solved By --}}
                            <td>{{ $ticket->solved_by_name ?? '—' }}</td>

                            <td>
                                {{ $ticket->assigned_to_name ?? 'Unassigned' }}
                            </td>
                            @endif
                            @if(!$isBranch && $isEngineer)
                                @php
                                    $note = trim((string) ($ticket->handoff_note ?? ''));
                                    $showNote = ((int) ($ticket->assigned_to ?? 0) === (int) auth()->id()) && $note !== '';
                                @endphp
                                <td class="small text-start">
                                    {{ $showNote ? \Illuminate\Support\Str::limit($note, 60) : '—' }}
                                </td>
                            @endif

                            {{-- Actions --}}
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
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#allTicketsTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']], // hidden TicketId column
            columnDefs: [
                { targets: [0], visible: false, searchable: false }
            ],
            language: {
                emptyTable: document.getElementById('allTicketsTable').dataset.emptyMessage || 'No tickets found.'
            }
        });
    });
</script>
@endsection
