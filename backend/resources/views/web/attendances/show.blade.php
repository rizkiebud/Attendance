@extends('layouts.app')

@section('title', 'Detail Absensi')
@section('page-title', 'Detail Absensi')

@section('content')
<div class="mb-3">
    <a href="{{ route('web.attendances.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Informasi Karyawan</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted small" width="130">Nama</td>
                            <td class="fw-semibold">{{ $attendance->employee->nama }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">NIP</td>
                            <td>{{ $attendance->employee->nip ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Jabatan</td>
                            <td>{{ $attendance->employee->jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Departemen</td>
                            <td>{{ $attendance->employee->departemen ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Tanggal</td>
                            <td>{{ $attendance->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Kantor</td>
                            <td>{{ $attendance->office->nama }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Status</td>
                            <td>
                                <span class="badge badge-{{ $attendance->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="row g-3">
            {{-- Check In --}}
            <div class="col-12">
                <div class="card table-card border-success border-opacity-25">
                    <div class="card-header bg-success bg-opacity-10 border-bottom py-3">
                        <h6 class="mb-0 fw-semibold text-success">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Absen Masuk
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($attendance->jam_masuk)
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">Jam Masuk</div>
                                        <div class="fw-bold fs-5">{{ $attendance->jam_masuk }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">Jarak dari Kantor</div>
                                        <div class="fw-semibold">{{ $attendance->jarak_masuk ? round($attendance->jarak_masuk).' meter' : '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-muted small mb-1">Koordinat</div>
                                        <div class="small">
                                            {{ $attendance->lat_masuk }}, {{ $attendance->lng_masuk }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    @if($attendance->foto_masuk)
                                        <img src="{{ asset('storage/' . $attendance->foto_masuk) }}"
                                            alt="Foto Masuk" class="img-fluid rounded" style="max-height:150px;">
                                    @else
                                        <div class="text-center py-4 text-muted bg-light rounded">
                                            <i class="bi bi-camera d-block mb-1"></i>
                                            <small>Tidak ada foto</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-0">Belum melakukan absen masuk</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Check Out --}}
            <div class="col-12">
                <div class="card table-card border-danger border-opacity-25">
                    <div class="card-header bg-danger bg-opacity-10 border-bottom py-3">
                        <h6 class="mb-0 fw-semibold text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Absen Keluar
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($attendance->jam_keluar)
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">Jam Keluar</div>
                                        <div class="fw-bold fs-5">{{ $attendance->jam_keluar }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">Durasi Kerja</div>
                                        <div class="fw-semibold">{{ $attendance->durasi_kerja ?? '-' }} jam</div>
                                    </div>
                                    <div>
                                        <div class="text-muted small mb-1">Koordinat</div>
                                        <div class="small">
                                            {{ $attendance->lat_keluar }}, {{ $attendance->lng_keluar }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    @if($attendance->foto_keluar)
                                        <img src="{{ asset('storage/' . $attendance->foto_keluar) }}"
                                            alt="Foto Keluar" class="img-fluid rounded" style="max-height:150px;">
                                    @else
                                        <div class="text-center py-4 text-muted bg-light rounded">
                                            <i class="bi bi-camera d-block mb-1"></i>
                                            <small>Tidak ada foto</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-0">Belum melakukan absen keluar</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
