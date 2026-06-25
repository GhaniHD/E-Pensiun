@extends('layouts.app')

@section('title', 'Regulasi / UU')
@section('page-title', 'Regulasi / UU')

@section('content')

    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="bi bi-bank2 me-2"></i>Regulasi / UU</h4>
            <p class="text-muted mb-0" style="font-size:0.875rem">
                Daftar peraturan dan undang-undang terkait pensiun.
            </p>
        </div>
        @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
            <a href="{{ route('regulations.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i> Tambah Regulasi
            </a>
        @endif
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body p-3">
            <form action="{{ route('regulations.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-600 mb-1" style="font-size:0.82rem">Cari Judul / Nomor</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Cari peraturan...">
                </div>
                <div class="col-8 col-md-4">
                    <label class="form-label fw-600 mb-1" style="font-size:0.82rem">Kategori</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search') || request('category'))
                        <a href="{{ route('regulations.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Empty State --}}
    @if($regulations->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bank2" style="font-size:3rem;color:#ccc"></i>
                <p class="mt-3 text-muted mb-0">Belum ada regulasi yang tersedia.</p>
                @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                    <a href="{{ route('regulations.create') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Sekarang
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:90px">Tahun</th>
                            <th>Judul / Nomor</th>
                            <th>Kategori</th>
                            <th style="width:160px" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($regulations as $regulation)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $regulation->year ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:0.88rem;color:var(--primary)">
                                        {{ $regulation->title }}
                                    </div>
                                    @if($regulation->number)
                                        <small class="text-muted" style="font-size:0.78rem">{{ $regulation->number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background:rgba(27,79,114,0.12);color:var(--primary);font-size:0.72rem">
                                        {{ $regulation->category }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('regulations.show', $regulation) }}"
                                           class="btn btn-primary btn-sm" title="Lihat">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        @if($regulation->file_path)
                                            <a href="{{ route('regulations.download', $regulation) }}"
                                               class="btn btn-outline-primary btn-sm" title="Unduh">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->isSdmKanwil() || auth()->user()->isTik())
                                            <a href="{{ route('regulations.edit', $regulation) }}"
                                               class="btn btn-outline-secondary btn-sm" title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <form action="{{ route('regulations.destroy', $regulation) }}" method="POST"
                                                  onsubmit="return confirm('Hapus regulasi \'{{ addslashes($regulation->title) }}\'? Tindakan ini tidak dapat dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $regulations->links() }}
        </div>
    @endif

@endsection

@section('styles')
<style>
    .fw-600 { font-weight: 600; }
</style>
@endsection
