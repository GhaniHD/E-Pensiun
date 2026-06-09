<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Documents ──────────────────────────────────────
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                ->constrained('applications')
                ->cascadeOnDelete();
            $table->foreignId('uploaded_by')
                ->constrained('users');
            $table->string('document_name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->boolean('is_verified')->default(null);
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();
        });

        // ── Document Templates ─────────────────────────────
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pension_type_id')
                ->constrained('pension_types')
                ->cascadeOnDelete();
            $table->string('document_name');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable()->comment('File contoh format berkas');
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Application Status Histories ───────────────────
        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                ->constrained('applications')
                ->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->foreignId('changed_by')
                ->constrained('users');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('documents');
    }
};
