<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'office_id' => 'required|exists:offices,id',
            'foto' => 'nullable|image|max:5120',
            'device_info' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        // Cek apakah sudah absen hari ini
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('tanggal', today())
            ->first();

        if ($existingAttendance && $existingAttendance->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk hari ini',
            ], 422);
        }

        $office = Office::findOrFail($request->office_id);
        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;
        $jarak = $office->hitungJarak($lat, $lng);

        // Cek apakah dalam radius
        if (!$office->dalamRadius($lat, $lng)) {
            return response()->json([
                'success' => false,
                'message' => "Anda berada di luar radius kantor. Jarak Anda: " . round($jarak) . " meter, radius maksimal: " . $office->radius . " meter",
                'data' => [
                    'jarak' => round($jarak),
                    'radius_kantor' => $office->radius,
                ],
            ], 422);
        }

        // Tentukan status (hadir/terlambat)
        $jamMasukKantor = Carbon::parse($office->jam_masuk);
        $toleransi = Carbon::parse($office->toleransi_terlambat);
        $batasWaktu = $jamMasukKantor->copy()->addHours($toleransi->hour)->addMinutes($toleransi->minute);
        $sekarang = Carbon::now();

        $status = $sekarang->gt($batasWaktu) ? 'terlambat' : 'hadir';

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absensi/masuk/' . today()->format('Y-m'), 'public');
        }

        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'tanggal' => today(),
            ],
            [
                'office_id' => $office->id,
                'jam_masuk' => $sekarang->format('H:i:s'),
                'lat_masuk' => $lat,
                'lng_masuk' => $lng,
                'jarak_masuk' => round($jarak, 2),
                'foto_masuk' => $fotoPath,
                'status' => $status,
                'device_info' => $request->device_info,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $status === 'terlambat'
                ? 'Absen masuk berhasil - Terlambat'
                : 'Absen masuk berhasil',
            'data' => [
                'attendance' => $attendance->load('office'),
                'status' => $status,
                'jarak' => round($jarak),
                'jam_masuk' => $sekarang->format('H:i:s'),
            ],
        ]);
    }

    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'foto' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('tanggal', today())
            ->first();

        if (!$attendance || !$attendance->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan absen masuk hari ini',
            ], 422);
        }

        if ($attendance->jam_keluar) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen keluar hari ini',
            ], 422);
        }

        $office = $attendance->office;
        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;
        $jarak = $office->hitungJarak($lat, $lng);

        // Validasi radius untuk keluar
        if (!$office->dalamRadius($lat, $lng)) {
            return response()->json([
                'success' => false,
                'message' => "Anda berada di luar radius kantor. Jarak Anda: " . round($jarak) . " meter",
                'data' => [
                    'jarak' => round($jarak),
                    'radius_kantor' => $office->radius,
                ],
            ], 422);
        }

        $sekarang = Carbon::now();

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absensi/keluar/' . today()->format('Y-m'), 'public');
        }

        $attendance->update([
            'jam_keluar' => $sekarang->format('H:i:s'),
            'lat_keluar' => $lat,
            'lng_keluar' => $lng,
            'jarak_keluar' => round($jarak, 2),
            'foto_keluar' => $fotoPath,
        ]);

        $attendance->refresh()->load('office');

        return response()->json([
            'success' => true,
            'message' => 'Absen keluar berhasil',
            'data' => [
                'attendance' => $attendance,
                'jam_keluar' => $sekarang->format('H:i:s'),
                'durasi_kerja' => $attendance->durasi_kerja,
            ],
        ]);
    }

    public function today(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        $attendance = Attendance::with('office')
            ->where('employee_id', $employee->id)
            ->whereDate('tanggal', today())
            ->first();

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }

    public function history(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $attendances = Attendance::with('office')
            ->where('employee_id', $employee->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'desc')
            ->get();

        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'tidak_hadir' => $attendances->where('status', 'tidak_hadir')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'attendances' => $attendances,
                'summary' => $summary,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ],
        ]);
    }

    public function offices()
    {
        $offices = Office::where('aktif', true)->get();
        return response()->json([
            'success' => true,
            'data' => $offices,
        ]);
    }
}
