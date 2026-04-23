<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model Opd
 * 
 * Merepresentasikan data Master Instansi (OPD).
 */
class Opd extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_opd',
        'nama_opd',
        'nama_kepala',
    ];

    /**
     * Relasi ke laporan Lhp.
     * 
     * @return HasMany
     */
    public function lhps(): HasMany
    {
        return $this->hasMany(Lhp::class, 'opd_id', 'id');
    }
}
