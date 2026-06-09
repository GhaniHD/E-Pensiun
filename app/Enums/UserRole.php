<?php

namespace App\Enums;

enum UserRole: string
{
    case SDM_KANWIL   = 'sdm_kanwil';   // Verifikasi berkas
    case SDM_KANTOR   = 'sdm_kantor';   // Upload berkas
    case TIK          = 'tik';          // Maintenance
    case PENSIUNAN    = 'pensiunan';    // Lihat status

    public function label(): string
    {
        return match($this) {
            self::SDM_KANWIL  => 'Staff SDM Kanwil',
            self::SDM_KANTOR  => 'Staff SDM Kantor Pelayanan',
            self::TIK         => 'Kepala TIK',
            self::PENSIUNAN   => 'Calon Pensiunan',
        };
    }

    public function canVerify(): bool
    {
        return $this === self::SDM_KANWIL;
    }

    public function canUpload(): bool
    {
        return in_array($this, [self::SDM_KANTOR, self::SDM_KANWIL]);
    }

    public function canManage(): bool
    {
        return $this === self::TIK;
    }
}
