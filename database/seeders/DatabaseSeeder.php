<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\DocumentTemplate;
use App\Models\PensionType;
use App\Models\Regulation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedPensionTypes();
        $this->seedRegulations();
        $this->seedArticles();
    }

    private function seedUsers(): void
    {
        // TIK (Kepala)
        User::create([
            'name'      => 'Kepala TIK',
            'nip'       => '198001012005011001',
            'email'     => 'tik@pensiun.go.id',
            'password'  => Hash::make('password'),
            'role'      => UserRole::TIK,
            'office'    => 'Kanwil',
            'is_active' => true,
        ]);

        // SDM Kanwil
        User::create([
            'name'      => 'Staff SDM Kanwil',
            'nip'       => '198501012010011001',
            'email'     => 'sdm.kanwil@pensiun.go.id',
            'password'  => Hash::make('password'),
            'role'      => UserRole::SDM_KANWIL,
            'office'    => 'Kanwil',
            'is_active' => true,
        ]);

        // SDM 6 KPKNL
        $offices = [
            'KPKNL Bandung',
            'KPKNL Bekasi',
            'KPKNL Bogor',
            'KPKNL Cirebon',
            'KPKNL Purwakarta',
            'KPKNL Sukabumi',
        ];

        foreach ($offices as $i => $office) {
            User::create([
                'name'      => "Staff SDM {$office}",
                'nip'       => '19900101' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'email'     => 'sdm.kantor' . ($i + 1) . '@pensiun.go.id',
                'password'  => Hash::make('password'),
                'role'      => UserRole::SDM_KANTOR,
                'office'    => $office,
                'is_active' => true,
            ]);
        }

        // Sample calon pensiunan
        User::create([
            'name'      => 'Budi Santoso',
            'nip'       => '196501012000011001',
            'email'     => 'budi@pensiun.go.id',
            'password'  => Hash::make('password'),
            'role'      => UserRole::PENSIUNAN,
            'office'    => 'KPKNL Bandung',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Siti Rahayu',
            'nip'       => '196601022000012001',
            'email'     => 'siti@pensiun.go.id',
            'password'  => Hash::make('password'),
            'role'      => UserRole::PENSIUNAN,
            'office'    => 'KPKNL Bekasi',
            'is_active' => true,
        ]);
    }

    private function seedPensionTypes(): void
    {
        $types = [
            [
                'name'        => 'Pensiun Batas Usia',
                'slug'        => 'pensiun-batas-usia',
                'description' => 'Pensiun yang diberikan kepada pegawai yang telah mencapai batas usia pensiun sesuai ketentuan yang berlaku.',
                'icon'        => 'bi-person-badge',
                'requirements' => [
                    'Fotokopi SK CPNS',
                    'Fotokopi SK PNS',
                    'Fotokopi SK Pangkat Terakhir',
                    'Fotokopi SK Jabatan Terakhir',
                    'Fotokopi KTP',
                    'Fotokopi Kartu Keluarga',
                    'Fotokopi Akta Nikah',
                    'Fotokopi Akta Kelahiran Anak',
                    'Pas Foto 3x4 (6 lembar)',
                    'Daftar Riwayat Hidup',
                    'Surat Pernyataan Tidak Sedang Menjalani Hukuman',
                    'Fotokopi NPWP',
                    'Fotokopi Buku Rekening',
                    'DP3 / SKP 2 Tahun Terakhir',
                    'Surat Keterangan Bebas Temuan',
                    'Fotokopi Karpeg',
                    'Fotokopi Taspen',
                    'Surat Pengantar dari Atasan',
                    'Fotokopi Ijazah Terakhir',
                    'Surat Keterangan Sehat dari Dokter',
                ],
            ],
            [
                'name'        => 'Pensiun Janda/Duda',
                'slug'        => 'pensiun-janda-duda',
                'description' => 'Pensiun yang diberikan kepada janda atau duda dari pegawai yang meninggal dunia saat masih aktif bertugas.',
                'icon'        => 'bi-heart',
                'requirements' => [
                    'Surat Kematian dari Rumah Sakit/Kelurahan',
                    'Fotokopi KTP Janda/Duda',
                    'Fotokopi Akta Nikah',
                    'Fotokopi SK CPNS Alm.',
                    'Fotokopi SK PNS Alm.',
                    'Fotokopi SK Pangkat Terakhir Alm.',
                    'Fotokopi Kartu Keluarga',
                    'Fotokopi Akta Kelahiran Anak',
                    'Surat Keterangan Ahli Waris',
                    'Pas Foto Janda/Duda 3x4 (6 lembar)',
                    'Fotokopi Karpeg Alm.',
                    'Fotokopi Taspen Alm.',
                    'Fotokopi NPWP',
                    'Fotokopi Buku Rekening',
                    'Daftar Riwayat Hidup Alm.',
                    'Surat Pengantar dari Instansi',
                    'Fotokopi Ijazah Terakhir Alm.',
                    'Fotokopi KTP Anak (jika ada)',
                    'Surat Pernyataan Janda/Duda Belum Menikah Lagi',
                    'Fotokopi Akte Cerai (jika cerai hidup)',
                ],
            ],
            [
                'name'        => 'Pensiun Wafat',
                'slug'        => 'pensiun-wafat',
                'description' => 'Pensiun yang diberikan kepada ahli waris pegawai yang meninggal dunia saat masih aktif dalam masa persiapan pensiun.',
                'icon'        => 'bi-file-earmark-medical',
                'requirements' => [
                    'Surat Kematian Resmi',
                    'Visum et Repertum (jika kecelakaan)',
                    'Fotokopi KTP Ahli Waris',
                    'Fotokopi Akta Nikah',
                    'Surat Keterangan Ahli Waris (dari Kelurahan)',
                    'Fotokopi SK CPNS',
                    'Fotokopi SK PNS',
                    'Fotokopi SK Pangkat Terakhir',
                    'Fotokopi Kartu Keluarga',
                    'Fotokopi Akta Kelahiran Anak',
                    'Pas Foto Ahli Waris 3x4 (4 lembar)',
                    'Fotokopi Karpeg',
                    'Fotokopi Taspen',
                    'Fotokopi NPWP',
                    'Fotokopi Buku Rekening Ahli Waris',
                    'Surat Pengantar dari Instansi',
                    'Daftar Riwayat Hidup',
                    'Fotokopi Ijazah Terakhir',
                    'Surat Pernyataan Tidak Sedang Menerima Pensiun Lain',
                    'Penetapan Ahli Waris dari Pengadilan (jika diperlukan)',
                ],
            ],
            [
                'name'        => 'Pensiun Dini',
                'slug'        => 'pensiun-dini',
                'description' => 'Pensiun yang diberikan kepada pegawai yang mengajukan pensiun sebelum mencapai batas usia pensiun atas permintaan sendiri.',
                'icon'        => 'bi-door-open',
                'requirements' => [
                    'Surat Permohonan Pensiun Dini',
                    'Surat Rekomendasi dari Atasan Langsung',
                    'Surat Persetujuan dari Pejabat Berwenang',
                    'Fotokopi SK CPNS',
                    'Fotokopi SK PNS',
                    'Fotokopi SK Pangkat Terakhir',
                    'Fotokopi SK Jabatan Terakhir',
                    'Fotokopi KTP',
                    'Fotokopi Kartu Keluarga',
                    'Fotokopi Akta Nikah',
                    'Pas Foto 3x4 (6 lembar)',
                    'Daftar Riwayat Hidup',
                    'DP3 / SKP 2 Tahun Terakhir',
                    'Surat Keterangan Bebas Temuan',
                    'Fotokopi NPWP',
                    'Fotokopi Buku Rekening',
                    'Fotokopi Karpeg',
                    'Fotokopi Taspen',
                    'Surat Keterangan Tidak Sedang Menjalani Hukuman Disiplin',
                    'Fotokopi Ijazah Terakhir',
                ],
            ],
            [
                'name'        => 'Pensiun Cacat',
                'slug'        => 'pensiun-cacat',
                'description' => 'Pensiun yang diberikan kepada pegawai yang mengalami cacat akibat kedinasan sehingga tidak dapat menjalankan tugasnya lagi.',
                'icon'        => 'bi-hospital',
                'requirements' => [
                    'Surat Keterangan Cacat dari Dokter Spesialis',
                    'Berita Acara Kejadian (jika cacat akibat kecelakaan dinas)',
                    'Surat Keterangan dari Tim Penguji Kesehatan',
                    'Fotokopi SK CPNS',
                    'Fotokopi SK PNS',
                    'Fotokopi SK Pangkat Terakhir',
                    'Fotokopi KTP',
                    'Fotokopi Kartu Keluarga',
                    'Fotokopi Akta Nikah',
                    'Pas Foto 3x4 (6 lembar)',
                    'Daftar Riwayat Hidup',
                    'Surat Rekomendasi dari Kepala Instansi',
                    'DP3 / SKP 2 Tahun Terakhir',
                    'Fotokopi NPWP',
                    'Fotokopi Buku Rekening',
                    'Fotokopi Karpeg',
                    'Fotokopi Taspen',
                    'Rekam Medis Lengkap',
                    'Fotokopi Ijazah Terakhir',
                    'Surat Pernyataan Tidak Sanggup Bekerja dari Atasan',
                ],
            ],
            [
                'name'        => 'Pensiun Pemberhentian',
                'slug'        => 'pensiun-pemberhentian',
                'description' => 'Pensiun yang diberikan kepada pegawai yang diberhentikan dengan hormat atas dasar penyederhanaan organisasi atau pertimbangan kepentingan dinas.',
                'icon'        => 'bi-briefcase',
                'requirements' => [
                    'SK Pemberhentian dari Pejabat Berwenang',
                    'Surat Keterangan Penyederhanaan Organisasi',
                    'Fotokopi SK CPNS',
                    'Fotokopi SK PNS',
                    'Fotokopi SK Pangkat Terakhir',
                    'Fotokopi SK Jabatan Terakhir',
                    'Fotokopi KTP',
                    'Fotokopi Kartu Keluarga',
                    'Fotokopi Akta Nikah',
                    'Pas Foto 3x4 (6 lembar)',
                    'Daftar Riwayat Hidup',
                    'DP3 / SKP 2 Tahun Terakhir',
                    'Surat Keterangan Bebas Temuan',
                    'Fotokopi NPWP',
                    'Fotokopi Buku Rekening',
                    'Fotokopi Karpeg',
                    'Fotokopi Taspen',
                    'Surat Pernyataan Tidak Sedang Menjalani Hukuman',
                    'Fotokopi Ijazah Terakhir',
                    'Surat Pengantar dari Instansi',
                ],
            ],
        ];

        foreach ($types as $type) {
            $requirements = $type['requirements'];
            unset($type['requirements']);

            $pensionType = PensionType::create([
                ...$type,
                'requirements' => $requirements,
                'is_active'    => true,
            ]);

            // Buat document templates
            foreach ($requirements as $i => $req) {
                DocumentTemplate::create([
                    'pension_type_id' => $pensionType->id,
                    'document_name'   => $req,
                    'description'     => 'Upload berkas ' . $req . ' yang telah dilegalisir.',
                    'is_required'     => true,
                    'sort_order'      => $i + 1,
                ]);
            }
        }
    }

    private function seedRegulations(): void
    {
        $regulations = [
            [
                'title'       => 'Undang-Undang Nomor 11 Tahun 1969 tentang Pensiun Pegawai dan Pensiun Janda/Duda Pegawai',
                'number'      => 'UU No. 11 Tahun 1969',
                'description' => 'Mengatur tentang hak pensiun bagi pegawai negeri sipil dan keluarganya.',
                'category'    => 'uu',
                'year'        => 1969,
            ],
            [
                'title'       => 'Peraturan Pemerintah Nomor 11 Tahun 2017 tentang Manajemen Pegawai Negeri Sipil',
                'number'      => 'PP No. 11 Tahun 2017',
                'description' => 'Mengatur manajemen PNS termasuk ketentuan pensiun batas usia.',
                'category'    => 'pp',
                'year'        => 2017,
            ],
            [
                'title'       => 'Peraturan Pemerintah Nomor 45 Tahun 2015 tentang Penyelenggaraan Program Jaminan Pensiun',
                'number'      => 'PP No. 45 Tahun 2015',
                'description' => 'Mengatur penyelenggaraan program jaminan pensiun bagi pekerja.',
                'category'    => 'pp',
                'year'        => 2015,
            ],
        ];

        foreach ($regulations as $regulation) {
            Regulation::create([...$regulation, 'is_active' => true]);
        }
    }

    private function seedArticles(): void
    {
        $tik = User::where('role', 'tik')->first();

        $articles = [
            [
                'title'     => 'Workshop Kewirausahaan Budidaya Tanaman untuk Persiapan Pensiun',
                'category'  => 'kewirausahaan',
                'content'   => '<p>Program Masa Persiapan Pensiun (MPP) hadir dengan berbagai kegiatan yang membantu pegawai mempersiapkan diri memasuki masa pensiun. Salah satunya adalah workshop kewirausahaan budidaya tanaman yang sangat diminati.</p><p>Workshop ini mengajarkan teknik budidaya sayuran organik, tanaman hias, hingga tanaman obat yang memiliki nilai ekonomis tinggi. Peserta akan mendapatkan pengetahuan praktis tentang cara memulai usaha pertanian skala rumahan.</p>',
                'excerpt'   => 'Program MPP menghadirkan workshop kewirausahaan budidaya tanaman untuk membantu persiapan masa pensiun.',
            ],
            [
                'title'     => 'Tips Mengelola Keuangan di Masa Pensiun',
                'category'  => 'keuangan',
                'content'   => '<p>Memasuki masa pensiun memerlukan perencanaan keuangan yang matang. Berikut beberapa tips mengelola keuangan agar tetap sejahtera di masa pensiun.</p><p>Pertama, buatlah anggaran bulanan yang realistis berdasarkan uang pensiun yang akan diterima. Kedua, diversifikasi sumber penghasilan dengan investasi yang tepat.</p>',
                'excerpt'   => 'Panduan praktis mengelola keuangan agar tetap sejahtera selama masa pensiun.',
            ],
        ];

        foreach ($articles as $article) {
            Article::create([
                ...$article,
                'author_id'    => $tik?->id ?? 1,
                'is_published' => true,
                'published_at' => now(),
            ]);
        }
    }
}
