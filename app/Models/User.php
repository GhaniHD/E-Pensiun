<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'office',
        'nip',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // ── Relations ──────────────────────────────────────────

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'user_id');
    }

    public function verifiedApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'verified_by');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    // ── Role helpers ───────────────────────────────────────

    public function isSdmKanwil(): bool
    {
        return $this->role === UserRole::SDM_KANWIL;
    }

    public function isSdmKantor(): bool
    {
        return $this->role === UserRole::SDM_KANTOR;
    }

    public function isTik(): bool
    {
        return $this->role === UserRole::TIK;
    }

    public function isPensiunan(): bool
    {
        return $this->role === UserRole::PENSIUNAN;
    }

    public function canVerify(): bool
    {
        return $this->role->canVerify();
    }

    public function canUpload(): bool
    {
        return $this->role->canUpload();
    }

    public function canManage(): bool
    {
        return $this->role->canManage();
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->role->label();
    }
}
