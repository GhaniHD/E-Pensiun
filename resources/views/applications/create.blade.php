@extends('layouts.app')

@section('title', 'Buat Pengajuan Pensiun')
@section('page-title', 'Buat Pengajuan Baru')

@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('applications.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <h4 class="mb-0"><i class="bi bi-plus-circle-fill me-2 text-primary"></i>Buat Pengajuan Pensiun</h4>
    </div>
    <p class="text-muted mb-0" style="font-size:.85rem">
        Isi formulir di bawah ini untuk mengajukan permohonan pensiun
    </p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Info Box --}}
        <div class="alert alert-info d-flex gap-2 align-items-start mb-4" style="border-radius:10px">
            <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
            <div style="font-size:.87rem">
                Setelah pengajuan dibuat, Anda akan diarahkan ke halaman detail untuk melengkapi
                berkas persyaratan. Pastikan semua dokumen telah disiapkan sebelum mengajukan.
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-plus me-2"></i>Formulir Pengajuan
            </div>
            <div class="card-body p-4">
                <form action="{{ route('applications.store') }}" method="POST">
                    @csrf

                    {{-- ══ BAGIAN 1: DATA PENGAJUAN DASAR ══ --}}
                    <h6 class="fw-semibold text-primary mb-3">
                        <i class="bi bi-clipboard-data me-2"></i>Data Pengajuan
                    </h6>

                    {{-- Pilih Pegawai --}}
                    <div class="mb-4">
                        <label for="user_id" class="form-label fw-semibold">
                            Nama Pegawai <span class="text-danger">*</span>
                        </label>
                        <select id="user_id" name="user_id"
                                class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">— Pilih pegawai —</option>
                            @foreach($pensiunanUsers as $pegawai)
                                <option value="{{ $pegawai->id }}"
                                    data-nama="{{ $pegawai->name }}"
                                    data-nip="{{ $pegawai->nip ?? '' }}"
                                    data-kantor="{{ $pegawai->office ?? '' }}"
                                    {{ old('user_id') == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->name }} — {{ $pegawai->nip ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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
                                    {{ old('pension_type_id', request('pension_type_id')) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('pension_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                            Rencana Tanggal Pensiun / TMT Pensiun <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="pension_date" name="pension_date"
                               class="form-control @error('pension_date') is-invalid @enderror"
                               value="{{ old('pension_date') }}"
                               min="{{ now()->addDay()->format('Y-m-d') }}">
                        @error('pension_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Akan terisi otomatis dari kalkulator di bawah, atau isi manual</div>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">
                            Catatan Tambahan <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Tuliskan informasi tambahan yang relevan..."
                                  maxlength="1000">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maksimal 1000 karakter</div>
                    </div>

                    {{-- ══ BAGIAN 2: DATA CALON PENSIUNAN + KALKULATOR (KPKNL & KANWIL) ══ --}}
                    @if(auth()->user()->isSdmKantor() || auth()->user()->isSdmKanwil())

                    <hr class="my-4">
                    <h6 class="fw-semibold text-primary mb-3">
                        <i class="bi bi-person-vcard me-2"></i>Data Calon Pensiunan
                    </h6>

                    <div class="row g-3 mb-3">
                        {{-- Nama Calon Pensiunan --}}
                        <div class="col-md-6">
                            <label for="nama_calon_pensiunan" class="form-label fw-semibold">
                                Nama Calon Pensiunan
                            </label>
                            <input type="text" id="nama_calon_pensiunan" name="nama_calon_pensiunan"
                                   class="form-control @error('nama_calon_pensiunan') is-invalid @enderror"
                                   value="{{ old('nama_calon_pensiunan') }}"
                                   placeholder="Akan terisi otomatis dari pilihan pegawai">
                            @error('nama_calon_pensiunan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- NIP --}}
                        <div class="col-md-6">
                            <label for="nip_calon_pensiunan" class="form-label fw-semibold">NIP</label>
                            <input type="text" id="nip_calon_pensiunan" name="nip_calon_pensiunan"
                                   class="form-control @error('nip_calon_pensiunan') is-invalid @enderror"
                                   value="{{ old('nip_calon_pensiunan') }}"
                                   maxlength="25"
                                   placeholder="18 digit NIP">
                            @error('nip_calon_pensiunan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- Unit Kerja / Kantor --}}
                        <div class="col-md-6">
                            <label for="unit_kerja" class="form-label fw-semibold">Unit Kerja / Kantor</label>
                            <input type="text" id="unit_kerja" name="unit_kerja"
                                   class="form-control @error('unit_kerja') is-invalid @enderror"
                                   value="{{ old('unit_kerja') }}"
                                   placeholder="Nama kantor/unit kerja">
                            @error('unit_kerja')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div class="col-md-6">
                            <label for="tanggal_lahir" class="form-label fw-semibold">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                                   class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                   value="{{ old('tanggal_lahir') }}">
                            @error('tanggal_lahir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Digunakan untuk menghitung TMT Pensiun</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        {{-- Jenis Pensiun BKN --}}
                        <div class="col-md-6">
                            <label for="jenis_pensiun_bkn" class="form-label fw-semibold">
                                Jenis Pensiun (Kategori BKN)
                            </label>
                            <select id="jenis_pensiun_bkn" name="jenis_pensiun_bkn"
                                    class="form-select @error('jenis_pensiun_bkn') is-invalid @enderror">
                                <option value="">— Pilih —</option>
                                <option value="BUP"       {{ old('jenis_pensiun_bkn') === 'BUP'       ? 'selected' : '' }}>BUP — Batas Usia Pensiun</option>
                                <option value="APS"       {{ old('jenis_pensiun_bkn') === 'APS'       ? 'selected' : '' }}>APS — Atas Permintaan Sendiri</option>
                                <option value="Janda/Duda"{{ old('jenis_pensiun_bkn') === 'Janda/Duda'? 'selected' : '' }}>Janda / Duda</option>
                                <option value="Cacat"     {{ old('jenis_pensiun_bkn') === 'Cacat'     ? 'selected' : '' }}>Cacat / Tidak Mampu Bekerja</option>
                                <option value="Meninggal" {{ old('jenis_pensiun_bkn') === 'Meninggal' ? 'selected' : '' }}>Meninggal Dunia</option>
                            </select>
                            @error('jenis_pensiun_bkn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kenaikan Pangkat --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kenaikan Pangkat</label>
                            <div class="d-flex gap-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="kenaikan_pangkat"
                                           id="kp_ya" value="1"
                                           {{ old('kenaikan_pangkat') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="kp_ya">Ya, Naik Pangkat</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="kenaikan_pangkat"
                                           id="kp_tidak" value="0"
                                           {{ old('kenaikan_pangkat') === '0' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="kp_tidak">Tidak</label>
                                </div>
                            </div>
                            @error('kenaikan_pangkat')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ══ KALKULATOR MASA KERJA ══ --}}
                    <hr class="my-4">
                    <h6 class="fw-semibold text-primary mb-1">
                        <i class="bi bi-calculator me-2"></i>Kalkulator Masa Kerja
                    </h6>
                    <p class="text-muted mb-3" style="font-size:.82rem">
                        Isi kolom input di bawah — hasil masa kerja akan terhitung otomatis dan tersimpan bersama pengajuan.
                    </p>

                    {{-- Input: TMT & usia pensiun --}}
                    <div class="p-3 mb-3 rounded-3" style="background:#f8f9fc;border:1px solid #dee2e6">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="tmt_cpns" class="form-label fw-semibold" style="font-size:.85rem">
                                    TMT CPNS <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="tmt_cpns" name="tmt_cpns"
                                       class="form-control form-control-sm @error('tmt_cpns') is-invalid @enderror"
                                       value="{{ old('tmt_cpns') }}">
                                @error('tmt_cpns') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="tmt_pns" class="form-label fw-semibold" style="font-size:.85rem">
                                    TMT PNS <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="tmt_pns" name="tmt_pns"
                                       class="form-control form-control-sm @error('tmt_pns') is-invalid @enderror"
                                       value="{{ old('tmt_pns') }}">
                                @error('tmt_pns') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="tmt_pangkat_terakhir" class="form-label fw-semibold" style="font-size:.85rem">
                                    TMT Pangkat Terakhir <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="tmt_pangkat_terakhir" name="tmt_pangkat_terakhir"
                                       class="form-control form-control-sm @error('tmt_pangkat_terakhir') is-invalid @enderror"
                                       value="{{ old('tmt_pangkat_terakhir') }}">
                                @error('tmt_pangkat_terakhir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            {{-- MK KP Terakhir (input manual) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem">
                                    Masa Kerja KP Terakhir <span class="text-muted fw-normal">(input manual dari SK)</span>
                                </label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="number" id="mk_kp_terakhir_tahun" name="mk_kp_terakhir_tahun"
                                           class="form-control form-control-sm @error('mk_kp_terakhir_tahun') is-invalid @enderror"
                                           value="{{ old('mk_kp_terakhir_tahun', 0) }}"
                                           min="0" max="99" style="width:80px">
                                    <span class="text-muted" style="font-size:.85rem">th</span>
                                    <input type="number" id="mk_kp_terakhir_bulan" name="mk_kp_terakhir_bulan"
                                           class="form-control form-control-sm @error('mk_kp_terakhir_bulan') is-invalid @enderror"
                                           value="{{ old('mk_kp_terakhir_bulan', 0) }}"
                                           min="0" max="11" style="width:80px">
                                    <span class="text-muted" style="font-size:.85rem">bln</span>
                                </div>
                                <div class="form-text">Dari SK kenaikan pangkat terakhir</div>
                            </div>

                            {{-- Usia Pensiun --}}
                            <div class="col-md-3">
                                <label for="usia_pensiun" class="form-label fw-semibold" style="font-size:.85rem">
                                    Batas Usia Pensiun
                                </label>
                                <select id="usia_pensiun" name="usia_pensiun"
                                        class="form-select form-select-sm @error('usia_pensiun') is-invalid @enderror">
                                    <option value="58" {{ old('usia_pensiun', '58') === '58' ? 'selected' : '' }}>58 Tahun (Umum)</option>
                                    <option value="60" {{ old('usia_pensiun') === '60' ? 'selected' : '' }}>60 Tahun (Pejabat Tertentu)</option>
                                </select>
                            </div>

                            {{-- Tombol Hitung --}}
                            <div class="col-md-3">
                                <button type="button" id="btn-hitung" class="btn btn-primary w-100"
                                        onclick="hitungMasaKerja()">
                                    <i class="bi bi-calculator-fill me-1"></i>Hitung
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Hasil Kalkulator --}}
                    <div id="hasil-kalkulator" style="display:none">
                        <div class="p-3 rounded-3 mb-3"
                             style="background:#eaf7f0;border:1px solid #b7e4cc">
                            <div class="fw-semibold mb-2" style="font-size:.88rem;color:#1a6640">
                                <i class="bi bi-check-circle-fill me-1"></i>Hasil Perhitungan Masa Kerja
                            </div>
                            <div class="row g-2">
                                <div class="col-sm-6 col-lg-3">
                                    <div class="p-2 rounded text-center" style="background:#fff;border:1px solid #c3e6cb">
                                        <div class="text-muted mb-1" style="font-size:.75rem">TMT Pensiun</div>
                                        <div class="fw-bold" id="tmt_pensiun_display" style="font-size:.88rem;color:#1a6640">—</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="p-2 rounded text-center" style="background:#fff;border:1px solid #c3e6cb">
                                        <div class="text-muted mb-1" style="font-size:.75rem">Masa Kerja Pensiun</div>
                                        <div class="fw-bold" id="mk_pensiun_display" style="font-size:.88rem;color:#1a6640">—</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="p-2 rounded text-center" style="background:#fff;border:1px solid #c3e6cb">
                                        <div class="text-muted mb-1" style="font-size:.75rem">Masa Kerja PNS</div>
                                        <div class="fw-bold" id="mk_pns_display" style="font-size:.88rem;color:#1a6640">—</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="p-2 rounded text-center" style="background:#fff;border:1px solid #c3e6cb">
                                        <div class="text-muted mb-1" style="font-size:.75rem">Masa Kerja Golongan</div>
                                        <div class="fw-bold" id="mk_golongan_display" style="font-size:.88rem;color:#1a6640">—</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 text-muted" style="font-size:.78rem">
                                <i class="bi bi-info-circle me-1"></i>
                                Masa Kerja dari Pangkat Terakhir: <span id="mk_dari_pangkat_display" class="fw-semibold">—</span>
                            </div>
                        </div>

                        {{-- Hidden fields: simpan hasil kalkulator --}}
                        <input type="hidden" id="mk_pensiun_tahun"  name="mk_pensiun_tahun">
                        <input type="hidden" id="mk_pensiun_bulan"  name="mk_pensiun_bulan">
                        <input type="hidden" id="mk_pns_tahun"      name="mk_pns_tahun">
                        <input type="hidden" id="mk_pns_bulan"      name="mk_pns_bulan">
                        <input type="hidden" id="mk_golongan_tahun" name="mk_golongan_tahun">
                        <input type="hidden" id="mk_golongan_bulan" name="mk_golongan_bulan">
                    </div>

                    @endif
                    {{-- ══ END KPKNL/KANWIL SECTION ══ --}}

                    <hr class="my-4">

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send-fill me-1"></i>Buat Pengajuan
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
// ── Pension type info ────────────────────────────────────────────
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
if (pensionSelect.value) updatePensionInfo();

// ── Auto-fill nama & NIP dari pilihan pegawai ────────────────────
const userSelect = document.getElementById('user_id');
if (userSelect) {
    userSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const namaEl   = document.getElementById('nama_calon_pensiunan');
        const nipEl    = document.getElementById('nip_calon_pensiunan');
        const kantorEl = document.getElementById('unit_kerja');
        if (namaEl   && opt.dataset.nama)   namaEl.value   = opt.dataset.nama;
        if (nipEl    && opt.dataset.nip)    nipEl.value    = opt.dataset.nip;
        if (kantorEl && opt.dataset.kantor) kantorEl.value = opt.dataset.kantor;
    });
}

// ── Kalkulator Masa Kerja ────────────────────────────────────────
function calcMK(start, end) {
    let years  = end.getFullYear()  - start.getFullYear();
    let months = end.getMonth()     - start.getMonth();
    if (months < 0) { years--; months += 12; }
    return { tahun: years, bulan: months };
}

function formatTanggal(date) {
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function hitungMasaKerja() {
    const tglLahirVal    = document.getElementById('tanggal_lahir')?.value;
    const usiaPensiun    = parseInt(document.getElementById('usia_pensiun')?.value) || 58;
    const tmtCpnsVal     = document.getElementById('tmt_cpns')?.value;
    const tmtPnsVal      = document.getElementById('tmt_pns')?.value;
    const tmtPangkatVal  = document.getElementById('tmt_pangkat_terakhir')?.value;
    const mkKpTahun      = parseInt(document.getElementById('mk_kp_terakhir_tahun')?.value) || 0;
    const mkKpBulan      = parseInt(document.getElementById('mk_kp_terakhir_bulan')?.value) || 0;

    if (!tglLahirVal) {
        alert('Isi Tanggal Lahir terlebih dahulu.');
        return;
    }

    // TMT Pensiun = tanggal 1 bulan setelah bulan ulang tahun ke-[usia_pensiun]
    // Contoh: lahir 27 April 1968, BUP 58 → ulang tahun April 2026 → TMT = 1 Mei 2026
    const lahir      = new Date(tglLahirVal);
    const tmtPensiun = new Date(lahir.getFullYear() + usiaPensiun, lahir.getMonth() + 1, 1);

    // Set pension_date
    document.getElementById('pension_date').value = tmtPensiun.toISOString().split('T')[0];
    document.getElementById('tmt_pensiun_display').textContent = formatTanggal(tmtPensiun);

    let mkDariPangkatBulan = 0;

    // Masa Kerja dari Pangkat Terakhir s.d. TMT Pensiun
    if (tmtPangkatVal) {
        const mk = calcMK(new Date(tmtPangkatVal), tmtPensiun);
        document.getElementById('mk_dari_pangkat_display').textContent =
            mk.tahun + ' th ' + mk.bulan + ' bln';
        mkDariPangkatBulan = mk.tahun * 12 + mk.bulan;
    }

    // Masa Kerja Golongan = MK KP Terakhir (manual) + MK dari Pangkat Terakhir
    const totalGolBulan = (mkKpTahun * 12 + mkKpBulan) + mkDariPangkatBulan;
    const mkGol = { tahun: Math.floor(totalGolBulan / 12), bulan: totalGolBulan % 12 };
    document.getElementById('mk_golongan_display').textContent =
        mkGol.tahun + ' th ' + mkGol.bulan + ' bln';
    document.getElementById('mk_golongan_tahun').value = mkGol.tahun;
    document.getElementById('mk_golongan_bulan').value = mkGol.bulan;

    // Masa Kerja PNS (tidak dihitung masa CPNS) = TMT PNS → TMT Pensiun
    if (tmtPnsVal) {
        const mk = calcMK(new Date(tmtPnsVal), tmtPensiun);
        document.getElementById('mk_pns_display').textContent =
            mk.tahun + ' th ' + mk.bulan + ' bln';
        document.getElementById('mk_pns_tahun').value = mk.tahun;
        document.getElementById('mk_pns_bulan').value = mk.bulan;
    }

    // Masa Kerja Pensiun = TMT CPNS → TMT Pensiun
    if (tmtCpnsVal) {
        const mk = calcMK(new Date(tmtCpnsVal), tmtPensiun);
        document.getElementById('mk_pensiun_display').textContent =
            mk.tahun + ' th ' + mk.bulan + ' bln';
        document.getElementById('mk_pensiun_tahun').value = mk.tahun;
        document.getElementById('mk_pensiun_bulan').value = mk.bulan;
    }

    document.getElementById('hasil-kalkulator').style.display = 'block';
}

// Auto-hitung jika ada old() value (validasi gagal, page reload)
(function () {
    const tglLahir = document.getElementById('tanggal_lahir');
    if (tglLahir && tglLahir.value) hitungMasaKerja();
})();
</script>
@endsection
