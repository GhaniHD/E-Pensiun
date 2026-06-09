<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('pension_type_id')
                  ->constrained('pension_types');
            $table->enum('status', [
                'pengisian_form',
                'pemberkasan',
                'upload',
                'verifikasi',
                'acc',
                'sk_terbit',
            ])->default('pengisian_form');
            $table->foreignId('verified_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->date('pension_date')->nullable();
            $table->text('rejection_note')->nullable();
            $table->string('sk_number')->nullable();
            $table->timestamp('sk_issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
