<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Kolom verifikasi tahap 1 — KPKNL Pelayanan (sdm_kantor)
            $table->enum('kantor_check_status', ['lengkap_sesuai', 'lengkap_tidak_sesuai', 'tidak_lengkap'])
                  ->nullable()->after('rejection_note');
            $table->text('kantor_check_note')->nullable()->after('kantor_check_status');
            $table->foreignId('kantor_checked_by')->nullable()->after('kantor_check_note')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('kantor_checked_at')->nullable()->after('kantor_checked_by');

            // Kolom verifikasi tahap 2 — DJKN Kanwil (sdm_kanwil)
            $table->enum('kanwil_status', ['sesuai', 'tidak_sesuai', 'pending'])
                  ->default('pending')->after('kantor_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['kantor_checked_by']);
            $table->dropColumn([
                'kantor_check_status', 'kantor_check_note',
                'kantor_checked_by', 'kantor_checked_at',
                'kanwil_status',
            ]);
        });
    }
};
