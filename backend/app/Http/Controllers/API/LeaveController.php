<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth()->user()->employee;

        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $leaves,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis' => 'required|in:izin,sakit,cuti',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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

        $dokumenPath = null;
        if ($request->hasFile('dokumen')) {
            $dokumenPath = $request->file('dokumen')->store('izin', 'public');
        }

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'dokumen' => $dokumenPath,
            'status' => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan berhasil diajukan',
            'data' => $leave,
        ], 201);
    }

    public function show($id)
    {
        $employee = auth()->user()->employee;

        $leave = LeaveRequest::where('employee_id', $employee->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $leave,
        ]);
    }
}
