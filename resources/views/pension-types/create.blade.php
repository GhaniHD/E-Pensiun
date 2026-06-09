@extends('layouts.app')

@section('title', 'Tambah Jenis Pensiun')
@section('page-title', 'Tambah Jenis Pensiun')

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.8rem">
            <li class="breadcrumb-item"><a href="{{ route('pension-types.index') }}" class="text-decoration-none">Jenis Pensiun</a></li>
            <li class="breadcrumb-item active">Tambah Baru</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i>
                    Tambah Jenis Pensiun Baru
                </div>
                <div class="card-body p-4">

                    <form action="{{ route('pension-types.store') }}" method="POST">
                        @csrf

                        {{-- Nama --}}
                        <div class="mb-4">
                            <label for="name" class="form-label fw-600">
                                Nama Jenis Pensiun <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="Contoh: Pensiun Batas Usia"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div class="mb-4">
                            <label for="slug" class="form-label fw-600">
                                Slug (URL) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text text-muted" style="font-size:0.82rem">/jenis-pensiun/</span>
                                <input
                                    type="text"
                                    id="slug"
                                    name="slug"
                                    class="form-control @error('slug') is-invalid @enderror"
                                    value="{{ old('slug') }}"
                                    placeholder="pensiun-batas-usia"
                                    required
                                >
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Otomatis terisi dari nama. Hanya huruf kecil, angka, dan tanda hubung.</div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-4">
                            <label for="description" class="form-label fw-600">Deskripsi</label>
                            <textarea
                                id="description"
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="3"
                                placeholder="Penjelasan singkat tentang jenis pensiun ini..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Icon --}}
                        <div class="mb-4">
                            <label for="icon" class="form-label fw-600">Icon Bootstrap Icons</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i id="icon-preview" class="bi bi-award" style="font-size:1.1rem;color:var(--primary)"></i>
                                </span>
                                <input
                                    type="text"
                                    id="icon"
                                    name="icon"
                                    class="form-control @error('icon') is-invalid @enderror"
                                    value="{{ old('icon', 'bi-award') }}"
                                    placeholder="bi-award"
                                >
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">
                                Nama class Bootstrap Icons.
                                <a href="https://icons.getbootstrap.com/" target="_blank" class="text-decoration-none">
                                    Lihat daftar icon <i class="bi bi-box-arrow-up-right" style="font-size:0.7rem"></i>
                                </a>
                            </div>
                            {{-- Quick icon picks --}}
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                @foreach(['bi-award','bi-person-badge','bi-file-earmark-person','bi-briefcase','bi-hospital','bi-shield-check','bi-clock-history','bi-people-fill'] as $ic)
                                    <button type="button" class="btn btn-outline-secondary btn-sm icon-pick"
                                            data-icon="{{ $ic }}" title="{{ $ic }}">
                                        <i class="bi {{ $ic }}"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Is Active --}}
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="is_active">
                                    Aktifkan jenis pensiun ini
                                </label>
                            </div>
                            <div class="form-text">Jenis pensiun yang tidak aktif tidak akan ditampilkan ke pengguna.</div>
                        </div>

                        <hr class="my-4">

                        {{-- Actions --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Simpan Jenis Pensiun
                            </button>
                            <a href="{{ route('pension-types.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('styles')
<style>
    .fw-600 { font-weight: 600; }
    .icon-pick.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }
</style>
@endsection

@section('scripts')
<script>
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    let slugManuallyEdited = slugInput.value.length > 0;

    nameInput.addEventListener('input', function () {
        if (!slugManuallyEdited) {
            slugInput.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        }
    });

    slugInput.addEventListener('input', function () {
        slugManuallyEdited = this.value.length > 0;
    });

    // Icon preview
    const iconInput   = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');

    iconInput.addEventListener('input', function () {
        iconPreview.className = 'bi ' + this.value;
    });

    // Quick icon pick buttons
    document.querySelectorAll('.icon-pick').forEach(btn => {
        btn.addEventListener('click', function () {
            const ic = this.dataset.icon;
            iconInput.value = ic;
            iconPreview.className = 'bi ' + ic;
            document.querySelectorAll('.icon-pick').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endsection
