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
 * Model Finding
 * 
 * Merepresentasikan Temuan dari suatu LHP.
 */
class Finding extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsAuditActivity;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'lhp_id',
        'kode_temuan',
        'uraian_temuan',
        'kondisi',
        'kriteria',
        'sebab',
        'akibat',
        'rekomendasi_teks',
        'kerugian_negara',
        'kerugian_daerah',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'kerugian_negara' => 'decimal:2',
        'kerugian_daerah' => 'decimal:2',
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

    /**
     * Relasi ke Rekomendasi.
     * 
     * @return HasMany
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'finding_id', 'id');
    }

    /**
     * Accessor untuk total kerugian (negara + daerah).
     * 
     * @return float
     */
    public function getTotalKerugianAttribute(): float
    {
        return (float) $this->kerugian_negara + (float) $this->kerugian_daerah;
    }
}
