<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar kantor ini diisi oleh klien sesuai data nyata.
        // URL folder Teams diisi setelah klien menyediakan link-nya.
        $offices = [
            // KPKNL Pelayanan (role sdm_kantor)
            ['name' => 'KPKNL Jakarta I',   'type' => 'kpknl', 'teams_folder_url' => null],
            ['name' => 'KPKNL Jakarta II',  'type' => 'kpknl', 'teams_folder_url' => null],
            ['name' => 'KPKNL Bandung',     'type' => 'kpknl', 'teams_folder_url' => null],
            ['name' => 'KPKNL Surabaya',    'type' => 'kpknl', 'teams_folder_url' => null],

            // DJKN Kanwil (role sdm_kanwil)
            ['name' => 'DJKN Kanwil Jakarta',  'type' => 'kanwil', 'teams_folder_url' => null],
            ['name' => 'DJKN Kanwil Bandung',  'type' => 'kanwil', 'teams_folder_url' => null],
            ['name' => 'DJKN Kanwil Surabaya', 'type' => 'kanwil', 'teams_folder_url' => null],
        ];

        foreach ($offices as $office) {
            Office::firstOrCreate(['name' => $office['name']], $office);
        }
    }
}
