<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model AuditTeam
 * 
 * Merepresentasikan pivot tim audit untuk suatu LHP.
 */
class AuditTeam extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'lhp_id',
        'user_id',
        'role',
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
     * Relasi ke User.
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
