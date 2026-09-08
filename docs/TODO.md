# TODO — Porting SemartHris → Laravel (hris-id-laravel)

Daftar kerja dihasilkan dari `doc/refactor-laravel.md` (proyek sumber SemartHris).
Fase bersifat sekuensial; setiap fase menghasilkan milestone yang dapat diuji.
Simbol: `[x]` selesai, `[ ]` belum, `[-]` sebagian/sebagian besar selesai.

> **Keputusan yang sudah dikunci:**
> - Laravel **12.x** (bukan 11) — terpasang 12.69.1
> - Skema DB dipertahankan 1:1 dari Doctrine → migration (UUID string PK bukan auto-increment)
> - Admin UI: **AdminLTE 4 + Bootstrap 5.3 via CDN** (bukan paket `jeroennoten/laravel-adminlte`)
> - DataTables **2.x via CDN** (bukan paket composer)
> - Role/permission: **spatie/laravel-permission** (8 role; kolom `roles` di `employees` di-drop)
> - Auth: user = **`Employee`** (Authenticatable) login via `username`/`password` (`Hash`)
> - API: Sanctum + `API Resource` (belum dikerjakan)
> - Enkripsi gaji/credential: port OpenSSL ke `app/Domain/Encryptor` (sudah)
> - Config global: **`config/hris.php`** (pengganti `Setting`/env `SEMART_*`)
> - Upload: spatie/laravel-medialibrary (terpasang)
> - CSV: league/csv (terpasang)

---

## Fase 0 — Fondasi & Persiapan

- [x] Setup Laravel 12 + instal paket: `spatie/laravel-permission`, `spatie/laravel-medialibrary`, `laravel/sanctum`, `league/csv`
- [x] `.env` DB MySQL (192.168.10.106), APP_NAME "HRIS Indonesia", locale `id`
- [x] Migration skema 1:1 (14 file): master, perusahaan, karyawan, absensi, payroll, spatie, sanctum
- [x] `sessions.user_id` & sanctum `tokenable_id` → string (UUID) — perbaikan agar session/auth persist
- [x] Model dasar seluruh entitas (36 model) + `Blameable` trait + casts
- [x] `config/hris.php` (format, security, curahan, bpjs, tax, attendance, overtime)
- [x] Seeder: 8 role (`RoleSeeder`) + 8 user demo per role (`DemoUserSeeder`, password `password123`)
- [x] Auth: user = `Employee`, guard Eloquent, `LoginController` (login/logout), dashboard + routes
- [x] Layout AdminLTE 4 + Bootstrap 5.3 + font Outfit, login page, dashboard stat cards
- [x] Feature test minimal (guest redirect, login page render)
- [x] Base Controller & pola Blade CRUD umum (index/create/edit/show) untuk reuse semua modul (`BaseController` + partials: `card`, `crud/*`)
- [x] Middleware: `checkRole:min_ranking` (menu), `setCompanyContext`, `setEmployeeContext` (session sticky)
- [x] `Gate::before` hierarchy role (urutan 8 role) sesuai §8 refactor doc (`config/hris.role_ranks` + `App\Support\Security`)

## Fase 1 — Master Data & Organisasi

- [x] Model: `EducationalInstitute`, `EducationTitle`, `SkillGroup`, `Skill`, `Region`, `City`, `Holiday`, `Reason`, `Contract`, `Company`, `CompanyAddress`, `CompanyDepartment`, `Department`, `JobLevel`, `JobTitle`
- [x] Port validator: kontrak unik (`app/Domain/Contract/CheckContract` + `app/Rules/UniqueContract`), dependensi form kota↔provinsi, departemen↔perusahaan, jabatan↔level (JS/ajax dynamic select → `GET /admin/api/options/{type}`)
- [x] Controller resource + view Blade AdminLTE grup menu "Master" & "Perusahaan": `MasterDataController` generik berbasis registry `app/Support/MasterModules` (index/data/create/store/show/edit/update/destroy/trash/restore/force-destroy)
- [x] DataTables 2.x (server-side) untuk list semua modul master (`app/Support/DataTableServer` + `public/js/hris-master.js` + CDN `datatables.net@2`)
- [x] Seed master data idempotent (`MasterDataSeeder`): region/kota, job level/title, departemen, reason, kontrak, perusahaan+alamat+dept, pendidikan, keahlian, hari libur
- [x] Restore/trash halaman (SoftDeletes + `deleted_by` Blameable)

## Fase 2 — Karyawan & Kontrak

- [x] Model: `Employee`, `EmployeeAddress`, `Placement`, `Mutation`, `CareerHistory`, `TaxGroupHistory` + relasi supervisor
- [x] Port domain: `SupervisorChecker` (bug rekursi diperbaiki), `UsernameGenerator` + `EmployeeAccountManager` (app/Domain/Employee, app/Domain/User, app/Domain/Job)
- [x] Observer: tulis `CareerHistory` saat penempatan/mutasi (`CareerHistoryService`), sinkron data karyawan saat mutasi (`MutationApplier`), default address (`EmployeeAddressObserver`), join date dari kontrak (`EmployeeObserver`)
- [x] Policy `EmployeePolicy` (port `SupervisorVoter`): view/update hanya anak buah dengan level jabatan berbeda; HR+ via `Gate::before`
- [x] Halaman profil karyawan, form dinamis, mutasi & promosi/demosi — CRUD modul karyawan (`employees`, `employee-addresses`, `placements`, `mutations`, `career-histories`) + form dependen + **upload foto profil via medialibrary** (koleksi `profile` `singleFile`, migrasi media `uuidMorphs`, thumbnail `thumb`, tipe field `image` di CRUD generik) + **halaman profil khusus** (`GET admin.employee.employees.{id}.profile`: foto, data pribadi, posisi, kontrak, alamat, timeline riwayat karir) + **form promosi/demosi khusus** (`GET/POST admin.employee.employees.{id}.promotion`: validasi type `p/d`, tulis `Mutation` → observer menerapkan posisi baru + riwayat karir otomatis)
- [x] Password default + generate username pada pembuatan karyawan (Observer `EmployeeObserver` + `UsernameGenerator`, `config/hris.default_password`; role default `Employee::DEFAULT_ROLE`) — `DEFAULT_ROLE` disamakan `ROLE_EMPLOYEE` → `EMPLOYEE` agar sesuai RoleSeeder

## Fase 3 — Absensi, Lembur & Cuti

- [x] Model: `Shiftment`, `Workshift`, `Attendance`, `AttendanceSummary`, `Overtime`, `Leave`
- [x] Port Overtime calculator: `WorkdayCalculator`, `HolidayCalculator` + base `Calculator` (app/Domain/Overtime)
- [ ] Port Attendance domain: Rule chain, Importer CSV, Processor, Summary
- [ ] Observer: workshift slicing overlap; absent-otomatis saat cuti submit (`SetAbsentWhenLeaveIsSubmited`)
- [ ] Halaman upload CSV (attendance & overtime), proses bulanan, rekap dengan hari libur
- [ ] Validasi periode: bulan tidak melewati periode berjalan / periode tutup

## Fase 4 — Payroll, BPJS, Pajak (paling kritis)

- [x] Model: `SalaryComponent`, `SalaryBenefit`, `SalaryBenefitHistory`, `SalaryAllowance`, `PayrollPeriod`, `Payroll`, `PayrollDetail`, `CompanyPayrollCost`, `Tax`, `TaxGroupHistory`
- [x] Port Tax calculator progresif 4 bracket (5/15/25/30%) — app/Domain/Tax
- [x] Port `Encryptor` RSA + `KeyLoader` — app/Domain/Encryptor
- [ ] Port Salary Processor chain (Attendance → Overtime → Fixed Salary → BPJS → Tax → StoreAsCompanyCost)
- [ ] `SalaryCast` Eloquent custom agar field gaji terenkripsi otomatis saat read/write
- [ ] Aturan: benefit hanya valid pada kontrak aktif; perubahan gaji wajib ada kontrak; perubahan tax group/risk ratio + history
- [ ] Form tunjangan/potongan, proses payroll per periode & closing, detail gaji, beban gaji perusahaan
- [ ] Laporan: slip gaji, beban gaji, riwayat gaji/pajak (export Excel/PDF: maatwebsite/excel + barryvdh/laravel-dompdf)
- [ ] **Uji banding numerik wajib**: hasil payroll Laravel vs Symfony pada dataset sama (PayrollDetail & CompanyPayrollCost)

## Fase 5 — API & Notifikasi

- [ ] Rute API per domain (Sanctum + `API Resource`), filter partial search (`code/name/fullName/shortName`), pagination (`p`,`ep`,`i`)
- [ ] Middleware role API & konteks perusahaan/karyawan
- [ ] (Opsional) Notifikasi email approval

## Fase 6 — Pengujian, Hardening & Cutover

- [ ] Unit test tiap calculator/processor (port test PHPUnit lama → Pest/PHPUnit)
- [ ] Feature test CRUD & auth per modul
- [ ] Uji banding komprehensif angka payroll/BPJS/PPH21
- [ ] Security review (RSA keys, permission), optimasi query, review config/hris.php
- [ ] Dokumentasi port per service (pertahankan komentar regulasi)
- [ ] Go-live checklist + seeding idempotent (`firstOrCreate`)

---

## Catatan implementasi penting (dari doc §10)

- UUID: model `$keyType='string'`, `$incrementing=false` (bukan HasUuids baru) — referensi lama tetap valid
- Gedmo → `SoftDeletes` + trait `Blameable` (`created_by/updated_by/deleted_by`) + `Model::restore()`
- Perhitungan: **jaga urutan & pembulatan `bcmath` identik** — jangan ubah rounding tanpa persetujuan
- Form dinamis dependensi pilihan (provinsi→kota, level→jabatan, perusahaan→departemen): ajax/`onchange` + partial Blade (Livewire opsional)
- Role hierarchy disimulasikan dengan urutan ranking di config + `Gate::before` (bukan bawaan spatie)

## Referensi

- Sumber: `SemartHris/doc/refactor-laravel.md` (dan `doc/arsitektur.md`)
- Algoritma inti: `src/Component/Salary/Processor`, `src/Component/Overtime/Calculator`, `src/Component/Tax/Calculator`, `src/Component/Attendance/Rule`
- Target repo: `git@github.com:fajarsulaksono/hris-id-laravel.git`