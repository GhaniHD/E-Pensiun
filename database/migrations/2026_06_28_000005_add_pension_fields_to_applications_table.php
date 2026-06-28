<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // ── Data Calon Pensiunan ───────────────────────────────
            $table->string('nama_calon_pensiunan')->nullable()->after('notes');
            $table->string('unit_kerja')->nullable()->after('nama_calon_pensiunan');
            $table->string('nip_calon_pensiunan', 25)->nullable()->after('unit_kerja');
            $table->date('tanggal_lahir')->nullable()->after('nip_calon_pensiunan');
            $table->string('jenis_pensiun_bkn')->nullable()->after('tanggal_lahir');
            // true = naik pangkat, false = tidak, null = belum diisi
            $table->boolean('kenaikan_pangkat')->nullable()->after('jenis_pensiun_bkn');

            // ── Input Kalkulator Masa Kerja ────────────────────────
            $table->unsignedTinyInteger('usia_pensiun')->default(58)->after('kenaikan_pangkat');
            $table->date('tmt_cpns')->nullable()->after('usia_pensiun');
            $table->date('tmt_pns')->nullable()->after('tmt_cpns');
            $table->date('tmt_pangkat_terakhir')->nullable()->after('tmt_pns');
            $table->unsignedTinyInteger('mk_kp_terakhir_tahun')->nullable()->after('tmt_pangkat_terakhir');
            $table->unsignedTinyInteger('mk_kp_terakhir_bulan')->nullable()->after('mk_kp_terakhir_tahun');

            // ── Hasil Kalkulator (disimpan untuk referensi & cetak) ─
            $table->unsignedSmallInteger('mk_pensiun_tahun')->nullable()->after('mk_kp_terakhir_bulan');
            $table->unsignedTinyInteger('mk_pensiun_bulan')->nullable()->after('mk_pensiun_tahun');
            $table->unsignedSmallInteger('mk_pns_tahun')->nullable()->after('mk_pensiun_bulan');
            $table->unsignedTinyInteger('mk_pns_bulan')->nullable()->after('mk_pns_tahun');
            $table->unsignedSmallInteger('mk_golongan_tahun')->nullable()->after('mk_pns_bulan');
            $table->unsignedTinyInteger('mk_golongan_bulan')->nullable()->after('mk_golongan_tahun');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'nama_calon_pensiunan',
                'unit_kerja',
                'nip_calon_pensiunan',
                'tanggal_lahir',
                'jenis_pensiun_bkn',
                'kenaikan_pangkat',
                'usia_pensiun',
                'tmt_cpns',
                'tmt_pns',
                'tmt_pangkat_terakhir',
                'mk_kp_terakhir_tahun',
                'mk_kp_terakhir_bulan',
                'mk_pensiun_tahun',
                'mk_pensiun_bulan',
                'mk_pns_tahun',
                'mk_pns_bulan',
                'mk_golongan_tahun',
                'mk_golongan_bulan',
            ]);
        });
    }
};
