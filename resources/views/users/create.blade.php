@extends('layouts.app')

@section('title', 'Tambah User Baru')
@section('page-title', 'Tambah User Baru')

@section('content')

{{-- ── BREADCRUMB ───────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb" style="font-size:.85rem">
        <li class="breadcrumb-item">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <i class="bi bi-people-fill me-1"></i>Manajemen User
            </a>
        </li>
        <li class="breadcrumb-item active">Tambah User Baru</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">

        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus-fill me-2"></i>Form Tambah Pengguna
            </div>
            <div class="card-body p-4">

                <form action="{{ route('users.store') }}" method="POST" autocomplete="off">
                    @csrf

                    {{-- ── INFORMASI DASAR ──────────────────── --}}
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
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: Budi Santoso, S.Sos">
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
                                   value="{{ old('nip') }}"
                                   placeholder="18 digit NIP"
                                   maxlength="18">
                            @error('nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Nomor Induk Pegawai (opsional).</div>
                        </div>

                        {{-- Email --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="contoh@instansi.go.id">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Digunakan untuk login.</div>
                        </div>

                        {{-- No. Telepon --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">No. Telepon</label>
                            <input type="text"
                                   name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}"
                                   placeholder="08xx-xxxx-xxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ── AKSES & JABATAN ──────────────────── --}}
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
                                        {{ old('role') === $role->value ? 'selected' : '' }}>
                                        {{ $role->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            {{-- Deskripsi role --}}
                            <div id="roleDesc" class="form-text mt-2" style="display:none"></div>
                        </div>

                        {{-- Kantor --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">Kantor / Unit Kerja</label>
                            <input type="text"
                                   name="office"
                                   id="officeInput"
                                   list="officeList"
                                   class="form-control @error('office') is-invalid @enderror"
                                   value="{{ old('office') }}"
                                   placeholder="Ketik atau pilih kantor...">
                            <datalist id="officeList">
                                @foreach($offices as $office)
                                    <option value="{{ $office }}">
                                @endforeach
                            </datalist>
                            @error('office')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Kantor wilayah atau kantor pelayanan.</div>
                        </div>

                        {{-- Status Aktif --}}
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="is_active"
                                       id="isActive"
                                       value="1"
                                       {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    Akun langsung aktif setelah dibuat
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ── PASSWORD ─────────────────────────── --}}
                    <h6 class="section-label">Password</h6>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       name="password"
                                       id="passwordInput"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min. 8 karakter">
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
                            <label class="form-label">
                                Konfirmasi Password <span class="text-danger">*</span>
                            </label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="passwordConfirm"
                                   class="form-control"
                                   placeholder="Ulangi password">
                            <div id="passwordMatch" class="form-text mt-1"></div>
                        </div>
                    </div>

                    {{-- ── INFO ROLE ─────────────────────────── --}}
                    <div class="alert alert-info py-2 mb-4" style="font-size:.83rem">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Panduan Role:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <li><strong>Staff SDM Kanwil</strong> — dapat memverifikasi berkas & memajukan status pengajuan.</li>
                            <li><strong>Staff SDM Kantor Pelayanan</strong> — dapat mengupload berkas & membuat pengajuan.</li>
                            <li><strong>Kepala TIK</strong> — dapat mengelola semua data, konten, dan akun pengguna.</li>
                            <li><strong>Calon Pensiunan</strong> — hanya dapat melihat dan memantau status pengajuannya.</li>
                        </ul>
                    </div>

                    {{-- ── TOMBOL ───────────────────────────── --}}
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-check-fill me-1"></i>Simpan Pengguna
                        </button>
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
        if (!cpw) { matchMsg.textContent = ''; return; }
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

    const roleSelect    = document.getElementById('roleSelect');
    const roleDescEl    = document.getElementById('roleDesc');

    roleSelect.addEventListener('change', () => {
        const desc = roleDesc[roleSelect.value];
        if (desc) {
            roleDescEl.textContent = desc;
            roleDescEl.style.display = 'block';
        } else {
            roleDescEl.style.display = 'none';
        }
    });

    // Trigger on load (jika old value ada)
    if (roleSelect.value) roleSelect.dispatchEvent(new Event('change'));
</script>
@endsection
