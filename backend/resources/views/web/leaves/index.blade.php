@extends('layouts.app')

@section('title', 'Permohonan Izin')
@section('page-title', 'Permohonan Izin')

@section('content')
<div class="card table-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="jenis" class="form-select">
                    <option value="">Semua Jenis</option>
                    <option value="izin" {{ request('jenis') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ request('jenis') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="cuti" {{ request('jenis') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Daftar Permohonan</h6>
        <span class="badge bg-warning">{{ $leaves->where('status', 'menunggu')->count() }} menunggu</span>
    </div>
    <div class="card-body p-0">
        @if($leaves->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clipboard-x empty-state-icon d-block mb-2"></i>
                <p>Tidak ada permohonan izin</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Durasi</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $leave->employee->nama }}</div>
                                <div class="text-muted small">{{ $leave->employee->departemen }}</div>
                            </td>
                            <td>
                                @php
                                    $jenisMap = ['izin' => ['info', 'Izin'], 'sakit' => ['secondary', 'Sakit'], 'cuti' => ['primary', 'Cuti']];
                                    $jenis = $jenisMap[$leave->jenis] ?? ['secondary', $leave->jenis];
                                @endphp
                                <span class="badge bg-{{ $jenis[0] }}">{{ $jenis[1] }}</span>
                            </td>
                            <td>
                                <div>{{ $leave->tanggal_mulai->format('d/m/Y') }}</div>
                                @if($leave->tanggal_mulai != $leave->tanggal_selesai)
                                    <div class="text-muted small">s/d {{ $leave->tanggal_selesai->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td>{{ $leave->durasi }} hari</td>
                            <td class="text-truncate" style="max-width:200px;">{{ $leave->alasan }}</td>
                            <td>
                                @php
                                    $statusMap = ['menunggu' => 'warning', 'disetujui' => 'success', 'ditolak' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $statusMap[$leave->status] }}">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('web.leaves.show', $leave) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
