<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - SIMPEG Attendance</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #343a40;
        }
    </style>
</head>
<body>
    @guest('web')
        @yield('content')
    @else
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <nav class="col-md-3 col-lg-2 d-md-block sidebar py-3">
                    <div class="position-sticky">
                        <h4 class="text-center mb-4">SIMPEG Admin</h4>
                        <ul class="nav flex-column">
                            <li class="nav-item mb-2">
                                <a class="nav-link px-3 py-2 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-primary' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link px-3 py-2 rounded {{ request()->routeIs('admin.employees.*') ? 'bg-primary' : '' }}" href="{{ route('admin.employees.index') }}">
                                    <i class="bi bi-people me-2"></i> Employees
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link px-3 py-2 rounded {{ request()->routeIs('admin.sites.*') ? 'bg-primary' : '' }}" href="{{ route('admin.sites.index') }}">
                                    <i class="bi bi-geo-alt me-2"></i> Work Sites
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link px-3 py-2 rounded {{ request()->routeIs('admin.reports.*') ? 'bg-primary' : '' }}" href="{{ route('admin.reports.index') }}">
                                    <i class="bi bi-file-earmark-bar-graph me-2"></i> Reports
                                </a>
                            </li>
                            <!-- More menu items will be added here -->
                        </ul>
                        <hr>
                        <div class="px-3">
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-light w-100">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>

                <!-- Main Content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light" style="min-height: 100vh;">
                    @yield('content')
                </main>
            </div>
        </div>
    @endguest

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
