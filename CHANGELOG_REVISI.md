# Changelog Revisi Pensiunku

## Versi 2.0 — Fitur Verifikasi Dua Tahap, Template Dokumen, Integrasi Link, dan Infografis SOP

### Ringkasan Perubahan

Implementasi 6 fitur baru sesuai Spesifikasi Perubahan Sistem Pensiunku.

---

### Fitur 1 — Format Contoh Dokumen Persyaratan
- Tombol "Lihat Contoh" dan "Unduh Contoh" sekarang tampil jelas di halaman detail pengajuan untuk semua role.
- Role Calon Pensiunan dapat melihat contoh format dokumen tanpa perlu akses tambahan.

### Fitur 2 — Checklist Verifikasi KPKNL Pelayanan (sdm_kantor)
- Tahap baru: **Verifikasi KPKNL Pelayanan** (antara Upload dan Verifikasi Kanwil).
- Setiap dokumen dapat dicek dengan 3 status: Lengkap & Sesuai / Lengkap tapi Tidak Sesuai / Tidak Lengkap.
- Catatan wajib diisi jika status bukan "Lengkap & Sesuai".
- Badge ringkasan menunjukkan jumlah dokumen bermasalah.
- Setelah semua dokumen dicek, petugas dapat: (a) Ajukan ke DJKN Kanwil, atau (b) Kembalikan ke Upload.

### Fitur 3 — Double Check DJKN Kanwil (sdm_kanwil)
- Tahap baru: **Verifikasi DJKN Kanwil** (setelah Verifikasi KPKNL).
- Setiap dokumen dicek dengan 2 status: Sesuai / Tidak Sesuai + catatan wajib jika Tidak Sesuai.
- Pengajuan hanya bisa di-ACC setelah semua dokumen berstatus "Sesuai".

### Fitur 4 — Tombol Link SI ASN BKN
- Tombol "Ajukan ke SI ASN BKN" muncul di header halaman detail pengajuan untuk role sdm_kanwil.
- URL dikonfigurasi via `.env`: `SIASN_BKN_URL=https://siasn.bkn.go.id`

### Fitur 5 — Link Folder Microsoft Teams
- Tombol "Buka Folder Teams" muncul di header halaman detail pengajuan untuk role sdm_kantor & sdm_kanwil.
- URL folder Teams disimpan per kantor di tabel `offices` (database).
- Jalankan seeder: `php artisan db:seed --class=OfficeSeeder` lalu update URL via database/Tinker.

### Fitur 6 — Infografis SOP di Dashboard Pensiunan
- Gambar/poster SOP ditampilkan di dashboard role Calon Pensiunan.
- Konfigurasi path gambar via `.env`: `SOP_IMAGE_PATH=images/sop-alur-pensiun.jpg`
- Simpan file gambar ke folder `public/images/`.

---

### Perubahan Database

```sql
-- 3 migration baru (jalankan: php artisan migrate)

-- 1. Kolom baru di tabel `documents`:
--    kantor_check_status, kantor_check_note, kantor_checked_by, kantor_checked_at, kanwil_status

-- 2. Enum `status` di tabel `applications` ditambah 2 nilai:
--    'verifikasi_kpknl', 'verifikasi_kanwil'
--    (menggantikan 'verifikasi' yang sebelumnya 1 tahap)
--    Total tahap: 6 → 7

-- 3. Tabel baru `offices`:
--    id, name, type (kpknl/kanwil), teams_folder_url, is_active
```

### Penamaan Role (label tampilan saja — kode tidak berubah)

| Kode (tetap) | Label baru |
|---|---|
| sdm_kantor | Staff KPKNL Pelayanan |
| sdm_kanwil | Staff DJKN Kanwil |
| tik | Kepala TIK |
| pensiunan | Calon Pensiunan |

### Cara Deploy

```bash
# 1. Jalankan migrasi
php artisan migrate

# 2. Jalankan seeder kantor (lalu update URL Teams via database)
php artisan db:seed --class=OfficeSeeder

# 3. Tambahkan variabel .env berikut:
SIASN_BKN_URL=https://siasn.bkn.go.id   # ganti dengan URL resmi klien
SOP_IMAGE_PATH=images/sop-alur-pensiun.jpg  # path relatif dari public/

# 4. Salin file gambar SOP ke:
#    public/images/sop-alur-pensiun.jpg

# 5. Bersihkan cache
php artisan config:clear
php artisan view:clear
```

### Input yang Masih Diperlukan dari Klien

- [ ] URL resmi SI ASN BKN → untuk `SIASN_BKN_URL` di `.env`
- [ ] File gambar/poster infografis SOP → simpan ke `public/images/`
- [ ] Daftar nama kantor KPKNL & Kanwil beserta URL folder Teams → update tabel `offices`
- [ ] File contoh format dokumen per jenis (PDF) → upload via menu yang sudah tersedia
