@props(['recommendation'])

<div x-data="{ open: false }" @open-evidence-modal-{{ $recommendation->id }}.window="open = true" x-cloak>
    <!-- Modal Backdrop -->
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
        <!-- Modal Content -->
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             @click.away="open = false"
             class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <x-lucide-upload-cloud class="w-5 h-5 text-blue-600" />
                    Unggah Bukti Tindak Lanjut
                </h3>
                <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-200 transition-colors">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            <form action="{{ route('skpd.evidence.store', $recommendation->id) }}" method="POST" enctype="multipart/form-data" class="p-6 pb-5">
                @csrf
                <div class="mb-5 bg-blue-50/50 p-3 rounded-lg border border-blue-100 text-sm">
                    <p class="font-bold text-blue-800 mb-1">Ref: {{ $recommendation->kode_rekomendasi }}</p>
                    <p class="text-blue-600 leading-tight">{{ \Illuminate\Support\Str::limit($recommendation->uraian_rekomendasi, 100) }}</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nominal Setoran (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                            <input type="number" name="nominal_setoran" required min="1" max="{{ $recommendation->remaining_balance }}" placeholder="Contoh: 15000000" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-mono shadow-sm">
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">*Maksimal sisa hutang/kewajiban: Rp {{ number_format($recommendation->remaining_balance, 0, ',', '.') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Dokumen Bukti (PDF/JPG/PNG)</label>
                        <input type="file" name="file_path" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-white transition-colors cursor-pointer shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan / Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" rows="3" placeholder="Tuliskan nomor referensi bank atau detail setoran..." class="w-full p-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm shadow-sm"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-5 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors flex items-center gap-2 shadow-sm shadow-blue-200">
                        <x-lucide-send class="w-4 h-4" /> Ajukan Validasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
