@extends('layouts.app-layout')

@section('title', 'Dashboard Utama')

@section('content')
<div class="mb-8 animate-fade-in-up">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard Interaktif</h1>
    <p class="text-slate-500 mt-1">Ringkasan ketaatan dan riwayat audit terkini secara real-time.</p>
</div>

@if(auth()->check() && auth()->user()->role === 'skpd')
    <!-- ======================= -->
    <!-- VIEW SKPD -->
    <!-- ======================= -->
    
    @php
        // Logika Alert Warna untuk SKPD Premium Banner
        $isDanger = $sisaKewajiban > 0;
        $gradientBanner = $isDanger
            ? 'bg-red-50 border-red-200 shadow-sm'
            : 'bg-emerald-50 border-emerald-200 shadow-sm';
        $iconClass = $isDanger ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white';
        $textClass = $isDanger ? 'text-red-900' : 'text-emerald-900';
    @endphp

    <div class="p-6 mb-8 rounded-3xl border bg-white flex flex-col sm:flex-row items-start sm:items-center gap-5 animate-fade-in-up delay-100 transition-all duration-300 shadow-sm {{ $gradientBanner }} relative overflow-hidden">
        <div class="p-3 rounded-2xl relative z-10 {{ $iconClass }}">
            @if($isDanger)
                <x-lucide-alert-triangle class="w-6 h-6" />
            @else
                <x-lucide-shield-check class="w-6 h-6" />
            @endif
        </div>
        <div class="flex-1 relative z-10">
            <h3 class="text-base font-extrabold {{ $textClass }}">
                {{ $isDanger ? 'Perhatian: Tunggakan Tindak Lanjut Terdeteksi' : 'Status Kepatuhan: Optimal & Bersih!' }}
            </h3>
            <p class="text-sm mt-1 {{ $isDanger ? 'text-red-700/80' : 'text-emerald-700/80' }} font-medium">
                @if($isDanger)
                    Total sisa kewajiban penagihan: <span class="font-black text-red-700 text-lg tracking-tight bg-white/50 px-2 py-0.5 rounded-lg border border-red-100">Rp {{ number_format($sisaKewajiban, 0, ',', '.') }}</span>
                @else
                    Seluruh kewajiban rekomendasi LHP telah diselesaikan. Pertahankan kinerja instansi Anda!
                @endif
            </p>
        </div>
        @if($isDanger && $pendingRecs->isNotEmpty())
        <a href="{{ route('lhp.show', $pendingRecs->first()->finding->lhp->id) }}" class="relative z-10 w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold text-sm rounded-xl transition-all shadow-[0_8px_20px_rgba(220,38,38,0.3)] hover:-translate-y-1 hover:shadow-[0_12px_25px_rgba(220,38,38,0.4)]">
            <span>Tindak Lanjut Sekarang</span>
            <x-lucide-arrow-right class="w-4 h-4" />
        </a>
        @endif
    </div>

    <!-- 3 Rekomendasi Belum Selesai -->
    <x-ui.card class="animate-fade-in-up delay-200 overflow-hidden !px-0 border-t-4 border-t-amber-400">
        <div class="flex items-center justify-between mb-2 px-6">
            <h2 class="text-lg font-bold text-slate-800">Daftar Tunggu Tindak Lanjut (Top 3)</h2>
            <x-ui.badge color="blue">{{ count($pendingRecs) }} Temuan Mendesak</x-ui.badge>
        </div>
        
        <div class="overflow-x-auto px-6 pb-2">
            @if(count($pendingRecs) === 0)
                <div class="py-12 flex flex-col items-center justify-center text-slate-400">
                    <x-lucide-check-circle class="w-12 h-12 mb-3 text-emerald-400" />
                    <p class="text-slate-500">Hebat! Tidak ada daftar tunggu tindak lanjut rekomendasi.</p>
                </div>
            @else
                <table class="w-full text-sm text-left align-middle border-collapse mt-4">
                    <thead>
                        <tr class="text-slate-500 border-b border-slate-200 uppercase tracking-wider text-[10px]">
                            <th class="pb-3 font-medium">Uraian Rekomendasi</th>
                            <th class="pb-3 font-medium">Referensi LHP</th>
                            <th class="pb-3 font-medium">Sisa Tagihan</th>
                            <th class="pb-3 font-medium text-center">Status</th>
                            <th class="pb-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingRecs as $task)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="py-4 font-semibold text-slate-700 max-w-[200px] truncate" title="{{ $task->uraian_rekomendasi }}">
                                {{ $task->kode_rekomendasi }} - {{ $task->uraian_rekomendasi }}
                            </td>
                            <td class="py-4 text-slate-600 truncate max-w-[150px]">{{ $task->finding->lhp->judul ?? '-' }}</td>
                            <td class="py-4 text-red-600 font-semibold font-mono text-xs">Rp {{ number_format($task->remaining_balance, 0, ',', '.') }}</td>
                            <td class="py-4 text-center">
                                <x-ui.badge color="{{ $task->status === 'proses' ? 'yellow' : 'red' }}">
                                    {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $task->status)) }}
                                </x-ui.badge>
                            </td>
                            <td class="py-4 text-right">
                                <a href="{{ route('lhp.show', $task->finding->lhp->id) }}" class="inline-block p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors" title="Lihat LHP & Tindak Lanjut">
                                    <x-lucide-arrow-right class="w-4 h-4" />
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </x-ui.card>
@else
    <!-- ======================= -->
    <!-- VIEW AUDITOR / ADMIN -->
    <!-- ======================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card 1: Total LHP (Blue) -->
        <x-ui.card class="animate-fade-in-up delay-100 flex flex-col justify-between border-l-4 border-l-blue-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-slate-500">Total LHP Tersimpan</h3>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg"><x-lucide-file-text class="w-5 h-5" /></div>
            </div>
            <div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($totalLhp) }}</p>
                <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">Dokumen resmi terekam</p>
            </div>
        </x-ui.card>

        <!-- Card 2: LHP Selesai (Produktivitas Publikasi) -->
        <x-ui.card class="animate-fade-in-up delay-300 flex flex-col justify-between bg-gradient-to-br from-blue-600 to-blue-700 !border-none text-white overflow-hidden relative shadow-md">
            <div class="absolute -right-4 -top-4 opacity-10">
                <x-lucide-badge-check class="w-24 h-24" />
            </div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <h3 class="text-sm font-medium text-white/90">LHP Selesai</h3>
                <div class="p-2 bg-white/20 text-white rounded-lg"><x-lucide-check-check class="w-5 h-5" /></div>
            </div>
            <div class="relative z-10">
                <p class="text-3xl font-bold text-white">{{ number_format($lhpSelesai) }}</p>
                <p class="text-xs text-white/80 mt-2 flex items-center gap-1">
                    Diselesaikan oleh {{ number_format($auditorSelesai) }} Auditor
                </p>
            </div>
        </x-ui.card>
    </div>

    <!-- Latest LHP List Section -->
    <x-ui.card class="animate-fade-in-up delay-400 overflow-hidden !px-0">
        <div class="flex items-center justify-between mb-6 px-6">
            <h2 class="text-lg font-bold text-slate-800">5 LHP Terpublikasi Terbaru</h2>
            <a href="{{ route('lhp.index') }}" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 text-xs font-medium rounded-xl transition-all outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 shadow-none">Lihat Daftar Lengkap</a>
        </div>
        
        <div class="overflow-x-auto px-6 pb-2">
            @if(count($latestLhps) === 0)
                <div class="py-12 flex flex-col items-center justify-center text-slate-400">
                    <x-lucide-folder-open class="w-12 h-12 mb-3 text-slate-300" />
                    <p class="text-slate-500">Belum ada dokumen LHP yang dibuat.</p>
                </div>
            @else
            <table class="w-full text-sm text-left align-middle border-collapse">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-100 uppercase tracking-wider text-[10px]">
                        <th class="pb-3 font-medium">Nomor LHP</th>
                        <th class="pb-3 font-medium">Tanggal</th>
                        <th class="pb-3 font-medium">Judul Pemeriksaan</th>
                        <th class="pb-3 font-medium">Target OPD</th>
                        <th class="pb-3 font-medium text-center">Status</th>
                        <th class="pb-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($latestLhps as $lhp)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="py-4 font-semibold text-slate-700">{{ $lhp->nomor_lhp }}</td>
                        <td class="py-4 text-slate-500 text-xs">{{ \Carbon\Carbon::parse($lhp->tgl_lhp)->format('d M Y') }}</td>
                        <td class="py-4 text-slate-600 truncate max-w-[200px]" title="{{ $lhp->judul }}">{{ $lhp->judul }}</td>
                        <td class="py-4 text-slate-600">{{ $lhp->opd->nama_opd ?? 'Unknown' }}</td>
                        <td class="py-4 text-center">
                            <x-ui.badge color="{{ $lhp->status === 'published' ? 'green' : 'slate' }}">
                                {{ ucfirst($lhp->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="py-4 text-right">
                            <a href="{{ route('lhp.show', $lhp->id) }}" class="inline-block p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors shadow-sm bg-white border border-slate-200" title="Mulai Tindak Lanjut">
                                <x-lucide-arrow-right class="w-4 h-4" />
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </x-ui.card>
@endif
@endsection
