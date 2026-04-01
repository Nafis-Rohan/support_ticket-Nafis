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
        }

        .card-style::before {
            content: "";
            position: absolute;
            inset: 0;
            background: #ddd;
            opacity: 0.3;
            z-index: 1;
        }

        .card-style .card-body {
            position: relative;
            z-index: 5;
        }
    </style>
</head>
<div class="container-fluid py-4">
    <h4 class="mb-3">🛠 Admin Dashboard</h4>
    <p class="text-muted mb-4">
        Overview for <strong id="adminDashTodayLabel">Loading…</strong>
    </p>
    <div id="adminDashError" class="alert alert-danger d-none" role="alert"></div>

    {{-- Top summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 card-style">
                <div class="card-body py-3 ">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Today's Tickets</div>
                            <div class="h4 mb-0" id="adminDashTodayTotal">—</div>
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
                    <div class="h4 mb-0 text-warning" id="adminDashTodayPending">—</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 card-style">
                <div class="card-body py-3">
                    <div class="text-muted small">Processing Today</div>
                    <div class="h4 mb-0 text-info" id="adminDashTodayProcessing">—</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 card-style">
                <div class="card-body py-3">
                    <div class="text-muted small">Solved Today</div>
                    <div class="h4 mb-0 text-success" id="adminDashTodaySolved">—</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts + summary row --}}
    <div class="row g-3 mb-4">
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

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">All-Time Ticket Summary</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Total Tickets:</strong> <span id="adminDashTotalTickets">—</span></p>
                    <p class="mb-1 text-warning"><strong>Pending:</strong> <span id="adminDashTotalPending">—</span></p>
                    <p class="mb-1 text-info"><strong>Processing:</strong> <span id="adminDashTotalProcessing">—</span></p>
                    <p class="mb-1 text-success"><strong>Solved:</strong> <span id="adminDashTotalSolved">—</span></p>
                </div>
            </div>
        </div>

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
                            <tbody id="adminDashIssuersBody">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Loading…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    let statusPieChartInstance = null;
    let ticketsHourChartInstance = null;

    function loadAxiosThen(fn) {
        if (typeof axios !== 'undefined') {
            fn();
            return;
        }
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js';
        script.onload = fn;
        script.onerror = function () {
            document.getElementById('adminDashError').textContent = 'Could not load Axios.';
            document.getElementById('adminDashError').classList.remove('d-none');
        };
        document.head.appendChild(script);
    }

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = value;
        }
    }

    function renderIssuers(rows) {
        var body = document.getElementById('adminDashIssuersBody');
        if (!rows || !rows.length) {
            body.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">No data available.</td></tr>';
            return;
        }
        body.innerHTML = rows.map(function (issuer, index) {
            var name = issuer.branch_name != null ? issuer.branch_name : 'Unknown';
            var count = issuer.total_tickets;
            return '<tr><td>' + (index + 1) + '</td><td>' + escapeHtml(String(name)) + '</td><td class="text-end">' + count + '</td></tr>';
        }).join('');
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function renderCharts(statusLabels, statusCounts, hoursLabels, hoursCounts) {
        var pieEl = document.getElementById('statusPieChart');
        var barEl = document.getElementById('ticketsHourChart');
        if (!pieEl || !barEl) {
            return;
        }

        if (statusPieChartInstance) {
            statusPieChartInstance.destroy();
        }
        if (ticketsHourChartInstance) {
            ticketsHourChartInstance.destroy();
        }

        var ctxPie = pieEl.getContext('2d');
        statusPieChartInstance = new Chart(ctxPie, {
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

        var ctxBar = barEl.getContext('2d');
        ticketsHourChartInstance = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: hoursLabels.length ? hoursLabels : ['—'],
                datasets: [{
                    label: 'Tickets',
                    data: hoursLabels.length ? hoursCounts : [0],
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    function applyPayload(data) {
        document.getElementById('adminDashError').classList.add('d-none');

        setText('adminDashTodayLabel', data.today_label || '');
        setText('adminDashTodayTotal', String(data.today_total ?? 0));
        setText('adminDashTodayPending', String(data.today_pending ?? 0));
        setText('adminDashTodayProcessing', String(data.today_processing ?? 0));
        setText('adminDashTodaySolved', String(data.today_solved ?? 0));

        setText('adminDashTotalTickets', String(data.total_tickets ?? 0));
        setText('adminDashTotalPending', String(data.total_pending ?? 0));
        setText('adminDashTotalProcessing', String(data.total_processing ?? 0));
        setText('adminDashTotalSolved', String(data.total_solved ?? 0));

        renderIssuers(data.top_issuers || []);

        renderCharts(
            data.status_labels || ['Pending', 'Processing', 'Solved'],
            data.status_counts_today || [0, 0, 0],
            data.hours_labels || [],
            data.hours_counts || []
        );
    }

    function fetchDashboard() {
        axios.get("{{ route('admin.dashboard.data') }}")
            .then(function (res) {
                applyPayload(res.data || {});
            })
            .catch(function (err) {
                var msg = (err.response && err.response.data && err.response.data.message)
                    ? err.response.data.message
                    : 'Could not load dashboard data.';
                document.getElementById('adminDashError').textContent = msg;
                document.getElementById('adminDashError').classList.remove('d-none');
                document.getElementById('adminDashIssuersBody').innerHTML =
                    '<tr><td colspan="3" class="text-center text-muted py-3">Failed to load.</td></tr>';
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadAxiosThen(fetchDashboard);
    });
})();
</script>
@endsection
