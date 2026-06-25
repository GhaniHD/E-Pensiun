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

    <form action="{{ route('pension-types.store') }}" method="POST">
        @csrf

        <div class="row g-4">

            {{-- ── KOLOM KIRI: Info Utama ──────────────────── --}}
            <div class="col-12 col-lg-5">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-info-circle-fill me-2"></i>Informasi Jenis Pensiun
                    </div>
                    <div class="card-body p-4">

                        {{-- Nama --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">
                                Nama Jenis Pensiun <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: Pensiun Batas Usia" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Slug --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Slug (URL) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text text-muted" style="font-size:0.8rem">/jenis-pensiun/</span>
                                <input type="text" id="slug" name="slug"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}"
                                       placeholder="pensiun-batas-usia" required>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">Otomatis terisi dari nama.</div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Deskripsi</label>
                            <textarea name="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Penjelasan singkat tentang jenis pensiun ini...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Icon --}}
                        <div class="mb-3">
                            <label class="form-label fw-600">Icon</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i id="icon-preview" class="bi bi-award" style="font-size:1.1rem;color:var(--primary)"></i>
                                </span>
                                <input type="text" id="icon" name="icon"
                                       class="form-control @error('icon') is-invalid @enderror"
                                       value="{{ old('icon', 'bi-award') }}" placeholder="bi-award">
                            </div>
                            <div class="mt-2 d-flex gap-1 flex-wrap">
                                @foreach(['bi-award','bi-person-badge','bi-file-earmark-person','bi-briefcase','bi-hospital','bi-shield-check','bi-clock-history','bi-people-fill'] as $ic)
                                    <button type="button" class="btn btn-outline-secondary btn-sm icon-pick"
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
                                       {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="is_active">
                                    Aktifkan jenis pensiun ini
                                </label>
                            </div>
                        </div>

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

                        <p class="text-muted mb-3" style="font-size:0.83rem">
                            <i class="bi bi-info-circle me-1"></i>
                            Tambahkan daftar berkas yang harus disiapkan calon pensiunan.
                            Klik <strong>+ Tambah Berkas</strong> untuk menambah baris baru.
                        </p>

                        {{-- Container baris berkas --}}
                        <div id="templateList" class="d-flex flex-column gap-2 mb-3">
                            {{-- Baris diisi via JS --}}
                        </div>

                        {{-- Tombol tambah --}}
                        <button type="button" id="btnAddTemplate"
                                class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Berkas
                        </button>

                        {{-- Info --}}
                        <div class="mt-3 p-2 rounded" style="background:#f8f9fa;font-size:0.78rem;color:#6c757d">
                            <i class="bi bi-lightbulb-fill me-1 text-warning"></i>
                            <strong>Tips:</strong> Tandai berkas sebagai <em>Wajib</em> jika harus dilampirkan.
                            Berkas opsional tetap dianjurkan namun tidak memblokir proses verifikasi.
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ── TOMBOL SIMPAN ────────────────────────────── --}}
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> Simpan Jenis Pensiun
            </button>
            <a href="{{ route('pension-types.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-x-circle"></i> Batal
            </a>
        </div>

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
    .template-row .drag-handle {
        cursor: grab;
        color: #adb5bd;
        font-size: 1rem;
        padding: 0 4px;
    }
    .badge-wajib   { background: #C0392B; font-size: 0.65rem; }
    .badge-opsional { background: #6c757d; font-size: 0.65rem; }
</style>
@endsection

@section('scripts')
<script>
    // ── Auto slug dari nama ────────────────────────────────
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    let slugEdited  = slugInput.value.length > 0;

    nameInput.addEventListener('input', function () {
        if (!slugEdited) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
        }
    });
    slugInput.addEventListener('input', () => slugEdited = slugInput.value.length > 0);

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
                    <div class="form-check form-switch mb-0" title="Wajib / Opsional">
                        <input class="form-check-input required-toggle" type="checkbox"
                               name="templates[${i}][is_required]" value="1"
                               ${data.is_required !== false ? 'checked' : ''}
                               id="req_${i}">
                        <label class="form-check-label" for="req_${i}" style="font-size:0.72rem;white-space:nowrap">
                            Wajib
                        </label>
                    </div>
                    <button type="button" class="btn btn-link btn-sm text-danger p-0 btn-remove-row" title="Hapus">
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

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    document.getElementById('btnAddTemplate').addEventListener('click', () => {
        templateList.appendChild(createTemplateRow());
        updateCount();
        // Focus ke input nama berkas yang baru
        const inputs = templateList.querySelectorAll('input[name$="[document_name]"]');
        inputs[inputs.length - 1].focus();
    });

    // ── Preload dari old() jika ada validasi error ─────────
    @if(old('templates'))
        @foreach(old('templates') as $idx => $tpl)
            templateList.appendChild(createTemplateRow({
                document_name: @json($tpl['document_name'] ?? ''),
                description:   @json($tpl['description'] ?? ''),
                is_required:   {{ isset($tpl['is_required']) ? 'true' : 'false' }},
            }));
        @endforeach
    @else
        // Tambahkan 3 baris kosong sebagai starter
        for (let x = 0; x < 3; x++) templateList.appendChild(createTemplateRow());
    @endif

    updateCount();
</script>
@endsection
