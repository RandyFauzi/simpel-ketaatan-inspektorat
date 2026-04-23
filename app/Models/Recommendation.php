<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model Recommendation
 * 
 * Merepresentasikan Rekomendasi berdasarkan suatu Temuan.
 */
class Recommendation extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsAuditActivity;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'finding_id',
        'kode_rekomendasi',
        'uraian_rekomendasi',
        'nilai_rekomendasi',
        'status',
        'status_tlhp',
        'catatan_tlhp',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'nilai_rekomendasi' => 'decimal:2',
    ];

    /**
     * Relasi ke Finding (Temuan).
     * 
     * @return BelongsTo
     */
    public function finding(): BelongsTo
    {
        return $this->belongsTo(Finding::class, 'finding_id', 'id');
    }

    /**
     * Relasi ke FollowUpEvidence (Bukti Tindak Lanjut).
     * 
     * @return HasMany
     */
    public function followUpEvidences(): HasMany
    {
        return $this->hasMany(FollowUpEvidence::class, 'recommendation_id', 'id');
    }

    /**
     * Accessor untuk total yang telah dibayar (approved).
     * 
     * @return float
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->followUpEvidences()
            ->where('status_verifikasi', 'approved')
            ->sum('nominal_setoran');
    }

    /**
     * Accessor untuk sisa saldo kerugian yang belum dibayar.
     * Menggunakan limit nilai_rekomendasi.
     * 
     * @return float
     */
    public function getRemainingBalanceAttribute(): float
    {
        return (float) $this->nilai_rekomendasi - (float) $this->total_paid;
    }
}
