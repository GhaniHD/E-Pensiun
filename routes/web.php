<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PensionTypeController;
use App\Http\Controllers\RegulationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Auth ───────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── Authenticated ──────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Jenis Pensiun ──────────────────────────────────────
    Route::get('/jenis-pensiun', [PensionTypeController::class, 'index'])
        ->name('pension-types.index');

    Route::middleware('role:tik,sdm_kanwil')->group(function () {
        Route::get('/jenis-pensiun/create', [PensionTypeController::class, 'create'])
            ->name('pension-types.create');
        Route::post('/jenis-pensiun', [PensionTypeController::class, 'store'])
            ->name('pension-types.store');
        Route::get('/jenis-pensiun/{pensionType}/edit', [PensionTypeController::class, 'edit'])
            ->name('pension-types.edit');
        Route::put('/jenis-pensiun/{pensionType}', [PensionTypeController::class, 'update'])
            ->name('pension-types.update');
        Route::delete('/jenis-pensiun/{pensionType}', [PensionTypeController::class, 'destroy'])
            ->name('pension-types.destroy');
    });

    Route::get('/jenis-pensiun/{pensionType}', [PensionTypeController::class, 'show'])
        ->name('pension-types.show');

    Route::get('/berkas-contoh/{documentTemplate}/download', [PensionTypeController::class, 'downloadTemplate'])
        ->name('document-templates.download');
    Route::get('/berkas-contoh/{documentTemplate}/preview', [PensionTypeController::class, 'previewTemplate'])
        ->name('document-templates.preview');

    Route::middleware('role:tik,sdm_kanwil')->group(function () {
        Route::post('/berkas-contoh/{documentTemplate}/upload', [PensionTypeController::class, 'uploadTemplate'])
            ->name('document-templates.upload');
    });
    // ── Pengajuan ──────────────────────────────────────────
    Route::get('/pengajuan', [ApplicationController::class, 'index'])
        ->name('applications.index');

    Route::middleware('role:sdm_kantor')->group(function () {
        Route::get('/pengajuan/create', [ApplicationController::class, 'create'])
            ->name('applications.create');
        Route::post('/pengajuan', [ApplicationController::class, 'store'])
            ->name('applications.store');
        Route::get('/pengajuan/{application}/edit', [ApplicationController::class, 'edit'])
            ->name('applications.edit');
        Route::put('/pengajuan/{application}', [ApplicationController::class, 'update'])
            ->name('applications.update');
    });

    // Tanpa middleware — otorisasi di controller
    Route::post('/pengajuan/{application}/advance', [ApplicationController::class, 'advance'])
        ->name('applications.advance');
    Route::post('/pengajuan/{application}/reject', [ApplicationController::class, 'reject'])
        ->name('applications.reject');
    Route::post('/pengajuan/{application}/cancel', [ApplicationController::class, 'cancel'])
        ->name('applications.cancel');
    Route::post('/pengajuan/{application}/return-upload', [ApplicationController::class, 'returnToUpload'])
        ->name('applications.return-upload');

    Route::get('/pengajuan/{application}', [ApplicationController::class, 'show'])
        ->name('applications.show');

    // ── Dokumen ────────────────────────────────────────────
    Route::post('/pengajuan/{application}/dokumen', [DocumentController::class, 'store'])
        ->name('documents.store');
    Route::post('/pengajuan/{application}/dokumen/single', [DocumentController::class, 'storeSingle'])
        ->name('documents.store-single');
    Route::get('/dokumen/{document}/preview', [DocumentController::class, 'preview'])
        ->name('documents.preview');
    Route::get('/dokumen/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download');
    Route::delete('/dokumen/{document}', [DocumentController::class, 'destroy'])
        ->name('documents.destroy');

    // Checklist KPKNL Pelayanan (sdm_kantor) — individual & bulk
    Route::middleware('role:sdm_kantor')->group(function () {
        Route::post('/dokumen/{document}/kantor-check', [DocumentController::class, 'kantorCheck'])
            ->name('documents.kantor-check');
        // BULK: simpan semua berkas sekaligus
        Route::post('/pengajuan/{application}/bulk-kantor-check', [DocumentController::class, 'bulkKantorCheck'])
            ->name('documents.bulk-kantor-check');
    });

    // Double-check DJKN Kanwil (sdm_kanwil) — individual & bulk
    Route::middleware('role:sdm_kanwil')->group(function () {
        Route::post('/dokumen/{document}/kanwil-check', [DocumentController::class, 'kanwilCheck'])
            ->name('documents.kanwil-check');
        // BULK: verifikasi semua berkas sekaligus
        Route::post('/pengajuan/{application}/bulk-kanwil-check', [DocumentController::class, 'bulkKanwilCheck'])
            ->name('documents.bulk-kanwil-check');
        // Method lama dipertahankan
        Route::post('/dokumen/{document}/verify', [DocumentController::class, 'verify'])
            ->name('documents.verify');
        Route::post('/dokumen/{document}/reject', [DocumentController::class, 'reject'])
            ->name('documents.reject');
    });

    // ── Artikel MPP ────────────────────────────────────────
    Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');

    Route::middleware('role:tik,sdm_kanwil')->group(function () {
        Route::get('/artikel/create', [ArticleController::class, 'create'])->name('articles.create');
        Route::post('/artikel', [ArticleController::class, 'store'])->name('articles.store');
        Route::get('/artikel/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/artikel/{article}', [ArticleController::class, 'update'])->name('articles.update');
        Route::delete('/artikel/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');
    });
    Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

    // ── Regulasi ───────────────────────────────────────────
    Route::get('/regulasi', [RegulationController::class, 'index'])->name('regulations.index');
    Route::get('/regulasi/{regulation}/preview', [RegulationController::class, 'preview'])->name('regulations.preview');
    Route::get('/regulasi/{regulation}/download', [RegulationController::class, 'download'])
        ->name('regulations.download');

    Route::middleware('role:tik,sdm_kanwil')->group(function () {
        Route::get('/regulasi/create', [RegulationController::class, 'create'])->name('regulations.create');
        Route::post('/regulasi', [RegulationController::class, 'store'])->name('regulations.store');
        Route::get('/regulasi/{regulation}/edit', [RegulationController::class, 'edit'])->name('regulations.edit');
        Route::put('/regulasi/{regulation}', [RegulationController::class, 'update'])->name('regulations.update');
        Route::delete('/regulasi/{regulation}', [RegulationController::class, 'destroy'])->name('regulations.destroy');
    });
    Route::get('/regulasi/{regulation}', [RegulationController::class, 'show'])->name('regulations.show');

    // ── Manajemen User (TIK only) ──────────────────────────
    Route::middleware('role:tik')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active');
    });
});
