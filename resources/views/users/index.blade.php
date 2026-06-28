@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')

{{-- ── PAGE HEADER ──────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between page-header">
    <div>
        <h4><i class="bi bi-people-fill me-2"></i>Manajemen User</h4>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Kelola akun pengguna sistem pengajuan pensiun.
        </p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah User Baru
    </a>
</div>

{{-- ── STATISTIK CEPAT ──────────────────────────────────── --}}
@php
    $totalUsers    = $users->total();
    $activeUsers   = \App\Models\User::where('is_active', true)->count();
    $inactiveUsers = \App\Models\User::where('is_active', false)->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.75rem;font-weight:700;color:var(--primary)">{{ \App\Models\User::count() }}</div>
            <div class="text-muted" style="font-size:.78rem">Total User</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.75rem;font-weight:700;color:var(--success)">{{ $activeUsers }}</div>
            <div class="text-muted" style="font-size:.78rem">Aktif</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.75rem;font-weight:700;color:var(--danger)">{{ $inactiveUsers }}</div>
            <div class="text-muted" style="font-size:.78rem">Nonaktif</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.75rem;font-weight:700;color:var(--accent)">{{ \App\Models\User::where('role','pensiunan')->count() }}</div>
            <div class="text-muted" style="font-size:.78rem">Calon Pensiunan</div>
        </div>
    </div>
</div>

{{-- ── FILTER ───────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
            {{-- Search --}}
            <div class="col-12 col-md-4">
                <label class="form-label mb-1" style="font-size:.8rem;font-weight:600">Cari</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Nama, NIP, atau email..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Filter Role --}}
            <div class="col-6 col-md-3">
                <label class="form-label mb-1" style="font-size:.8rem;font-weight:600">Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}"
                            {{ request('role') === $role->value ? 'selected' : '' }}>
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Kantor --}}
            <div class="col-6 col-md-3">
                <label class="form-label mb-1" style="font-size:.8rem;font-weight:600">Kantor</label>
                <select name="office" class="form-select form-select-sm">
                    <option value="">Semua Kantor</option>
                    @foreach($offices as $office)
                        <option value="{{ $office }}"
                            {{ request('office') === $office ? 'selected' : '' }}>
                            {{ $office }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol --}}
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i>Filter
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── TABEL USER ───────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-table me-2"></i>Daftar Pengguna</span>
        <span class="badge bg-secondary" style="font-weight:500">
            {{ $users->total() }} pengguna
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px">No</th>
                    <th>Nama Pengguna</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Kantor</th>
                    <th>Status</th>
                    <th style="width:160px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $i => $user)
                    <tr>
                        <td class="text-muted" style="font-size:.85rem">
                            {{ $users->firstItem() + $i }}
                        </td>

                        {{-- Nama --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:.9rem">{{ $user->name }}</div>
                                    @if($user->phone)
                                        <div class="text-muted" style="font-size:.75rem">
                                            <i class="bi bi-telephone me-1"></i>{{ $user->phone }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- NIP --}}
                        <td>
                            <span style="font-family:monospace;font-size:.82rem">
                                {{ $user->nip ?? '-' }}
                            </span>
                        </td>

                        {{-- Email --}}
                        <td style="font-size:.85rem">{{ $user->email }}</td>

                        {{-- Role --}}
                        <td>
                            @php
                                $roleColor = match($user->role->value) {
                                    'sdm_kanwil' => '#1A5632',
                                    'sdm_kantor' => '#1A5276',
                                    'tik'        => '#6C3483',
                                    'pensiunan'  => '#1E8449',
                                    default      => '#555',
                                };
                            @endphp
                            <span class="badge rounded-pill"
                                  style="background:{{ $roleColor }};font-size:.72rem;font-weight:500">
                                {{ $user->role->label() }}
                            </span>
                        </td>

                        {{-- Kantor --}}
                        <td style="font-size:.85rem">
                            {{ $user->office ?? '-' }}
                        </td>

                        {{-- Status --}}
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle"
                                      style="font-size:.72rem">
                                    <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle"
                                      style="font-size:.72rem">
                                    <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>Nonaktif
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td>
                            <div class="d-flex gap-1">
                                {{-- Edit --}}
                                <a href="{{ route('users.edit', $user) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>

                                {{-- Toggle Aktif/Nonaktif --}}
                                <form action="{{ route('users.toggle-active', $user) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @if($user->is_active)
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Nonaktifkan"
                                                onclick="return confirm('Nonaktifkan akun {{ addslashes($user->name) }}?')">
                                            <i class="bi bi-pause-circle-fill"></i>
                                        </button>
                                    @else
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-success"
                                                title="Aktifkan"
                                                onclick="return confirm('Aktifkan kembali akun {{ addslashes($user->name) }}?')">
                                            <i class="bi bi-play-circle-fill"></i>
                                        </button>
                                    @endif
                                </form>

                                {{-- Hapus --}}
                                <form action="{{ route('users.destroy', $user) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Hapus"
                                            onclick="return confirm('Hapus akun {{ addslashes($user->name) }}? Aksi ini tidak dapat dibatalkan.')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                            Tidak ada pengguna ditemukan.
                            @if(request()->hasAny(['search','role','office']))
                                <br>
                                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary mt-2">
                                    Reset Filter
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection

@section('styles')
<style>
    .user-avatar-sm {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--accent);
        color: #fff;
        font-size: .8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
@endsection
