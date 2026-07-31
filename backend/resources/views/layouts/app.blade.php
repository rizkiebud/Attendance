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

    @vite('resources/css/app.css')
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

        @php
            $u = auth()->user();
            $isAdmin = $u->isAdmin();
            $jabatan = $u->employee?->jabatan;
            $userLevels = $isAdmin ? ['full','manage','view'] : (\App\Http\Middleware\CheckJabatan::LEVELS[$jabatan] ?? ['view']);
            $canManage = in_array('manage', $userLevels);
        @endphp
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

            @if($canManage)
            <p class="sidebar-label mt-2">Manajemen</p>
            <a href="{{ route('web.employees.index') }}" class="sidebar-link {{ request()->routeIs('web.employees.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Karyawan</span>
            </a>
            <a href="{{ route('web.leaves.index') }}" class="sidebar-link {{ request()->routeIs('web.leaves.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i>
                <span>Permohonan Izin</span>
                @php
                    $pendingQuery = \App\Models\LeaveRequest::where('status', 'menunggu');
                    if (!$isAdmin && $u->employee?->departemen) {
                        $pendingQuery->whereHas('employee', fn($q) => $q->where('departemen', $u->employee->departemen));
                    }
                    $pendingLeaves = $pendingQuery->count();
                @endphp
                @if($pendingLeaves > 0)
                    <span class="badge bg-danger ms-auto">{{ $pendingLeaves }}</span>
                @endif
            </a>
            <a href="{{ route('web.offices.index') }}" class="sidebar-link {{ request()->routeIs('web.offices.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i>
                <span>Kantor & Lokasi</span>
            </a>
            @endif
        </nav>

        <div class="sidebar-bottom" style="position: absolute; bottom: 0; width: 100%; border-top: 1px solid rgba(255,255,255,0.1); padding: 1rem 1.25rem;">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #334155; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-person-fill text-white"></i>
                </div>
                <div class="sidebar-user-info">
                    <div style="color: #fff; font-size: 0.8rem; font-weight: 600; white-space: nowrap;">{{ auth()->user()->name }}</div>
                    <div style="color: #94a3b8; font-size: 0.7rem; white-space: nowrap;">
                        {{ auth()->user()->isAdmin() ? 'Administrator' : (auth()->user()->employee?->jabatan ?? 'Karyawan') }}
                    </div>
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
