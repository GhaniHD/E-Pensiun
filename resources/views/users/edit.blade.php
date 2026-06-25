@extends('layouts.app')

@section('title', 'Edit User — ' . $user->name)
@section('page-title', 'Edit User')

@section('content')

{{-- ── BREADCRUMB ───────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb" style="font-size:.85rem">
        <li class="breadcrumb-item">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <i class="bi bi-people-fill me-1"></i>Manajemen User
            </a>
        </li>
        <li class="breadcrumb-item active">Edit: {{ $user->name }}</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">

        {{-- ── INFO USER CARD ───────────────────────────── --}}
        <div class="card mb-3" style="border-left: 4px solid var(--accent)">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="user-avatar-lg">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:700;font-size:1rem">{{ $user->name }}</div>
                    <div class="d-flex gap-2 flex-wrap mt-1">
                        @php
                            $roleColor = match($user->role->value) {
                                'sdm_kanwil' => '#1B4F72',
                                'sdm_kantor' => '#1A5276',
                                'tik'        => '#6C3483',
                                'pensiunan'  => '#1E8449',
                                default      => '#555',
                            };
                        @endphp
                        <span class="badge rounded-pill"
                              style="background:{{ $roleColor }};font-size:.72rem">
                            {{ $user->role->label() }}
                        </span>
                        @if($user->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle"
                                  style="font-size:.72rem">
                                <i class="bi bi-circle-fill me-1" style="font-size:.45rem"></i>Aktif
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"
                                  style="font-size:.72rem">
                                <i class="bi bi-circle-fill me-1" style="font-size:.45rem"></i>Nonaktif
                            </span>
                        @endif
                        @if($user->nip)
                            <span class="text-muted" style="font-size:.78rem;font-family:monospace">
                                NIP: {{ $user->nip }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="ms-auto text-end d-none d-md-block">
                    <div class="text-muted" style="font-size:.75rem">Terdaftar sejak</div>
                    <div style="font-size:.85rem;font-weight:600">{{ $user->created_at->format('d M Y') }}</div>
                </div>
            </div>
        </div>

        {{-- ── FORM EDIT ────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil-square me-2"></i>Edit Data Pengguna
            </div>
            <div class="card-body p-4">

                <form action="{{ route('users.update', $user) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    {{-- ── INFORMASI DASAR ──────────────── --}}
                    <h6 class="section-label">Informasi Dasar</h6>

                    <div class="row g-3 mb-4">
                        {{-- Nama Lengkap --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}"
                                   placeholder="Nama lengkap">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- NIP --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">NIP</label>
                            <input type="text"
                                   name="nip"
                                   class="form-control @error('nip') is-invalid @enderror"
                                   value="{{ old('nip', $user->nip) }}"
                                   placeholder="18 digit NIP"
                                   maxlength="18">
                            @error('nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}"
                                   placeholder="email@instansi.go.id">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- No. Telepon --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">No. Telepon</label>
                            <input type="text"
                                   name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="08xx-xxxx-xxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ── AKSES & JABATAN ──────────────── --}}
                    <h6 class="section-label">Akses & Jabatan</h6>

                    <div class="row g-3 mb-4">
                        {{-- Role --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Role / Jabatan <span class="text-danger">*</span>
                            </label>
                            <select name="role"
                                    id="roleSelect"
                                    class="form-select @error('role') is-invalid @enderror">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}"
                                        {{ old('role', $user->role->value) === $role->value ? 'selected' : '' }}>
                                        {{ $role->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="roleDesc" class="form-text mt-2" style="display:none"></div>
                        </div>

                        {{-- Kantor --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">Kantor / Unit Kerja</label>
                            <input type="text"
                                   name="office"
                                   list="officeList"
                                   class="form-control @error('office') is-invalid @enderror"
                                   value="{{ old('office', $user->office) }}"
                                   placeholder="Ketik atau pilih kantor...">
                            <datalist id="officeList">
                                @foreach($offices as $office)
                                    <option value="{{ $office }}">
                                @endforeach
                            </datalist>
                            @error('office')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status Aktif --}}
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="is_active"
                                       id="isActive"
                                       value="1"
                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    Akun aktif (dapat login ke sistem)
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ── GANTI PASSWORD ───────────────── --}}
                    <h6 class="section-label">Ganti Password</h6>

                    <div class="alert alert-warning py-2 mb-3" style="font-size:.82rem">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Kosongkan kolom password jika tidak ingin mengubah password.
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password"
                                       name="password"
                                       id="passwordInput"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Kosongkan jika tidak diubah">
                                <button type="button"
                                        class="btn btn-outline-secondary"
                                        id="togglePassword"
                                        tabindex="-1">
                                    <i class="bi bi-eye-fill" id="toggleIcon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Minimal 8 karakter.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="passwordConfirm"
                                   class="form-control"
                                   placeholder="Ulangi password baru">
                            <div id="passwordMatch" class="form-text mt-1"></div>
                        </div>
                    </div>

                    {{-- ── TOMBOL ───────────────────────── --}}
                    <div class="d-flex gap-2 justify-content-between align-items-center">
                        {{-- Hapus (kiri) --}}
                        @if(!$user->applications()->exists())
                            <form action="{{ route('users.destroy', $user) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Hapus akun {{ addslashes($user->name) }} secara permanen?')">
                                    <i class="bi bi-trash-fill me-1"></i>Hapus Akun
                                </button>
                            </form>
                        @else
                            <span class="text-muted" style="font-size:.78rem">
                                <i class="bi bi-lock-fill me-1"></i>
                                Akun tidak dapat dihapus (memiliki data pengajuan)
                            </span>
                        @endif

                        {{-- Simpan & Batal (kanan) --}}
                        <div class="d-flex gap-2">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-floppy-fill me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@section('styles')
<style>
    .section-label {
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--primary);
        border-bottom: 2px solid var(--accent);
        padding-bottom: .4rem;
        margin-bottom: 1rem;
    }

    .user-avatar-lg {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--accent);
        color: #fff;
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
@endsection

@section('scripts')
<script>
    // ── Toggle lihat password ──────────────────────────────
    const passwordInput = document.getElementById('passwordInput');
    const toggleBtn     = document.getElementById('togglePassword');
    const toggleIcon    = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', () => {
        const isText = passwordInput.type === 'text';
        passwordInput.type = isText ? 'password' : 'text';
        toggleIcon.className = isText ? 'bi bi-eye-fill' : 'bi bi-eye-slash-fill';
    });

    // ── Cek konfirmasi password ────────────────────────────
    const passwordConfirm = document.getElementById('passwordConfirm');
    const matchMsg        = document.getElementById('passwordMatch');

    function checkPasswordMatch() {
        const pw  = passwordInput.value;
        const cpw = passwordConfirm.value;
        if (!pw && !cpw) { matchMsg.textContent = ''; return; }
        if (!cpw)        { matchMsg.textContent = ''; return; }
        if (pw === cpw) {
            matchMsg.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Password cocok</span>';
        } else {
            matchMsg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Password tidak cocok</span>';
        }
    }

    passwordInput.addEventListener('input', checkPasswordMatch);
    passwordConfirm.addEventListener('input', checkPasswordMatch);

    // ── Deskripsi role ─────────────────────────────────────
    const roleDesc = {
        'sdm_kanwil': ' Dapat memverifikasi berkas dan memajukan status pengajuan.',
        'sdm_kantor': ' Dapat mengupload berkas dan membuat pengajuan untuk calon pensiunan.',
        'tik':        ' Akses penuh: kelola user, konten, dan semua data sistem.',
        'pensiunan':  ' Hanya dapat melihat status pengajuan miliknya sendiri.',
    };

    const roleSelect = document.getElementById('roleSelect');
    const roleDescEl = document.getElementById('roleDesc');

    roleSelect.addEventListener('change', () => {
        const desc = roleDesc[roleSelect.value];
        if (desc) {
            roleDescEl.textContent = desc;
            roleDescEl.style.display = 'block';
        } else {
            roleDescEl.style.display = 'none';
        }
    });

    // Trigger on load
    if (roleSelect.value) roleSelect.dispatchEvent(new Event('change'));
</script>
@endsection
