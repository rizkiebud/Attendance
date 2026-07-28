@extends('layouts.app')

@section('title', 'Edit Kantor')
@section('page-title', 'Edit Kantor')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Edit Kantor: {{ $office->nama }}</h6>
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

                <form method="POST" action="{{ route('web.offices.update', $office) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Nama Kantor <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $office->nama) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $office->alamat) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Latitude <span class="text-danger">*</span></label>
                            <input type="number" name="latitude" class="form-control" value="{{ old('latitude', $office->latitude) }}" step="any" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Longitude <span class="text-danger">*</span></label>
                            <input type="number" name="longitude" class="form-control" value="{{ old('longitude', $office->longitude) }}" step="any" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Radius (meter)</label>
                            <input type="number" name="radius" class="form-control" value="{{ old('radius', $office->radius) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Jam Masuk</label>
                            <input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk', substr($office->jam_masuk, 0, 5)) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Jam Keluar</label>
                            <input type="time" name="jam_keluar" class="form-control" value="{{ old('jam_keluar', substr($office->jam_keluar, 0, 5)) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Toleransi Terlambat</label>
                            <input type="time" name="toleransi_terlambat" class="form-control" value="{{ old('toleransi_terlambat', substr($office->toleransi_terlambat, 0, 5)) }}" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="aktif" value="1"
                                    {{ old('aktif', $office->aktif) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small">Kantor Aktif</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('web.offices.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
