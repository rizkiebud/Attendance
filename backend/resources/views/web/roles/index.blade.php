@extends('layouts.app')

@section('title', 'Master Role')
@section('page-title', 'Master Role')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        {{-- <p class="text-muted mb-0">Kelola role untuk akses web (tidak terikat ke data karyawan)</p> --}}
    </div>
    <a href="{{ route('web.roles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Role
    </a>
</div>

<div class="card table-card">
    <div class="card-body p-0">
        @if($roles->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-shield-lock empty-state-icon d-block mb-2"></i>
                <p>Belum ada data role</p>
                <a href="{{ route('web.roles.create') }}" class="btn btn-primary btn-sm">Tambah Role</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Role</th>
                            <th>Label</th>
                            <th>Level Akses</th>
                            <th>Jumlah User</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $role->name }}</td>
                            <td>{{ $role->label }}</td>
                            <td>
                                @php
                                    $badge = match($role->level) {
                                        'full' => 'danger',
                                        'manage' => 'warning',
                                        'hrd' => 'info',
                                        default => 'secondary',
                                    };
                                    $label = match($role->level) {
                                        'full' => 'Full',
                                        'manage' => 'Manage',
                                        'hrd' => 'HRD',
                                        default => 'View',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('web.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                                @if(!in_array(strtolower($role->name), ['admin', 'administrator']))
                                <form action="{{ route('web.roles.destroy', $role) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Hapus role {{ $role->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection