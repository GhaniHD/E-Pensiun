@extends('layouts.app')

@section('title', 'Detail Pengajuan #' . $application->id)
@section('page-title', 'Detail Pengajuan')

@section('styles')
<style>
    /* ── STEPPER ────────────────────────────────────────── */
    .stepper { display:flex;align-items:flex-start;position:relative;overflow-x:auto;padding-bottom:.5rem; }
    .stepper::before { content:'';position:absolute;top:20px;left:5%;right:5%;height:3px;background:#dee2e6;z-index:0; }
    .step-item { flex:1;display:flex;flex-direction:column;align-items:center;position:relative;z-index:1;min-width:80px; }
    .step-circle { width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;border:3px solid #dee2e6;background:#fff;transition:all .3s; }
    .step-circle.done    { background:var(--bs-success,#1E8449);border-color:var(--bs-success,#1E8449);color:#fff; }
    .step-circle.current { background:#2E86C1;border-color:#2E86C1;color:#fff;box-shadow:0 0 0 4px rgba(46,134,193,.25);animation:pulse 1.8s infinite; }
    .step-circle.pending { background:#f8f9fa;border-color:#dee2e6;color:#adb5bd; }
    @keyframes pulse { 0%,100%{box-shadow:0 0 0 4px rgba(46,134,193,.25);}50%{box-shadow:0 0 0 8px rgba(46,134,193,.12);} }
    .step-label { font-size:.68rem;text-align:center;margin-top:.5rem;color:#6c757d;max-width:80px;line-height:1.3; }
    .step-label.current { color:#2E86C1;font-weight:600; }
    .step-label.done { color:#1E8449; }

    /* ── TIMELINE ───────────────────────────────────────── */
    .timeline { position:relative;padding-left:2rem; }
    .timeline::before { content:'';position:absolute;left:.75rem;top:0;bottom:0;width:2px;background:#dee2e6; }
    .timeline-item { position:relative;padding-bottom:1.5rem; }
    .timeline-item:last-child { padding-bottom:0; }
    .timeline-dot { position:absolute;left:-1.45rem;top:.25rem;width:14px;height:14px;border-radius:50%;background:var(--accent,#2E86C1);border:2px solid #fff;box-shadow:0 0 0 2px var(--accent,#2E86C1); }
    .timeline-dot.rejected { background:#C0392B;box-shadow:0 0 0 2px #C0392B; }

    /* ── DOCUMENT ITEM ──────────────────────────────────── */
    .doc-item { border:1px solid #e9ecef;border-radius:8px;padding:1rem 1.25rem;margin-bottom:.75rem;background:#fff;transition:border-color .2s; }
    .doc-item:hover { border-color:#adb5bd; }
    .doc-item.doc-uploaded  { border-left:4px solid #1E8449; }
    .doc-item.doc-missing   { border-left:4px solid #dee2e6; }
    .doc-item.doc-rejected  { border-left:4px solid #C0392B; }
    .doc-item.doc-bermasalah{ border-left:4px solid #D4AC0D; }

    /* ── CHECK PANEL ────────────────────────────────────── */
    .check-panel { background:#f8f9fa;border-radius:8px;padding:.75rem 1rem;margin-top:.75rem; }
    .check-panel select, .check-panel textarea { font-size:.85rem; }
</style>
@endsection

@section('content')

{{-- ── BREADCRUMB & HEADER ──────────────────────────────── --}}
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
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <span class="badge fs-6 bg-{{ $application->status->badgeColor() }} px-3 py-2">
            {{ $application->status->label() }}
        </span>

        {{-- Fitur 4: Tombol SI ASN BKN — hanya untuk sdm_kanwil --}}
        @if(auth()->user()->isSdmKanwil())
            <a href="{{ $siAsnBknUrl }}" target="_blank" rel="noopener"
               class="btn btn-warning btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>Ajukan ke SI ASN BKN</span>
            </a>
        @endif

        {{-- Fitur 5: Tombol Folder Teams — untuk sdm_kantor & sdm_kanwil --}}
        @if((auth()->user()->isSdmKantor() || auth()->user()->isSdmKanwil()) && $teamsFolderUrl)
            <a href="{{ $teamsFolderUrl }}" target="_blank" rel="noopener"
               class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-folder2"></i>
                <span>Buka Folder Teams</span>
            </a>
        @endif

        @if($application->status->value === 'pengisian_form' &&
            (auth()->user()->isPensiunan() || auth()->user()->isSdmKantor()))
            <a href="{{ route('applications.edit', $application) }}"
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil-fill me-1"></i>Edit
            </a>
        @endif
    </div>
</div>

{{-- Notifikasi jika pengajuan dibatalkan --}}
@if($application->isCancelled())
<div class="alert alert-danger py-2 mb-4 d-flex align-items-center gap-2" style="font-size:.87rem">
    <i class="bi bi-slash-circle-fill fs-5"></i>
    <div>
        <strong>Pengajuan ini telah dibatalkan.</strong>
        @if($application->rejection_note)
            Alasan: {{ $application->rejection_note }}
        @endif
    </div>
</div>
@endif

{{-- ── SECTION 1: INFO PENGAJUAN ────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person-lines-fill me-2"></i>Informasi Pengajuan</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Nama Pegawai</div>
                        <div class="fw-semibold mt-1">{{ $application->user->name }}</div>
                    </div>
                    @if($application->user->nip)
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">NIP</div>
                        <div class="fw-semibold mt-1 font-monospace">{{ $application->user->nip }}</div>
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Kantor</div>
                        <div class="fw-semibold mt-1">{{ $application->user->office ?? '—' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Jenis Pensiun</div>
                        <div class="fw-semibold mt-1">{{ $application->pensionType->name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Tanggal Pengajuan</div>
                        <div class="fw-semibold mt-1">{{ $application->created_at->format('d M Y') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Rencana Pensiun</div>
                        <div class="fw-semibold mt-1">{{ $application->pension_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    @if($application->verifier)
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Diverifikasi Oleh</div>
                        <div class="fw-semibold mt-1">{{ $application->verifier->name }}</div>
                    </div>
                    @endif
                    @if($application->sk_number)
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Nomor SK</div>
                        <div class="fw-semibold mt-1 font-monospace">{{ $application->sk_number }}</div>
                    </div>
                    @endif
                    @if($application->sk_issued_at)
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Tanggal Terbit SK</div>
                        <div class="fw-semibold mt-1">{{ $application->sk_issued_at->format('d M Y') }}</div>
                    </div>
                    @endif
                    @if($application->notes)
                    <div class="col-12">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Catatan</div>
                        <div class="mt-1 p-2 rounded" style="background:#f8f9fa;font-size:.88rem">{{ $application->notes }}</div>
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
                    <div class="fw-semibold" style="color:var(--primary)">{{ $application->status->label() }}</div>
                    <div class="text-muted" style="font-size:.8rem">
                        Tahap {{ $application->status->order() }} dari 7
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── SECTION 2: STEPPER STATUS ────────────────────────── --}}
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
                        @if($isDone) <i class="bi bi-check-lg"></i>
                        @else {{ $status->order() }}
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

{{-- ── BULK FORMS (hidden — untuk form-associated inputs) ───── --}}
@if(auth()->user()->isSdmKantor() && $application->status->value === \App\Enums\ApplicationStatus::VERIFIKASI_KPKNL->value)
<form id="bulk-kantor-form" action="{{ route('documents.bulk-kantor-check', $application) }}" method="POST" style="display:none">@csrf</form>
@endif
@if(auth()->user()->isSdmKanwil() && $application->status->value === \App\Enums\ApplicationStatus::VERIFIKASI_KANWIL->value)
<form id="bulk-kanwil-form" action="{{ route('documents.bulk-kanwil-check', $application) }}" method="POST" style="display:none">@csrf</form>
@endif

{{-- ── SECTION 3: BERKAS PERSYARATAN ───────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-paperclip me-2"></i>Berkas Persyaratan</span>
        @php
            $uploadedCount  = $application->documents->count();
            $totalRequired  = $application->pensionType->documentTemplates->count();
            $isKpknlStage   = $application->status->value === \App\Enums\ApplicationStatus::VERIFIKASI_KPKNL->value;
            $isKanwilStage  = $application->status->value === \App\Enums\ApplicationStatus::VERIFIKASI_KANWIL->value;

            // Badge ringkasan KPKNL
            $problematic = $application->documents->filter(fn($d)=>in_array($d->kantor_check_status,['lengkap_tidak_sesuai','tidak_lengkap']))->count();
            $checkedKantor = $application->documents->filter(fn($d)=>!is_null($d->kantor_check_status))->count();
        @endphp
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span class="badge {{ $uploadedCount >= $totalRequired ? 'bg-success' : 'bg-warning text-dark' }}">
                {{ $uploadedCount }} / {{ $totalRequired }} diunggah
            </span>
            @if($isKpknlStage && $checkedKantor > 0)
                @if($problematic > 0)
                    <span class="badge bg-danger">{{ $problematic }} berkas bermasalah</span>
                @else
                    <span class="badge bg-success">Semua berkas OK</span>
                @endif
            @endif
        </div>
    </div>

    <div class="card-body">
        @forelse($application->pensionType->documentTemplates as $template)
            @php
                $uploaded = $application->documents->firstWhere('document_name', $template->document_name);
                $hasKantorIssue = $uploaded && in_array($uploaded->kantor_check_status, ['lengkap_tidak_sesuai','tidak_lengkap']);
                $docClass = !$uploaded ? 'doc-missing'
                    : ($hasKantorIssue ? 'doc-bermasalah'
                    : ($uploaded->kanwil_status === 'tidak_sesuai' ? 'doc-rejected'
                    : 'doc-uploaded'));
            @endphp

            <div class="doc-item {{ $docClass }}">
                <div class="d-flex align-items-start gap-3 flex-wrap">

                    {{-- Icon & Info --}}
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                            <span class="fw-semibold" style="font-size:.92rem">{{ $template->document_name }}</span>
                            @if($template->is_required)
                                <span class="badge bg-light text-danger border border-danger" style="font-size:.68rem">Wajib</span>
                            @endif

                            {{-- Status badge berkas --}}
                            @if($uploaded)
                                @if($uploaded->kanwil_status === 'sesuai')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sesuai (Kanwil)</span>
                                @elseif($uploaded->kanwil_status === 'tidak_sesuai')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Tidak Sesuai (Kanwil)</span>
                                @elseif($uploaded->kantor_check_status)
                                    <span class="badge bg-{{ $uploaded->kantorCheckBadge() }}">
                                        {{ $uploaded->kantorCheckLabel() }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Menunggu Pengecekan</span>
                                @endif
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i>Belum Upload</span>
                            @endif
                        </div>

                        @if($template->description)
                            <div class="text-muted" style="font-size:.8rem">{{ $template->description }}</div>
                        @endif

                        @if($uploaded)
                            <div class="mt-1 d-flex align-items-center gap-3 flex-wrap">
                                <span class="text-muted" style="font-size:.8rem">
                                    <i class="bi bi-hdd me-1"></i>{{ $uploaded->file_size_human ?? '—' }}
                                </span>
                                @if($uploaded->kantor_check_note)
                                    <span class="text-warning" style="font-size:.8rem">
                                        <i class="bi bi-chat-square-text me-1"></i>
                                        KPKNL: {{ $uploaded->kantor_check_note }}
                                    </span>
                                @endif
                                @if($uploaded->rejection_note && $uploaded->kanwil_status === 'tidak_sesuai')
                                    <span class="text-danger" style="font-size:.8rem">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        Kanwil: {{ $uploaded->rejection_note }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- ═══ PANEL CHECKLIST KPKNL (Fitur 2) — Mode Bulk ═══ --}}
                        @if(auth()->user()->isSdmKantor() && $uploaded && $isKpknlStage)
                            <div class="check-panel mt-2">
                                <div class="fw-semibold mb-2" style="font-size:.82rem;color:#1B4F72">
                                    <i class="bi bi-clipboard2-check me-1"></i>Pengecekan KPKNL Pelayanan
                                </div>
                                {{-- Inputs terasosiasi dengan #bulk-kantor-form (tanpa nested form) --}}
                                <input type="hidden"
                                       name="checks[{{ $uploaded->id }}][document_id]"
                                       value="{{ $uploaded->id }}"
                                       form="bulk-kantor-form">
                                <div class="d-flex flex-column gap-2">
                                    <select name="checks[{{ $uploaded->id }}][kantor_check_status]"
                                            class="form-select form-select-sm"
                                            form="bulk-kantor-form"
                                            onchange="toggleKantorNote(this, {{ $uploaded->id }})" required>
                                        <option value="" disabled {{ !$uploaded->kantor_check_status ? 'selected' : '' }}>— Pilih Status —</option>
                                        <option value="lengkap_sesuai"
                                            {{ $uploaded->kantor_check_status === 'lengkap_sesuai' ? 'selected' : '' }}>
                                            ✅ Lengkap &amp; Sesuai
                                        </option>
                                        <option value="lengkap_tidak_sesuai"
                                            {{ $uploaded->kantor_check_status === 'lengkap_tidak_sesuai' ? 'selected' : '' }}>
                                            ⚠️ Lengkap tapi Tidak Sesuai
                                        </option>
                                        <option value="tidak_lengkap"
                                            {{ $uploaded->kantor_check_status === 'tidak_lengkap' ? 'selected' : '' }}>
                                            ❌ Tidak Lengkap
                                        </option>
                                    </select>
                                    <textarea name="checks[{{ $uploaded->id }}][kantor_check_note]" rows="2"
                                              id="kantor-note-{{ $uploaded->id }}"
                                              form="bulk-kantor-form"
                                              class="form-control form-control-sm"
                                              placeholder="Catatan (wajib jika bukan Lengkap &amp; Sesuai)..."
                                              style="{{ (!$uploaded->kantor_check_status || $uploaded->kantor_check_status === 'lengkap_sesuai') ? 'display:none' : '' }}"
                                    >{{ $uploaded->kantor_check_note }}</textarea>
                                </div>
                            </div>
                        @endif

                        {{-- ═══ PANEL DOUBLE-CHECK KANWIL (Fitur 3) — Mode Bulk ═══ --}}
                        @if(auth()->user()->isSdmKanwil() && $uploaded && $isKanwilStage)
                            <div class="check-panel mt-2" style="background:#fff9e6">
                                <div class="fw-semibold mb-2" style="font-size:.82rem;color:#856404">
                                    <i class="bi bi-clipboard2-check-fill me-1"></i>Verifikasi DJKN Kanwil
                                </div>
                                {{-- Inputs terasosiasi dengan #bulk-kanwil-form --}}
                                <input type="hidden"
                                       name="checks[{{ $uploaded->id }}][document_id]"
                                       value="{{ $uploaded->id }}"
                                       form="bulk-kanwil-form">
                                <input type="hidden"
                                       name="checks[{{ $uploaded->id }}][kanwil_status]"
                                       id="kanwil-status-{{ $uploaded->id }}"
                                       value="{{ ($uploaded->kanwil_status && $uploaded->kanwil_status !== 'pending') ? $uploaded->kanwil_status : '' }}"
                                       form="bulk-kanwil-form">
                                <div class="d-flex gap-2 mb-2">
                                    <button type="button"
                                            class="btn btn-sm {{ $uploaded->kanwil_status === 'sesuai' ? 'btn-success' : 'btn-outline-success' }} flex-fill kanwil-btn"
                                            data-status="sesuai"
                                            data-doc-id="{{ $uploaded->id }}"
                                            onclick="setKanwilStatus(this, {{ $uploaded->id }}, 'sesuai')">
                                        <i class="bi bi-check-circle me-1"></i>Sesuai
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm {{ $uploaded->kanwil_status === 'tidak_sesuai' ? 'btn-danger' : 'btn-outline-danger' }} flex-fill kanwil-btn"
                                            data-status="tidak_sesuai"
                                            data-doc-id="{{ $uploaded->id }}"
                                            onclick="setKanwilStatus(this, {{ $uploaded->id }}, 'tidak_sesuai')">
                                        <i class="bi bi-x-circle me-1"></i>Tidak Sesuai
                                    </button>
                                </div>
                                {{-- Area catatan Tidak Sesuai --}}
                                <div id="kanwil-note-wrapper-{{ $uploaded->id }}"
                                     style="{{ $uploaded->kanwil_status === 'tidak_sesuai' ? '' : 'display:none' }}">
                                    <textarea name="checks[{{ $uploaded->id }}][rejection_note]" rows="2"
                                              form="bulk-kanwil-form"
                                              class="form-control form-control-sm"
                                              placeholder="Catatan ketidaksesuaian (wajib)..."
                                    >{{ $uploaded->rejection_note }}</textarea>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Aksi --}}
                    <div class="d-flex flex-column gap-2 align-items-end flex-shrink-0">

                        {{-- Fitur 1: Tombol Lihat / Unduh Contoh (semua role) --}}
                        @if($template->file_path)
                            <a href="{{ route('document-templates.preview', $template) }}" target="_blank"
                               class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" title="Lihat contoh format">
                                <i class="bi bi-eye"></i>
                                <span class="d-none d-md-inline">Lihat Contoh</span>
                            </a>
                            <a href="{{ route('document-templates.download', $template) }}"
                               class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" title="Unduh contoh format">
                                <i class="bi bi-download"></i>
                                <span class="d-none d-md-inline">Unduh Contoh</span>
                            </a>
                            @if(auth()->user()->isTik())
                                <form action="{{ route('document-templates.upload', $template) }}" method="POST"
                                      enctype="multipart/form-data" class="d-flex align-items-center gap-1">
                                    @csrf
                                    <input type="file" name="file" accept="application/pdf"
                                           class="form-control form-control-sm" style="max-width:130px;font-size:.75rem">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Ganti contoh">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                            @endif
                        @else
                           @if(auth()->user()->isTik())
                                <form action="{{ route('document-templates.upload', $template) }}" method="POST"
                                      enctype="multipart/form-data" class="d-flex align-items-center gap-1">
                                    @csrf
                                    <input type="file" name="file" accept="application/pdf"
                                           class="form-control form-control-sm" style="max-width:130px;font-size:.75rem" required>
                                    <button type="submit" class="btn btn-outline-primary btn-sm" title="Unggah contoh format">
                                        <i class="bi bi-upload"></i>
                                    </button>
                                </form>
                            @elseif(auth()->user()->isPensiunan())
                                <span class="text-muted" style="font-size:.75rem">
                                    <i class="bi bi-dash"></i> Tidak ada contoh
                                </span>
                            @endif
                        @endif

                        {{-- Lihat file yang terupload --}}
                        @if($uploaded)
                            <a href="{{ route('documents.preview', $uploaded) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-eye me-1"></i>Lihat File
                            </a>
                        @endif

                        {{-- Hapus (sdm_kantor, saat belum verifikasi) --}}
                        @if(auth()->user()->isSdmKantor() && $uploaded && !$uploaded->is_verified)
                        @php
                            $bisaEdit = in_array($application->status->value, [
                                \App\Enums\ApplicationStatus::PENGISIAN_FORM->value,
                                \App\Enums\ApplicationStatus::PEMBERKASAN->value,
                                \App\Enums\ApplicationStatus::UPLOAD->value,
                            ]);
                        @endphp
                        @if($bisaEdit)
                            <form action="{{ route('documents.destroy', $uploaded) }}" method="POST"
                                  onsubmit="return confirm('Hapus berkas ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i>Hapus
                                </button>
                            </form>
                        @endif
                        @endif

                        {{-- Upload (sdm_kantor, jika belum ada atau bermasalah dan masih di tahap UPLOAD) --}}
                        @if(!$uploaded && auth()->user()->isSdmKantor() &&
                            in_array($application->status->value, [
                                \App\Enums\ApplicationStatus::PEMBERKASAN->value,
                                \App\Enums\ApplicationStatus::UPLOAD->value,
                            ]))
                            <button type="button" class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#uploadModal"
                                    data-doc-name="{{ $template->document_name }}">
                                <i class="bi bi-upload me-1"></i>Upload
                            </button>
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

{{-- ── SECTION: AKSI SDMKANTOR (Upload → Verifikasi KPKNL, dsb) ── --}}
@if(auth()->user()->isSdmKantor() && !$application->isCompleted() && !$application->isCancelled())
@php
    $totalTemplate = $application->pensionType->documentTemplates->count();
    $totalUploaded = $application->documents->whereNotNull('file_path')->count();
    $allUploaded   = $totalTemplate > 0 && $totalUploaded >= $totalTemplate;
    $allChecked    = $application->allDocumentsCheckedByKantor();
    $masalah       = $application->problematicDocumentsCount();
@endphp

    {{-- Tahap PEMBERKASAN → UPLOAD --}}
    @if($application->status->value === \App\Enums\ApplicationStatus::PEMBERKASAN->value)
    <div class="card mb-4" style="border-left:4px solid #2E86C1">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="fw-semibold" style="color:#1B4F72">
                    <i class="bi bi-file-earmark-check-fill me-1"></i>Konfirmasi Berkas Fisik
                </div>
                <div class="text-muted" style="font-size:.83rem">
                    Klik setelah berkas fisik dari calon pensiunan sudah diterima.
                </div>
            </div>
            <form action="{{ route('applications.advance', $application) }}" method="POST">
                @csrf
                <input type="hidden" name="note" value="Berkas fisik telah diterima oleh Staff KPKNL Pelayanan.">
                <button type="submit" class="btn btn-primary px-4"
                        onclick="return confirm('Konfirmasi berkas fisik sudah diterima?')">
                    <i class="bi bi-check-circle-fill me-2"></i>Konfirmasi Berkas Fisik Diterima
                </button>
            </form>
        </div>
    </div>

    {{-- Tahap UPLOAD → VERIFIKASI_KPKNL --}}
    @elseif($application->status->value === \App\Enums\ApplicationStatus::UPLOAD->value)
    <div class="card mb-4" style="border-left:4px solid {{ $allUploaded ? '#1E8449' : '#D4AC0D' }}">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                @if($allUploaded)
                    <div class="fw-semibold text-success">
                        <i class="bi bi-check-circle-fill me-1"></i>Semua berkas telah diunggah ({{ $totalUploaded }}/{{ $totalTemplate }})
                    </div>
                    <div class="text-muted" style="font-size:.83rem">Siap masuk ke tahap Verifikasi KPKNL Pelayanan.</div>
                @else
                    <div class="fw-semibold text-warning">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Berkas belum lengkap ({{ $totalUploaded }}/{{ $totalTemplate }})
                    </div>
                    <div class="text-muted" style="font-size:.83rem">Lengkapi semua berkas terlebih dahulu.</div>
                @endif
            </div>
            @if($allUploaded)
                <form action="{{ route('applications.advance', $application) }}" method="POST">
                    @csrf
                    <input type="hidden" name="note" value="Semua berkas terupload, masuk tahap Verifikasi KPKNL Pelayanan.">
                    <button type="submit" class="btn btn-success px-4"
                            onclick="return confirm('Lanjutkan ke tahap Verifikasi KPKNL Pelayanan?')">
                        <i class="bi bi-send-fill me-2"></i>Mulai Verifikasi KPKNL
                    </button>
                </form>
            @else
                <button class="btn btn-secondary px-4" disabled>
                    <i class="bi bi-send me-2"></i>Mulai Verifikasi KPKNL
                </button>
            @endif
        </div>
    </div>

    {{-- Tahap VERIFIKASI_KPKNL → VERIFIKASI_KANWIL atau Kembalikan ke Upload --}}
    @elseif($application->status->value === \App\Enums\ApplicationStatus::VERIFIKASI_KPKNL->value)
    @php
        $totalDocs   = $application->documents->count();
        $checkedKantor = $application->documents->filter(fn($d) => !is_null($d->kantor_check_status))->count();
        $pctChecked  = $totalDocs > 0 ? round($checkedKantor / $totalDocs * 100) : 0;
    @endphp
    <div class="card mb-4" style="border-left:4px solid #D4AC0D">
        <div class="card-body">
            <div class="fw-semibold mb-1" style="color:#856404">
                <i class="bi bi-clipboard2-check-fill me-2"></i>Pengecekan KPKNL Pelayanan
            </div>
            <p class="text-muted mb-3" style="font-size:.83rem">
                Tentukan status setiap berkas dengan dropdown di atas, lalu klik
                <strong>Simpan Semua Status</strong> untuk menyimpan seluruhnya sekaligus — tanpa perlu simpan satu per satu.
            </p>

            {{-- Progress bar dan tombol Simpan Semua --}}
            <div class="mb-3 p-3 rounded" style="background:#fffbe6;border:1px solid #ffc107">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div>
                        <span class="fw-semibold" style="font-size:.85rem;color:#856404">
                            <i class="bi bi-check2-square me-1"></i>
                            Progress Pengecekan:
                            <span id="kantor-check-counter">{{ $checkedKantor }}</span>
                            / {{ $totalDocs }} berkas
                        </span>
                        @if($totalDocs > 0 && ($application->pensionType->documentTemplates->count() > $totalDocs))
                            <span class="ms-2 text-danger" style="font-size:.78rem">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                {{ $application->pensionType->documentTemplates->count() - $totalDocs }} berkas belum diupload
                            </span>
                        @endif
                    </div>
                    <button type="submit" form="bulk-kantor-form"
                            class="btn btn-warning px-4 fw-semibold">
                        <i class="bi bi-save-fill me-2"></i>Simpan Semua Status Berkas
                    </button>
                </div>
                {{-- Progress bar --}}
                <div class="progress" style="height:8px">
                    <div class="progress-bar bg-warning" id="kantor-progress-bar"
                         role="progressbar" style="width:{{ $pctChecked }}%"
                         aria-valuenow="{{ $pctChecked }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            @if($allChecked)
                @if($masalah > 0)
                    <div class="alert alert-warning py-2 mb-3" style="font-size:.85rem">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Terdapat <strong>{{ $masalah }} berkas bermasalah</strong>. Pilih: kembalikan untuk perbaikan, atau tetap teruskan ke Kanwil dengan catatan.
                    </div>
                @else
                    <div class="alert alert-success py-2 mb-3" style="font-size:.85rem">
                        <i class="bi bi-check-circle-fill me-1"></i>Semua berkas dinyatakan Lengkap &amp; Sesuai. Siap diajukan ke DJKN Kanwil.
                    </div>
                @endif
                <div class="d-flex gap-2 flex-wrap">
                    <form action="{{ route('applications.advance', $application) }}" method="POST">
                        @csrf
                        <input type="hidden" name="note" value="Pengecekan KPKNL selesai, diajukan ke Verifikasi DJKN Kanwil.">
                        <button type="submit" class="btn btn-primary px-4"
                                onclick="return confirm('Ajukan ke DJKN Kanwil?')">
                            <i class="bi bi-send-fill me-2"></i>Ajukan ke DJKN Kanwil
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-danger px-4"
                            data-bs-toggle="modal" data-bs-target="#returnUploadModal">
                        <i class="bi bi-arrow-return-left me-2"></i>Kembalikan ke Upload
                    </button>
                </div>
            @else
                <div class="alert alert-info py-2 mb-3" style="font-size:.85rem">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    @if($totalDocs === 0)
                        Belum ada berkas yang diupload. Upload dulu sebelum melakukan pengecekan.
                    @else
                        Masih ada <strong>{{ $totalDocs - $checkedKantor }} berkas</strong> yang belum dicek.
                        Simpan status semua berkas terlebih dahulu.
                    @endif
                </div>
                <button class="btn btn-secondary px-4" disabled>
                    <i class="bi bi-send me-2"></i>Ajukan ke DJKN Kanwil
                </button>
            @endif
        </div>
    </div>
    @endif
@endif


{{-- ── SECTION 4: RIWAYAT STATUS ────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-clock-history me-2"></i>Riwayat Status</div>
    <div class="card-body">
        @if($application->statusHistories->count() > 0)
            <div class="timeline">
                @foreach($application->statusHistories->sortByDesc('created_at') as $history)
                    @php $isRejected = str_starts_with($history->note ?? '', 'DITOLAK:'); @endphp
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

{{-- ── BATALKAN PENGAJUAN (sdm_kantor) ───────────────────── --}}
@if(auth()->user()->isSdmKantor() && $application->canBeCancelled())
<div class="card mb-4" style="border-left:4px solid #C0392B">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="fw-semibold text-danger">
                <i class="bi bi-slash-circle me-1"></i>Batalkan Pengajuan
            </div>
            <div class="text-muted" style="font-size:.83rem">
                Pengajuan hanya bisa dibatalkan sebelum masuk tahap verifikasi.
            </div>
        </div>
        <button type="button" class="btn btn-outline-danger px-4"
                data-bs-toggle="modal" data-bs-target="#cancelModal">
            <i class="bi bi-slash-circle me-1"></i>Batalkan
        </button>
    </div>
</div>
@endif

{{-- ── SECTION 5: AKSI VERIFIKASI (SDM KANWIL) ─────────── --}}
@if(auth()->user()->isSdmKanwil())
    <div class="card mb-4" id="verifikasi">
        <div class="card-header" style="background:#fff9e6;color:#856404">
            <i class="bi bi-clipboard2-check-fill me-2"></i>Aksi Verifikasi DJKN Kanwil
        </div>
        <div class="card-body">
            @php
                $allApproved    = $application->allDocumentsApprovedByKanwil();
                $totalKanwilDoc = $application->documents->count();
                $verifiedKanwil = $application->documents->whereIn('kanwil_status', ['sesuai','tidak_sesuai'])->count();
                $pctKanwil      = $totalKanwilDoc > 0 ? round($verifiedKanwil / $totalKanwilDoc * 100) : 0;
            @endphp

            {{-- Progress bar + Simpan Semua — hanya saat tahap VERIFIKASI_KANWIL --}}
            @if($application->status->value === \App\Enums\ApplicationStatus::VERIFIKASI_KANWIL->value)
            <div class="mb-4 p-3 rounded" style="background:#fffbe6;border:1px solid #ffc107">
                <div class="fw-semibold mb-1" style="font-size:.85rem;color:#856404">
                    <i class="bi bi-clipboard2-check-fill me-1"></i>Verifikasi Dokumen — Mode Bulk
                </div>
                <p class="text-muted mb-2" style="font-size:.82rem">
                    Klik <strong>Sesuai</strong> atau <strong>Tidak Sesuai</strong> pada setiap berkas di atas,
                    lalu klik <strong>Simpan Semua</strong> untuk menyimpan sekaligus tanpa reload berulang.
                </p>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <span class="fw-semibold" style="font-size:.85rem;color:#856404">
                        <i class="bi bi-check2-square me-1"></i>
                        Progress:
                        <span id="kanwil-check-counter">{{ $verifiedKanwil }}</span>
                        / {{ $totalKanwilDoc }} berkas sudah diverifikasi
                    </span>
                    <button type="submit" form="bulk-kanwil-form"
                            class="btn btn-warning px-4 fw-semibold">
                        <i class="bi bi-save-fill me-2"></i>Simpan Semua Verifikasi Kanwil
                    </button>
                </div>
                <div class="progress" style="height:8px">
                    <div class="progress-bar {{ $allApproved ? 'bg-success' : 'bg-warning' }}"
                         id="kanwil-progress-bar"
                         role="progressbar" style="width:{{ $pctKanwil }}%"></div>
                </div>
                @if($totalKanwilDoc > 0 && !$allApproved)
                    @php $tidakSesuaiCount = $application->documents->where('kanwil_status','tidak_sesuai')->count(); @endphp
                    @if($tidakSesuaiCount > 0)
                        <div class="mt-2 text-danger" style="font-size:.8rem">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            {{ $tidakSesuaiCount }} berkas berstatus Tidak Sesuai — pengajuan belum bisa di-ACC.
                        </div>
                    @endif
                @endif
            </div>
            @endif

            <div class="row g-4">
                {{-- Advance ke ACC --}}
                @if($application->status->value === \App\Enums\ApplicationStatus::VERIFIKASI_KANWIL->value && $application->canAdvance())
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="border:1px solid #d1ecf1;background:#e8f4f8">
                            <h6 class="mb-1 text-primary">
                                <i class="bi bi-arrow-right-circle-fill me-2"></i>Majukan ke ACC
                            </h6>
                            @if(!$allApproved)
                                <div class="alert alert-warning py-2 mb-2" style="font-size:.82rem">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    Masih ada berkas yang belum berstatus "Sesuai". Semua berkas harus sesuai sebelum di-ACC.
                                </div>
                            @else
                                <p class="text-muted mb-3" style="font-size:.83rem">
                                    Semua berkas telah diverifikasi Sesuai. Majukan ke: <strong>ACC / Persetujuan</strong>
                                </p>
                            @endif
                            <form action="{{ route('applications.advance', $application) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="note" rows="2" class="form-control form-control-sm"
                                              placeholder="Catatan (opsional)..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm px-4"
                                        {{ !$allApproved ? 'disabled' : '' }}
                                        onclick="return confirm('Majukan ke tahap ACC?')">
                                    <i class="bi bi-check-circle-fill me-1"></i>Majukan ke ACC
                                </button>
                            </form>
                        </div>
                    </div>
                @elseif($application->canAdvance() && !in_array($application->status->value, [
                    \App\Enums\ApplicationStatus::VERIFIKASI_KPKNL->value,
                    \App\Enums\ApplicationStatus::VERIFIKASI_KANWIL->value,
                ]))
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="border:1px solid #d1ecf1;background:#e8f4f8">
                            <h6 class="mb-1 text-primary">
                                <i class="bi bi-arrow-right-circle-fill me-2"></i>Majukan ke {{ $application->status->next()->label() }}
                            </h6>
                            <form action="{{ route('applications.advance', $application) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="note" rows="2" class="form-control form-control-sm"
                                              placeholder="Catatan (opsional)..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm px-4"
                                        onclick="return confirm('Majukan pengajuan?')">
                                    <i class="bi bi-check-circle-fill me-1"></i>Majukan
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
                                <i class="bi bi-arrow-return-left me-2"></i>Kembalikan untuk Perbaikan
                            </h6>
                            <p class="text-muted mb-3" style="font-size:.83rem">
                                Kembalikan pengajuan ke tahap Pengisian Form disertai alasan.
                            </p>
                            <form action="{{ route('applications.reject', $application) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="rejection_note" rows="2" class="form-control form-control-sm"
                                              placeholder="Alasan penolakan..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm px-4"
                                        onclick="return confirm('Kembalikan pengajuan ini?')">
                                    <i class="bi bi-x-circle-fill me-1"></i>Kembalikan untuk Perbaikan
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

{{-- ── MODAL: UPLOAD BERKAS ──────────────────────────────── --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Upload Berkas</h5>
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
                        <label for="uploadFile" class="form-label fw-semibold">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" id="uploadFile" name="file"
                               class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text">Format: PDF, JPG, PNG. Maks 5MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── MODAL: KEMBALIKAN KE UPLOAD (sdm_kantor) ────────────── --}}
@if(auth()->user()->isSdmKantor())
<div class="modal fade" id="returnUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-arrow-return-left me-2"></i>Kembalikan ke Tahap Upload
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('applications.return-upload', $application) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted" style="font-size:.88rem">
                        Pengajuan akan dikembalikan ke tahap Upload agar berkas yang bermasalah dapat diperbaiki.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan <span class="text-danger">*</span></label>
                        <textarea name="return_note" rows="3" class="form-control"
                                  placeholder="Jelaskan berkas mana yang perlu diperbaiki..."
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4"
                            onclick="return confirm('Kembalikan pengajuan ke tahap Upload?')">
                        <i class="bi bi-arrow-return-left me-1"></i>Kembalikan ke Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ── MODAL: BATALKAN PENGAJUAN ── --}}
@if(auth()->user()->isSdmKantor() || auth()->user()->isTik())
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-slash-circle-fill me-2"></i>Batalkan Pengajuan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('applications.cancel', $application) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-3" style="font-size:.85rem">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Pengajuan yang dibatalkan <strong>tidak dapat dilanjutkan kembali</strong>.
                        Jika ingin mengajukan ulang, harus membuat pengajuan baru.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Alasan Pembatalan <span class="text-danger">*</span>
                        </label>
                        <textarea name="cancel_note" rows="3" class="form-control"
                                  placeholder="Jelaskan alasan pembatalan pengajuan ini..."
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4"
                            onclick="return confirm('Yakin ingin membatalkan pengajuan ini? Tindakan ini tidak bisa diurungkan.')">
                        <i class="bi bi-slash-circle-fill me-1"></i>Ya, Batalkan Pengajuan
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
    // ── Upload modal ─────────────────────────────────────────
    const uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function (e) {
            const docName = e.relatedTarget.dataset.docName;
            document.getElementById('uploadDocName').value            = docName;
            document.getElementById('uploadDocNameDisplay').textContent = docName;
        });
    }

    // ── KPKNL: toggle catatan per berkas ─────────────────────
    function toggleKantorNote(select, docId) {
        const noteEl = document.getElementById('kantor-note-' + docId);
        if (!noteEl) return;
        const hide = !select.value || select.value === 'lengkap_sesuai';
        noteEl.style.display = hide ? 'none' : '';
        updateKantorProgress();
    }

    function updateKantorProgress() {
        const selects = document.querySelectorAll('[name*="kantor_check_status"]');
        let filled = 0;
        selects.forEach(s => { if (s.value) filled++; });

        const counter = document.getElementById('kantor-check-counter');
        if (counter) counter.textContent = filled;

        const bar = document.getElementById('kantor-progress-bar');
        if (bar && selects.length > 0) {
            const pct = Math.round(filled / selects.length * 100);
            bar.style.width = pct + '%';
            bar.setAttribute('aria-valuenow', pct);
        }
    }

    // Inisialisasi KPKNL saat halaman load
    document.querySelectorAll('[name*="kantor_check_status"]').forEach(sel => {
        const m = sel.name.match(/checks\[(\d+)\]\[kantor_check_status\]/);
        if (m) toggleKantorNote(sel, m[1]);
    });

    // ── Kanwil: set status via tombol, simpan ke hidden input ─
    function setKanwilStatus(btn, docId, status) {
        // Update hidden input value
        const hidden = document.getElementById('kanwil-status-' + docId);
        if (hidden) hidden.value = status;

        // Update style tombol dalam panel yang sama
        const panel = btn.closest('.check-panel');
        panel.querySelectorAll('.kanwil-btn').forEach(b => {
            const s = b.dataset.status;
            // Reset ke outline
            b.classList.remove('btn-success', 'btn-danger');
            b.classList.add(s === 'sesuai' ? 'btn-outline-success' : 'btn-outline-danger');
        });
        // Aktifkan tombol yang diklik
        btn.classList.remove('btn-outline-success', 'btn-outline-danger');
        btn.classList.add(status === 'sesuai' ? 'btn-success' : 'btn-danger');

        // Tampilkan / sembunyikan textarea catatan
        const noteWrapper = document.getElementById('kanwil-note-wrapper-' + docId);
        if (noteWrapper) noteWrapper.style.display = (status === 'tidak_sesuai') ? '' : 'none';

        updateKanwilProgress();
    }

    function updateKanwilProgress() {
        const inputs = document.querySelectorAll('[name*="kanwil_status"]');
        let filled = 0;
        inputs.forEach(i => { if (i.value && i.value !== 'pending' && i.value !== '') filled++; });

        const counter = document.getElementById('kanwil-check-counter');
        if (counter) counter.textContent = filled;

        const bar = document.getElementById('kanwil-progress-bar');
        if (bar && inputs.length > 0) {
            const pct = Math.round(filled / inputs.length * 100);
            bar.style.width = pct + '%';
        }
    }

    // Inisialisasi Kanwil saat load
    updateKanwilProgress();
</script>

@endsection
