@extends('layouts.app')

@section('title', 'Detail Pengajuan #' . $application->id)
@section('page-title', 'Detail Pengajuan')

@section('styles')
<style>
    /* ── STEPPER ────────────────────────────────────────── */
    .stepper {
        display: flex;
        align-items: flex-start;
        position: relative;
        overflow-x: auto;
        padding-bottom: .5rem;
    }

    .stepper::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 5%;
        right: 5%;
        height: 3px;
        background: #dee2e6;
        z-index: 0;
    }

    .step-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        min-width: 90px;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        font-weight: 700;
        border: 3px solid #dee2e6;
        background: #fff;
        transition: all .3s;
    }

    .step-circle.done {
        background: var(--bs-success, #1E8449);
        border-color: var(--bs-success, #1E8449);
        color: #fff;
    }

    .step-circle.current {
        background: #2E86C1;
        border-color: #2E86C1;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(46,134,193,.25);
        animation: pulse 1.8s infinite;
    }

    .step-circle.pending {
        background: #f8f9fa;
        border-color: #dee2e6;
        color: #adb5bd;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(46,134,193,.25); }
        50%       { box-shadow: 0 0 0 8px rgba(46,134,193,.12); }
    }

    .step-label {
        font-size: .72rem;
        text-align: center;
        margin-top: .5rem;
        color: #6c757d;
        max-width: 80px;
        line-height: 1.3;
    }

    .step-label.current {
        color: #2E86C1;
        font-weight: 600;
    }

    .step-label.done { color: #1E8449; }

    /* ── TIMELINE ───────────────────────────────────────── */
    .timeline { position: relative; padding-left: 2rem; }

    .timeline::before {
        content: '';
        position: absolute;
        left: .75rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item:last-child { padding-bottom: 0; }

    .timeline-dot {
        position: absolute;
        left: -1.45rem;
        top: .25rem;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--accent, #2E86C1);
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px var(--accent, #2E86C1);
    }

    .timeline-dot.rejected { background: #C0392B; box-shadow: 0 0 0 2px #C0392B; }

    /* ── DOCUMENT ITEM ──────────────────────────────────── */
    .doc-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem 1.25rem;
        margin-bottom: .75rem;
        background: #fff;
        transition: border-color .2s;
    }

    .doc-item:hover { border-color: #adb5bd; }
    .doc-item.doc-uploaded  { border-left: 4px solid #1E8449; }
    .doc-item.doc-missing   { border-left: 4px solid #dee2e6; }
    .doc-item.doc-rejected  { border-left: 4px solid #C0392B; }
</style>
@endsection

@section('content')

{{-- ── BREADCRUMB & HEADER ─────────────────────────────────── --}}
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('applications.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <h4 class="mb-0">
                <i class="bi bi-folder2-open me-2 text-primary"></i>
                Detail Pengajuan #{{ $application->id }}
            </h4>
        </div>
        <p class="text-muted mb-0" style="font-size:.85rem">
            Dibuat: {{ $application->created_at->translatedFormat('d F Y, H:i') }} WIB
        </p>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <span class="badge fs-6 bg-{{ $application->status->badgeColor() }} px-3 py-2">
            {{ $application->status->label() }}
        </span>

        @if($application->status->value === 'pengisian_form' &&
            (auth()->user()->isPensiunan() || auth()->user()->isSdmKantor()))
            <a href="{{ route('applications.edit', $application) }}"
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil-fill me-1"></i>Edit
            </a>
        @endif
    </div>
</div>

{{-- ── SECTION 1: INFO PENGAJUAN ───────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person-lines-fill me-2"></i>Informasi Pengajuan</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            Nama Pegawai
                        </div>
                        <div class="fw-semibold mt-1">{{ $application->user->name }}</div>
                    </div>

                    @if($application->user->nip)
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            NIP
                        </div>
                        <div class="fw-semibold mt-1 font-monospace">{{ $application->user->nip }}</div>
                    </div>
                    @endif

                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            Kantor
                        </div>
                        <div class="fw-semibold mt-1">{{ $application->user->office ?? '—' }}</div>
                    </div>

                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            Jenis Pensiun
                        </div>
                        <div class="fw-semibold mt-1">{{ $application->pensionType->name }}</div>
                    </div>

                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            Tanggal Pengajuan
                        </div>
                        <div class="fw-semibold mt-1">
                            {{ $application->created_at->format('d M Y') }}
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            Rencana Pensiun
                        </div>
                        <div class="fw-semibold mt-1">
                            {{ $application->pension_date?->format('d M Y') ?? '—' }}
                        </div>
                    </div>

                    @if($application->verifier)
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            Diverifikasi Oleh
                        </div>
                        <div class="fw-semibold mt-1">{{ $application->verifier->name }}</div>
                    </div>
                    @endif

                    @if($application->sk_number)
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            Nomor SK
                        </div>
                        <div class="fw-semibold mt-1 font-monospace">{{ $application->sk_number }}</div>
                    </div>
                    @endif

                    @if($application->sk_issued_at)
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            Tanggal Terbit SK
                        </div>
                        <div class="fw-semibold mt-1">{{ $application->sk_issued_at->format('d M Y') }}</div>
                    </div>
                    @endif

                    @if($application->notes)
                    <div class="col-12">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                            Catatan
                        </div>
                        <div class="mt-1 p-2 rounded" style="background:#f8f9fa;font-size:.88rem">
                            {{ $application->notes }}
                        </div>
                    </div>
                    @endif

                    @if($application->rejection_note)
                    <div class="col-12">
                        <div class="alert alert-danger mb-0 py-2" style="font-size:.87rem">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Catatan Penolakan:</strong> {{ $application->rejection_note }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Card --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart-steps me-2"></i>Progress</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="width:130px;height:130px;position:relative;margin-bottom:1.25rem">
                    <svg viewBox="0 0 36 36" style="width:100%;height:100%;transform:rotate(-90deg)">
                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#e9ecef" stroke-width="3"/>
                        <circle cx="18" cy="18" r="15.9155" fill="none"
                                stroke="#2E86C1" stroke-width="3"
                                stroke-dasharray="{{ $application->progress_percentage }}, 100"
                                stroke-linecap="round"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
                        <span style="font-size:1.6rem;font-weight:700;color:var(--primary)">
                            {{ $application->progress_percentage }}%
                        </span>
                        <span style="font-size:.7rem;color:#6c757d">Selesai</span>
                    </div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold" style="color:var(--primary)">
                        {{ $application->status->label() }}
                    </div>
                    <div class="text-muted" style="font-size:.8rem">
                        Tahap {{ $application->status->order() }} dari 6
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── SECTION 2: STEPPER STATUS ───────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-diagram-3-fill me-2"></i>Alur Proses Pengajuan</div>
    <div class="card-body py-4">
        <div class="stepper">
            @foreach($allStatuses as $status)
                @php
                    $isDone    = $status->order() < $application->status->order();
                    $isCurrent = $status->value === $application->status->value;
                @endphp
                <div class="step-item">
                    <div class="step-circle {{ $isDone ? 'done' : ($isCurrent ? 'current' : 'pending') }}">
                        @if($isDone)
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $status->order() }}
                        @endif
                    </div>
                    <div class="step-label {{ $isDone ? 'done' : ($isCurrent ? 'current' : '') }}">
                        {{ $status->label() }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── SECTION 3: BERKAS PERSYARATAN ──────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-paperclip me-2"></i>Berkas Persyaratan</span>
        @php
            $uploadedCount = $application->documents->count();
            $totalRequired = $application->pensionType->documentTemplates->count();
        @endphp
        <span class="badge {{ $uploadedCount >= $totalRequired ? 'bg-success' : 'bg-warning text-dark' }}">
            {{ $uploadedCount }} / {{ $totalRequired }} diunggah
        </span>
    </div>
    <div class="card-body">
        @forelse($application->pensionType->documentTemplates as $template)
            @php
                $uploaded = $application->documents->firstWhere('document_name', $template->document_name);
            @endphp

            <div class="doc-item {{ $uploaded ? ($uploaded->is_verified === false && $uploaded->rejection_note ? 'doc-rejected' : 'doc-uploaded') : 'doc-missing' }}">
                <div class="d-flex align-items-start gap-3 flex-wrap">

                    {{-- Icon & Nama --}}
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                            <span class="fw-semibold" style="font-size:.92rem">
                                {{ $template->document_name }}
                            </span>
                            @if($uploaded)
                                @if($uploaded->is_verified === true)
    <span class="badge bg-success">
        <i class="bi bi-check-circle me-1"></i>Terverifikasi
    </span>
@elseif($uploaded->is_verified === false && $uploaded->rejection_note)
    <span class="badge bg-danger">
        <i class="bi bi-x-circle me-1"></i>Ditolak
    </span>
@else
    <span class="badge bg-warning text-dark">
        <i class="bi bi-hourglass-split me-1"></i>Menunggu Verifikasi
    </span>
@endif
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-dash-circle me-1"></i>Belum Upload
                                </span>
                            @endif
                        </div>

                        @if($template->description)
                            <div class="text-muted" style="font-size:.8rem">
                                {{ $template->description }}
                            </div>
                        @endif

                        @if($uploaded)
                            <div class="mt-2 d-flex align-items-center gap-3 flex-wrap">
                                <span class="text-muted" style="font-size:.8rem">
                                    <i class="bi bi-hdd me-1"></i>{{ $uploaded->file_size_human ?? '—' }}
                                </span>
                                @if($uploaded->rejection_note)
                                    <span class="text-danger" style="font-size:.8rem">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $uploaded->rejection_note }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Aksi --}}
                    <div class="d-flex flex-column gap-2 align-items-end">
                        {{-- Download contoh format --}}
                        @if($template->file_path)
                            <a href="{{ route('document-templates.download', $template) }}"
                               class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="bi bi-download me-1"></i>Contoh Format
                            </a>
                        @endif

                        {{-- Download file yang sudah diupload --}}
                        @if($uploaded)
                           <a href="{{ route('documents.preview', $uploaded) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-eye me-1"></i>Lihat File
                            </a>
                        @endif

                        {{-- Form upload (jika belum upload atau ditolak) --}}
                        @if((!$uploaded || ($uploaded->is_verified === false && $uploaded->rejection_note)) &&
                             (auth()->user()->isPensiunan() || auth()->user()->isSdmKantor() ||
                              $application->user_id === auth()->id()))
                            <button type="button"
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#uploadModal"
                                    data-doc-name="{{ $template->document_name }}">
                                <i class="bi bi-upload me-1"></i>
                                {{ $uploaded ? 'Upload Ulang' : 'Upload' }}
                            </button>
                        @endif

                        {{-- Verifikasi per dokumen (SDM Kanwil) --}}
                        @if(auth()->user()->isSdmKanwil() && $uploaded && is_null($uploaded->is_verified))
                            <div class="d-flex gap-1">
                                <form action="{{ route('documents.verify', $uploaded) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-lg me-1"></i>Verifikasi
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectDocModal"
                                        data-doc-id="{{ $uploaded->id }}">
                                    <i class="bi bi-x-lg me-1"></i>Tolak
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>
                Tidak ada template berkas untuk jenis pensiun ini
            </div>
        @endforelse
    </div>
</div>

{{-- ── SECTION 4: RIWAYAT STATUS ───────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-clock-history me-2"></i>Riwayat Status</div>
    <div class="card-body">
        @if($application->statusHistories->count() > 0)
            <div class="timeline">
                @foreach($application->statusHistories->sortByDesc('created_at') as $history)
                    @php
                        $isRejected = str_starts_with($history->note ?? '', 'DITOLAK:');
                    @endphp
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $isRejected ? 'rejected' : '' }}"></div>
                        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                            <div>
                                <div class="fw-semibold" style="font-size:.88rem">
                                   @if($history->from_status !== $history->to_status)
    <span class="text-muted">{{ $history->from_status->label() }}</span>
    <i class="bi bi-arrow-right mx-1 text-muted"></i>
@endif
<span class="{{ $isRejected ? 'text-danger' : 'text-primary' }}">
    {{ $history->to_status->label() }}
</span>
                                </div>
                                @if($history->actor)
                                    <div class="text-muted" style="font-size:.8rem">
                                        <i class="bi bi-person me-1"></i>{{ $history->actor->name }}
                                    </div>
                                @endif
                                @if($history->note)
                                    <div class="mt-1 p-2 rounded" style="background:#f8f9fa;font-size:.82rem;color:#555">
                                        <i class="bi bi-chat-square-text me-1"></i>{{ $history->note }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-muted text-end" style="font-size:.78rem;white-space:nowrap">
                                {{ $history->created_at->format('d M Y') }}<br>
                                {{ $history->created_at->format('H:i') }} WIB
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-3 text-muted">
                <i class="bi bi-clock opacity-25 fs-3 d-block mb-1"></i>
                Belum ada riwayat perubahan status
            </div>
        @endif
    </div>
</div>

{{-- ── SECTION 5: AKSI VERIFIKASI (SDM KANWIL) ────────────── --}}
@if(auth()->user()->isSdmKanwil())
    <div class="card mb-4" id="verifikasi">
        <div class="card-header" style="background:#fff9e6;color:#856404">
            <i class="bi bi-clipboard2-check-fill me-2"></i>Aksi Verifikasi
        </div>
        <div class="card-body">
            <div class="row g-4">

                {{-- Advance --}}
                @if($application->canAdvance())
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="border:1px solid #d1ecf1;background:#e8f4f8">
                            <h6 class="mb-1 text-primary">
                                <i class="bi bi-arrow-right-circle-fill me-2"></i>
                                Majukan ke Tahap Berikutnya
                            </h6>
                            <p class="text-muted mb-3" style="font-size:.83rem">
                                Majukan pengajuan ini ke:
                                <strong>{{ $application->status->next()->label() }}</strong>
                            </p>
                            <form action="{{ route('applications.advance', $application) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="note" rows="2" class="form-control form-control-sm"
                                              placeholder="Catatan (opsional)..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm px-4"
                                        onclick="return confirm('Majukan pengajuan ke tahap {{ $application->status->next()->label() }}?')">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    Majukan ke {{ $application->status->next()->label() }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Tolak / Kembalikan --}}
                @if(!$application->isCompleted())
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="border:1px solid #f5c6cb;background:#fdf3f3">
                            <h6 class="mb-1 text-danger">
                                <i class="bi bi-arrow-return-left me-2"></i>
                                Kembalikan untuk Perbaikan
                            </h6>
                            <p class="text-muted mb-3" style="font-size:.83rem">
                                Kembalikan pengajuan ke tahap Pengisian Form disertai alasan.
                            </p>
                            <form action="{{ route('applications.reject', $application) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="rejection_note" rows="2"
                                              class="form-control form-control-sm"
                                              placeholder="Alasan penolakan / perbaikan yang diperlukan..."
                                              required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm px-4"
                                        onclick="return confirm('Kembalikan pengajuan ini untuk perbaikan?')">
                                    <i class="bi bi-x-circle-fill me-1"></i>
                                    Kembalikan untuk Perbaikan
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                @if($application->isCompleted())
                    <div class="col-12">
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-trophy-fill me-2"></i>
                            <strong>Pengajuan telah selesai.</strong> SK Pensiun sudah diterbitkan.
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endif

{{-- ── MODAL: UPLOAD BERKAS ─────────────────────────────────── --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>Upload Berkas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('documents.store-single', $application) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="document_name" id="uploadDocName">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Dokumen</label>
                        <div id="uploadDocNameDisplay"
                             class="p-2 rounded fw-semibold"
                             style="background:#eef4fb;color:var(--primary);font-size:.9rem"></div>
                    </div>

                    <div class="mb-3">
                        <label for="uploadFile" class="form-label fw-semibold">
                            Pilih File <span class="text-danger">*</span>
                        </label>
                        <input type="file" id="uploadFile" name="file"
                               class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text">Format: PDF, JPG, PNG. Ukuran maksimal 5MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── MODAL: TOLAK DOKUMEN ─────────────────────────────────── --}}
@if(auth()->user()->isSdmKanwil())
    <div class="modal fade" id="rejectDocModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-x-circle-fill me-2"></i>Tolak Dokumen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectDocForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Alasan Penolakan <span class="text-danger">*</span>
                            </label>
                            <textarea name="rejection_note" rows="3" class="form-control"
                                      placeholder="Jelaskan alasan penolakan dokumen ini..."
                                      required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-danger btn-sm px-4">
                            <i class="bi bi-x-circle-fill me-1"></i>Tolak Dokumen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
<script>
    // Upload modal: isi nama dokumen
    const uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function (e) {
            const btn     = e.relatedTarget;
            const docName = btn.dataset.docName;
            document.getElementById('uploadDocName').value          = docName;
            document.getElementById('uploadDocNameDisplay').textContent = docName;
        });
    }

    // Reject doc modal: set form action
    const rejectDocModal = document.getElementById('rejectDocModal');
    if (rejectDocModal) {
        rejectDocModal.addEventListener('show.bs.modal', function (e) {
            const docId = e.relatedTarget.dataset.docId;
            document.getElementById('rejectDocForm').action = '/dokumen/' + docId + '/reject';
        });
    }
</script>
@endsection
