@php $user = auth()->user(); @endphp

{{-- Welcome --}}
<div class="card mb-4" style="background: linear-gradient(135deg, #1B4F72, #2E86C1); color:#fff; border:none">
    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0">Selamat datang, {{ $user->name }}!</h5>
            <div style="font-size:0.85rem;opacity:.85">
                {{ $user->role->label() }}
                @if($user->office) — {{ $user->office }} @endif
                &nbsp;|&nbsp; {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
        <i class="bi bi-award-fill" style="font-size:2.5rem;opacity:.3"></i>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:#1B4F72">
            <div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Pengajuan</div>
            </div>
            <i class="bi bi-folder2-open stat-icon ms-auto"></i>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:#2E86C1">
            <div>
                <div class="stat-value">{{ $stats['thisMonth'] }}</div>
                <div class="stat-label">Bulan Ini</div>
            </div>
            <i class="bi bi-calendar3 stat-icon ms-auto"></i>
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
        <div class="stat-card" style="background:#D4AC0D">
            <div>
                <div class="stat-value">{{ $stats['pending'] }}</div>
                <div class="stat-label">Sedang Diproses</div>
            </div>
            <i class="bi bi-hourglass-split stat-icon ms-auto"></i>
        </div>
    </div>
</div>

{{-- Progress --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span style="font-size:0.875rem;font-weight:600;color:#1B4F72">
                <i class="bi bi-graph-up-arrow me-1"></i>Tingkat Penyelesaian (SK Terbit)
            </span>
            <span class="fw-bold" style="color:#1E8449">{{ number_format($stats['percentCompleted'], 1) }}%</span>
        </div>
        <div class="progress" style="height:10px;border-radius:99px">
            <div class="progress-bar" role="progressbar"
                style="width:{{ $stats['percentCompleted'] }}%;background:#1E8449;border-radius:99px">
            </div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart-fill"></i>
                Pengajuan Pensiun per Bulan (12 Bulan Terakhir)
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart-fill"></i>
                Pengajuan per Kantor Pelayanan
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if(count($officeData) > 0)
                    <canvas id="officeChart" style="max-height:240px"></canvas>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-building" style="font-size:2rem"></i>
                        <p class="mt-2 mb-0" style="font-size:0.875rem">Belum ada data kantor</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Status Table + Aksi Cepat --}}
<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-list-check"></i>
                Rekap Status Pengajuan
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tahap</th>
                            <th class="text-center">Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statusData as $row)
                        <tr>
                            <td style="font-size:0.875rem;font-weight:500">{{ $row['label'] }}</td>
                            <td class="text-center"><strong>{{ $row['total'] }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $row['color'] }}">{{ $row['label'] }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3" style="font-size:0.875rem">
                                Belum ada data pengajuan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-lightning-charge-fill"></i>
                Aksi Cepat
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('applications.index') }}"
                   class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-folder2-open"></i> Lihat Semua Pengajuan
                </a>
                <a href="{{ route('pension-types.index') }}"
                   class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-journal-bookmark-fill"></i> Jenis Pensiun
                </a>
                <a href="{{ route('articles.index') }}"
                   class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-newspaper"></i> Artikel MPP
                </a>
                @if($user->isSdmKanwil() || $user->isTik())
                <a href="{{ route('applications.index') }}?status=verifikasi"
                   class="btn btn-warning d-flex align-items-center gap-2">
                    <i class="bi bi-clipboard2-check-fill"></i> Pengajuan Perlu Verifikasi
                </a>
                @endif
                @if($user->isTik())
                <a href="{{ route('users.index') }}"
                   class="btn btn-outline-danger d-flex align-items-center gap-2">
                    <i class="bi bi-people-fill"></i> Manajemen User
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
