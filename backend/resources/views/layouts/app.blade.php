<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Absensi KPPN')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-mini-width: 68px;
            --primary: #1a56db;
            --primary-dark: #1e40af;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #1a56db;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: width 0.3s ease, transform 0.3s ease;
            overflow-x: hidden;
        }

        .sidebar.mini {
            width: var(--sidebar-mini-width);
        }

        .sidebar.mini .sidebar-brand h5,
        .sidebar.mini .sidebar-brand small,
        .sidebar.mini .sidebar-link span,
        .sidebar.mini .sidebar-label,
        .sidebar.mini .sidebar-user-info,
        .sidebar.mini .sidebar-logout-text {
            display: none;
        }

        .sidebar.mini .sidebar-brand {
            padding: 1rem 0.5rem;
            text-align: center;
        }

        .sidebar.mini .sidebar-link {
            justify-content: center;
            padding: 0.75rem;
            gap: 0;
        }

        .sidebar.mini .sidebar-link i {
            font-size: 1.3rem;
        }

        .sidebar.mini .sidebar-link .badge {
            display: none;
        }

        .sidebar.mini .sidebar-bottom {
            padding: 0.75rem 0.5rem;
            text-align: center;
        }

        .sidebar.mini .sidebar-bottom .d-flex {
            justify-content: center;
        }

        .sidebar.mini .sidebar-bottom form {
            display: none;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand h5 {
            color: #fff;
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .sidebar-brand small {
            color: #94a3b8;
            font-size: 0.75rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-label {
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.5rem 1.25rem 0.25rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.25rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-link.active {
            background: rgba(26, 86, 219, 0.15);
            color: #60a5fa;
            border-left-color: #60a5fa;
        }

        .sidebar-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-content.mini {
            margin-left: var(--sidebar-mini-width);
        }

        /* Topbar */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.875rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .page-content {
            padding: 1.5rem;
        }

        /* Cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .stat-card .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* Tables */
        .table-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
        }

        .badge-hadir { background-color: #22c55e; }
        .badge-terlambat { background-color: #f59e0b; }
        .badge-tidak_hadir { background-color: #ef4444; }
        .badge-izin { background-color: #3b82f6; }
        .badge-sakit { background-color: #8b5cf6; }
        .badge-libur { background-color: #6b7280; }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center gap-2">
                <div style="background: #1a56db; border-radius: 8px; padding: 6px;">
                    <img src="{{ asset('logo.png') }}" alt="Logo" style="width: 34px; height: 34px; border-radius: 6px;">
                </div>
                <div>
                    <h5>Absensi KPPN</h5>
                    <small>Monitoring Karyawan</small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <p class="sidebar-label">Menu Utama</p>
            <a href="{{ route('web.dashboard') }}" class="sidebar-link {{ request()->routeIs('web.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('web.attendances.index') }}" class="sidebar-link {{ request()->routeIs('web.attendances.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i>
                <span>Data Absensi</span>
            </a>
            <a href="{{ route('web.attendances.laporan') }}" class="sidebar-link {{ request()->routeIs('web.attendances.laporan') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan</span>
            </a>

            <p class="sidebar-label mt-2">Manajemen</p>
            <a href="{{ route('web.employees.index') }}" class="sidebar-link {{ request()->routeIs('web.employees.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Karyawan</span>
            </a>
            <a href="{{ route('web.leaves.index') }}" class="sidebar-link {{ request()->routeIs('web.leaves.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i>
                <span>Permohonan Izin</span>
                @php $pendingLeaves = \App\Models\LeaveRequest::where('status', 'menunggu')->count(); @endphp
                @if($pendingLeaves > 0)
                    <span class="badge bg-danger ms-auto">{{ $pendingLeaves }}</span>
                @endif
            </a>
            <a href="{{ route('web.offices.index') }}" class="sidebar-link {{ request()->routeIs('web.offices.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i>
                <span>Kantor & Lokasi</span>
            </a>
        </nav>

        <div class="sidebar-bottom" style="position: absolute; bottom: 0; width: 100%; border-top: 1px solid rgba(255,255,255,0.1); padding: 1rem 1.25rem;">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #334155; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-person-fill text-white"></i>
                </div>
                <div class="sidebar-user-info">
                    <div style="color: #fff; font-size: 0.8rem; font-weight: 600; white-space: nowrap;">{{ auth()->user()->name }}</div>
                    <div style="color: #94a3b8; font-size: 0.7rem; white-space: nowrap;">Administrator</div>
                </div>
            </div>
            <form action="{{ route('web.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right me-1"></i> <span class="sidebar-logout-text">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="sidebarMiniToggle" title="Mini Sidebar">
                    <i class="bi bi-chevron-double-left" id="miniIcon"></i>
                </button>
                <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">
                    <i class="bi bi-calendar3 me-1"></i>
                    {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </span>
            </div>
        </div>

        <!-- Page content -->
        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        document.getElementById('sidebarMiniToggle')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const main = document.querySelector('.main-content');
            const icon = document.getElementById('miniIcon');
            const isMini = sidebar.classList.toggle('mini');
            main.classList.toggle('mini');
            icon.className = isMini ? 'bi bi-chevron-double-right' : 'bi bi-chevron-double-left';
        });
    </script>
    @stack('scripts')
</body>
</html>
