<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use FilterByDepartemen;

    public function index()
    {
        $today = today();
        $dep = $this->departemenFilter();

        $totalKaryawan = $this->filterEmployeeQuery(Employee::where('aktif', true))->count();

        $absensiHariIni = $this->filterAttendanceQuery(Attendance::whereDate('tanggal', $today));
        $hadirHariIni = (clone $absensiHariIni)->whereIn('status', ['hadir', 'terlambat'])->count();
        $terlambatHariIni = (clone $absensiHariIni)->where('status', 'terlambat')->count();
        // Tidak hadir = total karyawan dikurangi hadir, terlambat, izin & sakit.
        $tidakHadirHariIni = $totalKaryawan
            - $hadirHariIni
            - (clone $absensiHariIni)->whereIn('status', ['izin', 'sakit'])->count();

        // Absensi bulan ini
        $absensBulanIni = $this->filterAttendanceQuery(
            Attendance::whereYear('tanggal', now()->year)->whereMonth('tanggal', now()->month)
        );
        $totalHadir = (clone $absensBulanIni)->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalTerlambat = (clone $absensBulanIni)->where('status', 'terlambat')->count();
        $totalIzin = (clone $absensBulanIni)->whereIn('status', ['izin', 'sakit'])->count();

        // Permohonan menunggu
        $permohonanMenunggu = $this->filterLeaveQuery(LeaveRequest::where('status', 'menunggu'))->count();

        // Data absensi hari ini
        $absensiToday = $this->filterAttendanceQuery(
            Attendance::with(['employee.user'])->whereDate('tanggal', $today)
        )->orderBy('jam_masuk', 'desc')->take(10)->get();

        // Chart data - 7 hari terakhir
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $hadir = $this->filterAttendanceQuery(Attendance::whereDate('tanggal', $date))
                ->whereIn('status', ['hadir', 'terlambat'])->count();
            $terlambat = $this->filterAttendanceQuery(Attendance::whereDate('tanggal', $date))
                ->where('status', 'terlambat')->count();
            $chartData[] = [
                'tanggal' => $date->format('d/m'),
                'hadir' => $hadir,
                'terlambat' => $terlambat,
            ];
        }

        return view('web.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'terlambatHariIni',
            'tidakHadirHariIni',
            'totalHadir',
            'totalTerlambat',
            'totalIzin',
            'permohonanMenunggu',
            'absensiToday',
            'chartData'
        ));
    }
}
