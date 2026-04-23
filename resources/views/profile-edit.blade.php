@extends('layouts.app-layout')
@section('title', 'Profil Saya')

@section('content')
<div class="max-w-3xl mx-auto pt-4">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Profil</h1>
        <p class="text-slate-500 mt-1">Kelola data diri dan ubah password akun Anda.</p>
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

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm relative mb-8">
        <div class="p-8">
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-2xl shadow-lg shadow-blue-500/30">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $user->name }}</h2>
                    <p class="text-slate-500">{{ $user->email }}</p>
                    <div class="mt-2 text-xs font-bold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg inline-flex items-center gap-1.5 uppercase tracking-wider">
                        <x-lucide-shield class="w-3.5 h-3.5" /> {{ str_replace('_', ' ', $user->role) }}
                    </div>
                </div>
            </div>

            @if($user->role === 'skpd')
            <div class="mb-2">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Instansi Terdaftar</label>
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-sm font-semibold text-slate-700 flex items-center gap-3">
                    <x-lucide-building-2 class="w-5 h-5 text-slate-400" />
                    {{ $user->opd->nama_opd ?? 'Belum ada instansi yang dipetakan' }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm relative">
        <div class="p-8">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2 mb-6">
                <x-lucide-key class="w-5 h-5 text-amber-500" /> Ganti Password
            </h2>
            
            <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Password Saat Ini</label>
                    <input type="password" name="current_password" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-medium text-slate-700">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Password Baru</label>
                        <input type="password" name="new_password" required minlength="8" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-medium text-slate-700">
                        <p class="text-[10px] text-slate-400 mt-1.5">Minimal 8 karakter.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" required minlength="8" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-medium text-slate-700">
                    </div>
                </div>

                <div class="pt-4 mt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                        <x-lucide-save class="w-4 h-4" /> Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
