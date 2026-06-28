<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar KPKNL Pelayanan sesuai wilayah kerja DJKN Kanwil Jawa Barat.
        // URL folder Teams diisi setelah klien menyediakan link-nya.
        $offices = [
            // KPKNL Pelayanan (role sdm_kantor)
            ['name' => 'KPKNL Bandung',    'type' => 'kpknl', 'teams_folder_url' => null],
            ['name' => 'KPKNL Bekasi',     'type' => 'kpknl', 'teams_folder_url' => null],
            ['name' => 'KPKNL Bogor',      'type' => 'kpknl', 'teams_folder_url' => null],
            ['name' => 'KPKNL Cirebon',    'type' => 'kpknl', 'teams_folder_url' => null],
            ['name' => 'KPKNL Purwakarta', 'type' => 'kpknl', 'teams_folder_url' => null],
            ['name' => 'KPKNL Sukabumi',   'type' => 'kpknl', 'teams_folder_url' => null],

            // DJKN Kanwil (role sdm_kanwil)
            ['name' => 'DJKN Kanwil Jawa Barat', 'type' => 'kanwil', 'teams_folder_url' => null],
        ];

        foreach ($offices as $office) {
            Office::firstOrCreate(['name' => $office['name']], $office);
        }
    }
}
