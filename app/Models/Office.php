<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'name', 'type', 'teams_folder_url', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'kpknl'  => 'KPKNL Pelayanan',
            'kanwil' => 'DJKN Kanwil',
            default  => $this->type,
        };
    }
}
