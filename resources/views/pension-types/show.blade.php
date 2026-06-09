@extends('layouts.app')

@section('title', $pensionType->name)
@section('page-title', 'Detail Jenis Pensiun')

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.8rem">
            <li class="breadcrumb-item"><a href="{{ route('pension-types.index') }}" class="text-decoration-none">Jenis Pensiun</a></li>
            <li class="breadcrumb-item active">{{ $pensionType->name }}</li>
        </ol>
    </nav>

    {{-- Header Card --}}
    <div class="card mb-4" style="background:linear-gradient(135deg,#1B4F72,#2E86C1);border:none">
        <div class="card-body py-4 px-4">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div style="width:72px;height:72px;border-radius:16px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="bi {{ $pensionType->icon ?? 'bi-award' }}" style="font-size:2.2rem;color:#fff"></i>
                </div>
                <div style="flex:1">
                    <h4 class="text-white fw-bold mb-1">{{ $pensionType->name }}</h4>
                    @if($pensionType->description)
                        <p class="mb-0" style="color:rgba(255,255,255,0.8);font-size:0.9rem">{{ $pensionType->description }}</p>
                    @endif
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;font-size:0.78rem">
                            <i class="bi bi-file-earmark-text me-1"></i>{{ $templates->count() }} berkas persyaratan
                        </span>
                        <span class="badge" style="background:rgba(30,132,73,0.8);color:#fff;font-size:0.78rem">
                            <i class="bi bi-check-circle me-1"></i>Aktif
                        </span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if(auth()->user()->isPensiunan() || auth()->user()->isSdmKantor())
                        <a href="{{ route('applications.create') }}?pension_type_id={{ $pensionType->id }}"
                           class="btn btn-light fw-600 d-flex align-items-center gap-2">
                            <i class="bi bi-plus-circle-fill" style="color:var(--primary)"></i>
                            Ajukan Pensiun Ini
                        </a>
                    @endif
                    @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                        <a href="{{ route('pension-types.edit', $pensionType) }}"
                           class="btn btn-outline-light d-flex align-items-center gap-2">
                            <i class="bi bi-pencil-fill"></i> Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Document Templates --}}
    <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-ruled-fill"></i>
            Daftar Berkas Persyaratan
            <span class="badge ms-1" style="background:var(--primary)">{{ $templates->count() }}</span>
        </div>

        @if($templates->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-folder2" style="font-size:2.5rem;color:#ccc"></i>
                <p class="mt-2 text-muted mb-0" style="font-size:0.875rem">
                    Belum ada berkas persyaratan yang ditentukan untuk jenis pensiun ini.
                </p>
                @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                    <a href="{{ route('pension-types.edit', $pensionType) }}" class="btn btn-primary btn-sm mt-3">
                        <i class="bi bi-pencil-fill me-1"></i> Tambah Berkas
                    </a>
                @endif
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach($templates as $index => $template)
                    <div class="list-group-item px-4 py-3">
                        <div class="d-flex align-items-start gap-3">

                            {{-- Nomor urut --}}
                            <div class="doc-number">{{ $index + 1 }}</div>

                            {{-- Info dokumen --}}
                            <div style="flex:1;min-width:0">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <span class="fw-600" style="font-size:0.9rem;color:#212529">
                                        {{ $template->document_name }}
                                    </span>
                                    @if($template->is_required)
                                        <span class="badge" style="background:#C0392B;font-size:0.68rem">Wajib</span>
                                    @else
                                        <span class="badge bg-secondary" style="font-size:0.68rem">Opsional</span>
                                    @endif
                                </div>
                                @if($template->description)
                                    <p class="text-muted mb-0" style="font-size:0.82rem;line-height:1.5">
                                        {{ $template->description }}
                                    </p>
                                @endif
                            </div>

                            {{-- Tombol unduh contoh --}}
                            @if($template->file_path)
                                <a href="{{ route('document-templates.download', $template) }}"
                                   class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 flex-shrink-0"
                                   title="Unduh contoh format">
                                    <i class="bi bi-download"></i>
                                    <span class="d-none d-md-inline">Unduh Contoh</span>
                                </a>
                            @else
                                <span class="text-muted" style="font-size:0.75rem;white-space:nowrap;flex-shrink:0">
                                    <i class="bi bi-dash"></i> Tidak ada contoh
                                </span>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Info footer --}}
            <div class="card-footer" style="background:#f8f9fa;font-size:0.8rem;color:#6c757d">
                <i class="bi bi-info-circle me-1"></i>
                Berkas bertanda <span class="badge" style="background:#C0392B;font-size:0.65rem">Wajib</span>
                harus diunggah sebelum pengajuan dapat diproses.
                Berkas opsional dianjurkan untuk mempercepat verifikasi.
            </div>
        @endif
    </div>

    {{-- Back button --}}
    <div class="mt-3">
        <a href="{{ route('pension-types.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Jenis Pensiun
        </a>
    </div>

@endsection

@section('styles')
<style>
    .doc-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .fw-600 { font-weight: 600; }
    .list-group-item:hover { background: #f8fbff; }
</style>
@endsection
