<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model Lhp
 * 
 * Merepresentasikan Header Laporan Hasil Pemeriksaan (LHP).
 */
class Lhp extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsAuditActivity;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'nomor_lhp',
        'tgl_lhp',
        'judul',
        'tahun_anggaran',
        'opd_id',
        'simpulan_manual',
        'rekomendasi_manual',
        'penutup_manual',
        'penilaian_ketaatan',
        'kesesuaian_output',
        'hal_penting',
        'tindak_lanjut',
        'created_by',
        'status',
        'tim',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'tgl_lhp' => 'date',
    ];

    /**
     * Relasi ke OPD.
     * 
     * @return BelongsTo
     */
    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'opd_id', 'id');
    }

    /**
     * User pembuat awal dokumen LHP.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Relasi ke LhpContent (Narasi Laporan).
     * 
     * @return HasOne
     */
    public function content(): HasOne
    {
        return $this->hasOne(LhpContent::class, 'lhp_id', 'id');
    }



    /**
     * Relasi ke Findings (Temuan).
     * 
     * @return HasMany
     */
    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class, 'lhp_id', 'id')->orderBy('id', 'asc');
    }

    /**
     * Relasi ke Riwayat Reviu Kolaborasi (LhpReview).
     * 
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(LhpReview::class, 'lhp_id', 'id')->latest();
    }

    /**
     * Relasi ke Jejak Aktivitas Audit (LhpLog).
     * 
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(LhpLog::class, 'lhp_id', 'id')->latest('created_at')->latest('id');
    }
}
