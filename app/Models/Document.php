<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'uploaded_by',
        'document_name',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'is_verified',
        'verified_by',
        'verified_at',
        'rejection_note',
        // Kolom verifikasi tahap 1 (KPKNL)
        'kantor_check_status',
        'kantor_check_note',
        'kantor_checked_by',
        'kantor_checked_at',
        // Kolom verifikasi tahap 2 (Kanwil)
        'kanwil_status',
    ];

    protected function casts(): array
    {
        return [
            'is_verified'       => 'boolean',
            'verified_at'       => 'datetime',
            'kantor_checked_at' => 'datetime',
            'file_size'         => 'integer',
        ];
    }

    // ── Relations ──────────────────────────────────────────

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function kantorChecker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kantor_checked_by');
    }

    // ── Helpers ────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return route('documents.download', $this->id);
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576)
            return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)
            return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    // ── Verifikasi lama (dipertahankan untuk kompatibilitas) ──

    public function verify(User $verifier): void
    {
        $this->update([
            'is_verified'    => true,
            'verified_by'    => $verifier->id,
            'verified_at'    => now(),
            'rejection_note' => null,
        ]);
    }

    public function reject(User $verifier, string $note): void
    {
        $this->update([
            'is_verified'    => false,
            'verified_by'    => $verifier->id,
            'verified_at'    => now(),
            'rejection_note' => $note,
        ]);
    }

    // ── Helpers status checklist ───────────────────────────

    /** Label tampilan untuk kantor_check_status */
    public function kantorCheckLabel(): string
    {
        return match($this->kantor_check_status) {
            'lengkap_sesuai'       => 'Lengkap & Sesuai',
            'lengkap_tidak_sesuai' => 'Lengkap tapi Tidak Sesuai',
            'tidak_lengkap'        => 'Tidak Lengkap',
            default                => '—',
        };
    }

    /** Badge color untuk kantor_check_status */
    public function kantorCheckBadge(): string
    {
        return match($this->kantor_check_status) {
            'lengkap_sesuai'       => 'success',
            'lengkap_tidak_sesuai' => 'warning',
            'tidak_lengkap'        => 'danger',
            default                => 'secondary',
        };
    }

    /** Label tampilan untuk kanwil_status */
    public function kanwilStatusLabel(): string
    {
        return match($this->kanwil_status) {
            'sesuai'      => 'Sesuai',
            'tidak_sesuai'=> 'Tidak Sesuai',
            'pending'     => 'Belum Dicek',
            default       => '—',
        };
    }
}
