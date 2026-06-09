@extends('layouts.app')

@section('title', 'Edit Pengajuan #' . $application->id)
@section('page-title', 'Edit Pengajuan')

@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('applications.show', $application) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <h4 class="mb-0">
            <i class="bi bi-pencil-square me-2 text-primary"></i>
            Edit Pengajuan #{{ $application->id }}
        </h4>
    </div>
    <p class="text-muted mb-0" style="font-size:.85rem">
        Dibuat: {{ $application->created_at->translatedFormat('d F Y, H:i') }} WIB
    </p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- Info Box --}}
        <div class="alert alert-warning d-flex gap-2 align-items-start mb-4" style="border-radius:10px">
            <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
            <div style="font-size:.87rem">
                Pengajuan hanya dapat diedit selama masih berada di tahap
                <strong>Pengisian Form</strong>. Setelah diajukan ke tahap berikutnya,
                data tidak dapat diubah.
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-text me-2"></i>Formulir Edit Pengajuan
            </div>
            <div class="card-body p-4">
                <form action="{{ route('applications.update', $application) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Jenis Pensiun --}}
                    <div class="mb-4">
                        <label for="pension_type_id" class="form-label fw-semibold">
                            Jenis Pensiun <span class="text-danger">*</span>
                        </label>
                        <select id="pension_type_id" name="pension_type_id"
                                class="form-select @error('pension_type_id') is-invalid @enderror">
                            <option value="">— Pilih jenis pensiun —</option>
                            @foreach($pensionTypes as $type)
                                <option value="{{ $type->id }}"
                                    data-description="{{ $type->description }}"
                                    data-requirements="{{ $type->requirements_count ?? 0 }}"
                                    {{ old('pension_type_id', $application->pension_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('pension_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        {{-- Deskripsi jenis pensiun (dynamic) --}}
                        <div id="pension-type-info" class="mt-2" style="display:none">
                            <div class="p-3 rounded"
                                 style="background:#eef4fb;border-left:3px solid var(--accent);font-size:.85rem">
                                <div id="pension-type-desc" class="text-secondary mb-1"></div>
                                <div id="pension-type-req" class="text-primary fw-semibold"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal Rencana Pensiun --}}
                    <div class="mb-4">
                        <label for="pension_date" class="form-label fw-semibold">
                            Rencana Tanggal Pensiun <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="pension_date" name="pension_date"
                               class="form-control @error('pension_date') is-invalid @enderror"
                               value="{{ old('pension_date', $application->pension_date?->format('Y-m-d')) }}"
                               min="{{ now()->addDay()->format('Y-m-d') }}">
                        @error('pension_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Tanggal harus setelah hari ini</div>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">
                            Catatan Tambahan <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea id="notes" name="notes" rows="4"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Tuliskan informasi tambahan yang relevan dengan pengajuan Anda..."
                                  maxlength="1000">{{ old('notes', $application->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maksimal 1000 karakter</div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('applications.show', $application) }}"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle-fill me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
    const pensionSelect = document.getElementById('pension_type_id');
    const infoBox       = document.getElementById('pension-type-info');
    const descEl        = document.getElementById('pension-type-desc');
    const reqEl         = document.getElementById('pension-type-req');

    function updatePensionInfo() {
        const selected = pensionSelect.options[pensionSelect.selectedIndex];
        const desc     = selected.dataset.description;
        const req      = selected.dataset.requirements;

        if (pensionSelect.value && desc) {
            descEl.textContent = desc;
            reqEl.textContent  = req + ' berkas persyaratan diperlukan';
            infoBox.style.display = 'block';
        } else {
            infoBox.style.display = 'none';
        }
    }

    pensionSelect.addEventListener('change', updatePensionInfo);
    // Trigger on page load untuk tampilkan info jenis pensiun yang sudah dipilih
    if (pensionSelect.value) updatePensionInfo();
</script>
@endsection
