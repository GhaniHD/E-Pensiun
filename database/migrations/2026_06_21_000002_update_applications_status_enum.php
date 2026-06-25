<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL syntax
            DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
                'pengisian_form',
                'pemberkasan',
                'upload',
                'verifikasi_kpknl',
                'verifikasi_kanwil',
                'acc',
                'sk_terbit'
            ) NOT NULL DEFAULT 'pengisian_form'");

            DB::statement("ALTER TABLE application_status_histories MODIFY COLUMN from_status VARCHAR(50) NOT NULL");
            DB::statement("ALTER TABLE application_status_histories MODIFY COLUMN to_status VARCHAR(50) NOT NULL");
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
                'sk_terbit'
            ))");

            DB::statement("ALTER TABLE application_status_histories ALTER COLUMN from_status TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE application_status_histories ALTER COLUMN to_status TYPE VARCHAR(50)");
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
                'verifikasi',
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
                'verifikasi',
                'acc',
                'sk_terbit'
            ))");
        }
    }
};
