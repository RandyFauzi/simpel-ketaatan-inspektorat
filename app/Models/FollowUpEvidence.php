<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model FollowUpEvidence
 * 
 * Merepresentasikan Bukti dan Progres Tindak Lanjut dari SKPD.
 */
class FollowUpEvidence extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Nama tabel yang tidak dikenali fitur Pluralizer denga benar.
     * @var string
     */
    protected $table = 'follow_up_evidences';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'recommendation_id',
        'user_id',
        'file_path',
        'nominal_setoran',
        'status_verifikasi',
        'catatan_verifikator',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'nominal_setoran' => 'decimal:2',
    ];

    /**
     * Relasi ke Recommendation.
     * 
     * @return BelongsTo
     */
    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(Recommendation::class, 'recommendation_id', 'id');
    }

    /**
     * Relasi ke User pengunggah.
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
