<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Finding;
use App\Http\Requests\UpdateFindingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FindingController extends Controller
{
    /**
     * Tampilkan Repositori Global Temuan (Cross-LHP Findings)
     */
    public function index(Request $request): View
    {
        $query = Finding::query()
            ->select([
                'id',
                'lhp_id',
                'kode_temuan',
                'uraian_temuan',
                'kerugian_negara',
                'kerugian_daerah',
                'created_at',
            ])
            ->with([
                'lhp:id,nomor_lhp,tgl_lhp,judul,opd_id,status',
                'lhp.opd:id,nama_opd',
                'recommendations:id,finding_id,status_tlhp,catatan_tlhp',
            ])
            ->latest('created_at');

        // RBAC:
        $user = auth()->user();
        if ($user->role === 'skpd') {
            $query->whereHas('lhp', function($q) use ($user) {
                $q->where('opd_id', $user->opd_id)
                  ->whereIn('status', ['published', 'closed']);
            });
        } else {
            // Auditor & Admin melihat semua temuan dari LHP yang sudah diterbitkan minimal
            $query->whereHas('lhp', function($q) {
                $q->whereIn('status', ['published', 'closed']);
            });
        }

        // Pencarian Substantif (Uraian, Kode, Nama OPD)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('uraian_temuan', 'like', "%{$search}%")
                  ->orWhere('kode_temuan', 'like', "%{$search}%")
                  ->orWhereHas('lhp.opd', function ($q2) use ($search) {
                      $q2->where('nama_opd', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Cepat: Hanya Belum Selesai
        if ($request->input('filter') === 'belum_selesai') {
            $query->where(function ($filterQ) {
                // Kondisi 1: Punya rekomendasi tapi nilai kewajiban > setoran disetujui
                $filterQ->whereHas('recommendations', function ($q) {
                    $q->whereRaw('COALESCE(nilai_rekomendasi, 0) > (SELECT COALESCE(SUM(nominal_setoran), 0) FROM follow_up_evidences WHERE follow_up_evidences.recommendation_id = recommendations.id AND status_verifikasi = "approved")');
                })
                // Kondisi 2: Belum punya rekomendasi sama sekali (Otomatis belum selesai)
                ->orWhereDoesntHave('recommendations');
            });
        }

        $findings = $query->paginate(15)->withQueryString();

        return view('temuan-index', compact('findings'));
    }

    /**
     * Update existing Finding logic.
     *
     * @param UpdateFindingRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateFindingRequest $request, string $id): JsonResponse
    {
        try {
            $finding = Finding::findOrFail($id);
            $finding->update($request->validated());
            
            return response()->json([
                'message' => 'Detail temuan berhasil diperbarui.',
                'data' => $finding
            ]);
        } catch (\Throwable $e) {
            Log::error('Finding Update Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memperbarui data Temuan. Pastikan data valid.'
            ], 500);
        }
    }

    /**
     * Memperbarui Status TLHP dan Catatan pada Rekomendasi (Normal Form Submission).
     */
    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'ketua_tim'])) {
            abort(403, 'Akses Ditolak. Hanya Ketua Tim atau Administrator yang dapat memutakhirkan Status TLHP.');
        }

        $request->validate([
            'status_tlhp' => 'required|in:belum_selesai,dalam_proses,selesai,tidak_dapat_ditindaklanjuti',
            'catatan_tlhp' => 'nullable|string'
        ]);

        $recommendation = \App\Models\Recommendation::findOrFail($id);
        $recommendation->update([
            'status_tlhp' => $request->status_tlhp,
            'catatan_tlhp' => $request->catatan_tlhp,
        ]);

        return redirect()->back()->with('success', 'Status TLHP berhasil diperbarui!');
    }
}
