<?php

namespace App\Http\Controllers;

use App\Models\Lhp;
use App\Models\LhpReview;
use App\Models\User;
use App\Notifications\LhpWorkflowNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    /**
     * Menyimpan catatan reviu baru dan mengungkit status LHP.
     */
    public function store(Request $request, Lhp $lhp)
    {
        $currentUser = auth()->user();
        $role = $currentUser->role;
        $isIrban = Str::startsWith((string) $role, 'inspektur_pembantu');
        
        $allowedActions = [];
        if ($role === 'ketua_tim') {
            $allowedActions = ['draft', 'review_irban'];
        } elseif ($isIrban) {
            // Irban menjadi level final approval.
            $allowedActions = ['review_ketua', 'published'];
        }

        $request->validate([
            'action'  => ['required', \Illuminate\Validation\Rule::in($allowedActions)],
            'catatan' => 'nullable|string|max:5000',
        ]);

        $action = $request->action;
        
        $isReturning = false;
        $logAction = '';
        if ($role === 'ketua_tim' && $action === 'draft') {
            $isReturning = true;
            $logAction = 'Mengembalikan LHP ke Auditor dengan catatan revisi';
        } elseif ($role === 'ketua_tim' && $action === 'review_irban') {
            $logAction = 'Menyetujui dokumen dan meneruskannya ke Inspektur Pembantu I';
        } elseif ($isIrban && $action === 'review_ketua') {
            $isReturning = true;
            $logAction = 'Mengembalikan LHP ke tingkat Ketua Tim dengan catatan revisi';
        } elseif ($isIrban && $action === 'published') {
            $logAction = 'Mengesahkan dan menyelesaikan LHP secara resmi';
        }

        if ($isReturning && trim((string) $request->input('catatan', '')) === '') {
             return back()->withErrors(['catatan' => 'Catatan revisi wajib diisi jika mengembalikan LHP untuk perbaikan.'])->withInput();
        }

        DB::transaction(function () use ($request, $lhp, $action, $isReturning, $logAction, $currentUser) {
            // Auto-heal data legacy: jika tim LHP belum terisi, gunakan tim reviewer saat ini.
            if (empty($lhp->tim) && !empty($currentUser->tim)) {
                $lhp->update(['tim' => $currentUser->tim]);
            }

            // 1. Simpan catatan reviu (jika ada)
            if ($request->filled('catatan')) {
                LhpReview::create([
                    'lhp_id'          => $lhp->id,
                    'user_id'         => $currentUser->id,
                    'catatan'         => $request->catatan,
                    'status_perbaikan'=> $isReturning ? 'pending' : 'diperbaiki',
                ]);
            }

            // 2. Jika diteruskan, semua catatan sebelumnya dianggap diperbaiki
            if (!$isReturning) {
                $lhp->reviews()->where('status_perbaikan', 'pending')->update(['status_perbaikan' => 'diperbaiki']);
            }

            // 3. Update status LHP
            $lhp->update([
                'status' => $action
            ]);

            // Backfill auditor pembuat untuk data legacy agar target notifikasi selalu jelas.
            if (empty($lhp->created_by)) {
                $lhp->update(['created_by' => $this->resolveCreatorId($lhp)]);
            }

            // 4. Catat Jejak Aktivitas
            \App\Models\LhpLog::create([
                'lhp_id'  => $lhp->id,
                'user_id' => $currentUser->id,
                'action'  => $logAction
            ]);
        });

        $lhp->refresh();
        try {
            $this->dispatchWorkflowNotification($lhp, $role, $action, $isReturning, $currentUser->name);
        } catch (\Throwable $e) {
            // Notifikasi tidak boleh menggagalkan alur reviu utama.
            Log::warning('Gagal mengirim notifikasi workflow LHP', [
                'lhp_id' => $lhp->id,
                'role' => $role,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }

        $message = !$isReturning 
            ? 'LHP telah berhasil disetujui dan diteruskan/dipublikasikan.'
            : 'LHP telah dikembalikan dengan catatan revisi.';

        return redirect()->route('lhp.show', $lhp->id)->with('success', $message);
    }

    private function dispatchWorkflowNotification(Lhp $lhp, string $reviewerRole, string $action, bool $isReturning, string $reviewerName): void
    {
        $url = route('lhp.show', $lhp->id);

        if ($isReturning) {
            $recipient = User::find($lhp->created_by);
            if ($recipient) {
                $recipient->notify(new LhpWorkflowNotification(
                    $lhp,
                    'Revisi LHP: ' . $lhp->judul . ' dikembalikan oleh ' . $reviewerName . '. Cek catatannya.',
                    $url
                ));
            }
            return;
        }

        if ($reviewerRole === 'ketua_tim' && $action === 'review_irban') {
            $targets = User::query()
                ->where(function ($q) {
                    $q->where('role', 'inspektur_pembantu')
                      ->orWhere('role', 'inspektur_pembantu_1')
                      ->orWhere('role', 'inspektur_pembantu_2')
                      ->orWhere('role', 'inspektur_pembantu_3')
                      ->orWhere('role', 'inspektur_pembantu_4');
                })
                ->get();
            Notification::send($targets, new LhpWorkflowNotification(
                $lhp,
                'LHP Menunggu Reviu: ' . $lhp->judul . ' telah disetujui Ketua Tim.',
                $url
            ));
            return;
        }

        if (Str::startsWith((string) $reviewerRole, 'inspektur_pembantu') && $action === 'published') {
            $targets = User::query()
                ->where(function ($query) use ($lhp) {
                    $query->where(function ($q) {
                        $q->where('role', 'inspektur_daerah')
                          ->orWhere('role', 'admin');
                    })
                        ->orWhere(function ($q) use ($lhp) {
                            $q->whereIn('role', ['auditor', 'ketua_tim']);
                            if (!empty($lhp->tim)) {
                                $q->where('tim', $lhp->tim);
                            }
                        });
                })
                ->get();

            Notification::send($targets, new LhpWorkflowNotification(
                $lhp,
                'LHP Final! ' . $lhp->judul . ' telah disahkan di tingkat Irban.',
                $url
            ));
        }
    }

    private function resolveCreatorId(Lhp $lhp): ?string
    {
        if (!empty($lhp->created_by)) {
            return $lhp->created_by;
        }

        $logCreatorId = $lhp->logs()->oldest('id')->value('user_id');
        if (!empty($logCreatorId)) {
            return $logCreatorId;
        }

        return User::query()
            ->where('role', 'auditor')
            ->when($lhp->tim, fn ($q) => $q->where('tim', $lhp->tim))
            ->value('id');
    }

    /**
     * Batalkan Persetujuan (Unpublish) — Kembalikan status ke in_review.
     */
    public function unpublish(Request $request, Lhp $lhp)
    {
        if (!in_array(auth()->user()->role, ['admin', 'inspektur_daerah'])) {
            abort(403, 'Akses Ditolak. Hanya Inspektur Daerah atau Admin yang dapat membatalkan persetujuan LHP.');
        }

        DB::transaction(function () use ($lhp) {
            $lhp->update(['status' => 'review_inspektur']);

            // Catat aksi unpublish sebagai review entry
            LhpReview::create([
                'lhp_id'          => $lhp->id,
                'user_id'         => auth()->id(),
                'catatan'         => 'Persetujuan dibatalkan oleh ' . auth()->user()->name . '. LHP ditarik mundur ke tahapan Review Inspektur Daerah.',
                'status_perbaikan'=> 'pending',
            ]);

            // Catat jejak aktivitas
            \App\Models\LhpLog::create([
                'lhp_id'  => $lhp->id,
                'user_id' => auth()->id(),
                'action'  => 'Membatalkan publikasi dokumen dan menarik mundur status LHP (Unpublish)'
            ]);
        });

        return redirect()->route('lhp.show', $lhp->id)
            ->with('success', 'Persetujuan LHP berhasil dibatalkan. Status dikembalikan ke Review Inspektur Daerah.');
    }
}
