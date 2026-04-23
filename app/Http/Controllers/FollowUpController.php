<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Models\FollowUpEvidence;
use App\Services\FollowUpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FollowUpController extends Controller
{
    /**
     * Menerima Upload Bukti dari SKPD
     */
    public function store(Request $request, Recommendation $recommendation, FollowUpService $service)
    {
        // Hanya SKPD yang boleh mengunggah bukti
        if ($request->user()->role !== 'skpd') {
            abort(403, 'Hanya pengguna SKPD yang diizinkan mengunggah bukti tindak lanjut.');
        }

        $request->validate([
            'nominal_setoran' => 'required|numeric|min:0',
            'file_path' => 'required|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:5120',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $data = [
            'recommendation_id' => $recommendation->id,
            'user_id' => $request->user()->id,
            'nominal_setoran' => (float) $request->nominal_setoran,
            'catatan' => $request->catatan,
        ];

        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')->store('evidences', 'private');
        }

        $service->submitEvidence($data);

        return back()->with('success', 'Bukti tindak lanjut berhasil diunggah dan sedang menanti validasi Auditor.');
    }

    /**
     * Unduh/Lihat bukti tindak lanjut melalui jalur private dan berotorisasi.
     */
    public function download(Request $request, FollowUpEvidence $evidence)
    {
        $user = $request->user();
        $lhp = $evidence->recommendation->finding->lhp;

        if ($user->role === 'skpd') {
            abort_unless($user->opd_id === $lhp->opd_id, 403, 'Anda tidak berhak mengakses bukti ini.');
        }

        if (in_array($user->role, ['auditor', 'ketua_tim'], true)) {
            abort_unless(empty($lhp->tim) || $lhp->tim === $user->tim, 403, 'Anda tidak berhak mengakses bukti ini.');
        }

        $filename = basename((string) $evidence->file_path);
        if (Storage::disk('private')->exists((string) $evidence->file_path)) {
            return Storage::disk('private')->download((string) $evidence->file_path, $filename);
        }

        // Fallback legacy untuk bukti lama yang pernah disimpan ke disk public.
        if (Storage::disk('public')->exists((string) $evidence->file_path)) {
            return Storage::disk('public')->download((string) $evidence->file_path, $filename);
        }

        abort(404, 'Berkas bukti tidak ditemukan.');
    }

    /**
     * Eksekusi Verifikasi dari Auditor
     */
    public function verify(Request $request, FollowUpEvidence $evidence, FollowUpService $service)
    {
        $request->validate([
            'status_verifikasi' => 'required|in:approved,rejected',
            'catatan_verifikator' => 'nullable|string',
        ]);

        $service->verifyEvidence($evidence->id, [
            'status_verifikasi' => $request->status_verifikasi,
            'catatan_verifikator' => $request->catatan_verifikator,
        ]);

        return back()->with('success', 'Status verifikasi berkas berhasil dibekukan.');
    }
}
