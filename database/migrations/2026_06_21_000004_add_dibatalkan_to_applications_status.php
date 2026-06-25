<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
            'pengisian_form',
            'pemberkasan',
            'upload',
            'verifikasi_kpknl',
            'verifikasi_kanwil',
            'acc',
            'sk_terbit',
            'dibatalkan'
        ) NOT NULL DEFAULT 'pengisian_form'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
            'pengisian_form',
            'pemberkasan',
            'upload',
            'verifikasi_kpknl',
            'verifikasi_kanwil',
            'acc',
            'sk_terbit'
        ) NOT NULL DEFAULT 'pengisian_form'");
    }
};
