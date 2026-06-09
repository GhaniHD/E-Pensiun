<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case PENGISIAN_FORM  = 'pengisian_form';
    case PEMBERKASAN     = 'pemberkasan';
    case UPLOAD          = 'upload';
    case VERIFIKASI      = 'verifikasi';
    case ACC             = 'acc';
    case SK_TERBIT       = 'sk_terbit';

    public function label(): string
    {
        return match($this) {
            self::PENGISIAN_FORM => 'Pengisian Form',
            self::PEMBERKASAN    => 'Pemberkasan',
            self::UPLOAD         => 'Upload Persyaratan',
            self::VERIFIKASI     => 'Verifikasi',
            self::ACC            => 'ACC / Persetujuan',
            self::SK_TERBIT      => 'SK Pensiun Terbit',
        };
    }

    public function order(): int
    {
        return match($this) {
            self::PENGISIAN_FORM => 1,
            self::PEMBERKASAN    => 2,
            self::UPLOAD         => 3,
            self::VERIFIKASI     => 4,
            self::ACC            => 5,
            self::SK_TERBIT      => 6,
        };
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::PENGISIAN_FORM => 'secondary',
            self::PEMBERKASAN    => 'info',
            self::UPLOAD         => 'primary',
            self::VERIFIKASI     => 'warning',
            self::ACC            => 'success',
            self::SK_TERBIT      => 'dark',
        };
    }

    public function next(): ?self
    {
        return match($this) {
            self::PENGISIAN_FORM => self::PEMBERKASAN,
            self::PEMBERKASAN    => self::UPLOAD,
            self::UPLOAD         => self::VERIFIKASI,
            self::VERIFIKASI     => self::ACC,
            self::ACC            => self::SK_TERBIT,
            self::SK_TERBIT      => null,
        };
    }

    public static function allOrdered(): array
    {
        return [
            self::PENGISIAN_FORM,
            self::PEMBERKASAN,
            self::UPLOAD,
            self::VERIFIKASI,
            self::ACC,
            self::SK_TERBIT,
        ];
    }
}
