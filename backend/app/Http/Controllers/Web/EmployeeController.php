<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('user');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%")
                    ->orWhere('departemen', 'like', "%{$search}%");
            });
        }

        if ($request->departemen) {
            $query->where('departemen', $request->departemen);
        }

        if ($request->aktif !== null) {
            $query->where('aktif', $request->aktif);
        }

        $employees = $query->orderBy('nama')->paginate(15)->withQueryString();

        $departemens = Employee::distinct()->pluck('departemen')->filter()->sort()->values();

        return view('web.employees.index', compact('employees', 'departemens'));
    }

    public function create()
    {
        return view('web.employees.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'nullable|string|unique:employees,nip',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'jabatan' => 'nullable|string|max:255',
            'departemen' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama,
                'email' => $request->email,
                'password' => $request->password,
                'role' => 'karyawan',
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('karyawan/foto', 'public');
            }

            Employee::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama' => $request->nama,
                'jabatan' => $request->jabatan,
                'departemen' => $request->departemen,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'foto' => $fotoPath,
                'aktif' => true,
            ]);
        });

        return redirect()->route('web.employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'attendances' => function ($q) {
            $q->with('office')->orderBy('tanggal', 'desc')->take(10);
        }, 'leaveRequests' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(5);
        }]);

        $thisMonth = now();
        $monthlyStats = $employee->attendances()
            ->whereYear('tanggal', $thisMonth->year)
            ->whereMonth('tanggal', $thisMonth->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('web.employees.show', compact('employee', 'monthlyStats'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        return view('web.employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'nullable|string|unique:employees,nip,' . $employee->id,
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'departemen' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
            'aktif' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $fotoPath = $employee->foto;
        if ($request->hasFile('foto')) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto')->store('karyawan/foto', 'public');
        }

        $employee->update([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'departemen' => $request->departemen,
            'telepon' => $request->telepon,
            'alamat' => $request->alamat,
            'foto' => $fotoPath,
            'aktif' => $request->boolean('aktif', true),
        ]);

        $employee->user->update(['name' => $request->nama]);

        return redirect()->route('web.employees.show', $employee)
            ->with('success', 'Data karyawan berhasil diperbarui');
    }

    public function toggleAktif(Employee $employee)
    {
        $employee->update(['aktif' => !$employee->aktif]);

        $status = $employee->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('web.employees.index')
            ->with('success', "Karyawan {$employee->nama} berhasil {$status}");
    }

    public function destroy(Employee $employee)
    {
        $employee->user()->delete();
        return redirect()->route('web.employees.index')
            ->with('success', 'Karyawan berhasil dihapus');
    }
}
