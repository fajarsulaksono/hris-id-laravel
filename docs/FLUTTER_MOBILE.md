# Flutter Mobile Apps — Plan & Rekomendasi Arsitektur

Dokumen ini adalah rencana pengembangan aplikasi mobile (Flutter) sebagai pendamping web
HRIS. Kelayakan sudah diverifikasi: backend API yang dibutuhkan mobile telah dibangun pada
Fase 5 (Sanctum token auth, 86 rute di `routes/api.php`, API Resources, role-based ability,
scope perusahaan). Dengan demikian effort tersisa adalah sisi aplikasi Flutter plus
penyempurnaan kecil di backend.

---

## Ringkasan Prioritas

| Prioritas | Area | Ringkasan |
|---|---|---|
| 🔴 Tinggi | Backend mobile-readiness | Endpoint mutasi absensi/cuti/lembur, token expiry, rate-limit login |
| 🔴 Tinggi | MVP Self-Service | Auth, dashboard, absensi, cuti, lembur, payslip, notifikasi |
| 🟠 Sedang | Approval HR | Approve/reject lembur & cuti oleh atasan, pengumuman |
| 🟡 Lanjut | Advanced | Geofencing/QR clock-in, offline cache, push FCM |

---

## 1. Kelayakan

- API sudah **versioned** (`/api/v1`), **stateless** (Sanctum Bearer token) → kontrak REST bawaan
  yang ideal untuk Flutter.
- **Auth selesai**: `POST /api/v1/auth/login` → token ber-ability sesuai role; `logout`, `me`.
- **Scope otorisasi di server**: non-SUPER_ADMIN hanya melihat data perusahaannya (langsung atau
  via `employee_id`) — tidak perlu duplikasi logika di aplikasi.
- **Payslip aman**: nilai gaji di-`decrypt` saat serialisasi API Resource (`SalaryCast`), konteksnya
  dibatasi ke pemilik akun; dipastikan akses `payrolls` hanya milik karyawan bersangkutan.
- **Role-based UI** di mobile dapat membaca `roles` + ability dari `/auth/me` dan menyembunyikan
  menu sesuai hak (mirror `Security::can`).

---

## 2. Arsitektur Aplikasi

```
flutter_app (satu repo, self-service first)
├── lib/
│   ├── core/              Dio client, token storage, interceptors, error mapping
│   ├── features/
│   │   ├── auth/          login/logout, session (roles + abilities dari /auth/me)
│   │   ├── attendance/    clock-in/out + riwayat
│   │   ├── leave/         pengajuan cuti + riwayat
│   │   ├── overtime/      pengajuan lembur + riwayat
│   │   ├── payroll/       payslip (take home pay + detail)
│   │   └── profile/       data pribadi + notifikasi
│   └── shared/            widgets, theme, intl format (RP), permissions
```

### Keputusan teknis

| Aspek | Keputusan | Catatan |
|---|---|---|
| Base URL | `{APP_URL}/api/v1` | dev `http://localhost:9100/api/v1`; di deploy pakai `get-absolute-url` |
| HTTP client | Dio + interceptor `Authorization: Bearer` | 401 → redirect login |
| Token storage | `flutter_secure_storage` | jangan `shared_preferences` |
| State management | **Riverpod** (rekomendasi) atau Bloc | pilih satu, konsisten |
| Notifikasi | FCM → endpoint register device | backend saat ini email-only |
| Role-aware UI | simpan `roles` + abilities dari `/auth/me` | hide menu sesuai ability |

---

## 3. Fitur (Roadmap Bertahap)

### MVP — Employee Self-Service (prioritas utama, backend 90% siap)

1. **Auth** — login/logout via `/api/v1/auth/login`; simpan token; saat 401 minta login ulang.
2. **Dashboard** — saldo cuti, status lembur terakhir, info periode gaji berjalan.
3. **Absensi** — clock-in/out + riwayat. *(backend: tambah `mutable` attendance.)*
4. **Cuti** — pengajuan + riwayat. *(backend: tambah `mutable` leaves.)*
5. **Lembur** — pengajuan + riwayat + status approval (notifikasi otomatis).
6. **Payslip** — take home pay + rincian komponen dari `payrolls` + `payroll-details`.
7. **Notifikasi** — in-app history + push FCM.

### Fase 2 — HR & Approval

- Approve/reject lembur & cuti oleh atasan.
- Pengumuman / company news.
- Lihat data tim (scope perusahaan sudah berfungsi).

### Fase 3 — Advanced

- Geolocation / geofencing saat clock-in.
- QR code clock-in.
- Offline cache riwayat (baca saja).
- Multi-company.

### Tetap di Web (jangan pindah ke mobile)

- Seluruh CRUD data master admin (karyawan, komponen gaji, perusahaan, pajak).
- Proses & kalkulasi payroll, laporan/download.
- Konfigurasi sistem dan manajemen role.

---

## 4. Rekomendasi Backend (sebelum/selama develop app)

Backend sudah ~90% siap. Sisa gap:

1. **Endpoint mutasi untuk mobile** — `attendances`, `leaves`, `overtimes` saat ini `mutable: false`.
   Perlu: set `mutable: true` berikut rules-nya di `app/Support/ApiModules.php`, dan pastikan observer
   approval (mis. `OvertimeObserver`) tetap berjalan pada create via API.
2. **Token expiry** — `config/sanctum.php` `expiration` masih `null`. Set mis. 8 jam + endpoint
   `auth/refresh` (atau cukup login ulang otomatis di sisi app; paling sederhana).
3. **Rate-limit login** — `throttle:api` (atau `throttle:6,1`) pada `auth/login` untuk cegah brute-force.
4. **Push FCM** — tabel `device_tokens` + endpoint `auth/device` (register/unregister), lalu notifikasi
   yang kini `['mail']` diberi `via` push (`mail`, `fcm`) tanpa mengubah logic email.
5. **API docs** — layani kontrak API ke tim Flutter: `scribe` atau `scalar` (generasi dari atribut route).
6. **Security pass tambahan** — pastikan endpoint mutasi di API mengikuti `CheckApiRole` `manage_*`
   seperti pola Fase 5 yang sudah ada.

---

## 5. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Gaji ter-enkripsi RSA bocor di mobile | Akses payslip dibatasi scope pemilik akun (sudah); jangan simpan payroll di cache app |
| Duplikasi CRUD admin di mobile | Panduan: mobile = self-service + approval ringan; admin berat tetap web |
| Token dicuri dari device | `flutter_secure_storage` + biometric (LocalAuth) & batasi expiry token |
| Notifikasi email tidak cukup untuk mobile | Tambah FCM via `device_tokens` (akan tetap memakai engine notifikasi yang sama) |
| Base URL berubah tiap environment | Pakai build flavors (dev/staging/prod) + `--dart-define` |

---

## 6. Keterkaitan Fase Web

- Approval cuti/lembur (prioritas Tinggi di `docs/RECOMMENDATIONS.md`) harus selesai **sebelum**
  Fase 2 mobile, karena mobile hanya konsumen dari status approval tersebut.
- Endpoint mutasi absensi/cuti/lembur bagian dari item 4.1 di atas.