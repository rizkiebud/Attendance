@extends('layouts.app')

@section('title', 'Edit Role')
@section('page-title', 'Edit Role')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Form Edit Role</h6>
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

                <form method="POST" action="{{ route('web.roles.update', $role) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Label <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" value="{{ old('label', $role->label) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Level Akses <span class="text-danger">*</span></label>
                            <select name="level" class="form-select" required>
                                <option value="view" {{ old('level', $role->level) === 'view' ? 'selected' : '' }}>View (lihat saja)</option>
                                <option value="manage" {{ old('level', $role->level) === 'manage' ? 'selected' : '' }}>Manage (kelola data)</option>
                                <option value="hrd" {{ old('level', $role->level) === 'hrd' ? 'selected' : '' }}>HRD (absensi, laporan, karyawan, izin, kantor & lokasi)</option>
                                <option value="full" {{ old('level', $role->level) === 'full' ? 'selected' : '' }}>Full (semua akses)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Tetapkan User</label>
                            <div class="border rounded p-2" style="max-height: 260px; overflow-y: auto;" id="userList">
                                @foreach($users as $user)
                                    <label class="d-flex align-items-center gap-2 py-1 px-2 rounded user-opt" data-jabatan="{{ $user->employee?->jabatan ?? '' }}">
                                        <input type="checkbox" class="form-check-input m-0 user-check" name="users[]" value="{{ $user->id }}"
                                            {{ in_array($user->id, $role->users->pluck('id')->all()) ? 'checked' : '' }}>
                                        <span>{{ $user->name }} ({{ $user->email }})@if($user->employee?->jabatan) — {{ $user->employee->jabatan }}@endif</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="form-text">Centang untuk pilih. Bisa pilih lebih dari 1 user. Kosongkan untuk tidak ada user.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </button>
                        <a href="{{ route('web.roles.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection