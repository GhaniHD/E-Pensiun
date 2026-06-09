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
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'pension_date' => 'date',
            'sk_issued_at' => 'datetime',
            'from_status' => ApplicationStatus::class,
            'to_status' => ApplicationStatus::class,
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

        // Record history
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

    public function getProgressPercentageAttribute(): int
    {
        $total = count(ApplicationStatus::allOrdered());
        $current = $this->status->order();
        return (int) round(($current / $total) * 100);
    }
}
