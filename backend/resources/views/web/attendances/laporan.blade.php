@extends('layouts.app')

@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')

@section('content')
<div class="card table-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Bulan</label>
                <select name="bulan" class="form-select">
                    @foreach($months as $num => $nama)
                        <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Tahun</label>
                <select name="tahun" class="form-select">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Karyawan</label>
                <select name="employee_id" class="form-select">
                    <option value="">Semua Karyawan</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Tampilkan
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('web.attendances.export', ['bulan' => $bulan, 'tahun' => $tahun, 'employee_id' => request('employee_id')]) }}"
                    class="btn btn-success w-100">
                    <i class="bi bi-download me-1"></i>Export CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold">
            Rekap Kehadiran - {{ $months[$bulan] }} {{ $tahun }}
        </h6>
    </div>
    <div class="card-body p-0">
        @if($rekapKaryawan->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                <p>Tidak ada data untuk periode ini</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Terlambat</th>
                            <th class="text-center">Tidak Hadir</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">%Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapKaryawan as $rekap)
                        @php
                            $totalHadir = $rekap['hadir'] + $rekap['terlambat'];
                            $persentase = $rekap['total'] > 0 ? round(($totalHadir / $rekap['total']) * 100) : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $rekap['employee']->nama }}</div>
                                <div class="text-muted small">{{ $rekap['employee']->departemen }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $rekap['hadir'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $rekap['terlambat'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $rekap['tidak_hadir'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $rekap['izin'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $rekap['sakit'] }}</span>
                            </td>
                            <td class="text-center fw-semibold">{{ $rekap['total'] }}</td>
                            <td class="text-center">
                                <div class="progress" style="height:6px; min-width:80px;">
                                    <div class="progress-bar bg-{{ $persentase >= 80 ? 'success' : ($persentase >= 60 ? 'warning' : 'danger') }}"
                                        style="width:{{ $persentase }}%"></div>
                                </div>
                                <small class="text-muted">{{ $persentase }}%</small>
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
