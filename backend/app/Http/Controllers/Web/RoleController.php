<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('level')->orderBy('name')->get();
        return view('web.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('web.roles.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'label' => 'required|string|max:255',
            'level' => 'required|in:view,manage,full',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Role::create($request->only('name', 'label', 'level'));

        return redirect()->route('web.roles.index')
            ->with('success', 'Role berhasil ditambahkan');
    }

    public function edit(Role $role)
    {
        $users = User::orderBy('name')->get();
        return view('web.roles.edit', compact('role', 'users'));
    }

    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'label' => 'required|string|max:255',
            'level' => 'required|in:view,manage,full',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $role->update($request->only('name', 'label', 'level'));

        $selected = $request->input('users', []);
        DB::transaction(function () use ($role, $selected) {
            // Lepaskan role lama, lalu berikan ke user terpilih
            // Kolom `role` dibatasi enum ['admin','karyawan']; akses web via `role_id`.
            User::where('role_id', $role->id)->update(['role_id' => null, 'role' => 'karyawan']);
            User::whereIn('id', $selected)->update(['role_id' => $role->id, 'role' => 'karyawan']);
        });

        return redirect()->route('web.roles.index')
            ->with('success', 'Role berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'Role admin tidak dapat dihapus.');
        }

        User::where('role_id', $role->id)->update(['role_id' => null, 'role' => 'karyawan']);
        $role->delete();

        return redirect()->route('web.roles.index')
            ->with('success', 'Role berhasil dihapus');
    }
}