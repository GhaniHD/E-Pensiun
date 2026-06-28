@extends('layouts.app')

@section('title', $article->title)
@section('page-title', 'Detail Artikel')

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.8rem">
            <li class="breadcrumb-item"><a href="{{ route('articles.index') }}" class="text-decoration-none">Artikel MPP</a></li>
            <li class="breadcrumb-item active">{{ \Illuminate\Support\Str::limit($article->title, 50) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card">
                @if($article->thumbnail)
                    <img src="{{ Storage::url($article->thumbnail) }}" alt="{{ $article->title }}"
                         class="w-100" style="max-height:320px;object-fit:cover;border-radius:8px 8px 0 0">
                @endif
                <div class="card-body p-4">
                    <span class="badge mb-2" style="background:rgba(27,79,114,0.12);color:var(--primary);font-size:0.75rem">
                        {{ $article->category }}
                    </span>

                    <h4 class="fw-700 mb-2" style="color:var(--primary)">{{ $article->title }}</h4>

                    <div class="d-flex align-items-center gap-3 text-muted mb-4" style="font-size:0.82rem">
                        <span><i class="bi bi-person-circle me-1"></i>{{ $article->author?->name ?? '-' }}</span>
                        <span><i class="bi bi-calendar3 me-1"></i>{{ $article->published_at?->translatedFormat('d F Y') }}</span>
                    </div>

                    <div class="article-content" style="font-size:0.92rem;line-height:1.8;color:#333">
                        {!! $article->content !!}
                    </div>
                </div>
            </div>

            @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('articles.edit', $article) }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-fill"></i> Edit Artikel
                    </a>
                    <form action="{{ route('articles.destroy', $article) }}" method="POST"
                          onsubmit="return confirm('Hapus artikel ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2">
                            <i class="bi bi-trash-fill"></i> Hapus
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Related Articles --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-collection-fill me-2"></i>Artikel Terkait
                </div>
                <div class="card-body p-3">
                    @if($related->isEmpty())
                        <p class="text-muted mb-0" style="font-size:0.85rem">Tidak ada artikel terkait.</p>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($related as $item)
                                <a href="{{ route('articles.show', $item) }}" class="text-decoration-none related-item">
                                    <div class="d-flex gap-2 align-items-start">
                                        @if($item->thumbnail)
                                            <img src="{{ Storage::url($item->thumbnail) }}" alt=""
                                                 style="width:56px;height:56px;object-fit:cover;border-radius:6px;flex-shrink:0">
                                        @else
                                            <div style="width:56px;height:56px;border-radius:6px;flex-shrink:0;background:linear-gradient(135deg,#1A5632,#27AE60);display:flex;align-items:center;justify-content:center">
                                                <i class="bi bi-newspaper text-white"></i>
                                            </div>
                                        @endif
                                        <div style="min-width:0">
                                            <p class="mb-1 fw-600" style="font-size:0.82rem;color:var(--primary);line-height:1.3">
                                                {{ \Illuminate\Support\Str::limit($item->title, 60) }}
                                            </p>
                                            <small class="text-muted" style="font-size:0.72rem">
                                                {{ $item->published_at?->translatedFormat('d M Y') }}
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@section('styles')
<style>
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .article-content img { max-width: 100%; border-radius: 6px; }
    .related-item { transition: opacity 0.15s; }
    .related-item:hover { opacity: 0.7; }
</style>
@endsection
