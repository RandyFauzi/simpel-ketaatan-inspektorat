<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LhpLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'lhp_id',
        'user_id',
        'action',
    ];

    /**
     * LHP yang direkam aktivitasnya
     */
    public function lhp(): BelongsTo
    {
        return $this->belongsTo(Lhp::class);
    }

    /**
     * User yang melakukan aksi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
