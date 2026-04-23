<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FollowUpEvidence;
use App\Models\Recommendation;
use Illuminate\Support\Facades\DB;

class FollowUpService
{
    public function submitEvidence(array $data): FollowUpEvidence
    {
        return DB::transaction(function () use ($data) {
            $evidence = FollowUpEvidence::create([
                'recommendation_id' => $data['recommendation_id'],
                'user_id' => $data['user_id'],
                'nominal_setoran' => $data['nominal_setoran'],
                'file_path' => $data['file_path'] ?? null,
                'status_verifikasi' => 'pending',
                'catatan_verifikator' => null,
            ]);

            $rec = Recommendation::find($data['recommendation_id']);
            if ($rec && in_array($rec->status, ['belum_ditindaklanjuti', 'belum_sesuai'])) {
                $rec->update(['status' => 'proses']);
            }

            return $evidence;
        });
    }

    public function verifyEvidence(string $evidenceId, array $data): FollowUpEvidence
    {
        return DB::transaction(function () use ($evidenceId, $data) {
            $evidence = FollowUpEvidence::findOrFail($evidenceId);
            $evidence->update([
                'status_verifikasi' => $data['status_verifikasi'],
                'catatan_verifikator' => $data['catatan_verifikator'] ?? null,
            ]);

            $rec = $evidence->recommendation;
            
            // Kalkulasi ulang total tersertifikasi
            // get total_paid (yang otomatis mengecek sum bukti approved berkat Accessor)
            // Tapi untuk akurasi DB transaction, kita query langsung juga boleh:
            $totalSesuai = $rec->followUpEvidences()->where('status_verifikasi', 'approved')->sum('nominal_setoran');
            $kerugian = $rec->finding->kerugian_negara + $rec->finding->kerugian_daerah;
            
            if ($totalSesuai >= $kerugian) {
                $rec->update(['status' => 'sesuai']);
            } else {
                // Periksa apakah masih ada queue pending (kompatibel data lama "submitted")
                $hasPending = $rec->followUpEvidences()
                    ->whereIn('status_verifikasi', ['pending', 'submitted'])
                    ->exists();
                if ($hasPending) {
                    $rec->update(['status' => 'proses']);
                } else {
                    $rec->update(['status' => 'belum_sesuai']);
                }
            }

            return $evidence;
        });
    }
}
