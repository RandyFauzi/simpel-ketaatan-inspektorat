@extends('layouts.app-layout')
@section('title', 'Detail LHP - ' . $lhp->nomor_lhp)

@section('content')

{{-- ═══ DYNAMIC STATUS BANNER ═══ --}}
@if($lhp->status === 'draft')
    <div class="mb-6 bg-amber-50 border border-amber-200 p-4 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm animate-fade-in-up">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-amber-100 rounded-lg shrink-0">
                <x-lucide-file-edit class="w-5 h-5 text-amber-600" />
            </div>
            <div>
                <h3 class="text-sm font-bold text-amber-800">Pratinjau Draft</h3>
                <p class="text-xs text-amber-600">Dokumen ini masih berstatus draft. Ajukan ke Ketua Tim untuk proses reviu berjenjang.</p>
            </div>
        </div>
    </div>

@elseif(in_array($lhp->status, ['review_ketua', 'review_irban', 'review_inspektur']))
    <div class="mb-6 bg-blue-50 border border-blue-200 p-4 rounded-2xl flex items-center gap-3 shadow-sm animate-fade-in-up">
        <div class="p-2 bg-blue-100 rounded-lg shrink-0">
            <x-lucide-hourglass class="w-5 h-5 text-blue-600" />
        </div>
        <div>
            <h3 class="text-sm font-bold text-blue-800">
                Sedang Direviu — Menunggu Persetujuan 
                @if($lhp->status === 'review_ketua') Ketua Tim 
                @elseif($lhp->status === 'review_irban') Inspektur Pembantu I 
                @elseif($lhp->status === 'review_inspektur') Inspektur Daerah 
                @endif
            </h3>
            <p class="text-xs text-blue-600">Dokumen telah diajukan dan sedang dalam proses reviu. Anda tidak dapat mengedit profil secara bebas saat dokumen berada di meja reviu.</p>
        </div>
    </div>

@elseif($lhp->status === 'published')
    <div class="mb-6 bg-emerald-50 border border-emerald-200 p-4 rounded-2xl flex items-center gap-3 shadow-sm animate-fade-in-up">
        <div class="p-2 bg-emerald-100 rounded-lg shrink-0">
            <x-lucide-check-circle-2 class="w-5 h-5 text-emerald-600" />
        </div>
        <div>
            <h3 class="text-sm font-bold text-emerald-800">Dokumen Resmi — Telah Disahkan</h3>
            <p class="text-xs text-emerald-600">LHP ini telah disetujui oleh Inspektur Daerah dan dipublikasikan secara resmi.</p>
        </div>
    </div>
@endif

<!-- Header Area -->
<div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-6 animate-fade-in-up">
    <div class="flex-1">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-blue-600 mb-3 transition-colors">
            <x-lucide-arrow-left class="w-4 h-4 mr-1" /> Kembali ke Tinjauan
        </a>
        <h1 class="text-3xl font-bold text-slate-900 leading-tight mb-2">{{ $lhp->judul }}</h1>
        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
            <span class="inline-flex items-center gap-1.5 font-bold bg-blue-50 text-blue-800 px-2.5 py-1 rounded-md border border-blue-100">
                <x-lucide-file-digit class="w-4 h-4 text-blue-500" /> {{ $lhp->nomor_lhp }}
            </span>
            <span class="inline-flex items-center gap-1.5 font-medium">
                <x-lucide-building-2 class="w-4 h-4 text-slate-400" /> {{ $lhp->opd->nama_opd ?? 'Identitas Instansi Tidak Ditemukan' }}
            </span>
            <span class="inline-flex items-center gap-1.5 bg-slate-100 px-2.5 py-1 rounded-md">
                <x-lucide-calendar class="w-4 h-4 text-slate-400" /> TA {{ $lhp->tahun_anggaran }}
            </span>
        </div>
    </div>
    
    <div class="flex flex-col items-end gap-3 shrink-0">
        <x-ui.badge color="{{ $lhp->status === 'published' ? 'green' : ($lhp->status === 'closed' ? 'slate' : 'yellow') }}" class="text-sm px-4 py-1.5 shadow-sm border-2">
            <x-dynamic-component :component="'lucide-' . ($lhp->status === 'published' ? 'check-circle' : 'clock')" class="w-4 h-4 mr-1.5" />
            STATUS: {{ strtoupper($lhp->status) }}
        </x-ui.badge>
        
        <div class="flex items-center gap-2 mt-1">
            <a href="{{ route('auditor.lhp.export', $lhp->id) }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-xl shadow-sm hover:bg-slate-50 transition-colors">
                <x-lucide-file-text class="w-4 h-4" /> Review PDF
            </a>

            @if(auth()->user()->role === 'auditor' && $lhp->status === 'draft')
                <form action="{{ route('auditor.lhp.submit-review', $lhp->id) }}" method="POST" x-data @submit.prevent="Swal.fire({
                    title: 'Ajukan ke Ketua Tim?',
                    text: 'Ajukan LHP ini untuk direviu oleh Ketua Tim? Pastikan seluruh data telah dikonfirmasi dan lengkap.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Ajukan',
                    cancelButtonText: 'Batal'
                }).then((result) => { if (result.isConfirmed) $el.submit() })" class="inline-block">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 border border-amber-500 text-white text-sm font-bold rounded-xl shadow-sm hover:bg-amber-600 transition-colors">
                        <x-lucide-send class="w-4 h-4" /> Ajukan ke Ketua Tim
                    </button>
                </form>
            @endif

            @if((in_array(auth()->user()->role, ['auditor', 'admin']) || (auth()->user()->role === 'ketua_tim' && auth()->user()->tim === $lhp->tim)) && $lhp->status === 'draft')
                <a href="{{ route('auditor.lhp.create') }}?edit={{ $lhp->id }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-blue-600 text-white text-sm font-bold rounded-xl shadow-sm hover:bg-blue-700 transition-colors">
                    <x-lucide-edit class="w-4 h-4" /> Edit LHP
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Main Document Layout with Tab Logic -->
<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden animate-fade-in-up delay-100 relative" x-data="{ activeTab: 'info' }">
    <!-- Soft Inner Glow -->
    <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent pointer-events-none"></div>

    <!-- Navigation Tabs -->
    <div class="flex overflow-x-auto border-b border-slate-200/50 bg-slate-50/30 hide-scrollbar relative z-10 px-2 pt-2">
        <button @click="activeTab = 'info'" 
                x-bind:class="activeTab === 'info' ? 'border-b-2 border-blue-600 text-blue-700 font-bold bg-white/80 shadow-sm rounded-t-xl' : 'text-slate-500 font-medium hover:text-slate-700 hover:bg-slate-100/50 rounded-t-xl'" 
                class="px-6 py-4 text-sm whitespace-nowrap transition-all outline-none">
            <x-lucide-book-open class="w-4 h-4 inline-block mr-1.5 mb-0.5" x-bind:class="activeTab === 'info' ? 'text-blue-600' : ''" /> Bab Pendahuluan
        </button>
        <button @click="activeTab = 'temuan'" 
                x-bind:class="activeTab === 'temuan' ? 'border-b-2 border-blue-600 text-blue-700 font-bold bg-white/80 shadow-sm rounded-t-xl' : 'text-slate-500 font-medium hover:text-slate-700 hover:bg-slate-100/50 rounded-t-xl'" 
                class="px-6 py-4 text-sm whitespace-nowrap transition-all outline-none">
            <x-lucide-alert-circle class="w-4 h-4 inline-block mr-1.5 mb-0.5" x-bind:class="activeTab === 'temuan' ? 'text-blue-600' : ''" /> Registrasi Temuan ({{ $lhp->findings->count() }})
        </button>
        <button @click="activeTab = 'rekomendasi'" 
                x-bind:class="activeTab === 'rekomendasi' ? 'border-b-2 border-blue-600 text-blue-700 font-bold bg-white/80 shadow-sm rounded-t-xl' : 'text-slate-500 font-medium hover:text-slate-700 hover:bg-slate-100/50 rounded-t-xl'" 
                class="px-6 py-4 text-sm whitespace-nowrap transition-all outline-none">
            <x-lucide-list-checks class="w-4 h-4 inline-block mr-1.5 mb-0.5" x-bind:class="activeTab === 'rekomendasi' ? 'text-blue-600' : ''" /> Matriks Rekomendasi
        </button>

        <button @click="activeTab = 'reviu'" 
                x-bind:class="activeTab === 'reviu' ? 'border-b-2 border-blue-600 text-blue-700 font-bold bg-white/80 shadow-sm rounded-t-xl' : 'text-slate-500 font-medium hover:text-slate-700 hover:bg-slate-100/50 rounded-t-xl'" 
                class="px-6 py-4 text-sm whitespace-nowrap transition-all outline-none flex items-center gap-1.5">
            <x-lucide-message-square class="w-4 h-4" x-bind:class="activeTab === 'reviu' ? 'text-blue-600' : ''" /> Riwayat Reviu
            @if($lhp->reviews->count() > 0)
            <span class="bg-amber-100 text-amber-700 font-bold text-[10px] px-1.5 rounded-full">{{ $lhp->reviews->count() }}</span>
            @endif
        </button>

        <button @click="activeTab = 'jejak'" 
                x-bind:class="activeTab === 'jejak' ? 'border-b-2 border-emerald-500 text-emerald-700 font-bold bg-white/80 shadow-sm rounded-t-xl' : 'text-slate-500 font-medium hover:text-slate-700 hover:bg-slate-100/50 rounded-t-xl'" 
                class="px-6 py-4 text-sm whitespace-nowrap transition-all outline-none flex items-center gap-1.5">
            <x-lucide-history class="w-4 h-4" x-bind:class="activeTab === 'jejak' ? 'text-emerald-500' : ''" /> Jejak Aktivitas
        </button>
    </div>

    <!-- Active Tab Canvas -->
    <div class="p-6 md:p-8 min-h-[500px] relative">
        
        <!-- Tab 1: Bab I - Informasi Umum (Konteks Audit) -->
        <div x-show="activeTab === 'info'" x-transition.opacity.duration.300ms class="max-w-4xl mx-auto space-y-8 text-slate-700 text-[15px] leading-relaxed">
            <div class="prose prose-slate max-w-none">
                <div class="space-y-8">
                    <section>
                        <h2 class="text-lg font-bold border-b border-slate-200 pb-2 mb-4 text-slate-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded bg-blue-600 text-white flex items-center justify-center text-xs">1</span>
                            Dasar Audit
                        </h2>
                        <div class="pl-9 prose prose-sm max-w-none text-slate-600">
                            {!! \Mews\Purifier\Facades\Purifier::clean($lhp->content->metadata_tambahan['dasar_audit'] ?? 'Data tidak tersedia.', 'audit_wysiwyg') !!}
                        </div>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold border-b border-slate-200 pb-2 mb-4 text-slate-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded bg-blue-600 text-white flex items-center justify-center text-xs">2</span>
                            Tujuan Audit
                        </h2>
                        <div class="pl-9 prose prose-sm max-w-none text-slate-600">
                            {!! \Mews\Purifier\Facades\Purifier::clean($lhp->content->metadata_tambahan['tujuan_audit'] ?? 'Data tidak tersedia.', 'audit_wysiwyg') !!}
                        </div>
                    </section>

                    <section>
                        <h2 class="text-lg font-bold border-b border-slate-200 pb-2 mb-4 text-slate-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded bg-blue-600 text-white flex items-center justify-center text-xs">3</span>
                            Metodologi & Batasan
                        </h2>
                        <div class="pl-9 prose prose-sm max-w-none text-slate-600">
                            {!! \Mews\Purifier\Facades\Purifier::clean(($lhp->content->metadata_tambahan['metodologi_audit'] ?? $lhp->content->metadata_tambahan['metodologi'] ?? 'Data tidak tersedia.'), 'audit_wysiwyg') !!}
                        </div>
                    </section>

                    <section>
                        <h2 class="text-lg font-bold border-b border-slate-200 pb-2 mb-4 text-slate-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded bg-blue-600 text-white flex items-center justify-center text-xs">4</span>
                            Sasaran, Ruang Lingkup & Periode
                        </h2>
                        <div class="pl-9 prose prose-sm max-w-none text-slate-600">
                            {!! \Mews\Purifier\Facades\Purifier::clean(($lhp->content->metadata_tambahan['sasaran_audit'] ?? $lhp->content->metadata_tambahan['sasaran'] ?? 'Data tidak tersedia.'), 'audit_wysiwyg') !!}
                        </div>
                    </section>

                    <section>
                        <h2 class="text-lg font-bold border-b border-slate-200 pb-2 mb-4 text-slate-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded bg-blue-600 text-white flex items-center justify-center text-xs">5</span>
                            Informasi Auditi
                        </h2>
                        <div class="pl-9 prose prose-sm max-w-none text-slate-600">
                            @php
                                $infoAuditiChunks = array_filter([
                                    $lhp->content->metadata_tambahan['info_tujuan_program'] ?? null,
                                    $lhp->content->metadata_tambahan['info_kegiatan_program'] ?? null,
                                    $lhp->content->metadata_tambahan['info_lokasi_dana'] ?? null,
                                    $lhp->content->metadata_tambahan['info_sumber_dana'] ?? null,
                                    $lhp->content->metadata_tambahan['info_struktur_org'] ?? null,
                                ]);
                            @endphp
                            {!! \Mews\Purifier\Facades\Purifier::clean(!empty($infoAuditiChunks)
                                ? implode('<br><br>', $infoAuditiChunks)
                                : ($lhp->content->metadata_tambahan['info_auditi'] ?? 'Data tidak tersedia.'), 'audit_wysiwyg') !!}
                        </div>
                    </section>

                    @if($lhp->content && $lhp->content->bab_3_penutup)
                    <section class="pt-6 border-t border-slate-100">
                        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Penutup & Pernyataan</h2>
                        <div class="italic text-slate-500 pl-4 border-l-4 border-slate-100 prose prose-sm max-w-none">
                            {!! \Mews\Purifier\Facades\Purifier::clean($lhp->content->bab_3_penutup, 'audit_wysiwyg') !!}
                        </div>
                    </section>
                    @endif

                    <section class="pt-6 border-t border-slate-100">
                        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Tembusan</h2>
                        @php
                            $tembusanItems = $lhp->content->metadata_tambahan['tembusan'] ?? null;
                            if (!is_array($tembusanItems) || empty($tembusanItems)) {
                                $legacyTembusan = [
                                    $lhp->content->metadata_tambahan['tembusan_1'] ?? null,
                                    $lhp->content->metadata_tambahan['tembusan_2'] ?? null,
                                ];
                                $tembusanItems = array_values(array_filter($legacyTembusan, fn ($item) => is_string($item) && trim($item) !== ''));
                            }
                        @endphp
                        <div class="pl-4">
                            @forelse($tembusanItems as $idx => $item)
                                <p class="text-sm text-slate-600">{{ $idx + 1 }}. {{ $item }}</p>
                            @empty
                                <p class="text-sm text-slate-500 italic">Belum ada data tembusan.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- Tab 2: Bab II - Hasil Audit (Daftar Temuan) -->
        <div x-show="activeTab === 'temuan'" x-transition.opacity.duration.300ms style="display: none;">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 underline decoration-blue-500 decoration-4 underline-offset-8">Daftar Temuan Hasil Audit</h3>
                    <p class="text-sm text-slate-500 mt-4">Rekapitulasi defisiensi dan indikasi kerugian yang ditemukan selama proses pemeriksaan.</p>
                </div>
            </div>

            @if($lhp->findings->isEmpty())
                <div class="text-center py-20 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                    <x-lucide-shield-check class="w-12 h-12 mx-auto text-emerald-400 mb-4" />
                    <h4 class="text-lg font-bold text-slate-700">Tidak Ada Temuan</h4>
                    <p class="text-slate-500">Pemeriksaan tidak menemukan adanya ketidakpatuhan material.</p>
                </div>
            @else
                <div class="overflow-hidden border border-slate-200 rounded-2xl shadow-sm bg-white">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 font-bold text-slate-700 text-center w-16">NO</th>
                                <th class="px-6 py-4 font-bold text-slate-700 w-32">KODE</th>
                                <th class="px-6 py-4 font-bold text-slate-700">URAIAN MASALAH (TEMUAN)</th>
                                <th class="px-6 py-4 font-bold text-slate-700 text-right">KERUGIAN NEGARA</th>
                                <th class="px-6 py-4 font-bold text-slate-700 text-right">KERUGIAN DAERAH</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php $totalNegara = 0; $totalDaerah = 0; @endphp
                            @foreach($lhp->findings as $idx => $finding)
                            @php $totalNegara += $finding->kerugian_negara; $totalDaerah += $finding->kerugian_daerah; @endphp
                            <tr class="hover:bg-slate-50 transition-colors align-top">
                                <td class="px-6 py-4 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-blue-700">{{ $finding->kode_temuan }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-slate-800 font-medium leading-relaxed">{{ $finding->uraian_temuan }}</p>
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-red-600">
                                    {{ number_format($finding->kerugian_negara, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-red-600">
                                    {{ number_format($finding->kerugian_daerah, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-blue-50/50 border-t-2 border-blue-100">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right font-black text-blue-900 tracking-wider">TOTAL KERUGIAN</td>
                                <td class="px-6 py-4 text-right font-mono font-black text-red-700 bg-red-50/50">
                                    Rp {{ number_format($totalNegara, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-black text-red-700 bg-red-50/50">
                                    Rp {{ number_format($totalDaerah, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="mt-6 p-4 bg-amber-50 rounded-xl border border-amber-200 flex items-start gap-3">
                    <x-lucide-info class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
                    <p class="text-xs text-amber-800 leading-relaxed font-medium">
                        <strong>Catatan:</strong> Nilai temuan di atas bersifat material dan wajib ditindaklanjuti sesuai dengan rekomendasi yang diberikan pada Bab III.
                    </p>
                </div>
            @endif
        </div>

        <!-- Tab 3: Bagian Pertama - Matriks Rekomendasi (Tindak Lanjut) -->
        <div x-show="activeTab === 'rekomendasi'" x-transition.opacity.duration.300ms style="display: none;" class="space-y-6">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Matriks Pemantauan Rekomendasi</h3>
                    <p class="text-sm text-slate-500 mt-1">Status penyelesaian instruksi perbaikan dan penyetoran kerugian.</p>
                </div>
            </div>

            @if($lhp->findings->isEmpty())
                <div class="text-center py-12 text-slate-400 font-medium italic">Belum ada rekomendasi yang diterbitkan.</div>
            @else
                @foreach($lhp->findings as $finding)
                    @if($finding->recommendations->isNotEmpty())
                        <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm mb-6 transition-all duration-300 hover:shadow-md">
                            <!-- Linkage Header -->
                            <div class="bg-gradient-to-r from-slate-50/80 to-transparent border-b border-slate-100/60 px-6 py-5">
                                <span class="text-[10px] font-black text-blue-600 bg-blue-100/80 px-2.5 py-1 rounded-full tracking-widest uppercase">SUMBER TEMUAN: {{ $finding->kode_temuan }}</span>
                                <p class="text-sm font-bold text-slate-800 mt-3 leading-relaxed">{{ $finding->uraian_temuan }}</p>
                            </div>
                            
                            <table class="w-full text-sm text-left">
                                <thead class="text-[10px] text-slate-400 uppercase font-black bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3 w-32">KODE REC</th>
                                        <th class="px-6 py-3">INSTRUKSI AUDITOR</th>
                                        <th class="px-6 py-3 text-right">SISA KEWAJIBAN</th>
                                        <th class="px-6 py-3 text-center">STATUS</th>
                                        <th class="px-6 py-3 text-right">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($finding->recommendations as $rec)
                                    <tr class="align-top hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-5 font-bold text-slate-900">{{ $rec->kode_rekomendasi }}</td>
                                        <td class="px-6 py-5">
                                            <p class="text-slate-600 font-medium leading-relaxed">{{ $rec->uraian_rekomendasi }}</p>
                                        </td>
                                        <td class="px-6 py-5 text-right whitespace-nowrap">
                                            @php
                                                // Automatic Calculation of Remaining Balance
                                                $approvedPayments = $rec->followUpEvidences->where('status_verifikasi', 'approved')->sum('nominal_setoran');
                                                $initialLiability = $rec->nilai_rekomendasi ?? ($finding->kerugian_negara + $finding->kerugian_daerah);
                                                $remaining = max(0, $initialLiability - $approvedPayments);
                                            @endphp
                                            @if($remaining > 0)
                                                <span class="font-mono font-bold text-red-600 bg-red-50 px-2 py-1 rounded">
                                                    Rp {{ number_format($remaining, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="font-bold text-emerald-600 flex items-center justify-end gap-1.5">
                                                    <x-lucide-check-circle-2 class="w-4 h-4" /> LUNAS
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @php
                                                $statusColors = [
                                                    'sesuai' => 'green',
                                                    'belum_sesuai' => 'red',
                                                    'proses' => 'blue',
                                                    'tidak_dapat_ditindaklanjuti' => 'yellow'
                                                ];
                                                $color = $statusColors[$rec->status] ?? 'slate';
                                            @endphp
                                            <x-ui.badge color="{{ $color }}" class="text-[9px] font-black tracking-tighter uppercase px-2 py-0.5 rounded-md border border-{{ $color }}-200">
                                                {{ str_replace('_', ' ', $rec->status) }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            @if($lhp->status === 'published')
                                                @if(auth()->user()->role === 'skpd')
                                                    <button @click="$dispatch('open-evidence-modal-{{ $rec->id }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm hover:translate-y-[-1px]">
                                                        <x-lucide-plus-circle class="w-3.5 h-3.5" /> Input Tindak Lanjut
                                                    </button>
                                                @elseif(auth()->user()->role !== 'skpd')
                                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                                                        <x-lucide-shield-check class="w-3.5 h-3.5" /> Verifikasi
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-[10px] font-bold text-slate-300 italic">LHP Draft</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Evidence History -->
                                    @if($rec->followUpEvidences->isNotEmpty())
                                    <tr class="bg-slate-50/30">
                                        <td colspan="5" class="px-6 py-4">
                                            <div class="ml-10 space-y-2">
                                                @foreach($rec->followUpEvidences as $evidence)
                                                <div class="flex items-center justify-between p-4 bg-white border border-slate-200 rounded-2xl shadow-sm hover:border-blue-200/60 hover:-translate-y-0.5 transition-all duration-300 group">
                                                    <div class="flex items-center gap-4">
                                                        <div class="p-2.5 {{ $evidence->status_verifikasi === 'approved' ? 'bg-emerald-50 text-emerald-600 shadow-sm shadow-emerald-100' : 'bg-amber-50 text-amber-600 shadow-sm shadow-amber-100' }} rounded-xl transition-transform group-hover:scale-110 duration-300">
                                                            <x-dynamic-component :component="'lucide-' . ($evidence->status_verifikasi === 'approved' ? 'check' : 'clock')" class="w-5 h-5" />
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-slate-800">Setoran: Rp {{ number_format($evidence->nominal_setoran, 0, ',', '.') }}</p>
                                                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $evidence->created_at->format('d/m/Y H:i') }} • Verifikasi: <span class="font-bold {{ $evidence->status_verifikasi === 'approved' ? 'text-emerald-500' : 'text-amber-500' }}">{{ strtoupper($evidence->status_verifikasi) }}</span></p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <a href="{{ route('followup.download', $evidence->id) }}" target="_blank" class="p-2 bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Dokumen Fisik">
                                                            <x-lucide-file-text class="w-4 h-4" />
                                                        </a>
                                                        @if(auth()->user()->role !== 'skpd' && in_array($evidence->status_verifikasi, ['pending', 'submitted']))
                                                            <button @click="$dispatch('open-verify-modal-{{ $evidence->id }}')" class="text-[10px] font-black text-blue-600 hover:underline">VERIFIKASI SEKARANG</button>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(auth()->user()->role !== 'skpd' && in_array($evidence->status_verifikasi, ['pending', 'submitted']))
                                                    <x-modal-verify :evidence="$evidence" />
                                                @endif
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                    @endif

                                    <!-- Modals -->
                                    @if(auth()->user()->role === 'skpd')
                                        <x-modal-evidence :recommendation="$rec" />
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>



        <!-- Tab 5: Riwayat Reviu Kolaborasi -->
        <div x-show="activeTab === 'reviu'" x-transition.opacity.duration.300ms style="display: none;">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Timeline Chat Bubbles -->
                <div class="flex-1 space-y-6">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 border-b-4 border-amber-400 pb-1 inline-block">Catatan Supervisi Atasan</h3>
                            <p class="text-sm text-slate-500 mt-2">Riwayat interaksi revisi LHP antara Auditor dan Inspektur Pembantu.</p>
                        </div>
                    </div>

                    @forelse($lhp->reviews as $review)
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center font-bold shadow-sm shrink-0">
                            {{ strtoupper(substr($review->reviewer->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 bg-slate-50 border border-slate-200 p-5 rounded-2xl rounded-tl-sm shadow-sm relative">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h4 class="font-bold text-slate-800">{{ $review->reviewer->name }}</h4>
                                    <p class="text-[10px] uppercase font-black tracking-widest text-slate-400">{{ str_replace('_', ' ', $review->reviewer->role) }}</p>
                                </div>
                                
                            </div>
                            <div class="text-sm text-slate-700 leading-relaxed bg-white p-3 rounded-lg border border-slate-100">
                                {!! nl2br(e($review->catatan)) !!}
                            </div>
                            @if($review->status_perbaikan === 'diperbaiki')
                                <div class="mt-3 flex items-center gap-1.5 text-xs font-bold text-emerald-600">
                                    <x-lucide-check-check class="w-4 h-4" /> Telah Diperbaiki & Disetujui
                                </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-16 text-center text-slate-400 border-2 border-dashed border-slate-100 rounded-2xl">
                        <x-lucide-message-circle class="w-12 h-12 mx-auto mb-3 text-slate-200" />
                        <h4 class="text-lg font-bold text-slate-500">Belum Ada Catatan Reviu</h4>
                        <p class="text-sm mt-1">LHP ini belum dikembalikan dengan catatan revisi.</p>
                    </div>
                    @endforelse
                </div>

                <!-- Form Aksi Reviu -->
                @if( (auth()->user()->role === 'ketua_tim' && $lhp->status === 'review_ketua') || 
                     (str_starts_with((string) auth()->user()->role, 'inspektur_pembantu') && $lhp->status === 'review_irban') )
                <div class="w-full lg:w-96 shrink-0">
                    <div class="sticky top-24">
                        <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-6 shadow-sm">
                            <h4 class="text-indigo-900 font-extrabold mb-1 flex items-center gap-2">
                                <x-lucide-gavel class="w-5 h-5 text-indigo-600" /> Aksi Pemeriksaan
                            </h4>
                            <p class="text-[11px] text-indigo-600/70 mb-5 leading-relaxed font-medium">Berikan catatan khusus revisi atau teruskan LHP ini ke tahap berikutnya.</p>
                            
                            <form action="{{ route('lhp.review.store', $lhp->id) }}" method="POST" x-data>
                                @csrf
                                <div class="mb-5">
                                    <label class="block text-xs font-bold text-indigo-900 mb-2">Catatan Reviu (Wajib jika dikembalikan)</label>
                                    <textarea name="catatan" rows="4" placeholder="Tulis instruksi perbaikan..." class="w-full px-3 py-2 bg-white border border-indigo-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700 resize-y shadow-inner"></textarea>
                                </div>
                                <div class="space-y-3" x-data>
                                    @php
                                        $role = auth()->user()->role;
                                        $actionKembalikan = '';
                                        $actionTeruskan = '';
                                        $labelKembalikan = '';
                                        $labelTeruskan = '';
                                        $confirmTitle = '';
                                        
                                        if ($role === 'ketua_tim') {
                                            $actionKembalikan = 'draft';
                                            $labelKembalikan = 'Kembalikan (Draft)';
                                            $actionTeruskan = 'review_irban';
                                            $labelTeruskan = 'Teruskan ke Irban I';
                                            $confirmTitle = 'Teruskan ke Irban I?';
                                        } elseif (str_starts_with((string) $role, 'inspektur_pembantu')) {
                                            $actionKembalikan = 'review_ketua';
                                            $labelKembalikan = 'Kembalikan ke Ketua Tim';
                                            $actionTeruskan = 'published';
                                            $labelTeruskan = 'Sahkan LHP';
                                            $confirmTitle = 'Sahkan LHP ini?';
                                        }
                                    @endphp

                                    <button type="button" @click="
                                        const form = $el.closest('form');
                                        const catatanEl = form.querySelector('textarea[name=\'catatan\']');
                                        const catatan = (catatanEl?.value || '').trim();
                                        if (!catatan) {
                                            Swal.fire({
                                                title: 'Catatan Reviu Wajib Diisi',
                                                text: 'Isi Catatan Reviu terlebih dahulu sebelum mengembalikan LHP.',
                                                icon: 'warning',
                                                confirmButtonColor: '#f59e0b',
                                                confirmButtonText: 'Siap, Saya Isi'
                                            }).then(() => {
                                                if (catatanEl) catatanEl.focus();
                                            });
                                            return;
                                        }
                                        let input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'action';
                                        input.value = '{{ $actionKembalikan }}';
                                        form.appendChild(input);
                                        form.submit();
                                    " class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white border-2 border-amber-500 text-amber-600 hover:bg-amber-50 font-bold text-xs uppercase tracking-widest rounded-xl transition-colors">
                                        <x-lucide-corner-down-left class="w-4 h-4" /> {{ $labelKembalikan }}
                                    </button>
                                    
                                    <button type="button" @click="Swal.fire({
                                        title: '{{ $confirmTitle }}',
                                        text: 'Apakah Anda yakin ingin meneruskan tahapan reviu LHP ini?',
                                        icon: 'success',
                                        showCancelButton: true,
                                        confirmButtonColor: '#10b981',
                                        cancelButtonColor: '#94a3b8',
                                        confirmButtonText: 'Ya, Teruskan!',
                                        cancelButtonText: 'Batal'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            let input = document.createElement('input');
                                            input.type = 'hidden';
                                            input.name = 'action';
                                            input.value = '{{ $actionTeruskan }}';
                                            $el.closest('form').appendChild(input);
                                            $el.closest('form').submit();
                                        }
                                    })" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 border border-emerald-600 text-white hover:from-emerald-600 hover:to-green-700 font-bold text-xs uppercase tracking-widest rounded-xl transition-all shadow-md shadow-emerald-500/20">
                                        <x-lucide-check-circle class="w-4 h-4" /> {{ $labelTeruskan }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @elseif($lhp->status === 'published' && in_array(auth()->user()->role, ['inspektur_daerah', 'admin']))
                {{-- Revoke Approval Panel --}}
                <div class="w-full lg:w-96 shrink-0">
                    <div class="sticky top-24">
                        <div class="bg-red-50 border border-red-100 rounded-3xl p-6 shadow-sm">
                            <h4 class="text-red-900 font-extrabold mb-1 flex items-center gap-2">
                                <x-lucide-shield-alert class="w-5 h-5 text-red-600" /> Mitigasi Kesalahan
                            </h4>
                            <p class="text-[11px] text-red-600/70 mb-5 leading-relaxed font-medium">Gunakan fitur ini HANYA jika Anda tidak sengaja menyetujui LHP ini. Status akan ditarik mundur.</p>
                            
                            <form action="{{ route('lhp.unpublish', $lhp->id) }}" method="POST" x-data @submit.prevent="Swal.fire({
                                title: 'Batalkan Persetujuan?',
                                text: 'Status LHP akan ditarik kembali ke Review Inspektur. Publikasi akan dibatalkan.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#ef4444',
                                cancelButtonColor: '#94a3b8',
                                confirmButtonText: 'Ya, Batalkan!',
                                cancelButtonText: 'Tidak Jadi'
                            }).then((result) => { if (result.isConfirmed) $el.submit() })">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white border-2 border-red-400 text-red-600 hover:bg-red-50 font-bold text-xs uppercase tracking-widest rounded-xl transition-colors">
                                    <x-lucide-rotate-ccw class="w-4 h-4" /> Batalkan Persetujuan (Unpublish)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Tab 6: Jejak Aktivitas (Audit Trail) -->
        <div x-show="activeTab === 'jejak'" x-transition.opacity.duration.300ms style="display: none;">
            <div class="max-w-3xl mx-auto">
                <div class="mb-10 text-center">
                    <h3 class="text-2xl font-black text-slate-800 mb-2">Jejak Aktivitas Sistem</h3>
                    <p class="text-sm text-slate-500">Merekam kronologi aksi pengguna terhadap siklus hidup dokumen LHP secara mutlak.</p>
                </div>

                <div class="relative pl-6 border-l-2 border-slate-200 mt-6 space-y-8 pb-10">
                    <!-- Garis bantu pudar ke atas/bawah -->
                    <div class="absolute w-0.5 h-10 bg-gradient-to-b from-transparent to-slate-200 left-[-2px] -top-10"></div>
                    
                    @forelse($lhp->logs as $log)
                    <div class="relative group">
                        <!-- Timeline Dot (Warna Pintar Berdasarkan Kata Kunci Aksi) -->
                        <div class="absolute -left-[33px] top-1.5 w-4 h-4 rounded-full border-2 border-white shadow-sm transition-transform duration-300 group-hover:scale-125
                            @if(str_contains(strtolower($log->action), 'meneruskan') || str_contains(strtolower($log->action), 'mengajukan') || str_contains(strtolower($log->action), 'menciptakan')) bg-blue-500
                            @elseif(str_contains(strtolower($log->action), 'mengembalikan') || str_contains(strtolower($log->action), 'membatalkan') || str_contains(strtolower($log->action), 'menolak')) bg-amber-500
                            @elseif(str_contains(strtolower($log->action), 'mengesahkan') || str_contains(strtolower($log->action), 'mempublikasikan')) bg-emerald-500
                            @else bg-slate-400 @endif
                        "></div>
                        
                        <!-- Content Card -->
                        <div class="bg-white border border-slate-100 p-5 rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] hover:shadow-lg hover:border-blue-100 transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                                <h4 class="font-bold text-slate-800">{{ $log->user->name ?? 'System Auto-Task' }}</h4>
                                
                            </div>
                            <p class="text-[10px] font-black tracking-widest uppercase text-slate-300 mb-3">
                                {{ $log->user ? str_replace('_', ' ', $log->user->role) : 'SYSTEM GENERATED' }}
                            </p>
                            <p class="text-sm font-medium text-slate-700 leading-relaxed bg-slate-50/70 p-3.5 rounded-xl border border-slate-50">
                                {{ $log->action }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="py-10 text-center text-slate-400">
                        <x-lucide-ghost class="w-8 h-8 mx-auto mb-3 text-slate-300" />
                        <h4 class="font-bold text-slate-600 mb-1">Belum Ada Rekam Jejak</h4>
                        <p class="text-sm">Dokumen ini belum memiliki aktivitas struktural yang terekam.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
