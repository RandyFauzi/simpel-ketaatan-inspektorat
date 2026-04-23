<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes, LogsAuditActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'unit_kerja',
        'opd_id',
        'tim',
        'nip',
        'jabatan',
    ];

 

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relasi ke OPD (khusus role SKPD).
     */
    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'opd_id', 'id');
    }

    /**
     * Batasi atribut log untuk mencegah ekspos data sensitif.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('audit_trail')
            ->logOnly(['name', 'email', 'role', 'unit_kerja', 'opd_id', 'tim', 'nip', 'jabatan'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
