<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use FilterByDepartemen;

    public function index(Request $request)
    {
        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal) : today();

        $query = $this->filterAttendanceQuery(
            Attendance::with(['employee.user', 'office'])->whereDate('tanggal', $tanggal)
        );

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->departemen) {
            $query->whereHas('employee', fn($q) => $q->where('departemen', $request->departemen));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('jam_masuk', 'asc')->paginate(20)->withQueryString();

        $employees = $this->filterEmployeeQuery(Employee::where('aktif', true))->orderBy('nama')->get();

        $departemens = $this->filterEmployeeQuery(Employee::query())->distinct()->pluck('departemen')->filter()->sort()->values();

        $dep = $this->departemenFilter();
        $stats = [
            'hadir' => $this->filterAttendanceQuery(Attendance::whereDate('tanggal', $tanggal))->whereIn('status', ['hadir', 'terlambat'])->count(),
            'terlambat' => $this->filterAttendanceQuery(Attendance::whereDate('tanggal', $tanggal))->where('status', 'terlambat')->count(),
            'izin' => $this->filterAttendanceQuery(Attendance::whereDate('tanggal', $tanggal))->whereIn('status', ['izin', 'sakit'])->count(),
            'tidak_hadir' => $this->filterEmployeeQuery(Employee::where('aktif', true))->count()
                - $this->filterAttendanceQuery(Attendance::whereDate('tanggal', $tanggal))->whereIn('status', ['hadir', 'terlambat', 'izin', 'sakit'])->count(),
        ];

        return view('web.attendances.index', compact('attendances', 'employees', 'departemens', 'tanggal', 'stats'));
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['employee.user', 'office']);
        return view('web.attendances.show', compact('attendance'));
    }

    public function laporan(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $employeeId = $request->employee_id;

        $query = $this->filterAttendanceQuery(
            Attendance::with(['employee.user', 'office'])
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
        );

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $attendances = $query->orderBy('tanggal', 'desc')->orderBy('employee_id')->get();

        // Rekap per karyawan
        $rekapKaryawan = $attendances->groupBy('employee_id')->map(function ($items, $employeeId) {
            $employee = $items->first()->employee;
            return [
                'employee' => $employee,
                'hadir' => $items->where('status', 'hadir')->count(),
                'terlambat' => $items->where('status', 'terlambat')->count(),
                'tidak_hadir' => $items->where('status', 'tidak_hadir')->count(),
                'izin' => $items->where('status', 'izin')->count(),
                'sakit' => $items->where('status', 'sakit')->count(),
                'total' => $items->count(),
            ];
        })->values();

        $employees = $this->filterEmployeeQuery(Employee::where('aktif', true))->orderBy('nama')->get();

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $tanggalAwal = Carbon::create($tahun, $bulan, 1);
        $tanggalAkhir = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        return view('web.attendances.laporan', compact(
            'attendances', 'rekapKaryawan', 'employees', 'bulan', 'tahun', 'months', 'tanggalAwal', 'tanggalAkhir'
        ));
    }

    public function exportCsv(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $attendances = $this->filterAttendanceQuery(
            Attendance::with(['employee', 'office'])
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
        )->orderBy('tanggal')->orderBy('employee_id')->get();

        $filename = "absensi_{$bulan}_{$tahun}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NIP', 'Nama', 'Departemen', 'Tanggal', 'Jam Masuk', 'Jam Keluar', 'Status', 'Jarak Masuk (m)', 'Durasi Kerja']);

            foreach ($attendances as $a) {
                fputcsv($file, [
                    $a->employee->nip ?? '-',
                    $a->employee->nama,
                    $a->employee->departemen ?? '-',
                    $a->tanggal->format('d/m/Y'),
                    $a->jam_masuk ?? '-',
                    $a->jam_keluar ?? '-',
                    ucfirst(str_replace('_', ' ', $a->status)),
                    $a->jarak_masuk ?? '-',
                    $a->durasi_kerja ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
