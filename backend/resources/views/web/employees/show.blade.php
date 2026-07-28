@extends('layouts.app')

@section('title', 'Detail Karyawan - ' . $employee->nama)
@section('page-title', 'Detail Karyawan')

@section('content')
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('web.employees.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <a href="{{ route('web.employees.edit', $employee) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <form action="{{ route('web.employees.toggle-aktif', $employee) }}" method="POST"
        onsubmit="return confirm('Yakin {{ $employee->aktif ? 'menonaktifkan' : 'mengaktifkan' }} karyawan {{ $employee->nama }}?')">
        @csrf
        @if($employee->aktif)
            <button class="btn btn-warning btn-sm"><i class="bi bi-pause-circle me-1"></i>Nonaktifkan</button>
        @else
            <button class="btn btn-success btn-sm"><i class="bi bi-play-circle me-1"></i>Aktifkan</button>
        @endif
    </form>
</div>

<div class="row g-3">
    {{-- Profile --}}
    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-body text-center py-4">
                <div style="width:90px; height:90px; border-radius:50%; background:#dbeafe; margin:0 auto 1rem; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                    @if($employee->foto)
                        <img src="{{ asset('storage/' . $employee->foto) }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <i class="bi bi-person-fill" style="font-size:2.5rem; color:#1a56db;"></i>
                    @endif
                </div>
                <h5 class="fw-bold mb-1">{{ $employee->nama }}</h5>
                <p class="text-muted mb-1">{{ $employee->jabatan ?? 'Staf' }}</p>
                <span class="badge bg-primary">{{ $employee->departemen ?? '-' }}</span>
                @if(!$employee->aktif)
                    <span class="badge bg-secondary ms-1">Nonaktif</span>
                @endif

                <hr>
                <div class="text-start">
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-person-badge text-muted" style="width:20px;"></i>
                        <span class="small">{{ $employee->nip ?? 'Belum diisi' }}</span>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-envelope text-muted" style="width:20px;"></i>
                        <span class="small">{{ $employee->user->email }}</span>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-telephone text-muted" style="width:20px;"></i>
                        <span class="small">{{ $employee->telepon ?? 'Belum diisi' }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <i class="bi bi-geo-alt text-muted" style="width:20px;"></i>
                        <span class="small">{{ $employee->alamat ?? 'Belum diisi' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rekap Bulan Ini --}}
        <div class="card table-card mt-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Rekap Bulan Ini</h6>
            </div>
            <div class="card-body">
                @foreach(['hadir' => ['Hadir', '#22c55e'], 'terlambat' => ['Terlambat', '#f59e0b'], 'tidak_hadir' => ['Tidak Hadir', '#ef4444'], 'izin' => ['Izin', '#3b82f6'], 'sakit' => ['Sakit', '#8b5cf6']] as $key => [$label, $color])
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $color }};display:inline-block;"></span>
                        <span class="small">{{ $label }}</span>
                    </div>
                    <strong>{{ $monthlyStats[$key] ?? 0 }}</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Riwayat Absensi --}}
    <div class="col-lg-8">
        <div class="card table-card mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Riwayat Absensi Terbaru</h6>
            </div>
            <div class="card-body p-0">
                @if($employee->attendances->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-x d-block fs-2 mb-2"></i>
                        <small>Belum ada data absensi</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Status</th>
                                    <th>Jarak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->attendances as $att)
                                <tr>
                                    <td>{{ $att->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $att->jam_masuk ?? '-' }}</td>
                                    <td>{{ $att->jam_keluar ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $att->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $att->jarak_masuk ? round($att->jarak_masuk).'m' : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Riwayat Izin --}}
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Riwayat Permohonan Izin</h6>
            </div>
            <div class="card-body p-0">
                @if($employee->leaveRequests->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <small>Belum ada permohonan izin</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->leaveRequests as $leave)
                                <tr>
                                    <td>{{ ucfirst($leave->jenis) }}</td>
                                    <td>
                                        {{ $leave->tanggal_mulai->format('d/m') }} -
                                        {{ $leave->tanggal_selesai->format('d/m/Y') }}
                                    </td>
                                    <td class="text-truncate" style="max-width:150px;">{{ $leave->alasan }}</td>
                                    <td>
                                        @php
                                            $badgeMap = ['menunggu' => 'warning', 'disetujui' => 'success', 'ditolak' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $badgeMap[$leave->status] }}">
                                            {{ ucfirst($leave->status) }}
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
    </div>
</div>
@endsection
