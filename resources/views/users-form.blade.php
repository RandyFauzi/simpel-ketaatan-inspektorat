@extends('layouts.app-layout')
@section('title', isset($user) ? 'Edit Pengguna' : 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-4xl mx-auto pt-4">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('users.index') }}" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-blue-600 transition-colors shadow-sm">
            <x-lucide-arrow-left class="w-5 h-5" />
        </a>
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ isset($user) ? 'Edit Pengguna' : 'Pendaftaran Pengguna' }}</h1>
            <p class="text-slate-500 mt-1">{{ isset($user) ? 'Perbarui informasi dan hak akses akun.' : 'Tambahkan akun baru untuk Auditor, Irban, atau Instansi.' }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-start gap-3 shadow-sm">
            <x-lucide-alert-circle class="w-5 h-5 shrink-0 mt-0.5" />
            <div>
                <h3 class="font-bold">Gagal Menyimpan</h3>
                <ul class="list-disc list-inside text-sm mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm relative">
        <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST" class="p-8 space-y-6" x-data="{ role: '{{ old('role', $user->role ?? 'auditor') }}' }">
            @csrf
            @if(isset($user)) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- NAMA -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required placeholder="Masukkan nama lengkap pegwai..."
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-semibold text-slate-700">
                </div>

                <!-- EMAIL -->
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Email Akses <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required placeholder="email@audit.or.id"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-semibold text-slate-700">
                </div>

                <!-- NIP -->
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">NIP / Nomor Pegawai</label>
                    <input type="text" name="nip" value="{{ old('nip', $user->nip ?? '') }}" placeholder="Contoh: 19830223..."
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-semibold text-slate-700">
                </div>

                <!-- JABATAN -->
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Jabatan Fungsional</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan ?? '') }}" placeholder="Contoh: Auditor Ahli Muda"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-semibold text-slate-700">
                </div>

                <!-- PASSWORD -->
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">
                        Kata Sandi 
                        @if(!isset($user)) <span class="text-red-500">*</span> @else <span class="text-amber-500 font-normal ml-1">(Kosongkan jika tidak ingin diubah)</span> @endif
                    </label>
                    <input type="password" name="password" {{ !isset($user) ? 'required' : '' }} minlength="8" placeholder="Minimal 8 karakter"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-medium text-slate-700">
                </div>

                <!-- ROLE -->
                <div class="md:col-span-2 mt-2 pt-6 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-600 mb-3">Tingkat Kewenangan (Role) <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="admin" x-model="role" class="peer sr-only">
                            <div class="p-4 bg-white border-2 border-slate-200 rounded-2xl hover:bg-slate-50 peer-checked:border-slate-800 peer-checked:bg-slate-100 transition-all text-center">
                                <x-lucide-crown class="w-6 h-6 mx-auto mb-2 text-slate-400 peer-checked:text-slate-800" />
                                <span class="block text-xs font-bold text-slate-700 peer-checked:text-slate-900 uppercase tracking-wide">Admin</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="inspektur_daerah" x-model="role" class="peer sr-only">
                            <div class="p-4 bg-white border-2 border-slate-200 rounded-2xl hover:bg-slate-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all text-center">
                                <x-lucide-shield-check class="w-6 h-6 mx-auto mb-2 text-slate-400 peer-checked:text-emerald-600" />
                                <span class="block text-xs font-bold text-slate-700 peer-checked:text-emerald-700 uppercase tracking-wide">Inspektur Daerah</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="inspektur_pembantu_1" x-model="role" class="peer sr-only">
                            <div class="p-4 bg-white border-2 border-slate-200 rounded-2xl hover:bg-slate-50 peer-checked:border-amber-500 peer-checked:bg-amber-50 transition-all text-center">
                                <x-lucide-eye class="w-6 h-6 mx-auto mb-2 text-slate-400 peer-checked:text-amber-600" />
                                <span class="block text-xs font-bold text-slate-700 peer-checked:text-amber-700 uppercase tracking-wide">Irban I</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="ketua_tim" x-model="role" class="peer sr-only">
                            <div class="p-4 bg-white border-2 border-slate-200 rounded-2xl hover:bg-slate-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all text-center">
                                <x-lucide-users class="w-6 h-6 mx-auto mb-2 text-slate-400 peer-checked:text-blue-600" />
                                <span class="block text-xs font-bold text-slate-700 peer-checked:text-blue-700 uppercase tracking-wide">Ketua Tim</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="auditor" x-model="role" class="peer sr-only">
                            <div class="p-4 bg-white border-2 border-slate-200 rounded-2xl hover:bg-slate-50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all text-center">
                                <x-lucide-briefcase class="w-6 h-6 mx-auto mb-2 text-slate-400 peer-checked:text-indigo-600" />
                                <span class="block text-xs font-bold text-slate-700 peer-checked:text-indigo-700 uppercase tracking-wide">Auditor</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="skpd" x-model="role" class="peer sr-only">
                            <div class="p-4 bg-white border-2 border-slate-200 rounded-2xl hover:bg-slate-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all text-center">
                                <x-lucide-building-2 class="w-6 h-6 mx-auto mb-2 text-slate-400 peer-checked:text-emerald-600" />
                                <span class="block text-xs font-bold text-slate-700 peer-checked:text-emerald-700 uppercase tracking-wide">SKPD</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- TIM SELECTION (ONLY FOR KETUA_TIM & AUDITOR) -->
                <div class="md:col-span-2" x-show="['ketua_tim', 'auditor'].includes(role)" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Penugasan Tim Pokok <span class="text-red-500">*</span></label>
                    <select name="tim" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-semibold text-slate-700 select2">
                        <option value="">-- Pilih Tim (Tim 1 / Tim 2) --</option>
                        <option value="tim_1" {{ old('tim', $user->tim ?? '') == 'tim_1' ? 'selected' : '' }}>Tim 1</option>
                        <option value="tim_2" {{ old('tim', $user->tim ?? '') == 'tim_2' ? 'selected' : '' }}>Tim 2</option>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1.5"><x-lucide-info class="w-3 h-3 inline" /> Wajib dipilih jika pengguna adalah Ketua Tim atau Auditor.</p>
                </div>

                <!-- OPD SELECTION (ONLY FOR SKPD) -->
                <div class="md:col-span-2" x-show="role === 'skpd'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Instansi OPD <span class="text-red-500">*</span></label>
                    <select name="opd_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-semibold text-slate-700">
                        <option value="">-- Pilih OPD --</option>
                        @foreach(($opds ?? collect()) as $opd)
                            <option value="{{ $opd->id }}" {{ old('opd_id', $user->opd_id ?? '') == $opd->id ? 'selected' : '' }}>{{ $opd->nama_opd }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1.5"><x-lucide-info class="w-3 h-3 inline" /> Wajib dipilih jika role pengguna adalah SKPD.</p>
                </div>
            </div>

            <div class="pt-6 mt-8 border-t border-slate-100 flex justify-end gap-3">
                <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm shadow-blue-200">
                    <x-lucide-save class="w-4 h-4" /> {{ isset($user) ? 'Simpan Perubahan' : 'Daftarkan Pengguna' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
