@extends('layouts.app')

@section('title', 'Data Kantor & Lokasi')
@section('page-title', 'Kantor & Lokasi')

@section('content')
<div class="d-flex justify-content-end mb-4">
    <a href="{{ route('web.offices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Kantor
    </a>
</div>

<div class="row g-3">
    @forelse($offices as $office)
    <div class="col-lg-6">
        <div class="card table-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $office->nama }}</h5>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-geo-alt me-1"></i>{{ $office->alamat ?? 'Alamat belum diisi' }}
                        </p>
                    </div>
                    @if($office->aktif)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted small">Radius Absensi</div>
                            <div class="fw-bold">{{ $office->radius }} meter</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted small">Jam Kerja</div>
                            <div class="fw-bold">{{ substr($office->jam_masuk, 0, 5) }} - {{ substr($office->jam_keluar, 0, 5) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted small">Toleransi Terlambat</div>
                            <div class="fw-bold">{{ substr($office->toleransi_terlambat, 0, 5) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="text-muted small">Koordinat</div>
                            <div class="fw-bold small">{{ $office->latitude }}, {{ $office->longitude }}</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('web.offices.edit', $office) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <a href="https://maps.google.com/?q={{ $office->latitude }},{{ $office->longitude }}"
                        target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-map me-1"></i>Lihat Map
                    </a>
                    <form action="{{ route('web.offices.destroy', $office) }}" method="POST" class="ms-auto"
                        onsubmit="return confirm('Hapus kantor {{ $office->nama }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
        <div class="col-12">
            <div class="card table-card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-building fs-1 d-block mb-2"></i>
                    <p>Belum ada data kantor</p>
                    <a href="{{ route('web.offices.create') }}" class="btn btn-primary btn-sm">Tambah Kantor</a>
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-3">
    {{ $offices->links() }}
</div>
@endsection
