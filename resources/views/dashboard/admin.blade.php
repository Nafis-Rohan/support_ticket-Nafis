@extends('layouts.app')

@section('content')

<head>
    <style>
        #statusPieChart {
            width: 220px !important;
            height: 220px !important;
            max-width: 100%;
        }

        .card-style {
            position: relative;
            overflow: hidden;
            color: #2c2c2cff;
            background: transparent;
            /* remove your opacity + gray background */
        }

        .card-style::before {
            content: "";
            position: absolute;
            inset: 0;
            /* cover full card */
            background: #ddd;
            /* your background color */
            opacity: 0.3;
            /* transparency only for background */
            z-index: 1;
        }

        .card-style .card-body {
            position: relative;
            z-index: 5;
            /* ensures text stays CLEAR above background */
        }
    </style>
</head>
<div class="container-fluid py-4">
    <h4 class="mb-3">🛠 Admin Dashboard</h4>
    <p class="text-muted mb-4">
        Overview for <strong>{{ $today->format('d M, Y') }}</strong>
    </p>

    {{-- Top summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 card-style">
                <div class="card-body py-3 ">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Today's Tickets</div>
                            <div class="h4 mb-0">{{ $todayTotal }}</div>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">All statuses combined today</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 card-style">
                <div class="card-body py-3">
                    <div class="text-muted small">Pending Today</div>
                    <div class="h4 mb-0 text-warning">{{ $todayPending }}</div>
                    <div class="mt-2 small text-muted">Status: Pending (0)</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 card-style">
                <div class="card-body py-3">
                    <div class="text-muted small">Processing Today</div>
                    <div class="h4 mb-0 text-info">{{ $todayProcessing }}</div>
                    <div class="mt-2 small text-muted">Status: Processing (1)</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 card-style">
                <div class="card-body py-3">
                    <div class="text-muted small">Solved Today</div>
                    <div class="h4 mb-0 text-success">{{ $todaySolved }}</div>
                    <div class="mt-2 small text-muted">Status: Solved (2)</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts + summary row --}}
    <div class="row g-3 mb-4">
        {{-- Smaller pie chart --}}
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">Today's Tickets by Status</h6>
                    <small class="text-muted">Pending vs Processing vs Solved</small>
                </div>
                <div class="card-body">
                    <canvas id="statusPieChart" height="150"></canvas>
                </div>
            </div>
        </div>

        {{-- Bigger all-time summary moved up --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">All-Time Ticket Summary</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Total Tickets:</strong> {{ $totalTickets }}</p>
                    <p class="mb-1 text-warning"><strong>Pending:</strong> {{ $totalPending }}</p>
                    <p class="mb-1 text-info"><strong>Processing:</strong> {{ $totalProcessing }}</p>
                    <p class="mb-1 text-success"><strong>Solved:</strong> {{ $totalSolved }}</p>
                </div>
            </div>
        </div>

        {{-- Smaller tickets-per-hour chart, now after summary --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">Tickets Created by Hour (Today)</h6>
                    <small class="text-muted">Activity across the day</small>
                </div>
                <div class="card-body">
                    <canvas id="ticketsHourChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Top issuers (full-width or keep 8 if you prefer) --}}
    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Top 10 Ticket Issuers</h6>
                    <small class="text-muted">By total tickets</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Branch / User</th>
                                    <th class="text-end">Tickets</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topIssuers as $index => $issuer)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $issuer->branch_name ?? 'Unknown' }}</td>
                                    <td class="text-end">{{ $issuer->total_tickets }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        No data available.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from controller
        const statusLabels = @json($statusLabels);
        const statusCounts = @json($statusCountsToday);
        const hoursLabels = @json($hours);
        const hoursCounts = @json($hourCounts);

        // Status Pie Chart
        const ctxPie = document.getElementById('statusPieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: ['#ffc107', '#0dcaf0', '#198754'],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Tickets per hour (bar chart)
        const ctxBar = document.getElementById('ticketsHourChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: hoursLabels,
                datasets: [{
                    label: 'Tickets',
                    data: hoursCounts,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });
    });
</script>
@endsection