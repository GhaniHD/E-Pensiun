@extends('layouts.app')

@section('title', 'Edit Artikel')
@section('page-title', 'Edit Artikel')

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.8rem">
            <li class="breadcrumb-item"><a href="{{ route('articles.index') }}" class="text-decoration-none">Artikel MPP</a></li>
            <li class="breadcrumb-item"><a href="{{ route('articles.show', $article) }}" class="text-decoration-none">{{ \Illuminate\Support\Str::limit($article->title, 40) }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <form action="{{ route('articles.update', $article) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ── KOLOM KIRI: Konten ──────────────────── --}}
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-pencil-square me-2"></i>Konten Artikel
                    </div>
                    <div class="card-body p-4">

                        {{-- Judul --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $article->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Konten --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Isi Artikel <span class="text-danger">*</span></label>
                            <textarea name="content" rows="12"
                                      class="form-control @error('content') is-invalid @enderror">{{ old('content', $article->content) }}</textarea>
                            @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Anda dapat menggunakan tag HTML dasar seperti &lt;p&gt;, &lt;b&gt;, &lt;ul&gt;, &lt;img&gt;.</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── KOLOM KANAN: Publikasi ────────────────── --}}
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
                                   value="{{ old('category', $article->category) }}" required>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Thumbnail saat ini --}}
                        @if($article->thumbnail)
                            <div class="mb-3">
                                <label class="form-label fw-600">Thumbnail Saat Ini</label>
                                <img src="{{ Storage::url($article->thumbnail) }}" alt=""
                                     class="img-fluid rounded" style="max-height:140px;object-fit:cover">
                            </div>
                        @endif

                        {{-- Thumbnail baru --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">{{ $article->thumbnail ? 'Ganti Thumbnail' : 'Thumbnail' }}</label>
                            <input type="file" name="thumbnail" accept="image/*"
                                   class="form-control @error('thumbnail') is-invalid @enderror">
                            @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Format gambar, maks. 2MB. Biarkan kosong jika tidak ingin mengganti.</div>
                        </div>

                        {{-- Status publikasi --}}
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_published"
                                       name="is_published" value="1"
                                       {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="is_published">
                                    Publikasikan
                                </label>
                            </div>
                            <div class="form-text">Jika tidak dicentang, artikel disimpan sebagai draf.</div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ── TOMBOL SIMPAN ────────────────────────────── --}}
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> Simpan Perubahan
            </button>
            <a href="{{ route('articles.show', $article) }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
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
