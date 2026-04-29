<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lhp;
use App\Models\Opd;
use App\Models\User;
use App\Notifications\LhpWorkflowNotification;
use App\Services\LhpService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

class LhpController extends Controller
{
    private function isPimpinanRole(?string $role): bool
    {
        if (!$role) {
            return false;
        }

        return $role === 'admin'
            || $role === 'inspektur_daerah'
            || Str::startsWith($role, 'inspektur_pembantu');
    }

    /**
     * Daftar Seluruh LHP (Direktori)
     */
    public function index(Request $request): View
    {
        $query = Lhp::with('opd')->latest('tahun_anggaran')->latest('created_at');

        // Fitur Pencarian Cerdas
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lhp', 'like', "%{$search}%")
                  ->orWhere('judul', 'like', "%{$search}%")
                  ->orWhereHas('opd', function ($q2) use ($search) {
                      $q2->where('nama_opd', 'like', "%{$search}%");
                  });
            });
        }

        $user = auth()->user();

        // Role-based visibility per requirement terbaru klien.
        if ($user->role === 'admin') {
            // Admin: lihat semua status, tanpa filter tambahan.
        } elseif ($user->role === 'inspektur_daerah') {
            // Inspektur Daerah: jangan tampilkan draft.
            $query->where('status', '!=', 'draft');
        } elseif (Str::startsWith((string) $user->role, 'inspektur_pembantu')) {
            // Irban: tampilkan semua termasuk draft.
            // Catatan: filter wilayah irban belum diterapkan karena belum ada mapping wilayah khusus di tabel LHP.
        } elseif ($user->role === 'ketua_tim') {
            // Ketua Tim: semua status termasuk draft, dibatasi per tim.
            $query->where('tim', $user->tim);
        } elseif ($user->role === 'auditor') {
            // Auditor: semua status termasuk draft, dibatasi pembuat dokumen.
            // Mapping requirement "user_id = auth()->id()" ke kolom existing "created_by".
            $query->where('created_by', $user->id);
        } else {
            // Role lain (mis. skpd): hanya dokumen final/non-draft.
            $query->where('status', '!=', 'draft');
        }

        $lhps = $query->paginate(10)->withQueryString();

        return view('lhp-index', compact('lhps'));
    }

    /**
     * Form Wizard: Buat LHP Baru / Edit LHP yang dikembalikan
     */
    public function create(Request $request): View
    {
        $user = auth()->user();

        $lhp = null;
        if ($request->filled('edit')) {
            $lhp = Lhp::with(['content', 'findings.recommendations'])->find($request->edit);
            if (!$lhp) {
                abort(404, 'LHP tidak ditemukan.');
            }
            if (!$this->isPimpinanRole($user->role) && $user->tim !== $lhp->tim) {
                abort(403, 'Akses Ditolak. Anda hanya dapat mengedit LHP milik tim Anda sendiri.');
            }
        } else {
            if (!in_array($user->role, ['admin', 'auditor'])) {
                abort(403, 'Akses Ditolak. Anda tidak berwenang membuat LHP baru.');
            }
        }

        $opds = Opd::orderBy('nama_opd')->get();
        return view('lhp-form', compact('opds', 'lhp'));
    }

    /**
     * Simpan LHP secara Atomik (Draft) atau Update LHP yang sudah ada
     */
    public function store(Request $request, LhpService $service)
    {
        $user = auth()->user();
        $editId = $request->input('edit_id');

        if ($editId) {
            $existingLhp = Lhp::findOrFail($editId);
            if (!$this->isPimpinanRole($user->role) && $user->tim !== $existingLhp->tim) {
                abort(403, 'Akses Ditolak. Anda hanya dapat mengedit LHP milik tim Anda sendiri.');
            }
        } else {
            if (!in_array($user->role, ['admin', 'auditor'])) {
                abort(403, 'Akses Ditolak. Anda tidak berwenang membuat LHP baru.');
            }
        }

        $isDraft = $request->boolean('is_draft', true);

        $rules = [
            'nomor_lhp'      => ($isDraft ? 'nullable' : 'required') . '|string|max:100|unique:lhps,nomor_lhp' . ($editId ? ',' . $editId : ''),
            'tgl_lhp'        => ($isDraft ? 'nullable' : 'required') . '|date',
            'judul'          => ($isDraft ? 'nullable' : 'required') . '|string|max:500',
            'tahun_anggaran' => ($isDraft ? 'nullable' : 'required') . '|digits:4',
            'opd_id'         => ($isDraft ? 'nullable' : 'required') . '|exists:opds,id',
            'sifat'          => 'nullable|string|max:100',
            'lampiran'       => 'nullable|string|max:200',
            'tujuan_surat'   => 'nullable|string|max:255',
            'nomor_spt'      => 'nullable|string|max:255',
            'tanggal_spt'    => 'nullable|string|max:255',
            'dasar_audit'    => 'nullable|string',
            'tujuan_audit'   => 'nullable|string',
            'metodologi_audit' => 'nullable|string',
            'sasaran_audit'  => 'nullable|string',
            'bab_2_hasil_audit' => 'nullable|string',
            'bab_3_penutup'  => 'nullable|string',
            'batasan_tanggung_jawab' => 'nullable|string',
            'ruang_lingkup'  => 'nullable|string',
            'periode_audit'  => 'nullable|string',
            'info_tujuan_program' => 'nullable|string',
            'info_kegiatan_program' => 'nullable|string',
            'info_lokasi_dana' => 'nullable|string',
            'info_sumber_dana' => 'nullable|string',
            'info_struktur_org' => 'nullable|string',
            'penilaian_spi'  => 'nullable|string',
            'simpulan_audit' => 'nullable|string',
            'penilaian_ketaatan' => 'nullable|string',
            'kesesuaian_output' => 'nullable|string',
            'hal_penting_lainnya' => 'nullable|string',
            'tindak_lanjut_sebelumnya' => 'nullable|string',
            'metadata_tambahan.tim_pemeriksa' => 'nullable|array',
            'metadata_tambahan.tim_pemeriksa.*' => 'nullable|string|max:255',
            'metadata_tambahan.tembusan' => 'nullable|array',
            'metadata_tambahan.tembusan.*' => 'nullable|string|max:255',
            // Backward compatibility untuk payload lama.
            'tembusan'       => 'nullable|array',
            'tembusan.*'     => 'nullable|string|max:255',
        ];

        if (!$isDraft) {
            $rules = array_merge($rules, [
                'dasar_audit' => 'required|string',
                'tujuan_audit' => 'required|string',
                'sasaran_audit' => 'required|string',
                'penilaian_ketaatan' => 'required|string',
                'simpulan_audit' => 'required|string',
            ]);
        }

        $request->validate($rules, [
            'nomor_lhp.required'      => 'Nomor LHP wajib diisi.',
            'nomor_lhp.unique'        => 'Nomor LHP ini sudah terdaftar di sistem. Gunakan nomor yang berbeda.',
            'nomor_lhp.max'           => 'Nomor LHP maksimal 100 karakter.',
            'tgl_lhp.required'        => 'Tanggal LHP wajib diisi.',
            'tgl_lhp.date'            => 'Format tanggal tidak valid.',
            'judul.required'          => 'Judul Pemeriksaan wajib diisi.',
            'tahun_anggaran.required' => 'Tahun Anggaran wajib diisi.',
            'tahun_anggaran.digits'   => 'Tahun Anggaran harus berupa 4 digit angka (contoh: 2025).',
            'opd_id.required'         => 'Pilih OPD / Instansi terlebih dahulu.',
            'opd_id.exists'           => 'OPD yang dipilih tidak valid.',
        ]);

        if ($editId) {
            $lhp = $service->updateFullLhp($editId, $request->all());
            $message = 'Dokumen LHP berhasil diperbarui. Silakan tinjau sebelum mengajukan ulang.';
        } else {
            $lhp = $service->createFullLhp($request->all());
            $message = 'Dokumen LHP berhasil disimpan sebagai Draft. Silakan tinjau sebelum dipublikasikan.';
            
            \App\Models\LhpLog::create([
                'lhp_id' => $lhp->id,
                'user_id' => auth()->id(),
                'action' => 'Menciptakan Draft LHP Baru'
            ]);
        }

        return redirect()->route('auditor.lhp.preview', $lhp->id)
            ->with('success', $message);
    }

    public function autosave(Request $request, LhpService $service)
    {
        $user = auth()->user();
        $editId = $request->input('edit_id');

        if ($editId) {
            $existingLhp = Lhp::findOrFail($editId);
            if (!$this->isPimpinanRole($user->role) && $user->tim !== $existingLhp->tim) {
                abort(403, 'Akses Ditolak. Anda hanya dapat mengedit LHP milik tim Anda sendiri.');
            }
        } elseif (!in_array($user->role, ['admin', 'auditor'])) {
            abort(403, 'Akses Ditolak. Anda tidak berwenang membuat LHP baru.');
        }

        $payload = $request->all();
        $payload['is_draft'] = true;

        $lhp = $editId
            ? $service->updateFullLhp($editId, $payload)
            : $service->createFullLhp($payload);

        return response()->json([
            'ok' => true,
            'lhp_id' => $lhp->id,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Ajukan LHP ke Atasan untuk di Reviu.
     */
    public function submitForReview(Lhp $lhp)
    {
        if (!$this->isReadyForReview($lhp)) {
            return redirect()
                ->back()
                ->with('error', 'LHP belum siap diajukan. Lengkapi identitas utama, narasi audit inti, dan minimal 1 temuan.');
        }

        $lhp->update(['status' => 'review_ketua']);
        
        \App\Models\LhpLog::create([
            'lhp_id' => $lhp->id,
            'user_id' => auth()->id(),
            'action' => 'Mengajukan LHP untuk direviu oleh Ketua Tim'
        ]);

        $targetKetuaTim = User::query()
            ->where('role', 'ketua_tim')
            ->when($lhp->tim, fn ($q) => $q->where('tim', $lhp->tim))
            ->get();

        if ($targetKetuaTim->isNotEmpty()) {
            $url = route('lhp.show', $lhp->id);
            Notification::send(
                $targetKetuaTim,
                new LhpWorkflowNotification(
                    $lhp,
                    'LHP Baru menunggu reviu Anda: ' . $lhp->judul,
                    $url
                )
            );
        }

        return redirect()->back()->with('success', 'LHP berhasil diajukan ke Ketua Tim untuk direviu.');
    }

    private function isReadyForReview(Lhp $lhp): bool
    {
        $lhp->loadMissing('content', 'findings');
        $meta = $lhp->content->metadata_tambahan ?? [];

        $requiredMetaFields = [
            'dasar_audit',
            'tujuan_audit',
            'sasaran_audit',
            'penilaian_ketaatan',
            'simpulan_audit',
        ];

        $hasCoreIdentity = !empty($lhp->nomor_lhp)
            && !empty($lhp->tgl_lhp)
            && !empty($lhp->judul)
            && !empty($lhp->tahun_anggaran)
            && !empty($lhp->opd_id);

        $hasCoreNarrative = collect($requiredMetaFields)
            ->every(fn (string $key) => !empty(trim((string) ($meta[$key] ?? ''))));

        $hasFindings = $lhp->findings->count() > 0;

        return $hasCoreIdentity && $hasCoreNarrative && $hasFindings;
    }

    /**
     * Preview LHP sebelum dipublikasikan.
     */
    public function preview(Lhp $lhp): View
    {
        $user = auth()->user();
        if (!$this->isPimpinanRole($user->role) && $user->tim !== $lhp->tim) {
            abort(403, 'Akses Ditolak. Anda hanya dapat melihat pratinjau LHP milik tim Anda sendiri.');
        }

        $lhp->load([
            'opd',
            'content',
            'findings.recommendations.followUpEvidences.user',
            'reviews.reviewer'
        ]);

        return view('lhp-detail', [
            'lhp'        => $lhp,
            'previewMode' => true,
        ]);
    }

    /**
     * Menampilkan Detail LHP
     */
    public function show(Lhp $lhp, Request $request): View
    {
        $lhp->load([
            'opd',
            'content',
            'findings.recommendations.followUpEvidences.user',
            'reviews.reviewer',
            'logs.user'
        ]);

        $user = $request->user();

        return view('lhp-detail', compact('lhp'));
    }

    /**
     * Publikasikan LHP (Finalize) — Hanya Admin & Inspektur Pembantu
     */
    public function finalize(Lhp $lhp, LhpService $service)
    {
        if (!in_array(auth()->user()->role, ['admin', 'inspektur_pembantu', 'inspektur_pembantu_1'])) {
            abort(403, 'Akses Ditolak. Hanya Inspektur Pembantu atau Admin yang berwenang mempublikasikan LHP.');
        }

        $service->finalizeLhp($lhp->id);

        return redirect()->route('lhp.show', $lhp->id)
            ->with('success', 'LHP berhasil dipublikasikan! Dokumen sekarang aktif dan terlihat oleh SKPD terkait.');
    }

    /**
     * Export LHP ke Format Cetak Resmi (PDF) Menggunakan barryvdh/laravel-dompdf
     */
    public function export(Lhp $lhp)
    {
        $user = auth()->user();
        if (!$this->isPimpinanRole($user->role) && $user->tim !== $lhp->tim) {
            abort(403, 'Akses Ditolak. Anda hanya dapat mengekspor LHP milik tim Anda sendiri.');
        }

        $lhp->load(['opd', 'content', 'findings.recommendations']);
        $data = [
            'lhp' => $lhp,
            'kopSurat' => $this->buildKopSuratHtml(),
            'pageNumberOffset' => 0,
            'showPageNumberFrom' => 1,
        ];

        $safeName = 'LHP_' . str_replace('/', '_', $lhp->nomor_lhp) . '.pdf';
        $tempDir = storage_path('app/temp');

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $coverTempPath = $tempDir . DIRECTORY_SEPARATOR . 'cover_' . $lhp->id . '_' . uniqid() . '.pdf';
        $bodyTempPath = $tempDir . DIRECTORY_SEPARATOR . 'body_' . $lhp->id . '_' . uniqid() . '.pdf';
        $mergedBinary = '';

        try {
            Pdf::loadView('pdf.lhp-cover', $data)
                ->setPaper('a4', 'portrait')
                ->setWarnings(false)
                ->save($coverTempPath);

            Pdf::loadView('pdf.lhp-body', $data)
                ->setPaper('a4', 'portrait')
                ->setWarnings(false)
                ->setOption(['isPhpEnabled' => true])
                ->save($bodyTempPath);

            $pdfMerger = new Fpdi();

            foreach ([$coverTempPath, $bodyTempPath] as $sourcePath) {
                $pageCount = $pdfMerger->setSourceFile($sourcePath);

                for ($page = 1; $page <= $pageCount; $page++) {
                    $templateId = $pdfMerger->importPage($page);
                    $pdfMerger->AddPage();
                    $pdfMerger->useTemplate($templateId, ['adjustPageSize' => true]);
                }
            }

            $mergedBinary = $pdfMerger->Output('S');
        } finally {
            if (File::exists($coverTempPath)) {
                File::delete($coverTempPath);
            }

            if (File::exists($bodyTempPath)) {
                File::delete($bodyTempPath);
            }
        }

        return response($mergedBinary, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $safeName . '"');
    }

    public function destroy(Lhp $lhp)
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Akses ditolak.');

        $lhp->delete();

        return redirect()->route('lhp.index')
            ->with('success', 'LHP berhasil dihapus.');
    }

    private function buildKopSuratHtml(): string
    {
        $logoPath = public_path('logo.png');
        if (!file_exists($logoPath)) {
            $logoPath = base_path('../public_html/logo.png');
        }

        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $logoHtml = '<img src="' . $base64 . '" style="width: 80px; height: auto;">';
        } else {
            $logoHtml = '<div style="width: 80px; height: 100px; border: 1px solid #ccc; text-align: center; line-height: 100px; font-size: 10px;">LOGO</div>';
        }

        return '
            <table width="100%" style="border-collapse: collapse; margin-bottom: 25px;">
                <tr>
                    <td width="15%" style="text-align: center; vertical-align: middle; padding: 0; border: none;">
                        ' . $logoHtml . '
                    </td>
                    <td width="85%" style="text-align: center; line-height: 1.1; padding: 0; border: none;">
                        <div style="text-transform: uppercase; font-weight: bold; font-size: 16pt;">PEMERINTAH KABUPATEN BARITO SELATAN</div>
                        <div style="text-transform: uppercase; font-weight: bold; font-style: italic; font-size: 22pt; letter-spacing: 0.5px;">INSPEKTORAT DAERAH</div>
                        <div style="font-size: 10pt;">Jln. Pelita Raya No. 60 Buntok Kode Pos 73711 Kalimantan Tengah</div>
                        <div style="font-size: 10pt;">Telp. (0525) 21262 Fax (0525) 22357</div>
                        <div style="font-size: 9pt;">Email : inspektorat@baritoselatan.co.id / inspektoratdaerah.barsel@gmail.com</div>
                        <div style="font-size: 9pt;">Website : inspektorat.baritoselatankab.go.id</div>
                    </td>
                </tr>
            </table>
            <div style="border-top: 3px solid black; border-bottom: 1px solid black; height: 2px; margin-top: 8px; margin-bottom: 25px;"></div>
        ';
    }
}
