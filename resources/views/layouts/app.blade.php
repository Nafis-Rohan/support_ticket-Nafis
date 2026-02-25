<!DOCTYPE html>
<html>

<head>
    <title>IT Help Desk & Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 5 (recommended) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <! -- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">


        <style>
            body {
                overflow-x: hidden;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                font-family: 'Inter', sans-serif !important;
                font-size: 12px;
                color: #333;
            }

            main {
                flex: 1;
            }

            .sidebar {
                min-height: 100%;
            }

            .nav-link.active {
                background-color: #e9ecef;
                font-weight: bold;
            }

            .sidebar {
                background-color: #f8fafc;
                /* light background */
                border-right: 1px solid #e5e7eb;
                /* subtle separator */
            }

            /* Header logo + title */
            .sidebar-header img {
                border-radius: 10px;
            }

            .sidebar-header .fw-bold {
                letter-spacing: 0.3px;
            }

            .sidebar-nav .sidebar-link:hover {
                background-color: rgba(244, 244, 248, 1);
                color: #0d6efd !important;
                transform: translateX(2px);
            }
        </style>
</head>

<body>
    <!-- Navigation bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <h4>IT Help Desk & Support</h4>
            <div class="ms-auto d-flex align-items-center">
                <div class="text-end me-3">
                    <div class="fw-semibold">{{ Auth::user()->name }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main content with sidebar -->
    <main class="container-fluid">
        <div class="row g-0">
            <div class="col-md-3 col-lg-2 bg-light sidebar p-0">
                <div class="sidebar-inner d-flex flex-column h-100">

                    {{-- Logo and Title --}}
                    <div class="sidebar-header text-center py-4 border-bottom">
                        <img src="{{ asset('images/logo.png') }}"
                            alt="Logo"
                            class="img-fluid mb-2"
                            style="max-width: 100px;">
                        <div class="fw-bold text-primary" style="font-size: 14px;">
                            CDIP IT Helpdesk
                        </div>
                    </div>

                    {{-- Navigation --}}
                    <nav class="sidebar-nav flex-grow-1 p-3">
                        <ul class="nav flex-column">

                            @if(auth()->user()->role === 1)
                            <li class="nav-item mb-1">
                                <a class="nav-link sidebar-link {{ request()->is('admin/dashboard') ? 'active' : '' }}"
                                    href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->role == 2 || auth()->user()->role == 3)
                            <li class="nav-item mb-1">
                                <a class="nav-link sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}"
                                    href="{{ url('/dashboard') }}">
                                    <i class="fas fa-home me-2"></i> Dashboard
                                </a>
                            </li>
                            @endif

                            <li class="nav-item mb-1">
                                <a class="nav-link sidebar-link {{ request()->is('tickets*') ? 'active' : '' }}"
                                    href="{{ url('/tickets') }}">
                                    <i class="fas fa-ticket-alt me-2"></i> Tickets
                                </a>
                            </li>

                            @if(auth()->user()->role === 1)
                            <li class="nav-item mt-2">
                                <div class="text-muted small text-uppercase mb-1 px-2">
                                    Configuration
                                </div>
                                <a class="nav-link sidebar-link {{ request()->is('categories*') ? 'active' : '' }}"
                                    href="{{ route('categories.index') }}">
                                    <i class="fas fa-folder me-2"></i> Manage Categories
                                </a>
                                <a class="nav-link sidebar-link {{ request()->is('config/sub-categories*') ? 'active' : '' }}"
                                    href="{{ route('config.sub_categories') }}">
                                    <i class="fas fa-sitemap me-2"></i> Manage Sub Categories
                                </a>
                                <a class="nav-link sidebar-link {{ request()->is('config/engineer-mapping*') ? 'active' : '' }}"
                                    href="{{ route('config.engineer_mapping') }}">
                                    <i class="fas fa-user-cog me-2"></i> Engineer Mapping
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->role === 1)
                            <li class="nav-item mt-2">
                                <div class="text-muted small text-uppercase mb-1 px-2">
                                    Reports
                                </div>
                                <a class="nav-link sidebar-link {{ request()->is('reports/branch') ? 'active' : '' }}"
                                    href="{{ url('/reports/branch') }}">
                                    <i class="fas fa-building me-2"></i> Date-wise Report
                                </a>
                                <a class="nav-link sidebar-link {{ request()->is('reports/problem') ? 'active' : '' }}"
                                    href="{{ url('#') }}">
                                    <i class="fas fa-exclamation-circle me-2"></i> Problem-wise Report
                                </a>
                            </li>
                            @endif

                        </ul>
                    </nav>

                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-3">
        <div class="container text-center">
            <small>&copy; {{ date('Y') }} CDIP IT Services. All rights reserved.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>