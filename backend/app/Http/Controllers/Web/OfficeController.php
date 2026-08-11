<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::orderBy('nama')->paginate(10);
        return view('web.offices.index', compact('offices'));
    }

    public function create()
    {
        return view('web.offices.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:5000',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'toleransi_terlambat' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Office::create($request->only([
            'nama', 'alamat', 'latitude', 'longitude', 'radius',
            'jam_masuk', 'jam_keluar', 'toleransi_terlambat',
        ]) + ['aktif' => true]);

        return redirect()->route('web.offices.index')
            ->with('success', 'Kantor berhasil ditambahkan');
    }

    public function edit(Office $office)
    {
        return view('web.offices.edit', compact('office'));
    }

    public function update(Request $request, Office $office)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:5000',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'toleransi_terlambat' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $office->update($request->only([
            'nama', 'alamat', 'latitude', 'longitude', 'radius',
            'jam_masuk', 'jam_keluar', 'toleransi_terlambat',
        ]) + ['aktif' => $request->has('aktif') ? $request->boolean('aktif') : true]);

        return redirect()->route('web.offices.index')
            ->with('success', 'Kantor berhasil diperbarui');
    }

    public function destroy(Office $office)
    {
        $office->delete();
        return redirect()->route('web.offices.index')
            ->with('success', 'Kantor berhasil dihapus');
    }
}
