@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Total {{ $employees->total() }} karyawan terdaftar</p>
    </div>
    <a href="{{ route('web.employees.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Karyawan
    </a>
</div>

{{-- Filter --}}
<div class="card table-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, NIP, jabatan..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="departemen" class="form-select">
                    <option value="">Semua Departemen</option>
                    @foreach($departemens as $dep)
                        <option value="{{ $dep }}" {{ request('departemen') == $dep ? 'selected' : '' }}>{{ $dep }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="aktif" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
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
    <div class="card-body p-0">
        @if($employees->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2"></i>
                <p>Belum ada data karyawan</p>
                <a href="{{ route('web.employees.create') }}" class="btn btn-primary btn-sm">Tambah Karyawan</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th>Departemen</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:36px; height:36px; border-radius:50%; background:#dbeafe; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        @if($employee->foto)
                                            <img src="{{ asset('storage/' . $employee->foto) }}" alt="{{ $employee->nama }}"
                                                style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                                        @else
                                            <i class="bi bi-person-fill" style="color:#1a56db;"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $employee->nama }}</div>
                                        <div class="text-muted small">{{ $employee->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee->nip ?? '-' }}</td>
                            <td>{{ $employee->jabatan ?? '-' }}</td>
                            <td>{{ $employee->departemen ?? '-' }}</td>
                            <td>
                                @if($employee->aktif)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('web.employees.show', $employee) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('web.employees.edit', $employee) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('web.employees.toggle-aktif', $employee) }}" method="POST"
                                        onsubmit="return confirm('Yakin {{ $employee->aktif ? 'menonaktifkan' : 'mengaktifkan' }} karyawan {{ $employee->nama }}?')">
                                        @csrf
                                        @if($employee->aktif)
                                            <button class="btn btn-sm btn-outline-warning" title="Nonaktifkan"><i class="bi bi-pause-circle"></i></button>
                                        @else
                                            <button class="btn btn-sm btn-outline-success" title="Aktifkan"><i class="bi bi-play-circle"></i></button>
                                        @endif
                                    </form>
                                    <form action="{{ route('web.employees.destroy', $employee) }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus karyawan {{ $employee->nama }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
