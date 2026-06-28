<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pension_type_id',
        'status',
        'verified_by',
        'rejection_note',
        'pension_date',
        'sk_number',
        'sk_issued_at',
        'notes',

        // ── Data Calon Pensiunan ───────────────────────────────────
        'nama_calon_pensiunan',
        'unit_kerja',
        'nip_calon_pensiunan',
        'tanggal_lahir',
        'jenis_pensiun_bkn',
        'kenaikan_pangkat',

        // ── Input Kalkulator Masa Kerja ────────────────────────────
        'usia_pensiun',
        'tmt_cpns',
        'tmt_pns',
        'tmt_pangkat_terakhir',
        'mk_kp_terakhir_tahun',
        'mk_kp_terakhir_bulan',

        // ── Hasil Kalkulator (disimpan) ────────────────────────────
        'mk_pensiun_tahun',
        'mk_pensiun_bulan',
        'mk_pns_tahun',
        'mk_pns_bulan',
        'mk_golongan_tahun',
        'mk_golongan_bulan',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'pension_date' => 'date',
            'sk_issued_at' => 'datetime',
            'tanggal_lahir' => 'date',
            'tmt_cpns' => 'date',
            'tmt_pns' => 'date',
            'tmt_pangkat_terakhir' => 'date',
            'kenaikan_pangkat' => 'boolean',
        ];
    }

    // ── Relations ──────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pensionType(): BelongsTo
    {
        return $this->belongsTo(PensionType::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeByOffice($query, string $office)
    {
        return $query->whereHas('user', fn($q) => $q->where('office', $office));
    }

    public function scopeByStatus($query, ApplicationStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeByMonth($query, int $year, int $month)
    {
        return $query->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);
    }

    // ── Helpers ────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === ApplicationStatus::SK_TERBIT;
    }

    public function canAdvance(): bool
    {
        return $this->status->next() !== null;
    }

    public function advanceStatus(User $actor, ?string $note = null): bool
    {
        $nextStatus = $this->status->next();

        if ($nextStatus === null)
            return false;

        $oldStatus = $this->status;
        $this->status = $nextStatus;

        if ($nextStatus === ApplicationStatus::ACC || $nextStatus === ApplicationStatus::SK_TERBIT) {
            $this->verified_by = $actor->id;
        }

        $this->save();

        $this->statusHistories()->create([
            'from_status' => $oldStatus->value,
            'to_status' => $nextStatus->value,
            'changed_by' => $actor->id,
            'note' => $note,
        ]);

        return true;
    }

    public function rejectApplication(User $actor, string $note): bool
    {
        $oldStatus = $this->status;
        $this->status = ApplicationStatus::PENGISIAN_FORM;
        $this->rejection_note = $note;
        $this->save();

        $this->statusHistories()->create([
            'from_status' => $oldStatus->value,
            'to_status' => ApplicationStatus::PENGISIAN_FORM->value,
            'changed_by' => $actor->id,
            'note' => 'DITOLAK: ' . $note,
        ]);

        return true;
    }

    public function returnToUpload(User $actor, string $note): bool
    {
        $oldStatus = $this->status;
        $this->status = ApplicationStatus::UPLOAD;
        $this->rejection_note = $note;
        $this->save();

        $this->statusHistories()->create([
            'from_status' => $oldStatus->value,
            'to_status' => ApplicationStatus::UPLOAD->value,
            'changed_by' => $actor->id,
            'note' => 'DIKEMBALIKAN KE UPLOAD: ' . $note,
        ]);

        return true;
    }

    public function getProgressPercentageAttribute(): int
    {
        $total = count(ApplicationStatus::allOrdered());
        $current = $this->status->order();
        return (int) round(($current / $total) * 100);
    }

    public function isCancelled(): bool
    {
        return $this->status === ApplicationStatus::DIBATALKAN;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            ApplicationStatus::PENGISIAN_FORM,
            ApplicationStatus::PEMBERKASAN,
            ApplicationStatus::UPLOAD,
        ]);
    }

    public function cancelApplication(User $actor, string $note): bool
    {
        $oldStatus = $this->status;
        $this->status = ApplicationStatus::DIBATALKAN;
        $this->rejection_note = $note;
        $this->save();

        $this->statusHistories()->create([
            'from_status' => $oldStatus->value,
            'to_status' => ApplicationStatus::DIBATALKAN->value,
            'changed_by' => $actor->id,
            'note' => 'DIBATALKAN: ' . $note,
        ]);

        return true;
    }

    // ── Checklist helpers ──────────────────────────────────

    public function allDocumentsCheckedByKantor(): bool
    {
        if ($this->documents->isEmpty())
            return false;
        return $this->documents->every(fn($d) => !is_null($d->kantor_check_status));
    }

    public function problematicDocumentsCount(): int
    {
        return $this->documents->filter(
            fn($d) => in_array($d->kantor_check_status, ['lengkap_tidak_sesuai', 'tidak_lengkap'])
        )->count();
    }

    public function allDocumentsApprovedByKanwil(): bool
    {
        if ($this->documents->isEmpty())
            return false;
        return $this->documents->every(fn($d) => $d->kanwil_status === 'sesuai');
    }

    // ── Masa Kerja Helpers ─────────────────────────────────

    /** Label masa kerja golongan untuk tampilan (misal "32 th 2 bln") */
    public function getMkGolonganLabelAttribute(): ?string
    {
        if (is_null($this->mk_golongan_tahun))
            return null;
        return $this->mk_golongan_tahun . ' th ' . $this->mk_golongan_bulan . ' bln';
    }

    public function getMkPensiunLabelAttribute(): ?string
    {
        if (is_null($this->mk_pensiun_tahun))
            return null;
        return $this->mk_pensiun_tahun . ' th ' . $this->mk_pensiun_bulan . ' bln';
    }

    public function getMkPnsLabelAttribute(): ?string
    {
        if (is_null($this->mk_pns_tahun))
            return null;
        return $this->mk_pns_tahun . ' th ' . $this->mk_pns_bulan . ' bln';
    }
}
