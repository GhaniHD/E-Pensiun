@php $user = auth()->user(); @endphp

{{-- Welcome --}}
<div class="card mb-4" style="background: linear-gradient(135deg, #1A5632, #27AE60); color:#fff; border:none">
    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0">Selamat datang, {{ $user->name }}!</h5>
            <div style="font-size:0.85rem;opacity:.85">
                {{ $user->role->label() }}
                @if($user->office) — {{ $user->office }} @endif
                &nbsp;|&nbsp; {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
        <i class="bi bi-building-fill" style="font-size:2.5rem;opacity:.3"></i>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:#1A5632">
            <div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Pengajuan</div>
            </div>
            <i class="bi bi-folder2-open stat-icon ms-auto"></i>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:#D4AC0D">
            <div>
                <div class="stat-value">{{ $stats['pending'] }}</div>
                <div class="stat-label">Sedang Diproses</div>
            </div>
            <i class="bi bi-hourglass-split stat-icon ms-auto"></i>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:#1E8449">
            <div>
                <div class="stat-value">{{ $stats['completed'] }}</div>
                <div class="stat-label">SK Terbit</div>
            </div>
            <i class="bi bi-patch-check-fill stat-icon ms-auto"></i>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:#27AE60">
            <div>
                <div class="stat-value">{{ $stats['thisMonth'] }}</div>
                <div class="stat-label">Bulan Ini</div>
            </div>
            <i class="bi bi-calendar3 stat-icon ms-auto"></i>
        </div>
    </div>
</div>

<div class="row g-3">

    {{-- ── Daftar Pengajuan Kantor ──────────────────────────── --}}
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="d-flex align-items-center gap-2">
                    <i class="bi bi-list-check"></i>
                    Pengajuan Kantor {{ $user->office }}
                </span>
                <a href="{{ route('applications.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="font-size:.8rem">Nama Pegawai</th>
                            <th style="font-size:.8rem">Jenis Pensiun</th>
                            <th style="font-size:.8rem">Status</th>
                            <th style="font-size:.8rem">Berkas</th>
                            <th style="font-size:.8rem"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentApplications ?? [] as $app)
                        <tr>
                            <td>
                                <div style="font-size:.875rem;font-weight:500">{{ $app->user->name }}</div>
                                <div class="text-muted" style="font-size:.78rem">{{ $app->user->nip ?? '-' }}</div>
                            </td>
                            <td style="font-size:.85rem">{{ $app->pensionType->name }}</td>
                            <td>
                                <span class="badge bg-{{ $app->status->badgeColor() ?? 'secondary' }}" style="font-size:.75rem">
                                    {{ $app->status->label() }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $totalDocs    = $app->pensionType->documentTemplates->count();
                                    $uploadedDocs = $app->documents->whereNotNull('file_path')->count();
                                @endphp
                                <span style="font-size:.82rem;color:{{ $uploadedDocs == $totalDocs ? '#1E8449' : '#D4AC0D' }}">
                                    <i class="bi bi-paperclip me-1"></i>{{ $uploadedDocs }}/{{ $totalDocs }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('applications.show', $app) }}"
                                   class="btn btn-sm btn-outline-secondary py-0 px-2">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4" style="font-size:.875rem">
                                <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.4"></i>
                                Belum ada pengajuan di kantor ini
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Aksi Cepat ──────────────────────────────────────── --}}
    <div class="col-12 col-lg-4">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-lightning-charge-fill"></i>
                Aksi Cepat
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('applications.create') }}"
                   class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i> Buat Pengajuan Baru
                </a>
                <a href="{{ route('applications.index') }}"
                   class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-folder2-open"></i> Semua Pengajuan
                </a>
                <a href="{{ route('pension-types.index') }}"
                   class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-journal-bookmark-fill"></i> Jenis Pensiun
                </a>
                <a href="{{ route('articles.index') }}"
                   class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-newspaper"></i> Artikel MPP
                </a>
            </div>
        </div>

        {{-- Pengajuan butuh perhatian --}}
        @php
            $needsAttention = ($recentApplications ?? collect())->filter(fn($a) =>
                $a->documents->whereNotNull('file_path')->count() <
                $a->pensionType->documentTemplates->count()
            );
        @endphp
        @if($needsAttention->count() > 0)
        <div class="card" style="border-left:4px solid #D4AC0D">
            <div class="card-header d-flex align-items-center gap-2" style="background:#fffbea">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                Berkas Belum Lengkap
            </div>
            <div class="card-body p-0">
                @foreach($needsAttention->take(4) as $app)
                <div class="d-flex align-items-center justify-content-between px-3 py-2"
                     style="border-bottom:1px solid #f0f0f0;font-size:.83rem">
                    <div>
                        <div class="fw-semibold">{{ $app->user->name }}</div>
                        <div class="text-muted" style="font-size:.78rem">{{ $app->pensionType->name }}</div>
                    </div>
                    <a href="{{ route('applications.show', $app) }}"
                       class="btn btn-sm btn-warning py-0 px-2" style="font-size:.78rem">
                        Upload
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>
