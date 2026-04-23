<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model LhpContent
 * 
 * Merepresentasikan Narasi Laporan dari suatu LHP.
 */
class LhpContent extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsAuditActivity;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'lhp_id',
        'bab_1_info_umum',
        'bab_2_hasil_audit',
        'bab_3_penutup',
        'metadata_tambahan',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'metadata_tambahan' => 'array',
    ];

    /**
     * Relasi ke Lhp.
     * 
     * @return BelongsTo
     */
    public function lhp(): BelongsTo
    {
        return $this->belongsTo(Lhp::class, 'lhp_id', 'id');
    }
}
