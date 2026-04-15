@extends('layouts.app')

@section('content')
<div class="container py-5">

    @if(auth()->user()->role != 2)
    {{-- ====== CATEGORY DASHBOARD (TOP) ====== --}}
    <div class="text-center mb-4">
        <h3 class="text-secondary mb-3">Select a Category</h3>
    </div>

    <div class="row justify-content-center mb-5">
        @foreach($categories as $category)
        <div class="col-12 col-sm-6 col-md-3 mb-4">
            <a href="{{ route('dashboard.subcategories', $category->id) }}" class="text-decoration-none">
                <div class="card h-100 border-0 category-card
                            @if(Str::contains(strtolower($category->name), 'software')) software-issues
                            @elseif(Str::contains(strtolower($category->name), 'hardware')) hardware-issues
                            @elseif(Str::contains(strtolower($category->name), 'mail')) e-mail-issues
                            @else general-it-issues @endif">

                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        @if(Str::contains(strtolower($category->name), 'software'))
                        <i class="fa fa-bug fa-3x mb-3 text-white"></i>
                        @elseif(Str::contains(strtolower($category->name), 'hardware'))
                        <i class="fa fa-microchip fa-3x mb-3 text-white"></i>
                        @elseif(Str::contains(strtolower($category->name), 'mail'))
                        <i class="fa fa-envelope fa-3x mb-3 text-white"></i>
                        @else
                        <i class="fa fa-cogs fa-3x mb-3 text-white"></i>
                        @endif
                        <h5 class="font-weight-bold text-white mb-0">{{ $category->name }}</h5>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <div class="text-center mb-4">
        <hr>
    </div>
    @endif

    @if(auth()->user()->role == 2)
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">Total Pending + Processing</div>
                    <h3 class="mb-0" id="pendingProcessingCount">0</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">Today Ticket Count</div>
                    <h3 class="mb-0" id="todayTicketCount">0</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">Today Solved Count</div>
                    <h3 class="mb-0" id="todaySolvedCount">0</h3>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ====== MY TICKET SUMMARY  / INDEX (BOTTOM) ====== --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light text-grey d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @if(auth()->user()->role == 2)
                Today&rsquo;s tickets
                @else
                Today's Tickets
                @endif
            </h5>
        </div>
        <div class="card-body">
            @if(auth()->user()->role == 2)
            <form method="get" action="{{ route('dashboard') }}" id="engineerDashFilters" class="row row-cols-auto g-2 align-items-end mb-3 pb-2 border-bottom">
                <div class="col-12 col-sm-auto">
                    <label for="dash_filter_priority" class="form-label small mb-1 text-muted">Priority</label>
                    <select name="priority" id="dash_filter_priority" class="form-select form-select-sm" onchange="document.getElementById('engineerDashFilters').submit()">
                        <option value="all" {{ ($dashFilterPriority ?? 'all') === 'all' ? 'selected' : '' }}>All priorities</option>
                        <option value="high" {{ ($dashFilterPriority ?? '') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ ($dashFilterPriority ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="medium" {{ ($dashFilterPriority ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ ($dashFilterPriority ?? '') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="unset" {{ ($dashFilterPriority ?? '') === 'unset' ? 'selected' : '' }}>Not set</option>
                    </select>
                </div>
                <div class="col-12 col-sm-auto">
                    <label for="dash_filter_status" class="form-label small mb-1 text-muted">Status</label>
                    <select name="status" id="dash_filter_status" class="form-select form-select-sm" onchange="document.getElementById('engineerDashFilters').submit()">
                        <option value="all" {{ ($dashFilterStatus ?? 'all') === 'all' ? 'selected' : '' }}>Pending &amp; processing</option>
                        <option value="pending" {{ ($dashFilterStatus ?? '') === 'pending' ? 'selected' : '' }}>Pending only</option>
                        <option value="processing" {{ ($dashFilterStatus ?? '') === 'processing' ? 'selected' : '' }}>Processing only</option>
                    </select>
                </div>
                <div class="col-12 align-self-center">
                    <span class="text-muted small">Unsolved tickets created today (your categories).</span>
                </div>
            </form>
            @endif

            <div class="table-responsive">
                <table id="dashboardTicketsTable" class="table table-bordered table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th class="d-none">TicketId</th>
                            <th>SL</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Sub-Category</th>
                            @if(auth()->user()->role == 2)
                            <th>Priority</th>
                            @endif
                            <th>Status</th>
                            <th>Date</th>
                            <th>Solved By</th>
                            <th>Assigned To</th>
                            @if(auth()->user()->role == 3)
                            <th>Solved Message</th>
                            @endif
                            @if(auth()->user()->role == 2)
                            <th>Message</th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        @php
                        $dashboardRows = auth()->user()->role == 2 ? ($engineerTodayTickets ?? collect()) : $tickets;
                        @endphp
                        @forelse ($dashboardRows as $index => $ticket)
                        <tr>
                            <td class="d-none">{{ (int) $ticket->id }}</td>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $ticket->subject }}</td>

                            {{-- Category and Sub-Category--}}
                            <td>{{ $ticket->category_name ?? 'N/A' }}</td>
                            <td>{{ $ticket->sub_category_name ?? 'N/A' }}</td>

                            @if(auth()->user()->role == 2)
                            <td class="text-center">
                                @if(($ticket->priority_name ?? '') == 'High')
                                <span class="badge bg-success">High</span>
                                @elseif(($ticket->priority_name ?? '') == 'Urgent')
                                <span class="badge bg-danger">Urgent</span>
                                @elseif(($ticket->priority_name ?? '') == 'Medium')
                                <span class="badge bg-warning text-dark">Medium</span>
                                @elseif(($ticket->priority_name ?? '') == 'Low')
                                <span class="badge bg-info">Low</span>
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
                            @if(auth()->user()->role == 3)
                            @php
                            $solvedMsg = trim((string) ($ticket->solved_message ?? ''));
                            $showSolvedMsg = ((int) ($ticket->status ?? 0) === 2) && $solvedMsg !== '';
                            @endphp
                            <td class="small text-start">
                                {{ $showSolvedMsg ? \Illuminate\Support\Str::limit($solvedMsg, 60) : '—' }}
                            </td>
                            @endif
                            @if(auth()->user()->role == 2)
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
                        @empty
                        <tr>
                            {{-- Must match thead: engineer 12 cols, branch 11 cols, others 10 --}}
                            <td colspan="{{ auth()->user()->role == 2 ? 12 : (auth()->user()->role == 3 ? 11 : 10) }}" class="text-center text-muted">No tickets found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- if you’re paginating $tickets --}}
                @if(method_exists($tickets, 'links'))
                <div class="mt-3">
                    {{ $tickets->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // DataTables breaks if tbody column count does not match thead (e.g. wrong colspan on empty row).
        // Skip init when there is only the "no data" row with colspan.
        var $tbl = $('#dashboardTicketsTable');
        var $firstRow = $tbl.find('tbody tr:first');
        var hasColspanRow = $firstRow.find('td[colspan]').length > 0;
        if ($tbl.length && !hasColspanRow) {
            $tbl.DataTable({
                pageLength: 25,
                order: [
                    [0, 'desc']
                ], // hidden TicketId column
                columnDefs: [{
                    targets: [0],
                    visible: false,
                    searchable: false
                }]
            });
        }

        if ($('#pendingProcessingCount').length) {
            if (typeof axios === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js';
                script.onload = loadEngineerStats;
                document.head.appendChild(script);
            } else {
                loadEngineerStats();
            }
        }
    });

    function loadEngineerStats() {
        axios.get("{{ route('dashboard.engineer_stats') }}")
            .then(function(response) {
                const data = response.data || {};
                $('#pendingProcessingCount').text(data.pending_processing_count ?? 0);
                $('#todayTicketCount').text(data.today_ticket_count ?? 0);
                $('#todaySolvedCount').text(data.today_solved_count ?? 0);
            })
            .catch(function() {
                $('#pendingProcessingCount').text('0');
                $('#todayTicketCount').text('0');
                $('#todaySolvedCount').text('0');
            });
    }
</script>
@endsection

<style>
    /* Background */
    body {
        background-color: #f8fafc;
    }

    /* Category Cards */
    .category-card {
        border-radius: 14px;
        transition: all 0.3s ease;
        background: #EFECE3;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    /* Flat vibrant backgrounds */
    .software-issues {
        background-color: #FCB53B !important;
    }

    .hardware-issues {
        background-color: #ADADAD !important;
    }

    .e-mail-issues {
        background-color: #2670efff !important;
    }

    .general-it-issues {
        background-color: #FFE797 !important;
    }

    /* Hover effects */
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
        filter: brightness(1.1);
    }

    /* Text & Icon styling */
    .category-card i,
    .category-card h5 {
        color: #151515ff !important;
    }

    .category-card h5 {
        font-weight: 600;
        letter-spacing: 0.4px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .category-card .card-body {
            padding: 25px 10px;
        }
    }
</style>
@endsection