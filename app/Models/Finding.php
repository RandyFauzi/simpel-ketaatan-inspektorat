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
        'parent_id',
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
     * Parent temuan (untuk temuan beranak).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    /**
     * Sub-temuan dari temuan ini.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->orderBy('kode_temuan')
            ->orderBy('id');
    }

    /**
     * Generate kode sub-temuan berikutnya dari parent saat ini.
     * Contoh: parent "T-01" => child pertama "T-01.1".
     */
    public function nextSubCode(): string
    {
        $baseCode = trim((string) $this->kode_temuan);
        $maxSuffix = 0;

        foreach ($this->children()->pluck('kode_temuan') as $childCode) {
            $childCode = trim((string) $childCode);
            if (preg_match('/^' . preg_quote($baseCode, '/') . '\.(\d+)$/', $childCode, $matches) === 1) {
                $maxSuffix = max($maxSuffix, (int) $matches[1]);
            }
        }

        return $baseCode . '.' . ($maxSuffix + 1);
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
