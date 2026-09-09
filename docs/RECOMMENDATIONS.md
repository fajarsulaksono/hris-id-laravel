# Rekomendasi Pengembangan Lanjutan (hris-id-laravel)

Dokumen ini memuat saran prioritas untuk pengembangan fitur berikutnya. Rekomendasi disusun dari
gap yang masih tercatat di `docs/TODO.md`, temuan review Fase 4, serta peluang produk dari modul
yang sudah ada. Semua proposal mengikuti konvensi proyek: modul baru didaftarkan di
`app/Support/MasterModules.php`, keamanan memakai hierarki role (`config/hris.php` →
`security.*_menu`), dan setiap perubahan diuji dengan feature test.

---

## Ringkasan prioritas

| Prioritas | Area | Ringkasan |
|---|---|---|
| 🔴 Tinggi | Payroll/Pajak | Perbaiki rantai bracket pajak & wire aturan benefit/history yang belum dipakai |
| 🔴 Tinggi | Approval cuti/lembur | Workflow persetujuan agar self-service & cuti benar-benar operasional |
| 🟠 Sedang | API (Fase 5) | Rute Sanctum + API Resource per domain (belum ada) |
| 🟠 Sedang | Notifikasi | Email saat approval / pengajuan |
| 🟡 Lanjut | Konfigurasi & User | Konfigurasi dapat diedit; audit log & 2FA akun |
| 🟡 Lanjut | Karyawan | Tampilkan keluarga/pendidikan/keahlian di profil; import CSV karyawan |
| 🟡 Lanjut | Hardening | Unit test calculator port, security review, seeder idempotent |

---

## 1. Menuntaskan gap payroll & pajak (Fase 4)

`docs/TODO.md` mencatat beberapa gap yang belum dibereskan dan berdampak ke akurasi perhitungan:

1. **Sambungkan rantai bracket pajak** — `AppServiceProvider` membuat empat kalkulator tarif tanpa
   `setPrevious()`. Akibatnya PKP ≥ Rp50 juta dihitung dengan tarif tunggal, bukan progresif.
   - **Rekomendasi**: wire `setPrevious()` (Fourth→Third→Second→First) + tambah *golden test* untuk
     PKP di 100 jt & 300 jt (multi-bracket), bukan hanya bracket 5%.
2. **Keputusan risk ratio JKK** — port memakai nilai nyata (0.0024–0.0174) sedangkan sumber
   SemartHris selalu 0.24%. Dokumentasikan sebagai perbaikan disengaja atau samakan dengan aslinya.
3. **`TaxGroupHistoryObserver`** hanya menangani `creating`; tambah `updating/updated` agar edit
   riwayat pajak lama menulis ulang `tax_group`/`risk_ratio` karyawan.
4. **Wire aturan domain yang belum terpakai** — `ValidateBenefit` (validasi benefit hanya pada
   kontrak aktif, blokir edit setelah payroll ada), `ChangeBenefit` (perubahan gaji lewat riwayat,
   bukan edit langsung `SalaryBenefit`), serta guard saat `new_benefit_value` kosong.
5. **Perluas pengujian numerik** — golden dataset multi-bracket & multi-risk sehingga regresi
   terdeteksi otomatis (lihat `PayrollWorkflowTest`).

## 2. Workflow approval cuti & lembur

Saat ini `Leave` tersimpan langsung (tanpa status), dan lembur hanya punya `approved_by_id`
(auto-approve). Agar alur SDM realistis:

- Tambahkan status pengajuan (mis. `draft/pending/approved/rejected`) pada cuti/izin.
- Buat antrean persetujuan berdasarkan hierarki supervisor (`supervisor_id` + rank role).
- Email notifikasi (opsional) saat diajukan & diputuskan.
- Perpanjang self-service: status pengajuan tampil di "Cuti & Izin" (dapat memanfaatkan
  `my.leaves` yang sudah ada).

## 3. API (Fase 5) & integrasi

Belum ada rute API (Sanctum + `API Resource`). Rekomendasi:

- Buat resource per domain: employee, attendance, leave, overtime, payroll.
- Filter parsial (`code/name/fullName/shortName`) & pagination (`p`, `ep`, `i`) mengikuti pola
  `DataTableServer`.
- Middleware role API + konteks perusahaan/karyawan (reuse `CheckRoleRank`, `Security`).
- Kasus penggunaan nyata: aplikasi mobile absensi / portal self-service.

## 4. Notifikasi email

- Kirim email saat pengajuan cuti/izin (ke atasan) dan saat lembur butuh persetujuan.
- Kirim pemberitahuan slip gaji tersedia.
- Manfaatkan `laravel/notifications` (sudah terpasang di `Employee` via `Notifiable`).

## 5. Penguatan Manajemen User & Konfigurasi

- **Manajemen User** (sudah ada list/edit role-password):
  - Audit log perubahan username/password/role (dengan `created_by`/`updated_by`).
  - MFA/2FA opsional untuk role tinggi.
  - Kunci akun setelah beberapa gagal login (rate limiting sudah tersedia di framework).
- **Konfigurasi** (saat ini read-only):
  - Tambahkan tabel `settings` atau antarmuka menulis nilai aman ke cache+`.env` dengan validasi,
    sehingga "atur" benar-benar tersimpan.
  - Pisahkan konfigurasi per-perusahaan bila diperlukan.

## 6. Karyawan & self-service

- **Profil**: tampilkan ringkasan keluarga/pendidikan/keahlian (data sudah ada sebagai modul CRUD)
  di halaman profil admin dan/atau `Data Saya`.
- **Import CSV karyawan** mirip pola import absensi/lembur yang sudah ada.
- **Data Saya**: tambahkan ubah kata sandi sendiri, unggah dokumen (medialibrary sudah siap), dan
  notifikasi status cuti.
- Tampilkan sisa cuti yang benar-benar terhitung (decrement saat cuti disetujui).

## 7. Hardening, kualitas & cutover (Fase 6)

- Port unit test calculator/processor lama menjadi PHPUnit (payroll, tax, attendance, overtime).
- Review keamanan: RSA keys jangan ikut ter-commit, permission & `Gate`, validasi upload.
- Optimasi query (hindari N+1 di list & profil), index untuk kolom yang sering difilter.
- Seeder idempotent (`firstOrCreate`) agar `migrate --seed` aman diulang.
- Dokumentasi regulasi BPJS/PPH21 pada kalkulator tetap dipertahankan.

## 8. Catatan teknis pengembangan

- Modul CRUD baru cukup: migration → model (+ accessor) → daftar di `MasterModules`
  (menu, kolom, searchable, order, fields) → otomatis dapat list/create/show/trash.
- Halaman khusus (report/action) ikuti pola controller `Admin\*` + view `admin/*` + `x-card`.
- Breadcrumb otomatis mengikuti route; untuk halaman baru pastikan masuk ke
  `app/Support/Breadcrumbs` bila trail-nya bukan menu standar.
- Jangan menambah dependensi tanpa persetujuan; pakai API versi terpasang (cek `composer show`).
- Setiap fitur baru wajib feature test (lihat pola `tests/Feature/*`).
