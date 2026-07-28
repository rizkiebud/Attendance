@extends('layouts.app')

@section('title', 'Tambah Kantor')
@section('page-title', 'Tambah Kantor')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Form Tambah Kantor / Lokasi Absensi</h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('web.offices.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Nama Kantor <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required placeholder="Contoh: KPPN Utama">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Latitude <span class="text-danger">*</span></label>
                            <input type="number" name="latitude" class="form-control" value="{{ old('latitude') }}"
                                step="any" required placeholder="-6.1751">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Longitude <span class="text-danger">*</span></label>
                            <input type="number" name="longitude" class="form-control" value="{{ old('longitude') }}"
                                step="any" required placeholder="106.8272">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Radius (meter) <span class="text-danger">*</span></label>
                            <input type="number" name="radius" class="form-control" value="{{ old('radius', 100) }}" required min="10" max="5000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Jam Masuk <span class="text-danger">*</span></label>
                            <input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk', '08:00') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Jam Keluar <span class="text-danger">*</span></label>
                            <input type="time" name="jam_keluar" class="form-control" value="{{ old('jam_keluar', '17:00') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Toleransi Terlambat <span class="text-danger">*</span></label>
                            <input type="time" name="toleransi_terlambat" class="form-control" value="{{ old('toleransi_terlambat', '00:30') }}" required>
                            <div class="form-text">HH:MM dari jam masuk</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </button>
                        <a href="{{ route('web.offices.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
