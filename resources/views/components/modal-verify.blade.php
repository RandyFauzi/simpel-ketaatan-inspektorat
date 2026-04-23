@props(['evidence'])

<div x-data="{ open: false }" @open-verify-modal-{{ $evidence->id }}.window="open = true" x-cloak>
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
                    <x-lucide-shield-check class="w-5 h-5 text-emerald-600" />
                    Verifikasi Dokumen Setoran
                </h3>
                <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-200 transition-colors">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            <form action="{{ route('auditor.followup.verify', $evidence->id) }}" method="POST" class="p-6 pb-5">
                @csrf
                @method('PATCH')
                <div class="mb-5 grid grid-cols-2 gap-4">
                    <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                        <p class="text-[10px] uppercase font-bold text-blue-500 mb-0.5">Dikirim Oleh Pengguna</p>
                        <p class="text-sm font-bold text-blue-900">{{ $evidence->user->name ?? 'User SKPD' }}</p>
                    </div>
                    <div class="bg-amber-50 p-3 rounded-xl border border-amber-100/50">
                        <p class="text-[10px] uppercase font-bold text-amber-500 mb-0.5">Nominal Setor</p>
                        <p class="text-sm font-mono font-black text-amber-700">Rp {{ number_format($evidence->nominal_setoran, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <a href="{{ route('followup.download', $evidence->id) }}" target="_blank" class="w-full flex items-center justify-center gap-2 py-3 bg-white shadow-sm border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors font-bold text-slate-700 text-sm group">
                        <x-lucide-external-link class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-colors" /> Tinjau Lampiran Dokumen
                    </a>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Keputusan Validasi Auditor</label>
                        <div class="grid grid-cols-2 gap-3" x-data="{ status: 'approved' }">
                            <label class="cursor-pointer">
                                <input type="radio" name="status_verifikasi" value="approved" x-model="status" class="peer sr-only">
                                <div class="px-4 py-3 border-2 border-slate-200 bg-white rounded-xl text-center peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-slate-500 font-bold transition-all shadow-sm">
                                    <x-lucide-check-circle class="w-5 h-5 mx-auto mb-1" />
                                    Setujui Berkas
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status_verifikasi" value="rejected" x-model="status" class="peer sr-only">
                                <div class="px-4 py-3 border-2 border-slate-200 bg-white rounded-xl text-center peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 text-slate-500 font-bold transition-all shadow-sm">
                                    <x-lucide-x-circle class="w-5 h-5 mx-auto mb-1" />
                                    Tolak Dokumen
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            Catatan Audit
                            <span class="text-red-500 text-[10px] font-medium bg-red-50 px-2 py-0.5 rounded">*Wajib jika ditolak</span>
                        </label>
                        <textarea name="catatan_verifikator" rows="3" placeholder="Sebutkan alasan penolakan atau catatan opsional saat setujui..." class="w-full p-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm shadow-sm"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-5 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-colors flex items-center gap-2 shadow-sm">
                        <x-lucide-shield-alert class="w-4 h-4" /> Eksekusi Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
