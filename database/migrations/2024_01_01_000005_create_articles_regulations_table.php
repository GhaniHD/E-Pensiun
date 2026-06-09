<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Articles ───────────────────────────────────────
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('category')->default('umum')
                  ->comment('umum, kewirausahaan, kesehatan, keuangan, dll');
            $table->foreignId('author_id')
                  ->constrained('users');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // ── Regulations ────────────────────────────────────
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('number')->nullable()->comment('Nomor UU/PP/Peraturan');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('category')->default('uu')
                  ->comment('uu, pp, peraturan_menteri, dll');
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulations');
        Schema::dropIfExists('articles');
    }
};
