@extends('layouts.app-layout')
@section('title', 'Buat LHP Baru')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/suneditor@2.47.8/dist/css/suneditor.min.css">
    <script src="https://cdn.jsdelivr.net/npm/suneditor@2.47.8/dist/suneditor.min.js" defer></script>
    <style>
        .sun-editor {
            border-color: rgb(226 232 240) !important;
            border-radius: 0.75rem !important;
            font-family: inherit !important;
        }

        .sun-editor .se-wrapper {
            min-height: 180px;
        }

        .sun-editor-large .sun-editor .se-wrapper {
            min-height: 320px;
        }

        .sun-editor-findings-large .sun-editor .se-wrapper {
            min-height: 250px;
        }

        .sun-editor .se-btn-list {
            z-index: 40;
        }

        .sun-editor .se-toolbar {
            display: none;
        }

        .sun-editor.is-active .se-toolbar {
            display: block;
        }

        .sun-editor-editable ol {
            list-style-type: decimal;
        }

        .sun-editor-editable ol ol {
            list-style-type: lower-alpha;
        }

        .sun-editor-editable ol ol ol {
            list-style-type: upper-alpha;
        }

        .sun-editor-editable ol ol ol ol {
            list-style-type: lower-roman;
        }

        .sun-editor-editable ul ul {
            list-style-type: circle;
        }
    </style>

    <div x-data="lhpWizard()" x-cloak class="max-w-6xl mx-auto pb-40">
        <div class="mb-6">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600 mb-3">
                <x-lucide-arrow-left class="w-4 h-4" /> Kembali ke Dashboard
            </a>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-800">Form Penyusunan LHP</h1>
                    <p class="text-sm text-slate-500 mt-1">Form dibagi 4 langkah untuk menjaga fokus auditor.</p>
                </div>
                <div
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-semibold">
                    <x-lucide-shield-check class="w-4 h-4" />
                    <span x-text="autosaveLabel"></span>
                    <button type="button" @click="resetFormDraft()"
                        class="ml-1 inline-flex items-center gap-1 px-2 py-1 rounded-lg border border-emerald-300 bg-white/80 text-emerald-700 hover:bg-white"
                        title="Reset form">
                        <x-lucide-trash-2 class="w-3.5 h-3.5" />
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4">
                <h3 class="text-sm font-bold text-red-700 mb-2">Gagal menyimpan dokumen</h3>
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-xs text-red-600">- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <template x-for="(item, idx) in steps" :key="idx">
                    <button type="button" @click="goToStep(idx + 1)" class="text-left px-3 py-3 rounded-xl border"
                        :class="step === idx + 1 ? 'border-blue-500 bg-blue-50' : (step > idx + 1 ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-white')">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded-full text-[11px] font-bold flex items-center justify-center"
                                :class="step === idx + 1 ? 'bg-blue-600 text-white' : (step > idx + 1 ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500')">
                                <span x-text="idx + 1"></span>
                            </div>
                            <p class="text-xs font-bold text-slate-700" x-text="item.title"></p>
                        </div>
                        <p class="text-[11px] text-slate-500" x-text="item.desc"></p>
                    </button>
                </template>
            </div>
        </div>

        <div x-show="validationError"
            class="mb-4 bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2">
            <x-lucide-info class="w-4 h-4 text-amber-600 mt-0.5" />
            <p class="text-xs text-amber-700 font-semibold" x-text="validationError"></p>
        </div>

        <form id="lhp-main-form" action="{{ route('auditor.lhp.store') }}" method="POST"
            @submit.prevent="submitForm($event)">
            @csrf
            <input type="hidden" name="is_draft" value="1">
            <input type="hidden" name="edit_id" x-model="serverEditId">

            <div x-show="step === 1" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                        <h2 class="font-bold text-slate-700 flex items-center gap-2"><x-lucide-file-badge-2
                                class="w-5 h-5 text-blue-600" />Step 1 - Identitas Dokumen</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor LHP <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="nomor_lhp" x-model="form.nomor_lhp"
                                    placeholder="700/01/LHP/Insp/2026"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm"
                                    :class="fieldErrors.nomor_lhp ? '!border-red-300 !bg-red-50/50' : ''">
                                <p class="text-xs text-slate-500 mt-1">Nomor surat resmi LHP.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal LHP <span
                                        class="text-red-500">*</span></label>
                                <input type="date" name="tgl_lhp" x-model="form.tgl_lhp"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm"
                                    :class="fieldErrors.tgl_lhp ? '!border-red-300 !bg-red-50/50' : ''">
                                <p class="text-xs text-slate-500 mt-1">Tanggal resmi penerbitan.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Sifat Surat</label>
                                <input type="text" name="sifat" x-model="form.sifat" placeholder="Biasa"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
                                <p class="text-xs text-slate-500 mt-1">Contoh: Biasa, Segera.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Lampiran</label>
                                <input type="text" name="lampiran" x-model="form.lampiran" placeholder="1 (satu) berkas"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
                                <p class="text-xs text-slate-500 mt-1">Jumlah lampiran surat.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Anggaran <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="tahun_anggaran" x-model="form.tahun_anggaran" maxlength="4"
                                    placeholder="2026" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm"
                                    :class="fieldErrors.tahun_anggaran ? '!border-red-300 !bg-red-50/50' : ''">
                                <p class="text-xs text-slate-500 mt-1">Harus 4 digit angka.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">OPD / Instansi <span
                                        class="text-red-500">*</span></label>
                                <select name="opd_id" x-model="form.opd_id"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm"
                                    :class="fieldErrors.opd_id ? '!border-red-300 !bg-red-50/50' : ''">
                                    <option value="">-- Pilih OPD --</option>
                                    @foreach($opds as $opd)
                                        <option value="{{ $opd->id }}">{{ $opd->nama_opd }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-slate-500 mt-1">Pilih OPD auditi.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Judul Pemeriksaan <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="judul" x-model="form.judul"
                                placeholder="Audit Ketaatan atas Pengadaan Barang/Jasa TA 2026"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm"
                                :class="fieldErrors.judul ? '!border-red-300 !bg-red-50/50' : ''">
                            <p class="text-xs text-slate-500 mt-1">Judul akan tampil di cover PDF.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tujuan Surat (Yth.)</label>
                                <input type="text" name="tujuan_surat" x-model="form.tujuan_surat"
                                    placeholder="Bupati Barito Selatan"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
                                <p class="text-xs text-slate-500 mt-1">Pihak penerima surat pengantar.</p>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-slate-700">Tim Pemeriksa (Nama &amp; Gelar)</label>
                                    <button type="button" @click="addTimPemeriksa()"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                        <x-lucide-plus class="w-3 h-3" /> Tambah Tim Pemeriksa
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(item, idx) in form.tim_pemeriksa" :key="'tim-pemeriksa-'+idx">
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="metadata_tambahan[tim_pemeriksa][]" x-model="form.tim_pemeriksa[idx]"
                                                placeholder="Contoh: Nama Lengkap, S.E., M.A.P."
                                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
                                            <button type="button" @click="removeTimPemeriksa(idx)"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100"
                                                title="Hapus Tim Pemeriksa">
                                                <x-lucide-trash-2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Minimal 1 baris. Tambahkan sesuai susunan tim pemeriksa.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor SPT</label>
                                    <input type="text" name="nomor_spt" x-model="form.nomor_spt"
                                        placeholder="Contoh: 700/123/ITKAB/2026"
                                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal SPT</label>
                                    <input type="date" name="tanggal_spt" x-model="form.tanggal_spt"
                                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-slate-700">Tembusan (Surat Penyampaian)</label>
                                    <button type="button" @click="addTembusan()"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                        <x-lucide-plus class="w-3 h-3" /> Tambah Tembusan
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(item, idx) in form.tembusan" :key="'tembusan-'+idx">
                                        <div class="flex items-center gap-2">
                                            <textarea name="metadata_tambahan[tembusan][]" x-model="form.tembusan[idx]" rows="1"
                                                placeholder="Contoh: Wakil Bupati Kabupaten Barito Selatan"
                                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm leading-normal min-h-[42px] resize-y"></textarea>
                                            <button type="button" @click="removeTembusan(idx)"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100"
                                                title="Hapus Tembusan">
                                                <x-lucide-trash-2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Daftar pihak penerima tembusan pada halaman akhir surat penyampaian.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div x-show="step === 2" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                        <h2 class="font-bold text-slate-700 flex items-center gap-2"><x-lucide-book-open-text
                                class="w-5 h-5 text-blue-600" />Step 2 - Informasi Umum (BAB I)</h2>
                        <p class="text-xs text-slate-500 mt-1">Gunakan accordion agar layar tidak panjang.</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <button type="button" @click="toggleAccordion('p1')"
                                class="w-full px-4 py-3 bg-slate-50 hover:bg-slate-100 flex items-center justify-between text-left">
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Panel 1</p>
                                    <p class="text-xs text-slate-500">Dasar, Tujuan, Metodologi, Batasan</p>
                                </div>
                                <x-lucide-chevron-down class="w-4 h-4 text-slate-500 transition-transform"
                                    x-bind:class="openAccordions.p1 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordions.p1" x-transition.opacity.duration.200ms
                                class="p-4 space-y-4 bg-white">
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">1. Dasar Audit</label>
                                    <p class="text-xs text-slate-500 mb-2">Dasar hukum dan surat tugas audit.</p><input
                                        id="dasar_audit" type="hidden" name="dasar_audit"
                                        x-model="form.dasar_audit"><trix-editor input="dasar_audit"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor>
                                </div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">2a. Tujuan Audit</label>
                                    <p class="text-xs text-slate-500 mb-2">Tujuan assurance audit.</p><input
                                        id="tujuan_audit" type="hidden" name="tujuan_audit"
                                        x-model="form.tujuan_audit"><trix-editor input="tujuan_audit"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor>
                                </div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">2b. Metodologi
                                        Audit</label>
                                    <p class="text-xs text-slate-500 mb-2">Metode uji dokumen dan lapangan.</p><input
                                        id="metodologi_audit" type="hidden" name="metodologi_audit"
                                        x-model="form.metodologi_audit"><trix-editor input="metodologi_audit"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor>
                                </div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">2c. Batasan Tanggung
                                        Jawab</label>
                                    <p class="text-xs text-slate-500 mb-2">Batas kewenangan auditor.</p><input
                                        id="batasan_tanggung_jawab" type="hidden" name="batasan_tanggung_jawab"
                                        x-model="form.batasan_tanggung_jawab"><trix-editor input="batasan_tanggung_jawab"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor>
                                </div>
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <button type="button" @click="toggleAccordion('p2')"
                                class="w-full px-4 py-3 bg-slate-50 hover:bg-slate-100 flex items-center justify-between text-left">
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Panel 2</p>
                                    <p class="text-xs text-slate-500">Sasaran, Ruang Lingkup, Periode</p>
                                </div>
                                <x-lucide-chevron-down class="w-4 h-4 text-slate-500 transition-transform"
                                    x-bind:class="openAccordions.p2 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordions.p2" x-transition.opacity.duration.200ms
                                class="p-4 space-y-4 bg-white">
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">3a. Sasaran
                                        Audit</label><input id="sasaran_audit" type="hidden" name="sasaran_audit"
                                        x-model="form.sasaran_audit"><trix-editor input="sasaran_audit"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">3b. Ruang
                                        Lingkup</label><input id="ruang_lingkup" type="hidden" name="ruang_lingkup"
                                        x-model="form.ruang_lingkup"><trix-editor input="ruang_lingkup"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">3c. Periode
                                        Audit</label><input id="periode_audit" type="hidden" name="periode_audit"
                                        x-model="form.periode_audit"><trix-editor input="periode_audit"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <button type="button" @click="toggleAccordion('p3')"
                                class="w-full px-4 py-3 bg-slate-50 hover:bg-slate-100 flex items-center justify-between text-left">
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Panel 3</p>
                                    <p class="text-xs text-slate-500">Informasi Auditi</p>
                                </div>
                                <x-lucide-chevron-down class="w-4 h-4 text-slate-500 transition-transform"
                                    x-bind:class="openAccordions.p3 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordions.p3" x-transition.opacity.duration.200ms
                                class="p-4 space-y-4 bg-white">
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">4a. Tujuan
                                        Program</label><input id="info_tujuan_program" type="hidden"
                                        name="info_tujuan_program" x-model="form.info_tujuan_program"><trix-editor
                                        input="info_tujuan_program"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">4b. Kegiatan
                                        Program</label><input id="info_kegiatan_program" type="hidden"
                                        name="info_kegiatan_program" x-model="form.info_kegiatan_program"><trix-editor
                                        input="info_kegiatan_program"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">4c. Lokasi Program dan
                                        Alokasi Dana</label><input id="info_lokasi_dana" type="hidden"
                                        name="info_lokasi_dana" x-model="form.info_lokasi_dana"><trix-editor
                                        input="info_lokasi_dana"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">4d. Sumber
                                        Dana</label><input id="info_sumber_dana" type="hidden" name="info_sumber_dana"
                                        x-model="form.info_sumber_dana"><trix-editor input="info_sumber_dana"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1.5">4e. Struktur
                                        Organisasi</label><input id="info_struktur_org" type="hidden"
                                        name="info_struktur_org" x-model="form.info_struktur_org"><trix-editor
                                        input="info_struktur_org"
                                        class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <button type="button" @click="toggleAccordion('p4')"
                                class="w-full px-4 py-3 bg-slate-50 hover:bg-slate-100 flex items-center justify-between text-left">
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Panel 4</p>
                                    <p class="text-xs text-slate-500">Penilaian SPI</p>
                                </div>
                                <x-lucide-chevron-down class="w-4 h-4 text-slate-500 transition-transform"
                                    x-bind:class="openAccordions.p4 ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="openAccordions.p4" x-transition.opacity.duration.200ms class="p-4 bg-white"><label
                                    class="block text-xs font-bold text-slate-700 mb-1.5">5. Penilaian SPI</label><input
                                    id="penilaian_spi" type="hidden" name="penilaian_spi"
                                    x-model="form.penilaian_spi"><trix-editor input="penilaian_spi"
                                    class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="step === 3" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                        <h2 class="font-bold text-slate-700 flex items-center gap-2"><x-lucide-scroll-text
                                class="w-5 h-5 text-indigo-600" />Step 3 - Uraian Hasil dan Simpulan</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1.5">Penilaian Ketaatan</label>
                                <p class="text-xs text-slate-500 mb-2">Uraian kepatuhan terhadap ketentuan.</p><input
                                    id="penilaian_ketaatan" type="hidden" name="penilaian_ketaatan"
                                    x-model="form.penilaian_ketaatan"><trix-editor input="penilaian_ketaatan"
                                    class="bg-white border border-slate-200 rounded-xl"></trix-editor>
                            </div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1.5">Kesesuaian Output</label>
                                <p class="text-xs text-slate-500 mb-2">Bandingkan output aktual dengan target.</p><input
                                    id="kesesuaian_output" type="hidden" name="kesesuaian_output"
                                    x-model="form.kesesuaian_output"><trix-editor input="kesesuaian_output"
                                    class="bg-white border border-slate-200 rounded-xl"></trix-editor>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1.5">Hal-hal Penting
                                    Lainnya</label><input id="hal_penting_lainnya" type="hidden" name="hal_penting_lainnya"
                                    x-model="form.hal_penting_lainnya"><trix-editor input="hal_penting_lainnya"
                                    class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1.5">Tindak Lanjut
                                    Sebelumnya</label><input id="tindak_lanjut_sebelumnya" type="hidden"
                                    name="tindak_lanjut_sebelumnya" x-model="form.tindak_lanjut_sebelumnya"><trix-editor
                                    input="tindak_lanjut_sebelumnya"
                                    class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                        </div>
                        <div class="trix-large border border-slate-200 rounded-2xl p-4 bg-slate-50/40">
                            <label class="block text-sm font-bold text-slate-800 mb-1.5">Simpulan Audit (BAGIAN PERTAMA
                                PDF)</label>
                            <p class="text-xs text-slate-500 mb-3">Gunakan list bertingkat bila diperlukan.</p>
                            <input id="simpulan_audit" type="hidden" name="simpulan_audit" x-model="form.simpulan_audit">
                            <trix-editor input="simpulan_audit"
                                class="bg-white border border-slate-200 rounded-xl"></trix-editor>
                        </div>
                    </div>
                </div>
            </div>
            <div x-show="step === 4" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between gap-3">
                        <h2 class="font-bold text-slate-700 flex items-center gap-2"><x-lucide-layout-list
                                class="w-5 h-5 text-amber-600" />Step 4 - Kertas Kerja Temuan dan Rekomendasi</h2>
                        <button type="button" @click="addFinding()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg"><x-lucide-plus
                                class="w-4 h-4" /> Tambah Temuan</button>
                    </div>
                    <div class="p-6 space-y-5">
                        <template x-if="form.findings.length === 0">
                            <div class="text-center py-10 border border-dashed border-slate-200 rounded-2xl">
                                <x-lucide-inbox class="w-9 h-9 text-slate-300 mx-auto mb-3" />
                                <p class="text-sm text-slate-500 font-semibold">Belum ada temuan. Klik tombol Tambah Temuan.
                                </p>
                            </div>
                        </template>
                        <template x-for="(finding, fIdx) in form.findings" :key="'finding-'+fIdx">
                            <div class="border border-slate-200 rounded-2xl p-5 bg-slate-50/30">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-2"><span
                                            class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 text-xs font-extrabold flex items-center justify-center"
                                            x-text="fIdx + 1"></span>
                                        <p class="text-sm font-bold text-slate-700">Temuan ke-<span
                                                x-text="fIdx + 1"></span></p>
                                    </div>
                                    <button type="button" @click="removeFinding(fIdx)"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg"><x-lucide-trash-2
                                            class="w-3.5 h-3.5" /> Hapus</button>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
                                    <div><label class="block text-xs font-bold text-slate-700 mb-1.5">Kode
                                            Temuan</label><input type="text" :name="'findings['+fIdx+'][kode_temuan]'"
                                            x-model="finding.kode_temuan" :placeholder="'T-'+(fIdx+1)"
                                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"></div>
                                    <div class="lg:col-span-3">
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Uraian Temuan <span
                                                class="text-red-500">*</span></label>
                                        <p class="text-xs text-slate-500 mb-2">Tuliskan uraian detail temuan. Area ini
                                            diperluas agar nyaman mengetik narasi panjang.</p>
                                        <input type="hidden" :id="'finding_'+fIdx+'_uraian_temuan'"
                                            :name="'findings['+fIdx+'][uraian_temuan]'" x-model="finding.uraian_temuan">
                                        <trix-editor :input="'finding_'+fIdx+'_uraian_temuan'"
                                            class="bg-white border border-slate-200 rounded-xl trix-findings-large"></trix-editor>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div><label class="block text-xs font-bold text-slate-700 mb-1.5">Rekomendasi
                                            Teks</label><input type="hidden" :id="'finding_'+fIdx+'_rekomendasi_teks'"
                                            :name="'findings['+fIdx+'][rekomendasi_teks]'"
                                            x-model="finding.rekomendasi_teks"><trix-editor
                                            :input="'finding_'+fIdx+'_rekomendasi_teks'"
                                            class="bg-white border border-slate-200 rounded-xl"></trix-editor></div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div><label class="block text-xs font-bold text-slate-700 mb-1.5">Kerugian Negara
                                            (Rp)</label><input type="number" :name="'findings['+fIdx+'][kerugian_negara]'"
                                            x-model.number="finding.kerugian_negara" min="0"
                                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm font-mono">
                                    </div>
                                    <div><label class="block text-xs font-bold text-slate-700 mb-1.5">Kerugian Daerah
                                            (Rp)</label><input type="number" :name="'findings['+fIdx+'][kerugian_daerah]'"
                                            x-model.number="finding.kerugian_daerah" min="0"
                                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm font-mono">
                                    </div>
                                </div>
                                <div class="mt-5 border-t border-dashed border-slate-200 pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-xs font-bold text-slate-700 uppercase">Rekomendasi Turunan</p>
                                        <button type="button" @click="addRecommendation(fIdx)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg"><x-lucide-plus
                                                class="w-3 h-3" /> Tambah Rekomendasi</button>
                                    </div>
                                    <template x-if="!finding.recommendations || finding.recommendations.length === 0">
                                        <p class="text-xs text-slate-400">Belum ada rekomendasi untuk temuan ini.</p>
                                    </template>
                                    <div class="space-y-3">
                                        <template x-for="(rec, rIdx) in finding.recommendations"
                                            :key="'rec-'+fIdx+'-'+rIdx">
                                            <div class="border border-slate-200 rounded-xl p-3 bg-white">
                                                <div class="flex justify-end mb-2"><button type="button"
                                                        @click="removeRecommendation(fIdx, rIdx)"
                                                        class="text-xs font-bold text-red-600 hover:text-red-700">Hapus</button>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                                    <div><label
                                                            class="block text-[11px] font-bold text-slate-700 mb-1">Kode</label><input
                                                            type="text"
                                                            :name="'findings['+fIdx+'][recommendations]['+rIdx+'][kode_rekomendasi]'"
                                                            x-model="rec.kode_rekomendasi"
                                                            :placeholder="'R-'+(fIdx+1)+'.'+(rIdx+1)"
                                                            class="w-full px-2.5 py-2 border border-slate-200 rounded-lg text-xs">
                                                    </div>
                                                    <div class="md:col-span-2"><label
                                                            class="block text-[11px] font-bold text-slate-700 mb-1">Uraian
                                                            Rekomendasi <span class="text-red-500">*</span></label><input
                                                            type="text"
                                                            :name="'findings['+fIdx+'][recommendations]['+rIdx+'][uraian_rekomendasi]'"
                                                            x-model="rec.uraian_rekomendasi"
                                                            class="w-full px-2.5 py-2 border border-slate-200 rounded-lg text-xs">
                                                    </div>
                                                    <div><label
                                                            class="block text-[11px] font-bold text-slate-700 mb-1">Nilai
                                                            (Rp) <span class="text-red-500">*</span></label><input
                                                            type="number"
                                                            :name="'findings['+fIdx+'][recommendations]['+rIdx+'][nilai_rekomendasi]'"
                                                            x-model.number="rec.nilai_rekomendasi" min="0"
                                                            class="w-full px-2.5 py-2 border border-slate-200 rounded-lg text-xs font-mono">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="form.findings.length > 0"
                            class="pt-3 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase">Total Estimasi Kerugian</span>
                            <span class="text-lg font-black text-red-600 font-mono"
                                x-text="'Rp ' + totalKerugian().toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="bab_2_hasil_audit" x-model="form.bab_2_hasil_audit">
            <input type="hidden" name="bab_3_penutup" x-model="form.bab_3_penutup">
        </form>

        <div class="fixed bottom-0 left-0 right-0 w-full bg-white border-t border-slate-200 z-40">
            <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
                <button type="button" @click="prevStep()" x-show="step > 1"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50"><x-lucide-arrow-left
                        class="w-4 h-4" /> Sebelumnya</button>
                <div x-show="step === 1"></div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="submitCurrentForm()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 text-sm font-bold text-slate-700 hover:bg-slate-50"><x-lucide-save
                            class="w-4 h-4" /> Simpan Draft</button>
                    <button type="button" @click="step < 4 ? nextStep() : submitCurrentForm()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold"><span
                            x-text="step === 4 ? 'Simpan & Preview' : 'Selanjutnya'"></span><x-lucide-arrow-right
                            class="w-4 h-4" /></button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const AUTOSAVE_URL = @json(route('auditor.lhp.autosave'));
        const CSRF_TOKEN = @json(csrf_token());
        document.addEventListener('DOMContentLoaded', function () {
            const initializedEditors = new Map();

            const getEditorContextClass = (trixNode) => {
                if (trixNode.classList.contains('trix-findings-large')) {
                    return 'sun-editor-findings-large';
                }
                if (trixNode.closest('.trix-large')) {
                    return 'sun-editor-large';
                }
                return '';
            };

            const initSunEditorFromTrix = (trixNode) => {
                if (!trixNode || trixNode.dataset.sunInitialized === '1') return;
                const hiddenInputId = trixNode.getAttribute('input');
                if (!hiddenInputId) return;

                const hiddenInput = document.getElementById(hiddenInputId);
                if (!hiddenInput) return;

                const wrapper = document.createElement('div');
                const contextClass = getEditorContextClass(trixNode);
                if (contextClass) wrapper.classList.add(contextClass);

                const textarea = document.createElement('textarea');
                textarea.id = 'sun_' + hiddenInputId;
                textarea.className = 'suneditor-sync w-full';
                textarea.value = hiddenInput.value || '';
                wrapper.appendChild(textarea);

                trixNode.insertAdjacentElement('afterend', wrapper);
                trixNode.style.display = 'none';
                trixNode.dataset.sunInitialized = '1';

                const editor = SUNEDITOR.create(textarea, {
                    height: 'auto',
                    minHeight: trixNode.classList.contains('trix-findings-large') ? '250px' : '180px',
                    buttonList: [
                        ['undo', 'redo'],
                        ['font', 'fontSize', 'formatBlock'],
                        ['bold', 'underline', 'italic', 'strike'],
                        ['fontColor', 'hiliteColor'],
                        ['align', 'horizontalRule'],
                        ['list', 'outdent', 'indent'],
                        ['table', 'link'],
                        ['removeFormat', 'codeView']
                    ],
                    formats: ['p', 'h1', 'h2', 'h3', 'blockquote'],
                    defaultTag: 'p',
                });

                editor.onChange = function (contents) {
                    hiddenInput.value = contents;
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                };

                const setActiveEditor = () => {
                    document.querySelectorAll('.sun-editor.is-active').forEach((node) => {
                        node.classList.remove('is-active');
                    });

                    const editorRoot = wrapper.querySelector('.sun-editor');
                    if (editorRoot) {
                        editorRoot.classList.add('is-active');
                    }
                };

                const editableNode = wrapper.querySelector('.sun-editor-editable');
                if (editableNode) {
                    editableNode.addEventListener('focus', setActiveEditor);
                    editableNode.addEventListener('click', setActiveEditor);
                }

                // Default: toolbar disembunyikan, tampil saat editor ini aktif.
                setTimeout(() => {
                    const editorRoot = wrapper.querySelector('.sun-editor');
                    if (editorRoot) {
                        editorRoot.classList.remove('is-active');
                    }
                }, 0);

                initializedEditors.set(hiddenInputId, { editor, hiddenInput });
            };

            const bootstrapAllEditors = () => {
                document.querySelectorAll('trix-editor[input]').forEach(initSunEditorFromTrix);
            };

            bootstrapAllEditors();

            const observer = new MutationObserver(() => {
                bootstrapAllEditors();
            });
            observer.observe(document.body, { childList: true, subtree: true });

            const form = document.getElementById('lhp-main-form');
            if (form) {
                form.addEventListener('submit', function () {
                    initializedEditors.forEach((ctx) => {
                        ctx.hiddenInput.value = ctx.editor.getContents();
                        ctx.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                });
            }
        });

        function lhpWizard() {
            const editData = {!! json_encode($lhp) !!};
            const editContent = editData ? editData.content : null;
            const editMeta = editContent && editContent.metadata_tambahan ? editContent.metadata_tambahan : null;
            const isEdit = !!editData;
            const existingFindings = isEdit && editData.findings ? editData.findings.map(f => ({
                kode_temuan: f.kode_temuan || '', uraian_temuan: f.uraian_temuan || '', rekomendasi_teks: f.rekomendasi_teks || '', kerugian_negara: f.kerugian_negara || 0, kerugian_daerah: f.kerugian_daerah || 0,
                recommendations: (f.recommendations || []).map(r => ({ kode_rekomendasi: r.kode_rekomendasi || '', uraian_rekomendasi: r.uraian_rekomendasi || '', nilai_rekomendasi: r.nilai_rekomendasi || 0 }))
            })) : [];

            const storageKey = isEdit ? ('lhp_draft_edit_' + editData.id) : 'lhp_draft_new';
            const normalizeDateInput = (value) => {
                if (!value) return '';
                const str = String(value);
                return str.length >= 10 ? str.slice(0, 10) : str;
            };

            return {
                step: 1,
                steps: [
                    { title: 'Identitas', desc: 'Cover dan surat pengantar' },
                    { title: 'Informasi Umum', desc: 'BAB I dengan accordion' },
                    { title: 'Uraian & Simpulan', desc: 'BAB II + BAGIAN PERTAMA' },
                    { title: 'Temuan', desc: 'Kertas kerja dan rekomendasi' }
                ],
                openAccordions: { p1: true, p2: false, p3: false, p4: false },
                validationError: '',
                fieldErrors: {},
                submitting: false,
                autosaveTimer: null,
                autosaveServerTimer: null,
                lastServerAutosaveAt: null,
                serverEditId: isEdit ? editData.id : '',
                autosaveLabel: 'Auto-save aktif',
                form: {
                    nomor_lhp: isEdit ? editData.nomor_lhp : '',
                    tgl_lhp: isEdit ? normalizeDateInput(editData.tgl_lhp || editData.tanggal_lhp) : '',
                    judul: isEdit ? editData.judul : '',
                    tahun_anggaran: isEdit ? String(editData.tahun_anggaran) : '{{ old("tahun_anggaran") ?? date("Y") }}',
                    opd_id: isEdit ? String(editData.opd_id) : '{{ old("opd_id") ?? "" }}',
                    sifat: isEdit && editMeta ? (editMeta.sifat || '') : '',
                    lampiran: isEdit && editMeta ? (editMeta.lampiran || '') : '1 (satu) berkas',
                    tujuan_surat: isEdit && editMeta ? (editMeta.tujuan_surat || '') : '',
                    nomor_spt: isEdit && editMeta ? (editMeta.nomor_spt || '') : '',
                    tanggal_spt: isEdit && editMeta ? (editMeta.tanggal_spt || '') : '',
                    tim_pemeriksa: (() => {
                        if (!isEdit || !editMeta) return [''];
                        if (Array.isArray(editMeta.tim_pemeriksa) && editMeta.tim_pemeriksa.length > 0) return editMeta.tim_pemeriksa;
                        if (Array.isArray(editMeta.tembusan) && editMeta.tembusan.length > 0) return editMeta.tembusan;
                        const legacy = [editMeta.tembusan_1 || '', editMeta.tembusan_2 || ''].filter(Boolean);
                        return legacy.length > 0 ? legacy : [''];
                    })(),
                    tembusan: (() => {
                        if (!isEdit || !editMeta) return [''];
                        if (Array.isArray(editMeta.tembusan) && editMeta.tembusan.length > 0) return editMeta.tembusan;
                        const legacy = [editMeta.tembusan_1 || '', editMeta.tembusan_2 || ''].filter(Boolean);
                        return legacy.length > 0 ? legacy : [''];
                    })(),
                    dasar_audit: isEdit && editMeta ? (editMeta.dasar_audit || '') : '',
                    tujuan_audit: isEdit && editMeta ? (editMeta.tujuan_audit || '') : '',
                    metodologi_audit: isEdit && editMeta ? (editMeta.metodologi_audit || '') : '',
                    batasan_tanggung_jawab: isEdit && editMeta ? (editMeta.batasan_tanggung_jawab || '') : '',
                    sasaran_audit: isEdit && editMeta ? (editMeta.sasaran_audit || '') : '',
                    ruang_lingkup: isEdit && editMeta ? (editMeta.ruang_lingkup || '') : '',
                    periode_audit: isEdit && editMeta ? (editMeta.periode_audit || '') : '',
                    info_tujuan_program: isEdit && editMeta ? (editMeta.info_tujuan_program || '') : '',
                    info_kegiatan_program: isEdit && editMeta ? (editMeta.info_kegiatan_program || '') : '',
                    info_lokasi_dana: isEdit && editMeta ? (editMeta.info_lokasi_dana || '') : '',
                    info_sumber_dana: isEdit && editMeta ? (editMeta.info_sumber_dana || '') : '',
                    info_struktur_org: isEdit && editMeta ? (editMeta.info_struktur_org || '') : '',
                    penilaian_spi: isEdit && editMeta ? (editMeta.penilaian_spi || '') : '',
                    penilaian_ketaatan: isEdit && editMeta ? (editMeta.penilaian_ketaatan || '') : '',
                    kesesuaian_output: isEdit && editMeta ? (editMeta.kesesuaian_output || '') : '',
                    hal_penting_lainnya: isEdit && editMeta ? (editMeta.hal_penting_lainnya || '') : '',
                    tindak_lanjut_sebelumnya: isEdit && editMeta ? (editMeta.tindak_lanjut_sebelumnya || '') : '',
                    simpulan_audit: isEdit && editMeta ? (editMeta.simpulan_audit || '') : '',
                    bab_2_hasil_audit: isEdit && editContent ? (editContent.bab_2_hasil_audit || '') : '',
                    bab_3_penutup: isEdit && editContent ? (editContent.bab_3_penutup || '') : '',
                    findings: isEdit ? existingFindings : []
                },

                init() {
                    const persisted = localStorage.getItem(storageKey);
                    if (persisted) {
                        try { this.form = Object.assign(this.form, JSON.parse(persisted)); } catch (e) { }
                    }
                    this.$watch('form', () => this.queueAutosave(), { deep: true });
                },
                toggleAccordion(id) { this.openAccordions[id] = !this.openAccordions[id]; },
                goToStep(target) { this.clearErrors(); this.step = target; window.scrollTo({ top: 0, behavior: 'smooth' }); },
                clearErrors() { this.validationError = ''; this.fieldErrors = {}; },
                validateStep1() {
                    this.clearErrors();
                    let ok = true;
                    if (!this.form.nomor_lhp.trim()) { this.fieldErrors.nomor_lhp = 'Nomor LHP wajib diisi.'; ok = false; }
                    if (!this.form.tgl_lhp) { this.fieldErrors.tgl_lhp = 'Tanggal wajib dipilih.'; ok = false; }
                    if (!this.form.judul.trim()) { this.fieldErrors.judul = 'Judul pemeriksaan wajib diisi.'; ok = false; }
                    if (!this.form.tahun_anggaran.trim() || !/^\d{4}$/.test(this.form.tahun_anggaran.trim())) { this.fieldErrors.tahun_anggaran = 'Tahun anggaran harus 4 digit.'; ok = false; }
                    if (!this.form.opd_id) { this.fieldErrors.opd_id = 'Pilih OPD terlebih dahulu.'; ok = false; }
                    if (!ok) this.validationError = 'Lengkapi data identitas wajib sebelum lanjut.';
                    return ok;
                },
                validateStep4() {
                    if (this.form.findings.length === 0) { this.validationError = 'Tambahkan minimal 1 temuan pada step 4.'; return false; }
                    for (let i = 0; i < this.form.findings.length; i++) {
                        if (!this.form.findings[i].uraian_temuan.trim()) { this.validationError = 'Uraian temuan ke-' + (i + 1) + ' masih kosong.'; return false; }
                    }
                    return true;
                },
                validateCurrentStep() { return true; },
                nextStep() { if (this.step < 4) { this.clearErrors(); this.step++; window.scrollTo({ top: 0, behavior: 'smooth' }); } },
                prevStep() { if (this.step > 1) { this.clearErrors(); this.step--; window.scrollTo({ top: 0, behavior: 'smooth' }); } },
                addTimPemeriksa() { this.form.tim_pemeriksa.push(''); },
                removeTimPemeriksa(idx) {
                    if (this.form.tim_pemeriksa.length <= 1) return;
                    this.form.tim_pemeriksa.splice(idx, 1);
                },
                addTembusan() { this.form.tembusan.push(''); },
                removeTembusan(idx) {
                    if (this.form.tembusan.length <= 1) return;
                    this.form.tembusan.splice(idx, 1);
                },
                addFinding() { this.form.findings.push({ kode_temuan: '', uraian_temuan: '', rekomendasi_teks: '', kerugian_negara: 0, kerugian_daerah: 0, recommendations: [] }); },
                removeFinding(idx) { this.form.findings.splice(idx, 1); },
                addRecommendation(fIdx) { if (!this.form.findings[fIdx].recommendations) this.form.findings[fIdx].recommendations = []; this.form.findings[fIdx].recommendations.push({ kode_rekomendasi: '', uraian_rekomendasi: '', nilai_rekomendasi: 0 }); },
                removeRecommendation(fIdx, rIdx) { this.form.findings[fIdx].recommendations.splice(rIdx, 1); },
                totalKerugian() { return this.form.findings.reduce((sum, f) => sum + (parseFloat(f.kerugian_negara) || 0) + (parseFloat(f.kerugian_daerah) || 0), 0); },
                queueAutosave() {
                    if (this.autosaveTimer) clearTimeout(this.autosaveTimer);
                    this.autosaveTimer = setTimeout(() => this.saveLocal(false), 800);

                    if (this.autosaveServerTimer) clearTimeout(this.autosaveServerTimer);
                    this.autosaveServerTimer = setTimeout(() => this.saveDraftToServer(), 1600);
                },
                saveLocal(showLabel) {
                    try {
                        localStorage.setItem(storageKey, JSON.stringify(this.form));
                        const now = new Date();
                        const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        this.autosaveLabel = 'Tersimpan otomatis ' + time;
                        if (showLabel) setTimeout(() => { this.autosaveLabel = 'Auto-save aktif'; }, 2200);
                    } catch (e) { }
                },
                async saveDraftToServer() {
                    if (this.submitting) return;

                    const payload = {
                        ...this.form,
                        is_draft: 1,
                        edit_id: this.serverEditId || undefined
                    };

                    try {
                        const response = await fetch(AUTOSAVE_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(payload),
                            credentials: 'same-origin'
                        });

                        if (!response.ok) {
                            throw new Error('Gagal autosave ke server');
                        }

                        const data = await response.json();
                        this.lastServerAutosaveAt = data.updated_at || null;
                        if (!this.serverEditId && data.lhp_id) {
                            this.serverEditId = data.lhp_id;
                        }
                    } catch (error) {
                        console.error(error);
                    }
                },
                submitCurrentForm() {
                    this.submitting = true;
                    this.saveLocal(true);
                    // Jangan hapus draft lokal di sini.
                    // Jika validasi gagal (contoh: nomor LHP duplikat), user tetap bisa lanjut dari isian terakhir.
                    document.getElementById('lhp-main-form').submit();
                },
                submitForm(event) { event.preventDefault(); this.submitCurrentForm(); },
                resetFormDraft() {
                    if (confirm('Kosongkan semua isian form ini?')) {
                        localStorage.removeItem(storageKey);
                        window.location.reload();
                    }
                }
            };
        }
    </script>
@endsection
