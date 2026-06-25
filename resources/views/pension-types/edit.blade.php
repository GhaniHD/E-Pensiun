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

    <form action="{{ route('pension-types.update', $pensionType) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ── KOLOM KIRI: Info Utama ──────────────────── --}}
            <div class="col-12 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-pencil-fill me-2"></i>Informasi Jenis Pensiun
                    </div>
                    <div class="card-body p-4">

                        {{-- Nama --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">
                                Nama Jenis Pensiun <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $pensionType->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Deskripsi</label>
                            <textarea name="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Penjelasan singkat...">{{ old('description', $pensionType->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Icon --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Icon</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i id="icon-preview" class="bi {{ old('icon', $pensionType->icon ?? 'bi-award') }}"
                                       style="font-size:1.1rem;color:var(--primary)"></i>
                                </span>
                                <input type="text" id="icon" name="icon"
                                       class="form-control @error('icon') is-invalid @enderror"
                                       value="{{ old('icon', $pensionType->icon) }}" placeholder="bi-award">
                            </div>
                            <div class="mt-2 d-flex gap-1 flex-wrap">
                                @foreach(['bi-award','bi-person-badge','bi-file-earmark-person','bi-briefcase','bi-hospital','bi-shield-check','bi-clock-history','bi-people-fill'] as $ic)
                                    <button type="button" class="btn btn-outline-secondary btn-sm icon-pick {{ old('icon', $pensionType->icon) === $ic ? 'active' : '' }}"
                                            data-icon="{{ $ic }}" title="{{ $ic }}">
                                        <i class="bi {{ $ic }}"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Status aktif --}}
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active"
                                       name="is_active" value="1"
                                       {{ old('is_active', $pensionType->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="is_active">
                                    Aktifkan jenis pensiun ini
                                </label>
                            </div>
                        </div>

                        {{-- Info pengajuan terkait --}}
                        @if($pensionType->applications()->count() > 0)
                            <div class="alert alert-warning py-2 mb-0" style="font-size:0.82rem">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                Jenis pensiun ini memiliki
                                <strong>{{ $pensionType->applications()->count() }} pengajuan aktif</strong>.
                                Perubahan berkas akan berlaku untuk pengajuan baru saja.
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- ── KOLOM KANAN: Berkas Persyaratan ────────── --}}
            <div class="col-12 col-lg-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-file-earmark-ruled-fill me-2"></i>Berkas Persyaratan</span>
                        <span class="badge bg-secondary" id="templateCount">0 berkas</span>
                    </div>
                    <div class="card-body p-3">

                        <div class="alert alert-info py-2 mb-3" style="font-size:0.82rem">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Perubahan di sini akan <strong>mengganti seluruh daftar berkas</strong>.
                            Hapus yang tidak diperlukan, tambah yang baru.
                        </div>

                        {{-- Container baris berkas --}}
                        <div id="templateList" class="d-flex flex-column gap-2 mb-3">
                            {{-- Diisi via JS --}}
                        </div>

                        {{-- Tombol tambah --}}
                        <button type="button" id="btnAddTemplate"
                                class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Berkas
                        </button>

                        <div class="mt-3 p-2 rounded" style="background:#f8f9fa;font-size:0.78rem;color:#6c757d">
                            <i class="bi bi-lightbulb-fill me-1 text-warning"></i>
                            <strong>Tips:</strong> Urutan berkas sesuai urutan di daftar ini (atas = pertama).
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ── TOMBOL SIMPAN ────────────────────────────── --}}
        <div class="d-flex gap-2 mt-4 align-items-center">
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> Simpan Perubahan
            </button>
            <a href="{{ route('pension-types.show', $pensionType) }}"
               class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-x-circle"></i> Batal
            </a>
            <div class="ms-auto">
                <button type="button" id="btnHapus"
                        class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-trash-fill"></i> Hapus Jenis Pensiun Ini
                </button>
            </div>
        </div>

    </form>

    {{-- Form DELETE di LUAR form UPDATE —— cegah nested form --}}
    <form id="deleteForm" action="{{ route('pension-types.destroy', $pensionType) }}"
          method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>

@endsection

@section('styles')
<style>
    .fw-600 { font-weight: 600; }
    .icon-pick.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .template-row {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .template-row:hover { border-color: var(--accent); }
    .drag-handle { cursor: grab; color: #adb5bd; font-size: 1rem; padding: 0 4px; }
</style>
@endsection

@section('scripts')
<script>
    // ── Icon preview ───────────────────────────────────────
    const iconInput   = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');
    iconInput.addEventListener('input', () => iconPreview.className = 'bi ' + iconInput.value);
    document.querySelectorAll('.icon-pick').forEach(btn => {
        btn.addEventListener('click', function () {
            const ic = this.dataset.icon;
            iconInput.value = ic;
            iconPreview.className = 'bi ' + ic;
            document.querySelectorAll('.icon-pick').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ── Template berkas (dynamic rows) ────────────────────
    const templateList  = document.getElementById('templateList');
    const templateCount = document.getElementById('templateCount');
    let rowIndex = 0;

    function updateCount() {
        const n = templateList.querySelectorAll('.template-row').length;
        templateCount.textContent = n + ' berkas';
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function createTemplateRow(data = {}) {
        const i   = rowIndex++;
        const row = document.createElement('div');
        row.className = 'template-row';
        row.innerHTML = `
            <div class="d-flex align-items-start gap-2">
                <span class="drag-handle mt-2"><i class="bi bi-grip-vertical"></i></span>
                <div style="flex:1">
                    <input type="text"
                           name="templates[${i}][document_name]"
                           class="form-control form-control-sm mb-1"
                           placeholder="Nama berkas, contoh: Fotokopi SK CPNS"
                           value="${escHtml(data.document_name || '')}"
                           required>
                    <input type="text"
                           name="templates[${i}][description]"
                           class="form-control form-control-sm"
                           placeholder="Keterangan singkat (opsional)"
                           value="${escHtml(data.description || '')}">
                </div>
                <div class="d-flex flex-column align-items-center gap-1 ms-1">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox"
                               name="templates[${i}][is_required]" value="1"
                               ${data.is_required !== false ? 'checked' : ''}
                               id="req_${i}">
                        <label class="form-check-label" for="req_${i}" style="font-size:0.72rem;white-space:nowrap">
                            Wajib
                        </label>
                    </div>
                    <button type="button" class="btn btn-link btn-sm text-danger p-0 btn-remove-row" title="Hapus baris">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `;
        row.querySelector('.btn-remove-row').addEventListener('click', () => {
            row.remove();
            updateCount();
        });
        return row;
    }

    document.getElementById('btnAddTemplate').addEventListener('click', () => {
        templateList.appendChild(createTemplateRow());
        updateCount();
        const inputs = templateList.querySelectorAll('input[name$="[document_name]"]');
        inputs[inputs.length - 1].focus();
    });

    // ── Load data existing dari database ──────────────────
    @if(old('templates'))
        {{-- Jika ada validasi error, pakai old() --}}
        @foreach(old('templates') as $idx => $tpl)
            templateList.appendChild(createTemplateRow({
                document_name: @json($tpl['document_name'] ?? ''),
                description:   @json($tpl['description'] ?? ''),
                is_required:   {{ isset($tpl['is_required']) ? 'true' : 'false' }},
            }));
        @endforeach
    @else
        {{-- Load dari database --}}
        @foreach($templates as $tpl)
            templateList.appendChild(createTemplateRow({
                document_name: @json($tpl->document_name),
                description:   @json($tpl->description ?? ''),
                is_required:   {{ $tpl->is_required ? 'true' : 'false' }},
            }));
        @endforeach
        {{-- Jika belum ada berkas sama sekali, tambah 1 baris kosong --}}
        @if($templates->isEmpty())
            templateList.appendChild(createTemplateRow());
        @endif
    @endif

    updateCount();

    // ── Tombol hapus (form DELETE terpisah) ───────────────
    document.getElementById('btnHapus').addEventListener('click', function () {
        if (confirm('Hapus "{{ addslashes($pensionType->name) }}"?\nSemua berkas persyaratan akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.')) {
            document.getElementById('deleteForm').submit();
        }
    });
</script>
@endsection
