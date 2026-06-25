@extends('layouts.app')

@section('title', 'Tambah Regulasi')
@section('page-title', 'Tambah Regulasi')

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.8rem">
            <li class="breadcrumb-item"><a href="{{ route('regulations.index') }}" class="text-decoration-none">Regulasi / UU</a></li>
            <li class="breadcrumb-item active">Tambah Baru</li>
        </ol>
    </nav>

    <form action="{{ route('regulations.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">

            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle-fill me-2"></i>Informasi Regulasi
                    </div>
                    <div class="card-body p-4">

                        {{-- Judul --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" placeholder="Contoh: Undang-Undang Aparatur Sipil Negara" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            {{-- Nomor --}}
                            <div class="col-12 col-md-8 mb-3">
                                <label class="form-label fw-600">Nomor</label>
                                <input type="text" name="number"
                                       class="form-control @error('number') is-invalid @enderror"
                                       value="{{ old('number') }}" placeholder="Contoh: UU No. 5 Tahun 2014">
                                @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Tahun --}}
                            <div class="col-12 col-md-4 mb-3">
                                <label class="form-label fw-600">Tahun</label>
                                <input type="number" name="year" min="1900" max="{{ date('Y') + 1 }}"
                                       class="form-control @error('year') is-invalid @enderror"
                                       value="{{ old('year') }}" placeholder="{{ date('Y') }}">
                                @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Deskripsi</label>
                            <textarea name="description" rows="4"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Ringkasan isi regulasi...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- File PDF --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">File Dokumen (PDF)</label>
                            <input type="file" name="file" accept="application/pdf"
                                   class="form-control @error('file') is-invalid @enderror">
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Format PDF, maks. 10MB.</div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-gear-fill me-2"></i>Pengaturan
                    </div>
                    <div class="card-body p-4">

                        {{-- Kategori --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="category"
                                   class="form-control @error('category') is-invalid @enderror"
                                   value="{{ old('category') }}" placeholder="Contoh: Kepegawaian" required
                                   list="categoryList">
                            <datalist id="categoryList">
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Status aktif --}}
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active"
                                       name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="is_active">
                                    Aktifkan regulasi ini
                                </label>
                            </div>
                            <div class="form-text">Regulasi nonaktif tidak akan tampil di daftar publik.</div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ── TOMBOL SIMPAN ────────────────────────────── --}}
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> Simpan Regulasi
            </button>
            <a href="{{ route('regulations.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-x-circle"></i> Batal
            </a>
        </div>

    </form>

@endsection

@section('styles')
<style>
    .fw-600 { font-weight: 600; }
</style>
@endsection
