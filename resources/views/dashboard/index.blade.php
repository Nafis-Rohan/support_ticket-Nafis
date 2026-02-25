@extends('layouts.app')

@section('content')
<div class="container py-5">

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

    {{-- ====== MY TICKET SUMMARY  / INDEX (BOTTOM) ====== --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light text-grey d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Tickets</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>SL</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Sub-Category</th>
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
                            <td>{{ $ticket->subject }}</td>

                            {{-- Category and Sub-Category--}}
                            <td>{{ $ticket->category_name ?? 'N/A' }}</td>
                            <td>{{ $ticket->sub_category_name ?? 'N/A' }}</td>

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
                                {{ !empty($ticket->assigned_engineers) ? implode(', ', $ticket->assigned_engineers) : ($ticket->assigned_to_name ?? 'Unassigned') }}
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
                            {{-- note: If no data --}}
                            <td colspan="9" class="text-center text-muted">No tickets found.</td>
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