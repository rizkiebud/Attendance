<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    use FilterByDepartemen;

    public function index(Request $request)
    {
        $periode = $request->periode ?: now()->format('Y-m'); // contoh: 2026-08

        $query = $this->filterEmployeeQuery(Employee::query())
            ->with(['payrolls' => fn($q) => $q->where('periode', $periode . '-01')]);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                    ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        $employees = $query->orderBy('nama')->paginate(15)->withQueryString();

        return view('web.payrolls.index', compact('employees', 'periode'));
    }

    public function create(Request $request)
    {
        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $periode = $request->periode ?: now()->format('Y-m');
        $employee = Employee::findOrFail($request->employee_id);
        $payroll = Payroll::where('employee_id', $employee->id)->where('periode', $periode . '-01')->first();
        $jumlahHadir = Payroll::hitungJumlahHadir($employee->id, $periode . '-01');

        return view('web.payrolls.create', compact('employee', 'periode', 'payroll', 'jumlahHadir'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'periode' => 'required|date_format:Y-m',
            'gaji_pokok' => 'required|numeric|min:0',
            'tunjangan' => 'nullable|numeric|min:0',
            'uang_makan' => 'nullable|numeric|min:0',
            'uang_transport' => 'nullable|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'gaji_pokok' => $request->gaji_pokok,
            'tunjangan' => $request->tunjangan ?? 0,
            'uang_makan' => $request->uang_makan ?? 0,
            'uang_transport' => $request->uang_transport ?? 0,
            'tunjangan_jabatan' => $request->tunjangan_jabatan ?? 0,
            'jumlah_hadir' => Payroll::hitungJumlahHadir($request->employee_id, $request->periode . '-01'),
            'bonus' => $request->bonus ?? 0,
            'keterangan' => $request->keterangan,
            'created_by' => Auth::id(),
        ];

        // Potongan: jika kosong/biarkan auto, hitung dari absensi bulan berjalan
        if ($request->filled('potongan')) {
            $data['potongan'] = $request->potongan;
        } else {
            $data['potongan'] = Payroll::hitungPotonganDariAbsensi(
                $request->employee_id,
                $request->periode . '-01',
                $request->gaji_pokok
            );
        }

        Payroll::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'periode' => $request->periode . '-01',
            ],
            $data
        );

        return redirect()->route('web.payrolls.index', ['periode' => $request->periode])
            ->with('success', 'Data gaji berhasil disimpan');
    }

    public function markPaid(Request $request, Payroll $payroll)
    {
        $payroll->update([
            'status' => 'lunas',
            'tanggal_bayar' => now()->toDateString(),
        ]);

        return back()->with('success', 'Gaji ' . $payroll->employee->nama . ' ditandai lunas');
    }

    public function destroy(Payroll $payroll)
    {
        $periode = $payroll->periode;
        $payroll->delete();

        return redirect()->route('web.payrolls.index', ['periode' => $periode->format('Y-m')])
            ->with('success', 'Data gaji berhasil dihapus');
    }
}