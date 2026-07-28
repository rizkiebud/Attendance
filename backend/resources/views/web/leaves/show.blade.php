@extends('layouts.app')

@section('title', 'Detail Permohonan Izin')
@section('page-title', 'Detail Permohonan Izin')

@section('content')
<div class="mb-3">
    <a href="{{ route('web.leaves.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row g-3 justify-content-center">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold">Detail Permohonan</h6>
                @php
                    $statusMap = ['menunggu' => 'warning', 'disetujui' => 'success', 'ditolak' => 'danger'];
                @endphp
                <span class="badge bg-{{ $statusMap[$leave->status] }} px-3 py-2">
                    {{ ucfirst($leave->status) }}
                </span>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted small" width="140">Karyawan</td>
                        <td class="fw-semibold">{{ $leave->employee->nama }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Departemen</td>
                        <td>{{ $leave->employee->departemen ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Jenis</td>
                        <td>{{ ucfirst($leave->jenis) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Tanggal Mulai</td>
                        <td>{{ $leave->tanggal_mulai->locale('id')->isoFormat('D MMMM Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Tanggal Selesai</td>
                        <td>{{ $leave->tanggal_selesai->locale('id')->isoFormat('D MMMM Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Durasi</td>
                        <td><strong>{{ $leave->durasi }} hari</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Alasan</td>
                        <td>{{ $leave->alasan }}</td>
                    </tr>
                    @if($leave->dokumen)
                    <tr>
                        <td class="text-muted small">Dokumen</td>
                        <td>
                            <a href="{{ asset('storage/' . $leave->dokumen) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-paperclip me-1"></i>Lihat Dokumen
                            </a>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted small">Diajukan</td>
                        <td>{{ $leave->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</td>
                    </tr>
                    @if($leave->approved_at)
                    <tr>
                        <td class="text-muted small">Diproses oleh</td>
                        <td>{{ $leave->approvedBy->name ?? '-' }} pada {{ $leave->approved_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                    @if($leave->catatan_admin)
                    <tr>
                        <td class="text-muted small">Catatan Admin</td>
                        <td class="text-{{ $leave->status == 'ditolak' ? 'danger' : 'success' }}">
                            {{ $leave->catatan_admin }}
                        </td>
                    </tr>
                    @endif
                </table>

                @if($leave->status === 'menunggu')
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <form action="{{ route('web.leaves.approve', $leave) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="catatan" class="form-control form-control-sm" rows="2"
                                        placeholder="Catatan (opsional)"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Setujui permohonan ini?')">
                                    <i class="bi bi-check-lg me-1"></i>Setujui
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('web.leaves.reject', $leave) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="catatan" class="form-control form-control-sm" rows="2"
                                        placeholder="Alasan penolakan *" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('Tolak permohonan ini?')">
                                    <i class="bi bi-x-lg me-1"></i>Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
