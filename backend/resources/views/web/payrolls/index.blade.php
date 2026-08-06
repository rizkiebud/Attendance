@extends('layouts.app')

@section('title', 'Penggajian')
@section('page-title', 'Penggajian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-auto">
            <input type="month" name="periode" class="form-control" value="{{ $periode }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-calendar2-check me-1"></i>Tampilkan
            </button>
        </div>
    </form>
</div>

<div class="card table-card">
    <div class="card-body p-0">
        @if($employees->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-wallet2 empty-state-icon d-block mb-2"></i>
                <p>Belum ada data karyawan</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:40px;">#</th>
                            <th>Karyawan</th>
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th class="text-end">Gaji Pokok</th>
                            <th class="text-end">Tunjangan</th>
                            <th class="text-end">Uang Makan</th>
                            <th class="text-end">Transport</th>
                            <th class="text-end">Tunj. Jabatan</th>
                            <th class="text-end">Hari Hadir</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Potongan</th>
                            <th class="text-end">Total</th>
                            {{-- <th>Status</th> --}}
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        @php
                            $payroll = optional($employee->payrolls->first());
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle avatar-circle-sm">
                                        @if($employee->foto)
                                            <img src="{{ asset('storage/' . $employee->foto) }}" alt="{{ $employee->nama }}">
                                        @else
                                            <i class="bi bi-person-fill"></i>
                                        @endif
                                    </div>
                                    <span class="fw-semibold">{{ $employee->nama }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $employee->nip ?? '-' }}</td>
                            <td>{{ $employee->jabatan ?? '-' }}</td>
                            <td class="text-end">{{ number_format($employee->payrolls->first()->gaji_pokok ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($employee->payrolls->first()->tunjangan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($employee->payrolls->first()->uang_makan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($employee->payrolls->first()->uang_transport ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($employee->payrolls->first()->tunjangan_jabatan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ $employee->payrolls->first()->jumlah_hadir ?? 0 }}</td>
                            <td class="text-end">{{ number_format($employee->payrolls->first()->bonus ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">{{ number_format($employee->payrolls->first()->potongan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">{{ number_format($employee->payrolls->first()->total ?? 0, 0, ',', '.') }}</td>
                            {{-- <td>
                                @if($employee->payrolls->isNotEmpty())
                                    @if($employee->payrolls->first()->status === 'lunas')
                                        <span class="badge bg-success">Lunas</span>
                                    @else
                                        <span class="badge bg-warning">Draft</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Belum diisi</span>
                                @endif
                            </td> --}}
                            <td class="text-end">
                                <a href="{{ route('web.payrolls.create', ['employee_id' => $employee->id, 'periode' => $periode]) }}"
                                    class="btn btn-sm {{ $employee->payrolls->isNotEmpty() ? 'btn-outline-primary' : 'btn-primary' }}">
                                    <i class="bi {{ $employee->payrolls->isNotEmpty() ? 'bi-pencil' : 'bi-plus-lg' }} me-1"></i>
                                    {{ $employee->payrolls->isNotEmpty() ? 'Edit' : 'Input Gaji' }}
                                </a>
                                @if($employee->payrolls->isNotEmpty() && $employee->payrolls->first()->status !== 'lunas')
                                <form action="{{ route('web.payrolls.paid', $employee->payrolls->first()) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" title="Tandai lunas">
                                        <i class="bi bi-check2-circle"></i>
                                    </button>
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

<div class="mt-3">
    {{ $employees->links() }}
</div>
@endsection