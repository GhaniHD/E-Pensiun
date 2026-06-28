@extends('layouts.app')

@section('title', 'Jenis Pensiun')
@section('page-title', 'Jenis Pensiun')

@section('content')

    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="bi bi-journal-bookmark-fill me-2"></i>Jenis Pensiun</h4>
            <p class="text-muted mb-0" style="font-size:0.875rem">
                Pilih jenis pensiun untuk melihat persyaratan dan mengajukan permohonan.
            </p>
        </div>
        @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
            <a href="{{ route('pension-types.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i> Tambah Jenis Pensiun
            </a>
        @endif
    </div>

    {{-- Empty State --}}
    @if($pensionTypes->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-journal-x" style="font-size:3rem;color:#ccc"></i>
                <p class="mt-3 text-muted mb-0">Belum ada jenis pensiun yang tersedia.</p>
                @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                    <a href="{{ route('pension-types.create') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Sekarang
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($pensionTypes as $type)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 pension-card">
                        <div class="card-body d-flex flex-column">

                            {{-- Icon & Name --}}
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="pension-icon-wrap">
                                    <i class="bi {{ $type->icon ?? 'bi-award' }}"></i>
                                </div>
                                <div style="flex:1;min-width:0">
                                    <h6 class="fw-700 mb-1" style="color:var(--primary);font-size:0.95rem;line-height:1.3">
                                        {{ $type->name }}
                                    </h6>
                                    <span class="badge" style="background:rgba(27,79,114,0.12);color:var(--primary);font-size:0.72rem">
                                        <i class="bi bi-file-earmark-text me-1"></i>
                                        {{ $type->documentTemplates()->count() }} berkas persyaratan
                                    </span>
                                </div>
                            </div>

                            {{-- Description --}}
                            <p class="text-muted mb-0" style="font-size:0.85rem;flex:1;line-height:1.6">
                                {{ $type->description ? \Illuminate\Support\Str::limit($type->description, 120) : 'Klik tombol di bawah untuk melihat detail persyaratan jenis pensiun ini.' }}
                            </p>

                            <hr class="my-3">

                            {{-- Actions --}}
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="{{ route('pension-types.show', $type) }}"
                                   class="btn btn-primary btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-eye-fill"></i> Lihat Persyaratan
                                </a>

                                @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                                    <a href="{{ route('pension-types.edit', $type) }}"
                                       class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form action="{{ route('pension-types.destroy', $type) }}" method="POST"
                                          onsubmit="return confirm('Hapus jenis pensiun \'{{ addslashes($type->name) }}\'? Tindakan ini tidak dapat dibatalkan.')">
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
            @endforeach
        </div>
    @endif

@endsection

@section('styles')
<style>
    .pension-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1A5632, #27AE60);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .pension-icon-wrap i {
        font-size: 1.6rem;
        color: #fff;
    }
    .pension-card {
        transition: transform 0.15s, box-shadow 0.15s;
        border: 1px solid #e9ecef !important;
    }
    .pension-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(27,79,114,0.12) !important;
    }
    .fw-700 { font-weight: 700; }
</style>
@endsection
