@extends('layouts.app')

@section('title', 'Artikel MPP')
@section('page-title', 'Artikel MPP')

@section('content')

    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="bi bi-newspaper me-2"></i>Artikel MPP</h4>
            <p class="text-muted mb-0" style="font-size:0.875rem">
                Informasi dan tips seputar Masa Persiapan Pensiun.
            </p>
        </div>
        @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
            <a href="{{ route('articles.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i> Tulis Artikel
            </a>
        @endif
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body p-3">
            <form action="{{ route('articles.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-600 mb-1" style="font-size:0.82rem">Cari Judul</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Cari artikel...">
                </div>
                <div class="col-8 col-md-4">
                    <label class="form-label fw-600 mb-1" style="font-size:0.82rem">Kategori</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search') || request('category'))
                        <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Empty State --}}
    @if($articles->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-newspaper" style="font-size:3rem;color:#ccc"></i>
                <p class="mt-3 text-muted mb-0">Belum ada artikel yang dipublikasikan.</p>
                @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                    <a href="{{ route('articles.create') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tulis Artikel Pertama
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($articles as $article)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 article-card">
                        @if($article->thumbnail)
                            <img src="{{ Storage::url($article->thumbnail) }}" alt="{{ $article->title }}"
                                 class="article-thumb">
                        @else
                            <div class="article-thumb d-flex align-items-center justify-content-center"
                                 style="background:linear-gradient(135deg,#1B4F72,#2E86C1)">
                                <i class="bi bi-newspaper" style="font-size:2.2rem;color:#fff;opacity:0.7"></i>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <span class="badge mb-2 align-self-start"
                                  style="background:rgba(27,79,114,0.12);color:var(--primary);font-size:0.72rem">
                                {{ $article->category }}
                            </span>
                            <h6 class="fw-700 mb-2" style="color:var(--primary);font-size:0.95rem;line-height:1.3">
                                {{ $article->title }}
                            </h6>
                            <p class="text-muted mb-0" style="font-size:0.85rem;flex:1;line-height:1.6">
                                {{ \Illuminate\Support\Str::limit($article->excerpt, 110) }}
                            </p>
                            <hr class="my-3">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted" style="font-size:0.75rem">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $article->published_at?->translatedFormat('d M Y') }}
                                </small>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('articles.show', $article) }}"
                                       class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-eye-fill"></i> Baca
                                    </a>
                                    @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                                        <a href="{{ route('articles.edit', $article) }}"
                                           class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('articles.destroy', $article) }}" method="POST"
                                              onsubmit="return confirm('Hapus artikel \'{{ addslashes($article->title) }}\'? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $articles->links() }}
        </div>
    @endif

@endsection

@section('styles')
<style>
    .article-card {
        transition: transform 0.15s, box-shadow 0.15s;
        border: 1px solid #e9ecef !important;
        overflow: hidden;
    }
    .article-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(27,79,114,0.12) !important;
    }
    .article-thumb {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
</style>
@endsection
