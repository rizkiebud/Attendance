@extends('layouts.app')

@section('title', 'Input Gaji')
@section('page-title', 'Input Gaji')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-body">
                <h6 class="fw-bold mb-1">{{ $employee->nama }}</h6>
                <p class="text-muted small mb-1">{{ $employee->jabatan ?? '-' }}</p>
                <span class="badge bg-primary">{{ $employee->departemen ?? '-' }}</span>
                <hr>
                <div class="small text-muted">
                    <div class="d-flex gap-2 mb-1"><i class="bi bi-person-badge"></i>{{ $employee->nip ?? '-' }}</div>
                    <div class="d-flex gap-2 mb-1"><i class="bi bi-calendar3"></i>Periode {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">{{ $payroll ? 'Edit' : 'Tambah' }} Rincian Gaji</h6>
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

                <form method="POST" action="{{ route('web.payrolls.store') }}">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <input type="hidden" name="periode" value="{{ $periode }}">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Gaji Pokok <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="gaji_pokok" class="form-control"
                                    value="{{ old('gaji_pokok', $payroll->gaji_pokok ?? '') }}" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-light border small mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Jumlah hari hadir bulan ini: <strong>{{ $jumlahHadir }}</strong>
                                (status hadir + terlambat). Tunjangan di bawah = tarif <strong>per hari</strong>.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tunjangan (per hari)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="tunjangan" class="form-control"
                                    value="{{ old('tunjangan', $payroll->tunjangan ?? 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Uang Makan (per hari)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="uang_makan" class="form-control"
                                    value="{{ old('uang_makan', $payroll->uang_makan ?? 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Uang Transport (per hari)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="uang_transport" class="form-control"
                                    value="{{ old('uang_transport', $payroll->uang_transport ?? 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tunjangan Jabatan (per hari)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="tunjangan_jabatan" class="form-control"
                                    value="{{ old('tunjangan_jabatan', $payroll->tunjangan_jabatan ?? 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Bonus</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="bonus" class="form-control"
                                    value="{{ old('bonus', $payroll->bonus ?? 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Potongan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="potongan" class="form-control"
                                    value="{{ old('potongan', $payroll->potongan ?? '') }}" min="0" step="0.01"
                                    placeholder="Kosongkan untuk hitung otomatis dari absensi">
                            </div>
                            <div class="form-text">Kosong = hitung otomatis dari absensi bulan ini.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $payroll->keterangan ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </button>
                        <a href="{{ route('web.payrolls.index', ['periode' => $periode]) }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection