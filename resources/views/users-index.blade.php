@extends('layouts.app-layout')
@section('title', 'Manajemen Pengguna')

@section('content')

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-2">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Akun</h1>
        <p class="text-slate-500 mt-1">Kelola data Auditor, Inspektur Pembantu I, dan Instansi SKPD.</p>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-[0_8px_20px_rgba(37,99,235,0.3)] hover:-translate-y-1 hover:shadow-[0_12px_25px_rgba(37,99,235,0.4)] transition-all">
            <x-lucide-user-plus class="w-4 h-4" /> Tambah Pengguna
        </a>
    </div>
</div>

@if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-start gap-3 shadow-sm">
        <x-lucide-alert-circle class="w-5 h-5 shrink-0 mt-0.5" />
        <div>
            <h3 class="font-bold">Peringatan!</h3>
            <ul class="list-disc list-inside text-sm mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm relative">
    <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent pointer-events-none"></div>
    
    <div class="overflow-x-auto relative z-10 p-2">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-400 uppercase font-black bg-slate-50 border-b border-slate-100">
                <tr>
                    <th scope="col" class="px-6 py-4 w-16 text-center">NO</th>
                    <th scope="col" class="px-6 py-4">INFORMASI AKUN</th>
                    <th scope="col" class="px-6 py-4">ROLE (PERAN)</th>
                    <th scope="col" class="px-6 py-4">PENUGASAN TIM</th>
                    <th scope="col" class="px-6 py-4 text-right">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-6 py-5 text-center text-slate-400 font-medium">
                        {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-600 shrink-0">
                                {{ substr($u->name, 0, 1) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800">{{ $u->name }}</span>
                                <span class="text-xs text-slate-500">{{ $u->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        @php
                            $roleColors = [
                                'admin' => 'bg-slate-100 text-slate-700 border-slate-200',
                                'inspektur_daerah' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'inspektur_pembantu_1' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'ketua_tim' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'auditor' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'skpd' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            ];
                            $c = $roleColors[$u->role] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                            $icons = [
                                'admin' => 'crown',
                                'inspektur_daerah' => 'shield-check',
                                'inspektur_pembantu_1' => 'eye',
                                'ketua_tim' => 'users',
                                'auditor' => 'briefcase',
                                'skpd' => 'building-2',
                            ];
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg border {{ $c }}">
                            <x-dynamic-component :component="'lucide-' . ($icons[$u->role] ?? 'user')" class="w-3 h-3" />
                            {{ str_replace('_', ' ', $u->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-5">
                        @if(in_array($u->role, ['ketua_tim', 'auditor']))
                            <span class="text-xs text-slate-600 font-semibold uppercase">{{ str_replace('_', ' ', $u->tim ?? 'Belum dipetakan') }}</span>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('users.edit', $u->id) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all tooltip" title="Edit Akun">
                                <x-lucide-edit class="w-4 h-4" />
                            </a>
                            
                            @if(auth()->id() !== $u->id)
                            <form action="{{ route('users.destroy', $u->id) }}" method="POST" x-data @submit.prevent="Swal.fire({
                                title: 'Hapus Akun?',
                                text: 'Apakah Anda yakin ingin menonaktifkan akun ini secara permanen?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#ef4444',
                                cancelButtonColor: '#94a3b8',
                                confirmButtonText: 'Ya, Hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => { if (result.isConfirmed) $el.submit() })" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all tooltip" title="Hapus Akun">
                                    <x-lucide-trash-2 class="w-4 h-4" />
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-400">
                        <div class="flex flex-col items-center justify-center">
                            <x-lucide-users class="w-12 h-12 mb-3 text-slate-300" />
                            <p class="text-lg font-bold text-slate-500">Tidak Ada Data</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($users->hasPages())
<div class="mt-6 flex justify-end">
    {{ $users->links() }}
</div>
@endif

@endsection
