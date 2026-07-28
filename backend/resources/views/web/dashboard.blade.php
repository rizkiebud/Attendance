@extends('layouts.app')

@section('title', 'Dashboard - Absensi KPPN')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background: #dbeafe;">
                    <i class="bi bi-people-fill" style="color: #1a56db;"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Karyawan</div>
                    <div class="fw-bold fs-4">{{ $totalKaryawan }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background: #dcfce7;">
                    <i class="bi bi-check-circle-fill" style="color: #16a34a;"></i>
                </div>
                <div>
                    <div class="text-muted small">Hadir Hari Ini</div>
                    <div class="fw-bold fs-4">{{ $hadirHariIni }}</div>
                    <div class="text-danger small">{{ $terlambatHariIni }} terlambat</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background: #fee2e2;">
                    <i class="bi bi-x-circle-fill" style="color: #dc2626;"></i>
                </div>
                <div>
                    <div class="text-muted small">Tidak Hadir</div>
                    <div class="fw-bold fs-4">{{ $tidakHadirHariIni }}</div>
                    <div class="text-muted small">hari ini</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background: #fef3c7;">
                    <i class="bi bi-clipboard-check-fill" style="color: #d97706;"></i>
                </div>
                <div>
                    <div class="text-muted small">Permohonan Izin</div>
                    <div class="fw-bold fs-4">{{ $permohonanMenunggu }}</div>
                    <div class="text-warning small">menunggu persetujuan</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Chart --}}
    <div class="col-lg-8">
        <div class="card table-card h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Rekap Kehadiran 7 Hari Terakhir</h6>
            </div>
            <div class="card-body">
                <canvas id="attendanceChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Monthly Summary --}}
    <div class="col-lg-4">
        <div class="card table-card h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Rekap Bulan Ini</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill" style="background:#22c55e; width:10px; height:10px;"></span>
                        <span class="small">Hadir</span>
                    </div>
                    <strong>{{ $totalHadir }}</strong>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill" style="background:#f59e0b; width:10px; height:10px;"></span>
                        <span class="small">Terlambat</span>
                    </div>
                    <strong>{{ $totalTerlambat }}</strong>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill" style="background:#3b82f6; width:10px; height:10px;"></span>
                        <span class="small">Izin/Sakit</span>
                    </div>
                    <strong>{{ $totalIzin }}</strong>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill" style="background:#ef4444; width:10px; height:10px;"></span>
                        <span class="small">Tidak Hadir</span>
                    </div>
                    <strong>-</strong>
                </div>

                <div class="mt-3 pt-2">
                    <a href="{{ route('web.attendances.laporan') }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i>Lihat Laporan Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Absensi Hari Ini --}}
<div class="card table-card">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">Absensi Hari Ini</h6>
        <a href="{{ route('web.attendances.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="card-body p-0">
        @if($absensiToday->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1 mb-2 d-block"></i>
                <p>Belum ada data absensi hari ini</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Jarak (m)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absensiToday as $absensi)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $absensi->employee->nama }}</div>
                                <div class="text-muted small">{{ $absensi->employee->jabatan }}</div>
                            </td>
                            <td>{{ $absensi->jam_masuk ?? '-' }}</td>
                            <td>{{ $absensi->jam_keluar ?? '-' }}</td>
                            <td>{{ $absensi->jarak_masuk ? round($absensi->jarak_masuk) . 'm' : '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $absensi->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $absensi->status)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
const chartData = @json($chartData);
const ctx = document.getElementById('attendanceChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.tanggal),
        datasets: [
            {
                label: 'Hadir',
                data: chartData.map(d => d.hadir),
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderRadius: 4,
            },
            {
                label: 'Terlambat',
                data: chartData.map(d => d.terlambat),
                backgroundColor: 'rgba(245, 158, 11, 0.8)',
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: '#f1f5f9' }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
