<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        $today = today();
        $thisMonth = now();

        // Absensi hari ini
        $todayAttendance = Attendance::with('office')
            ->where('employee_id', $employee->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Rekap bulan ini
        $monthlyAttendances = Attendance::where('employee_id', $employee->id)
            ->whereYear('tanggal', $thisMonth->year)
            ->whereMonth('tanggal', $thisMonth->month)
            ->get();

        $summary = [
            'hadir' => $monthlyAttendances->whereIn('status', ['hadir', 'terlambat'])->count(),
            'terlambat' => $monthlyAttendances->where('status', 'terlambat')->count(),
            'tidak_hadir' => $monthlyAttendances->where('status', 'tidak_hadir')->count(),
            'izin_sakit' => $monthlyAttendances->whereIn('status', ['izin', 'sakit'])->count(),
        ];

        // Riwayat 7 hari terakhir
        $recentAttendances = Attendance::with('office')
            ->where('employee_id', $employee->id)
            ->where('tanggal', '>=', now()->subDays(7))
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => $employee->load('user'),
                'today_attendance' => $todayAttendance,
                'monthly_summary' => $summary,
                'recent_attendances' => $recentAttendances,
                'current_time' => now()->format('H:i:s'),
                'current_date' => $today->format('d F Y'),
            ],
        ]);
    }
}
