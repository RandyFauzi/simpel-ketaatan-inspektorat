<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lhp;
use App\Models\Finding;
use App\Models\Recommendation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Terapkan scope visibilitas LHP berdasarkan role pengguna.
     */
    private function applyLhpRoleScope(Builder $query, $user): Builder
    {
        if (in_array($user->role, ['auditor', 'ketua_tim'], true)) {
            $query->where(function ($q) use ($user) {
                // Fallback legacy agar metrik dashboard konsisten dengan daftar LHP.
                $q->where('tim', $user->tim)
                  ->orWhereNull('tim');
            });
        }

        return $query;
    }

    /**
     * Menampilkan Dashboard Utama berdasarkan Role (SKPD vs Auditor/Admin)
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Data Spesifik untuk SKPD
        if ($user && $user->role === 'skpd' && $user->opd_id) {
            // Mengambil 3 Rekomendasi yang belum selesai (Pending Tasks)
            $pendingRecs = Recommendation::with(['finding.lhp'])
                ->whereHas('finding.lhp', function ($q) use ($user) {
                    $q->where('opd_id', $user->opd_id)->where('status', 'published');
                })
                ->whereIn('status', ['belum_sesuai', 'proses'])
                ->latest()
                ->take(3)
                ->get();
            
            // Menghitung Sisa Kewajiban dari seluruh Rekomendasi di OPD tersebut secara agregasi (One-Hit Query)
            $sisaKewajiban = (float) Recommendation::whereHas('finding.lhp', function ($q) use ($user) {
                    $q->where('opd_id', $user->opd_id)->where('status', 'published');
                })
                ->selectRaw("SUM(recommendations.nilai_rekomendasi - COALESCE(
                    (SELECT SUM(nominal_setoran) FROM follow_up_evidences 
                     WHERE follow_up_evidences.recommendation_id = recommendations.id 
                     AND follow_up_evidences.status_verifikasi = 'approved'), 0
                )) as total_sisa")
                ->value('total_sisa') ?? 0;

            return view('lhp-dashboard', compact('pendingRecs', 'sisaKewajiban'));
        }

        // 2. Data Dashboard untuk role non-SKPD (dengan scope yang konsisten)
        $scopedLhpQuery = fn (Builder $query) => $this->applyLhpRoleScope($query, $user);

        // Menghitung agregat utama LHP dalam satu query untuk menekan query count.
        $lhpAggregate = $scopedLhpQuery(Lhp::query())
            ->selectRaw("
                COUNT(*) as total_lhp,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as lhp_selesai,
                COUNT(DISTINCT CASE WHEN status = 'published' AND created_by IS NOT NULL THEN created_by END) as auditor_selesai
            ")
            ->first();

        $totalLhp = (int) ($lhpAggregate->total_lhp ?? 0);
        $lhpSelesai = (int) ($lhpAggregate->lhp_selesai ?? 0);
        $auditorSelesai = (int) ($lhpAggregate->auditor_selesai ?? 0);

        $totalTemuan = Finding::query()
            ->whereHas('lhp', fn (Builder $query) => $scopedLhpQuery($query))
            ->count();

        $kerugianAggregate = Finding::query()
            ->whereHas('lhp', fn (Builder $query) => $scopedLhpQuery($query))
            ->selectRaw('COALESCE(SUM(kerugian_negara), 0) as kerugian_negara, COALESCE(SUM(kerugian_daerah), 0) as kerugian_daerah')
            ->first();

        $kerugianNegara = (float) ($kerugianAggregate->kerugian_negara ?? 0);
        $kerugianDaerah = (float) ($kerugianAggregate->kerugian_daerah ?? 0);
        $totalKerugian = (float) $kerugianNegara + (float) $kerugianDaerah;

        // Mengambil 5 LHP terbaru dengan scope role yang konsisten
        $latestLhps = $this->applyLhpRoleScope(Lhp::with('opd'), $user)
            ->latest('tgl_lhp')
            ->take(5)
            ->get();

        return view('lhp-dashboard', compact(
            'totalLhp', 'totalTemuan', 'totalKerugian', 
            'lhpSelesai', 'auditorSelesai', 'latestLhps'
        ));
    }
}
