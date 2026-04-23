<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model LhpReview
 * 
 * Merepresentasikan catatan kolaborasi/reviu dari atasan pada suatu LHP.
 */
class LhpReview extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'lhp_id',
        'user_id',
        'catatan',
        'status_perbaikan',
    ];

    /**
     * Relasi ke LHP.
     */
    public function lhp(): BelongsTo
    {
        return $this->belongsTo(Lhp::class, 'lhp_id', 'id');
    }

    /**
     * Relasi ke Pewenang/Pembuat Catatan (Inspektur Pembantu).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
