<?php

namespace App\Enums;

enum UserRole: string
{
    case SDM_KANWIL = 'sdm_kanwil';   // Verifikasi tahap 2 (DJKN Kanwil)
    case SDM_KANTOR = 'sdm_kantor';   // Checklist tahap 1 (KPKNL Pelayanan)
    case TIK = 'tik';          // Admin sistem
    case PENSIUNAN = 'pensiunan';    // Lihat status (read-only)

    public function label(): string
    {
        return match ($this) {
            self::SDM_KANWIL => 'Staff DJKN Kanwil',
            self::SDM_KANTOR => 'Staff KPKNL Pelayanan',
            self::TIK => 'Kepala TIK',
            self::PENSIUNAN => 'Calon Pensiunan',
        };
    }

    public function canVerify(): bool
    {
        // sdm_kanwil = verifikasi tahap 2
        return $this === self::SDM_KANWIL;
    }

    public function canKantorCheck(): bool
    {
        // sdm_kantor = checklist tahap 1
        return $this === self::SDM_KANTOR;
    }

    public function canUpload(): bool
    {
        return $this === self::SDM_KANTOR;
    }

    public function canManage(): bool
    {
        return $this === self::TIK;
    }
}
