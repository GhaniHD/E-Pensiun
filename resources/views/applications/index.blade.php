@extends('layouts.app')

@section('title', 'Pengajuan Pensiun')
@section('page-title', 'Pengajuan Pensiun')

@section('content')

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4><i class="bi bi-folder2-open me-2"></i>Daftar Pengajuan Pensiun</h4>
        <p class="text-muted mb-0" style="font-size:.85rem">
            Kelola dan pantau status semua pengajuan pensiun
        </p>
    </div>
    @if(auth()->user()->isPensiunan() || auth()->user()->isSdmKantor())
        <a href="{{ route('applications.create') }}" class="btn btn-primary">   
            <i class="bi bi-plus-circle-fill me-1"></i> Buat Pengajuan Baru
        </a>
    @endif
</div>

{{-- ── FILTER BAR ───────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('applications.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label form-label-sm text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}"
                            {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-6 col-md-3">
                <label class="form-label form-label-sm text-muted mb-1">Jenis Pensiun</label>
                <select name="pension_type" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    @foreach($pensionTypes as $type)
                        <option value="{{ $type->id }}"
                            {{ request('pension_type') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                <div class="col-sm-6 col-md-3">
                    <label class="form-label form-label-sm text-muted mb-1">Kantor</label>
                    <input type="text" name="office" class="form-control form-control-sm"
                           placeholder="Nama kantor..."
                           value="{{ request('office') }}">
                </div>
            @endif

            <div class="col-sm-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── TABEL PENGAJUAN ──────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-table me-2"></i>Daftar Pengajuan</span>
        <span class="badge bg-secondary">{{ $applications->total() }} total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">#</th>
                        @if(!auth()->user()->isPensiunan())
                            <th>Nama Pegawai</th>
                        @endif
                        <th>Jenis Pensiun</th>
                        <th>Tgl Pengajuan</th>
                        <th>Rencana Pensiun</th>
                        @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                            <th>Kantor</th>
                        @endif
                        <th>Status</th>
                        <th class="text-center" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td class="ps-3 text-muted" style="font-size:.82rem">
                                {{ $applications->firstItem() + $loop->index }}
                            </td>

                            @if(!auth()->user()->isPensiunan())
                                <td>
                                    <div class="fw-semibold" style="font-size:.88rem">
                                        {{ $app->user->name }}
                                    </div>
                                    @if($app->user->nip)
                                        <div class="text-muted" style="font-size:.75rem">
                                            NIP: {{ $app->user->nip }}
                                        </div>
                                    @endif
                                </td>
                            @endif

                            <td style="font-size:.88rem">
                                <i class="bi bi-journal-bookmark me-1 text-primary"></i>
                                {{ $app->pensionType->name }}
                            </td>

                            <td style="font-size:.85rem; white-space:nowrap">
                                {{ $app->created_at->format('d M Y') }}
                            </td>

                            <td style="font-size:.85rem; white-space:nowrap">
                                @if($app->pension_date)
                                    {{ $app->pension_date->format('d M Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                                <td style="font-size:.82rem">
                                    {{ $app->user->office ?? '—' }}
                                </td>
                            @endif

                            <td>
                                <span class="badge bg-{{ $app->status->badgeColor() }}">
                                    {{ $app->status->label() }}
                                </span>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('applications.show', $app) }}"
                                   class="btn btn-sm btn-outline-primary px-2">
                                    <i class="bi bi-eye-fill me-1"></i>Detail
                                </a>
                                @if(auth()->user()->isSdmKanwil())
                                    <a href="{{ route('applications.show', $app) }}#verifikasi"
                                       class="btn btn-sm btn-outline-warning px-2 mt-1">
                                        <i class="bi bi-clipboard2-check me-1"></i>Verifikasi
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                Belum ada pengajuan
                                @if(request()->hasAny(['status', 'pension_type', 'office']))
                                    yang sesuai filter
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($applications->hasPages())
        <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-3">
            {{ $applications->links() }}
        </div>
    @endif
</div>

@endsection
