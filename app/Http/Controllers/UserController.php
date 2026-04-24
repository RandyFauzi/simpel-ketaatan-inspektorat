<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(): View
    {
        $users = User::orderBy('role')->latest()->paginate(15);
        return view('users-index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $opds = Opd::orderBy('nama_opd')->get();
        return view('users-form', compact('opds'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'inspektur_daerah', 'inspektur_pembantu_1', 'ketua_tim', 'auditor', 'skpd'])],
            'tim' => ['nullable', 'required_if:role,ketua_tim,auditor', Rule::in(['tim_1', 'tim_2'])],
            'opd_id' => ['nullable', 'required_if:role,skpd', 'exists:opds,id'],
            'nip' => 'nullable|string|max:50|unique:users',
            'jabatan' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Nama pengguna ini sudah terdaftar/digunakan dalam sistem.',
            'email.unique' => 'Email akses ini sudah terdaftar/digunakan dalam sistem.',
            'nip.unique' => 'NIP/Nomor Pegawai ini sudah terdaftar.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'tim' => in_array($request->role, ['ketua_tim', 'auditor']) ? $request->tim : null,
            'opd_id' => $request->role === 'skpd' ? $request->opd_id : null,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
        ]);

        return redirect()->route('users.index')->with('success', 'Akun Pengguna berhasil didaftarkan ke sistem.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $opds = Opd::orderBy('nama_opd')->get();
        return view('users-form', compact('user', 'opds'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'inspektur_daerah', 'inspektur_pembantu_1', 'ketua_tim', 'auditor', 'skpd'])],
            'tim' => ['nullable', 'required_if:role,ketua_tim,auditor', Rule::in(['tim_1', 'tim_2'])],
            'opd_id' => ['nullable', 'required_if:role,skpd', 'exists:opds,id'],
            'password' => 'nullable|string|min:8',
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'jabatan' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Nama pengguna ini sudah terdaftar/digunakan dalam sistem.',
            'email.unique' => 'Email akses ini sudah terdaftar/digunakan dalam sistem.',
            'nip.unique' => 'NIP/Nomor Pegawai ini sudah terdaftar.',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'tim' => in_array($request->role, ['ketua_tim', 'auditor']) ? $request->tim : null,
            'opd_id' => $request->role === 'skpd' ? $request->opd_id : null,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data Akun Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['message' => 'Penolakan Akses: Anda tidak dapat menghapus akun Anda sendiri.']);
        }
        
        // Disable instead of hard deleting to preserve audit history via SoftDeletes
        $user->delete();
        
        return redirect()->route('users.index')->with('success', 'Akses Pengguna berhasil dinonaktifkan secara permanen.');
    }
}
