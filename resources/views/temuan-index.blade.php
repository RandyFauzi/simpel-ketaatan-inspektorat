@extends('layouts.app-layout')
@section('title', 'Repositori Temuan Audit Global')

@section('content')

<!-- Wrapper state for Alpine JS -->
<div x-data="{ modalOpen: false, selectedRecId: null, selectedStatus: 'belum_selesai', selectedCatatan: '', recTitle: '' }">

<!-- Header Area -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8 pt-2">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Repositori Temuan</h1>
        <p class="text-slate-500 mt-1">Pemantauan lintas-LHP untuk seluruh defisiensi dan indikasi kerugian.</p>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
        <!-- Search Bar -->
        <form action="{{ route('temuan.index') }}" method="GET" class="relative group w-full sm:w-80">
            @if(request('filter'))
                <input type="hidden" name="filter" value="{{ request('filter') }}">
            @endif
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                <x-lucide-search class="w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
            </div>
            <input type="text" name="search" value="{{ request('search') }}" 
                   class="w-full bg-white border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 block pl-10 px-4 py-2.5 shadow-sm transition-all" 
                   placeholder="Cari Uraian, Kode, OPD...">
            @if(request('search'))
            <a href="{{ route('temuan.index', ['filter' => request('filter')]) }}" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-red-500 transition-colors">
                <x-lucide-x-circle class="w-4 h-4" />
            </a>
            @endif
        </form>

        <!-- Fast Filter -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(request('filter') === 'belum_selesai')
                <a href="{{ route('temuan.index', ['search' => request('search')]) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-bold text-sm rounded-xl shadow-sm transition-all">
                    <x-lucide-filter-x class="w-4 h-4" /> Hapus Filter
                </a>
            @else
                <a href="{{ route('temuan.index', ['search' => request('search'), 'filter' => 'belum_selesai']) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 hover:text-blue-600 font-bold text-sm rounded-xl shadow-sm transition-all">
                    <x-lucide-filter class="w-4 h-4" /> Belum Selesai
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Data Table Card -->
<div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm relative animate-fade-in-up">
    <!-- Soft Inner Glow -->
    <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent pointer-events-none"></div>
    
    <div class="overflow-x-auto relative z-10">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-400 uppercase font-black bg-slate-50 border-b border-slate-100">
                <tr>
                    <th scope="col" class="px-6 py-4 w-64">SUMBER LHP & TANGGAL</th>
                    <th scope="col" class="px-6 py-4 w-48">INSTANSI (OPD)</th>
                    <th scope="col" class="px-6 py-4">URAIAN TEMUAN</th>
                    <th scope="col" class="px-6 py-4 text-right">NILAI TEMUAN</th>
                    <th scope="col" class="px-6 py-4 text-center">STATUS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($findings as $finding)
                <tr class="hover:bg-slate-50/50 transition-colors group align-top">
                    <td class="px-6 py-5">
                        <a href="{{ route('lhp.show', $finding->lhp->id) }}" class="flex flex-col gap-1.5 hover:bg-blue-50/50 p-2 -m-2 rounded-xl transition-colors group/link">
                            <span class="inline-flex items-center gap-1.5 font-black text-blue-700 bg-blue-50/80 w-max px-2.5 py-0.5 rounded border border-blue-100 text-[11px] tracking-wide group-hover/link:bg-blue-100 group-hover/link:border-blue-200 transition-colors">
                                <x-lucide-external-link class="w-3.5 h-3.5 text-blue-500" /> {{ $finding->lhp->nomor_lhp }}
                            </span>
                            <span class="text-xs text-slate-500 font-medium flex items-center gap-1.5 mt-1">
                                <x-lucide-calendar class="w-3.5 h-3.5" /> {{ \Carbon\Carbon::parse($finding->lhp->tgl_lhp)->translatedFormat('d M Y') }}
                            </span>
                        </a>
                    </td>
                    <td class="px-6 py-5">
                        <div class="font-bold text-slate-700 leading-snug">{{ $finding->lhp->opd->nama_opd ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-col gap-2">
                            <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 w-max rounded uppercase tracking-wider">{{ $finding->kode_temuan }}</span>
                            <p class="text-slate-800 font-medium leading-relaxed line-clamp-3 group-hover:line-clamp-none transition-all">{{ $finding->uraian_temuan }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-right">
                        @php
                            $totalKerugian = $finding->kerugian_negara + $finding->kerugian_daerah;
                        @endphp
                        @if($totalKerugian > 0)
                            <div class="font-black text-red-600 font-mono tracking-tight bg-red-50/50 px-2 py-1 rounded inline-block border border-red-100/50">
                                Rp {{ number_format($totalKerugian, 0, ',', '.') }}
                            </div>
                        @else
                            <span class="text-xs font-bold text-slate-300 italic">NIHIL</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 align-top w-64">
                        <div class="flex flex-col gap-2">
                            @forelse($finding->recommendations as $idx => $rec)
                                @php
                                    $btnColor = match($rec->status_tlhp ?? 'belum_selesai') {
                                        'selesai' => 'emerald',
                                        'dalam_proses' => 'amber',
                                        'tidak_dapat_ditindaklanjuti' => 'slate',
                                        default => 'red'
                                    };
                                    $btnIcon = match($rec->status_tlhp ?? 'belum_selesai') {
                                        'selesai' => 'check-circle-2',
                                        'dalam_proses' => 'loader',
                                        'tidak_dapat_ditindaklanjuti' => 'slash',
                                        default => 'clock'
                                    };
                                    $label = str_replace('_', ' ', strtoupper($rec->status_tlhp ?? 'belum_selesai'));
                                @endphp
                                <button type="button" 
                                    @click="
                                        modalOpen = true; 
                                        selectedRecId = '{{ $rec->id }}'; 
                                        selectedStatus = '{{ $rec->status_tlhp ?? 'belum_selesai' }}'; 
                                        selectedCatatan = '{{ str_replace(["\r", "\n", "'"], ['\r', '\n', "\'"], $rec->catatan_tlhp ?? '') }}';
                                        recTitle = 'Rec {{ $idx + 1 }}';
                                    "
                                    class="text-left flex items-start gap-2 px-3 py-2 rounded-xl border border-{{$btnColor}}-200 bg-{{$btnColor}}-50 hover:bg-{{$btnColor}}-100 text-{{$btnColor}}-700 transition-all shadow-sm group w-full">
                                    <x-dynamic-component :component="'lucide-' . $btnIcon" class="w-4 h-4 shrink-0 mt-0.5" />
                                    <div class="flex-1 flex flex-col">
                                        <span class="text-[10px] font-black tracking-wider uppercase">{{ $label }}</span>
                                        @if($rec->catatan_tlhp)
                                            <span class="text-[9px] font-medium opacity-80 mt-0.5 max-w-[140px] truncate font-mono">{{ $rec->catatan_tlhp }}</span>
                                        @endif
                                    </div>
                                    <x-lucide-edit-3 class="w-3.5 h-3.5 opacity-0 group-hover:opacity-100 transition-opacity shrink-0 mt-0.5" />
                                </button>
                            @empty
                                <span class="text-[10px] font-black text-slate-400 italic text-center w-full block">BELUM ADA REKOMENDASI</span>
                            @endforelse
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-24">
                        <div class="flex flex-col items-center justify-center text-center px-4">
                            <div class="w-20 h-20 mb-6 rounded-3xl bg-slate-50 flex items-center justify-center border border-slate-100 shadow-sm relative">
                                <x-lucide-inbox class="w-10 h-10 text-slate-300" />
                                <span class="absolute top-4 right-4 w-3 h-3 bg-emerald-400 rounded-full border-2 border-white ring-2 ring-emerald-50"></span>
                            </div>
                            <h3 class="text-xl font-extrabold text-slate-700 tracking-tight">Belum Ada Temuan Audit</h3>
                            <p class="text-slate-500 mt-2 max-w-sm leading-relaxed">Tingkat kepatuhan sangat optimal! Belum ada indikasi defisiensi atau temuan kerugian material yang dimuat di dalam sistem.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination Meta -->
@if($findings->hasPages())
<div class="mt-6 flex justify-end">
    {{ $findings->links() }}
</div>
@endif

    <!-- AlpineJS Modal Form Status TLHP -->
    <div x-show="modalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 xl:p-0" x-cloak>
        <!-- Backdrop -->
        <div x-show="modalOpen" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-slate-900/60"
             @click="modalOpen = false"></div>

        <!-- Modal Panel -->
        <div x-show="modalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="relative bg-white rounded-3xl w-full max-w-lg shadow-[0_20px_60px_rgb(0,0,0,0.15)] border border-white/50 overflow-hidden flex flex-col">
            
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 border border-blue-200/50">
                        <x-lucide-activity class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900" x-text="`Update Status TLHP - ${recTitle}`">Update Status TLHP</h3>
                        <p class="text-xs text-slate-500 font-medium">Lengkapi dokumentasi catatan progres terbaru.</p>
                    </div>
                </div>
                <button @click="modalOpen = false" type="button" class="text-slate-400 hover:bg-slate-200 hover:text-slate-700 p-2 rounded-xl transition-colors">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            <form :action="`/recommendation/${selectedRecId}/status`" method="POST" class="flex flex-col flex-1">
                @csrf
                @method('PUT')
                
                <div class="p-6 space-y-6 flex-1 overflow-y-auto">
                    <!-- Dropdown Status -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2">Status Penyelesaian</label>
                        <select name="status_tlhp" x-model="selectedStatus" required
                                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 block p-3 px-4 shadow-sm transition-all focus:bg-white outline-none cursor-pointer">
                            <option value="belum_selesai">BELUM SELESAI</option>
                            <option value="dalam_proses">DALAM PROSES</option>
                            <option value="selesai">SELESAI SESUAI</option>
                            <option value="tidak_dapat_ditindaklanjuti">TIDAK DAPAT DITINDAKLANJUTI</option>
                        </select>
                    </div>

                    <!-- Catatan Textarea -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2">
                            Catatan Bukti / STS
                        </label>
                        <textarea name="catatan_tlhp" x-model="selectedCatatan" rows="4" placeholder="Masukkan Nomor STS / Catatan tindak lanjut..."
                                  class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 block p-4 shadow-sm transition-all focus:bg-white resize-y font-mono leading-relaxed outline-none"></textarea>
                        <p class="text-[10px] text-slate-400 mt-2 flex items-start gap-1.5"><x-lucide-info class="w-3 h-3 shrink-0 mt-0.5" /> Catatan ini berharga untuk jejak penagihan Inspektorat.</p>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 rounded-b-3xl">
                    <button type="button" @click="modalOpen = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-200 bg-white border border-slate-300 rounded-xl transition-colors focus:ring-4 focus:ring-slate-100 outline-none">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-sm shadow-blue-200 focus:ring-4 focus:ring-blue-500/20 outline-none flex items-center gap-2">
                        <x-lucide-save class="w-4 h-4" /> Simpan Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
