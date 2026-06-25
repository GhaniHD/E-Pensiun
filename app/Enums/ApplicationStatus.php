<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case PENGISIAN_FORM = 'pengisian_form';
    case PEMBERKASAN = 'pemberkasan';
    case UPLOAD = 'upload';
    case VERIFIKASI_KPKNL = 'verifikasi_kpknl';   // BARU — cek tahap 1 oleh sdm_kantor
    case VERIFIKASI_KANWIL = 'verifikasi_kanwil';  // BARU — double-check tahap 2 oleh sdm_kanwil
    case ACC = 'acc';
    case SK_TERBIT = 'sk_terbit';
    case DIBATALKAN = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::PENGISIAN_FORM => 'Pengisian Form',
            self::PEMBERKASAN => 'Pemberkasan',
            self::UPLOAD => 'Upload Persyaratan',
            self::VERIFIKASI_KPKNL => 'Verifikasi KPKNL Pelayanan',
            self::VERIFIKASI_KANWIL => 'Verifikasi DJKN Kanwil',
            self::ACC => 'ACC / Persetujuan',
            self::SK_TERBIT => 'SK Pensiun Terbit',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::PENGISIAN_FORM => 1,
            self::PEMBERKASAN => 2,
            self::UPLOAD => 3,
            self::VERIFIKASI_KPKNL => 4,
            self::VERIFIKASI_KANWIL => 5,
            self::ACC => 6,
            self::SK_TERBIT => 7,
            self::DIBATALKAN => 0,
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENGISIAN_FORM => 'secondary',
            self::PEMBERKASAN => 'info',
            self::UPLOAD => 'primary',
            self::VERIFIKASI_KPKNL => 'warning',
            self::VERIFIKASI_KANWIL => 'warning',
            self::ACC => 'success',
            self::SK_TERBIT => 'dark',
            self::DIBATALKAN => 'danger',
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::PENGISIAN_FORM => self::PEMBERKASAN,
            self::PEMBERKASAN => self::UPLOAD,
            self::UPLOAD => self::VERIFIKASI_KPKNL,
            self::VERIFIKASI_KPKNL => self::VERIFIKASI_KANWIL,
            self::VERIFIKASI_KANWIL => self::ACC,
            self::ACC => self::SK_TERBIT,
            self::SK_TERBIT => null,
            self::DIBATALKAN => null,
        };
    }

    public static function allOrdered(): array
    {
        return [
            self::PENGISIAN_FORM,
            self::PEMBERKASAN,
            self::UPLOAD,
            self::VERIFIKASI_KPKNL,
            self::VERIFIKASI_KANWIL,
            self::ACC,
            self::SK_TERBIT,
        ];
    }
}
