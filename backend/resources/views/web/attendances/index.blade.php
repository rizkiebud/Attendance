@extends('layouts.app')

@section('title', 'Data Absensi')
@section('page-title', 'Data Absensi')

@section('content')
{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card stat-card border-0 bg-success bg-opacity-10">
            <div class="card-body py-3">
                <div class="small text-muted">Hadir</div>
                <div class="fw-bold fs-4 text-success">{{ $stats['hadir'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card stat-card border-0 bg-warning bg-opacity-10">
            <div class="card-body py-3">
                <div class="small text-muted">Terlambat</div>
                <div class="fw-bold fs-4 text-warning">{{ $stats['terlambat'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card stat-card border-0 bg-info bg-opacity-10">
            <div class="card-body py-3">
                <div class="small text-muted">Izin/Sakit</div>
                <div class="fw-bold fs-4 text-info">{{ $stats['izin'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card stat-card border-0 bg-danger bg-opacity-10">
            <div class="card-body py-3">
                <div class="small text-muted">Tidak Hadir</div>
                <div class="fw-bold fs-4 text-danger">{{ $stats['tidak_hadir'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card table-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ $tanggal->format('Y-m-d') }}">
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
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach(['hadir', 'terlambat', 'tidak_hadir', 'izin', 'sakit'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('web.attendances.laporan') }}" class="btn btn-outline-success w-100">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i>Laporan
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-calendar3 me-2"></i>
            Absensi {{ $tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}
        </h6>
        <span class="badge bg-primary">{{ $attendances->total() }} data</span>
    </div>
    <div class="card-body p-0">
        @if($attendances->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x empty-state-icon d-block mb-2"></i>
                <p>Tidak ada data absensi untuk tanggal ini</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Durasi</th>
                            <th>Jarak</th>
                            <th>Kantor</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $att)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $att->employee->nama }}</div>
                                <div class="text-muted small">{{ $att->employee->departemen }}</div>
                            </td>
                            <td>{{ $att->jam_masuk ?? '-' }}</td>
                            <td>{{ $att->jam_keluar ?? '-' }}</td>
                            <td>{{ $att->durasi_kerja ?? '-' }}</td>
                            <td>{{ $att->jarak_masuk ? round($att->jarak_masuk).'m' : '-' }}</td>
                            <td class="text-truncate" style="max-width:150px;">{{ $att->office->nama }}</td>
                            <td>
                                <span class="badge badge-{{ $att->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('web.attendances.show', $att) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
