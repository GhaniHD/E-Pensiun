@extends('layouts.app')

@section('title', $regulation->title)
@section('page-title', 'Detail Regulasi')

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.8rem">
            <li class="breadcrumb-item"><a href="{{ route('regulations.index') }}" class="text-decoration-none">Regulasi / UU</a></li>
            <li class="breadcrumb-item active">{{ \Illuminate\Support\Str::limit($regulation->title, 50) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="reg-icon-wrap">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                        </div>
                        <div>
                            <span class="badge mb-2" style="background:rgba(27,79,114,0.12);color:var(--primary);font-size:0.75rem">
                                {{ $regulation->category }}
                            </span>
                            <h5 class="fw-700 mb-1" style="color:var(--primary)">{{ $regulation->title }}</h5>
                            <div class="text-muted" style="font-size:0.85rem">
                                @if($regulation->number)
                                    <span class="me-3"><i class="bi bi-hash me-1"></i>{{ $regulation->number }}</span>
                                @endif
                                @if($regulation->year)
                                    <span><i class="bi bi-calendar3 me-1"></i>Tahun {{ $regulation->year }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($regulation->description)
                        <hr>
                        <h6 class="fw-600 mb-2">Deskripsi</h6>
                        <p style="font-size:0.92rem;line-height:1.8;color:#333">
                            {{ $regulation->description }}
                        </p>
                    @endif

                   @if($regulation->file_path)
    <hr>
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="fw-600 mb-0">Dokumen</h6>
        <a href="{{ route('regulations.download', $regulation) }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
            <i class="bi bi-download"></i> Unduh PDF
        </a>
    </div>
    <div class="ratio ratio-4x3" style="--bs-aspect-ratio:130%">
        <iframe src="{{ route('regulations.preview', $regulation) }}"
                style="border:1px solid #e2e8f0;border-radius:8px"
                title="Pratinjau {{ $regulation->title }}">
        </iframe>
    </div>
@else
                        <hr>
                        <p class="text-muted mb-0" style="font-size:0.85rem">
                            <i class="bi bi-info-circle me-1"></i>Belum ada file dokumen yang diunggah.
                        </p>
                    @endif
                </div>
            </div>

            @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('regulations.edit', $regulation) }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-fill"></i> Edit Regulasi
                    </a>
                    <form action="{{ route('regulations.destroy', $regulation) }}" method="POST"
                          onsubmit="return confirm('Hapus regulasi ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2">
                            <i class="bi bi-trash-fill"></i> Hapus
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Status --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle-fill me-2"></i>Informasi
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted" style="font-size:0.85rem">Status</span>
                        @if($regulation->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted" style="font-size:0.85rem">Kategori</span>
                        <span class="fw-600" style="font-size:0.85rem">{{ $regulation->category }}</span>
                    </div>
                    @if($regulation->year)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size:0.85rem">Tahun</span>
                            <span class="fw-600" style="font-size:0.85rem">{{ $regulation->year }}</span>
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
    .reg-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1B4F72, #2E86C1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .reg-icon-wrap i { font-size: 1.6rem; color: #fff; }
</style>
@endsection
