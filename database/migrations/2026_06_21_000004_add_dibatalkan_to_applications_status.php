<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
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
        } else {
            // PostgreSQL syntax
            DB::statement("ALTER TABLE applications DROP CONSTRAINT IF EXISTS applications_status_check");
            DB::statement("ALTER TABLE applications ALTER COLUMN status TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE applications ADD CONSTRAINT applications_status_check CHECK (status IN (
                'pengisian_form',
                'pemberkasan',
                'upload',
                'verifikasi_kpknl',
                'verifikasi_kanwil',
                'acc',
                'sk_terbit',
                'dibatalkan'
            ))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
                'pengisian_form',
                'pemberkasan',
                'upload',
                'verifikasi_kpknl',
                'verifikasi_kanwil',
                'acc',
                'sk_terbit'
            ) NOT NULL DEFAULT 'pengisian_form'");
        } else {
            DB::statement("ALTER TABLE applications DROP CONSTRAINT IF EXISTS applications_status_check");
            DB::statement("ALTER TABLE applications ALTER COLUMN status TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE applications ADD CONSTRAINT applications_status_check CHECK (status IN (
                'pengisian_form',
                'pemberkasan',
                'upload',
                'verifikasi_kpknl',
                'verifikasi_kanwil',
                'acc',
                'sk_terbit'
            ))");
        }
    }
};
