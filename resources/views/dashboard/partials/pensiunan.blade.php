@php
    $user        = auth()->user();
    $application = $application ?? $myApplication ?? null;
@endphp

{{-- Welcome --}}
<div class="card mb-4" style="background:linear-gradient(135deg,#1B4F72,#2E86C1);color:#fff;border:none">
    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0">Selamat datang, {{ $user->name }}!</h5>
            <div style="font-size:0.85rem;opacity:.85">
                {{ $user->role->label() }}
                @if($user->office) — {{ $user->office }} @endif
                &nbsp;|&nbsp; {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
        <i class="bi bi-person-badge-fill" style="font-size:2.5rem;opacity:.3"></i>
    </div>
</div>

{{-- ── BARIS 1: Status Pengajuan ───────────────────────────── --}}
@if(!$application)

<div class="row g-3 mb-4">
    {{-- Card Belum Ada Pengajuan --}}
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-earmark-plus" style="font-size:3rem;color:#2E86C1;opacity:.5"></i>
                <h5 class="mt-3 mb-1" style="color:#1B4F72">Belum Ada Pengajuan</h5>
                <p class="text-muted mb-0" style="font-size:.875rem">
                    Pengajuan pensiun Anda akan dibuatkan oleh Staff KPKNL Pelayanan.<br>
                    Silakan hubungi kantor pelayanan untuk memulai proses.
                </p>
                <div class="mt-4 p-3 rounded d-inline-block" style="background:#eef4fb;font-size:.85rem;color:#1B4F72">
                    <i class="bi bi-telephone-fill me-1"></i>
                    Hubungi <strong>Staff KPKNL Pelayanan</strong> untuk membuat pengajuan
                </div>
            </div>
        </div>
    </div>

    {{-- Infografis SOP --}}
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3-fill text-primary"></i> Infografis SOP
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="padding:10px;overflow:hidden;">
                @if(file_exists(public_path('images/sop-pensiun.png')))
                    <img src="{{ asset('images/sop-pensiun.png') }}"
                         alt="Infografis SOP Alur Pengajuan Pensiun"
                         style="width:100%;height:100%;object-fit:contain;">
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-image" style="font-size:1.8rem;display:block;margin-bottom:.5rem;"></i>
                        <small>Letakkan <code>images/sop-pensiun.png</code></small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@else

{{-- Info Cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card h-100" style="border-left:4px solid #1B4F72">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em">Jenis Pensiun</div>
                <div class="fw-bold" style="color:#1B4F72;font-size:.95rem">{{ $application->pensionType->name }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100" style="border-left:4px solid #2E86C1">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em">Rencana Pensiun</div>
                <div class="fw-bold" style="color:#1B4F72;font-size:.95rem">
                    {{ $application->pension_date?->translatedFormat('d F Y') ?? '-' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100" style="border-left:4px solid var(--warning)">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em">Status Saat Ini</div>
                <span class="badge bg-{{ $application->status->badgeColor() ?? 'warning' }}" style="font-size:.85rem">
                    {{ $application->status->label() }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Row: Alur Progress (kiri) + Infografis SOP (kanan) --}}
<div class="row g-3 mb-4">
    {{-- Kolom kiri: Alur Progress --}}
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-signpost-split-fill"></i> Alur Progress Pengajuan
            </div>
            <div class="card-body px-4 py-3">
                @php
                    $allStatuses  = $allStatuses ?? \App\Enums\ApplicationStatus::allOrdered();
                    $currentIndex = collect($allStatuses)->search(fn($s) => $s->value === $application->status->value);
                @endphp
                <div class="d-flex flex-column gap-0">
                    @foreach($allStatuses as $i => $status)
                        @php
                            $isDone    = $i < $currentIndex;
                            $isCurrent = $i === $currentIndex;
                            $history   = $application->statusHistories->first(
                                fn($h) => $h->to_status->value === $status->value
                            );
                        @endphp
                        <div class="d-flex gap-3 align-items-start" style="min-height:60px">
                            <div class="d-flex flex-column align-items-center" style="width:32px;flex-shrink:0">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:32px;height:32px;flex-shrink:0;
                                            background:{{ $isDone ? '#1E8449' : ($isCurrent ? '#2E86C1' : '#e9ecef') }};
                                            color:{{ ($isDone || $isCurrent) ? '#fff' : '#adb5bd' }};font-size:.8rem">
                                    @if($isDone) <i class="bi bi-check-lg"></i>
                                    @elseif($isCurrent) <i class="bi bi-arrow-right-circle-fill"></i>
                                    @else {{ $i + 1 }}
                                    @endif
                                </div>
                                @if(!$loop->last)
                                <div style="width:2px;flex:1;min-height:16px;margin-top:2px;background:{{ $isDone ? '#1E8449' : '#e9ecef' }}"></div>
                                @endif
                            </div>
                            <div class="pb-3" style="padding-top:5px">
                                <div class="fw-semibold" style="font-size:.875rem;
                                    color:{{ $isCurrent ? '#1B4F72' : ($isDone ? '#1E8449' : '#adb5bd') }}">
                                    {{ $status->label() }}
                                    @if($isCurrent) <span class="badge bg-primary ms-1" style="font-size:.7rem">Sekarang</span> @endif
                                </div>
                                @if($history)
                                <div class="text-muted" style="font-size:.78rem">
                                    {{ $history->created_at->translatedFormat('d M Y, H:i') }}
                                    @if($history->note) &mdash; {{ $history->note }} @endif
                                </div>
                                @elseif($i > $currentIndex)
                                <div class="text-muted" style="font-size:.78rem">Menunggu</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom kanan: Infografis SOP --}}
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3-fill text-primary"></i> Infografis SOP
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="padding:10px;overflow:hidden;">
                @if(file_exists(public_path('images/sop-pensiun.png')))
                    <img src="{{ asset('images/sop-pensiun.png') }}"
                         alt="Infografis SOP Alur Pengajuan Pensiun"
                         style="width:100%;height:100%;object-fit:contain;">
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-image" style="font-size:1.8rem;display:block;margin-bottom:.5rem;"></i>
                        <small>Letakkan <code>images/sop-pensiun.png</code></small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Row: Kelengkapan Berkas (di bawah, full width) --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-folder2-open"></i> Kelengkapan Berkas
            </div>
            <div class="card-body">
                @php
                    $templates  = $application->pensionType->documentTemplates ?? collect();
                    $docs       = $application->documents ?? collect();
                    $total      = $templates->count();
                    $uploaded   = $docs->whereNotNull('file_path')->count();
                    $pct        = $total > 0 ? round($uploaded / $total * 100) : 0;
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size:.875rem;font-weight:600;color:#1B4F72">{{ $uploaded }}/{{ $total }} berkas diunggah</span>
                    <span class="fw-bold" style="color:{{ $pct == 100 ? '#1E8449' : '#D4AC0D' }}">{{ $pct }}%</span>
                </div>
                <div class="progress mb-3" style="height:8px;border-radius:99px">
                    <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $pct == 100 ? '#1E8449' : '#2E86C1' }};border-radius:99px"></div>
                </div>
                <div class="d-flex flex-column gap-2">
                    @forelse($templates as $template)
                        @php $doc = $docs->firstWhere('document_name', $template->document_name); @endphp
                        <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f8f9fa;font-size:.82rem">
                            @if($doc && $doc->is_verified)
                                <i class="bi bi-check-circle-fill text-success flex-shrink-0"></i>
                            @elseif($doc && $doc->file_path)
                                <i class="bi bi-clock-fill text-warning flex-shrink-0"></i>
                            @else
                                <i class="bi bi-x-circle-fill flex-shrink-0" style="color:#ccc"></i>
                            @endif
                            <span>{{ $template->document_name }}</span>
                            @if($doc && $doc->is_verified)
                                <span class="ms-auto badge bg-success" style="font-size:.7rem">Terverifikasi</span>
                            @elseif($doc && $doc->file_path)
                                <span class="ms-auto badge bg-warning text-dark" style="font-size:.7rem">Menunggu</span>
                            @else
                                <span class="ms-auto badge bg-light text-muted" style="font-size:.7rem">Belum</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted text-center py-2" style="font-size:.85rem">Tidak ada template berkas</div>
                    @endforelse
                </div>
                @if($application->rejection_note)
                    <div class="mt-3 p-2 rounded" style="border-left:4px solid var(--danger);background:#fef2f2;">
                        <div class="fw-semibold text-danger mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Catatan Verifikator</div>
                        {{ $application->rejection_note }}
                    </div>
                @endif
                <a href="{{ route('applications.show', $application) }}"
                   class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2 mt-3">
                    <i class="bi bi-eye-fill"></i> Lihat Detail Lengkap
                </a>
            </div>
        </div>
    </div>
</div>

@endif

{{-- ── BARIS 3: Artikel & Regulasi Terbaru ────────────────── --}}
<div class="row g-3">

    {{-- Artikel MPP --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="d-flex align-items-center gap-2">
                    <i class="bi bi-newspaper"></i> Artikel MPP Terbaru
                </span>
                <a href="{{ route('articles.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($latestArticles ?? [] as $article)
                <a href="{{ route('articles.show', $article->slug) }}"
                   class="d-flex gap-3 align-items-start px-3 py-3 text-decoration-none"
                   style="border-bottom:1px solid #f5f5f5;transition:background .15s"
                   onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background=''">
                    <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:#eef4fb">
                        <i class="bi bi-file-earmark-text" style="color:#2E86C1;font-size:1.1rem"></i>
                    </div>
                    <div style="min-width:0">
                        <div class="fw-semibold text-truncate" style="font-size:.875rem;color:#1B4F72">{{ $article->title }}</div>
                        <div class="text-muted" style="font-size:.78rem">{{ $article->created_at->translatedFormat('d M Y') }}</div>
                    </div>
                </a>
                @empty
                <div class="text-center text-muted py-4" style="font-size:.875rem">
                    <i class="bi bi-inbox" style="font-size:1.5rem;display:block;opacity:.4;margin-bottom:.5rem"></i>
                    Belum ada artikel
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Regulasi --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text-fill"></i> Regulasi Terbaru
                </span>
                <a href="{{ route('regulations.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($latestRegulations ?? [] as $regulation)
                <a href="{{ route('regulations.show', $regulation) }}"
                   class="d-flex gap-3 align-items-start px-3 py-3 text-decoration-none"
                   style="border-bottom:1px solid #f5f5f5;transition:background .15s"
                   onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background=''">
                    <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:#eaf7ef">
                        <i class="bi bi-journal-text" style="color:#1E8449;font-size:1.1rem"></i>
                    </div>
                    <div style="min-width:0">
                        <div class="fw-semibold text-truncate" style="font-size:.875rem;color:#1B4F72">{{ $regulation->title }}</div>
                        <div class="text-muted" style="font-size:.78rem">{{ $regulation->created_at->translatedFormat('d M Y') }}</div>
                    </div>
                </a>
                @empty
                <div class="text-center text-muted py-4" style="font-size:.875rem">
                    <i class="bi bi-inbox" style="font-size:1.5rem;display:block;opacity:.4;margin-bottom:.5rem"></i>
                    Belum ada regulasi
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
