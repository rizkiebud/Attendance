@extends('layouts.app')

@section('title', 'Edit Karyawan - ' . $employee->nama)
@section('page-title', 'Edit Karyawan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Edit Data: {{ $employee->nama }}</h6>
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

                <form method="POST" action="{{ route('web.employees.update', $employee) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $employee->nama) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">No. ID</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip', $employee->nip) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Posisi</label>
                            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $employee->jabatan) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Departemen</label>
                            <input type="text" name="departemen" class="form-control" value="{{ old('departemen', $employee->departemen) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">No. Telepon</label>
                            <input type="text" name="telepon" class="form-control" value="{{ old('telepon', $employee->telepon) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Foto</label>
                            @if($employee->foto)
                                <div class="mb-1">
                                    <img src="{{ asset('storage/' . $employee->foto) }}" alt="foto" height="60" class="rounded">
                                </div>
                            @endif
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $employee->alamat) }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="aktif" value="1"
                                    {{ old('aktif', $employee->aktif) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small">Karyawan Aktif</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('web.employees.show', $employee) }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
