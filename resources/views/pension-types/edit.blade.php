@extends('layouts.app')

@section('title', 'Edit ' . $pensionType->name)
@section('page-title', 'Edit Jenis Pensiun')

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.8rem">
            <li class="breadcrumb-item"><a href="{{ route('pension-types.index') }}" class="text-decoration-none">Jenis Pensiun</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pension-types.show', $pensionType) }}" class="text-decoration-none">{{ $pensionType->name }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-fill"></i>
                    Edit Jenis Pensiun
                </div>
                <div class="card-body p-4">

                    <form action="{{ route('pension-types.update', $pensionType) }}" method="POST">
                        @csrf
                        @method('PUT')

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
                                value="{{ old('name', $pensionType->name) }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            >{{ old('description', $pensionType->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Icon --}}
                        <div class="mb-4">
                            <label for="icon" class="form-label fw-600">Icon Bootstrap Icons</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i id="icon-preview" class="bi {{ old('icon', $pensionType->icon ?? 'bi-award') }}"
                                       style="font-size:1.1rem;color:var(--primary)"></i>
                                </span>
                                <input
                                    type="text"
                                    id="icon"
                                    name="icon"
                                    class="form-control @error('icon') is-invalid @enderror"
                                    value="{{ old('icon', $pensionType->icon) }}"
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
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                @foreach(['bi-award','bi-person-badge','bi-file-earmark-person','bi-briefcase','bi-hospital','bi-shield-check','bi-clock-history','bi-people-fill'] as $ic)
                                    <button type="button" class="btn btn-outline-secondary btn-sm icon-pick {{ old('icon', $pensionType->icon) === $ic ? 'active' : '' }}"
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
                                       value="1" {{ old('is_active', $pensionType->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="is_active">
                                    Aktifkan jenis pensiun ini
                                </label>
                            </div>
                            <div class="form-text">Jenis pensiun yang tidak aktif tidak akan ditampilkan ke pengguna.</div>
                        </div>

                        {{-- Berkas Persyaratan info --}}
                        @php $templateCount = $pensionType->documentTemplates()->count(); @endphp
                        @if($templateCount > 0)
                            <div class="alert alert-info d-flex align-items-center gap-2 py-2" style="font-size:0.85rem">
                                <i class="bi bi-info-circle-fill flex-shrink-0"></i>
                                <span>
                                    Jenis pensiun ini memiliki <strong>{{ $templateCount }} berkas persyaratan</strong>.
                                    <a href="{{ route('pension-types.show', $pensionType) }}" class="alert-link">Lihat detail</a>
                                </span>
                            </div>
                        @endif

                        <hr class="my-4">

                        {{-- Actions --}}
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('pension-types.show', $pensionType) }}"
                               class="btn btn-outline-secondary d-flex align-items-center gap-2">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <form action="{{ route('pension-types.destroy', $pensionType) }}" method="POST"
                                  class="ms-auto"
                                  onsubmit="return confirm('Hapus \'{{ addslashes($pensionType->name) }}\'? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2">
                                    <i class="bi bi-trash-fill"></i> Hapus
                                </button>
                            </form>
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
