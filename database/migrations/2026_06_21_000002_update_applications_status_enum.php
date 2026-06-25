<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom status di applications menjadi string sementara agar bisa menambah nilai enum baru
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
            'pengisian_form',
            'pemberkasan',
            'upload',
            'verifikasi_kpknl',
            'verifikasi_kanwil',
            'acc',
            'sk_terbit'
        ) NOT NULL DEFAULT 'pengisian_form'");

        // Ubah kolom from_status & to_status di application_status_histories
        DB::statement("ALTER TABLE application_status_histories MODIFY COLUMN from_status VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE application_status_histories MODIFY COLUMN to_status VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
            'pengisian_form',
            'pemberkasan',
            'upload',
            'verifikasi',
            'acc',
            'sk_terbit'
        ) NOT NULL DEFAULT 'pengisian_form'");
    }
};
