@extends('layouts.app-layout')
@section('title', 'Direktori Laporan Hasil Pemeriksaan (LHP)')

@section('content')

<!-- Header Area -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-2">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Daftar Laporan LHP</h1>
        <p class="text-slate-500 mt-1">Direktori sentral arsip Laporan Hasil Pemeriksaan Inspektorat Daerah.</p>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3">
        <!-- Search Bar -->
        <form action="{{ route('lhp.index') }}" method="GET" class="relative group w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                <x-lucide-search class="w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
            </div>
            <input type="text" name="search" value="{{ request('search') }}" 
                   class="w-full bg-white border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 block pl-10 px-4 py-2.5 shadow-sm transition-all" 
                   placeholder="Cari Nomor, Judul, OPD...">
            @if(request('search'))
            <a href="{{ route('lhp.index') }}" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-red-500 transition-colors">
                <x-lucide-x-circle class="w-4 h-4" />
            </a>
            @endif
        </form>

        @if(auth()->user()->role !== 'skpd')
        <!-- Add Button for Admin/Auditor -->
        <a href="{{ route('auditor.lhp.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-[0_8px_20px_rgba(37,99,235,0.3)] hover:-translate-y-1 hover:shadow-[0_12px_25px_rgba(37,99,235,0.4)] transition-all">
            <x-lucide-plus class="w-4 h-4" /> Buat LHP Baru
        </a>
        @endif
    </div>
</div>

<!-- Data Table Card -->
<div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm relative">
    <!-- Soft Inner Glow -->
    <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent pointer-events-none"></div>
    
    <div class="overflow-x-auto relative z-10">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-400 uppercase font-black bg-slate-50 border-b border-slate-100">
                <tr>
                    <th scope="col" class="px-6 py-4 w-16 text-center">NO</th>
                    <th scope="col" class="px-6 py-4">INFORMASI LHP</th>
                    <th scope="col" class="px-6 py-4">OPD / INSTANSI</th>
                    <th scope="col" class="px-6 py-4 text-center">STATUS</th>
                    <th scope="col" class="px-6 py-4 text-right">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($lhps as $index => $lhp)
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-6 py-5 text-center text-slate-400 font-medium">
                        {{ $lhps->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-col gap-1.5">
                            <span class="inline-flex items-center gap-1.5 font-black text-blue-700 bg-blue-50 w-max px-2.5 py-0.5 rounded border border-blue-100 text-[11px] tracking-wide">
                                <x-lucide-file-digit class="w-3.5 h-3.5 text-blue-500" /> {{ $lhp->nomor_lhp }}
                            </span>
                            <span class="font-bold text-slate-800 line-clamp-2 leading-snug">{{ $lhp->judul }}</span>
                            <span class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                                <x-lucide-calendar class="w-3.5 h-3.5" /> TA {{ $lhp->tahun_anggaran }} • {{ \Carbon\Carbon::parse($lhp->tgl_lhp)->translatedFormat('d F Y') }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0">
                                <x-lucide-building-2 class="w-5 h-5 text-slate-400" />
                            </div>
                            <span class="font-bold text-slate-700">{{ $lhp->opd->nama_opd ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-center">
                        @php
                            $colors = [
                                'draft' => 'slate',
                                'published' => 'blue',
                                'closed' => 'emerald'
                            ];
                            $c = $colors[$lhp->status] ?? 'slate';
                        @endphp
                        <x-ui.badge color="{{ $c }}" class="text-[10px] font-black tracking-widest uppercase px-2.5 py-1 rounded-lg border">
                            {{ $lhp->status === 'published' ? 'AKTIF' : strtoupper($lhp->status) }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <!-- Detail Action -->
                            <a href="{{ route('lhp.show', $lhp->id) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all tooltip" title="Lihat Detail LHP">
                                <x-lucide-eye class="w-5 h-5" />
                            </a>
                            
                            <!-- Edit Action (Only Draft & Admin/Auditor) -->
                            @if(auth()->user()->role !== 'skpd' && $lhp->status === 'draft')
                            <a href="{{ route('auditor.lhp.create') }}?edit={{ $lhp->id }}" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-xl transition-all tooltip" title="Lanjutkan Pengisian Draft">
                                <x-lucide-edit-3 class="w-5 h-5" />
                            </a>
                            @endif

                            @if(auth()->user()->role === 'admin')
                            <form action="{{ route('lhp.destroy', $lhp->id) }}" method="POST" x-data
                                @submit.prevent="Swal.fire({
                                    title: 'Hapus LHP?',
                                    text: 'Data LHP yang dihapus tidak dapat dikembalikan.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#dc2626',
                                    cancelButtonColor: '#94a3b8',
                                    confirmButtonText: 'Ya, Hapus',
                                    cancelButtonText: 'Batal',
                                    reverseButtons: true
                                }).then((result) => { if (result.isConfirmed) $el.submit() })">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all tooltip" title="Hapus LHP">
                                    <x-lucide-trash-2 class="w-5 h-5" />
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-24">
                        <div class="flex flex-col items-center justify-center text-center px-4">
                            <div class="w-20 h-20 mb-6 rounded-3xl bg-slate-50 flex items-center justify-center border border-slate-100 shadow-sm">
                                <x-lucide-folder-open class="w-10 h-10 text-slate-300" />
                            </div>
                            <h3 class="text-xl font-extrabold text-slate-700 tracking-tight">Belum Ada Laporan yang Diterbitkan</h3>
                            <p class="text-slate-500 mt-2 max-w-sm leading-relaxed">Saat ini arsip LHP masih kosong. Laporan akan muncul di sini setelah Auditor menerbitkan LHP resmi.</p>
                            @if(auth()->user()->role !== 'skpd')
                            <a href="{{ route('auditor.lhp.create') }}" class="mt-6 inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white border border-slate-200 hover:border-blue-300 hover:bg-blue-50 text-blue-600 font-bold text-sm rounded-xl shadow-sm transition-all">
                                <x-lucide-plus class="w-4 h-4" /> Buat Laporan Pertama
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination Meta -->
@if($lhps->hasPages())
<div class="mt-6 flex justify-end">
    {{ $lhps->links() }}
</div>
@endif

@endsection
