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
- [x] Port Overtime domain lengkap: `OvertimeCalculator` (chain), `OvertimeChecker`, `OvertimeCalculatorService`, `OvertimeImporter`, `OvertimeProcessor` (marker `PROCESSED#`, simpan via `saveQuietly` agar observer tidak men-strip marker)
- [x] Port Attendance domain: Rule chain (`AttendanceRule` + `RuleInterface` + `NotQualifiedException`), `AttendanceCalculator`, `WorkshiftFinder`, `WorkshiftSlicer` (`SLICED BY SYSTEM`), `HolidayChecker`, `WorkdayCalculator`, `AttendanceImporter` (CSV), `AttendanceProcessor` (full/partial bulan, absent default reason), `AttendanceSummaryCalculator`, `ValidateAttendance`
- [x] Validasi periode: `PeriodValidator` menolak bulan melewati periode berjalan atau ≤ `hris.attendance.closed_through` (`InvalidAttendancePeriodException`)
- [x] Observer: workshift slicing overlap; absent-otomatis saat cuti submit (`SetAbsentWhenLeaveIsSubmited`); hitung attendance & overtime otomatis saat simpan; auto-approve lembur (`hris.overtime.auto_approved`)
- [x] Modul CRUD `attendances`, `attendance-summaries`, `overtimes`, `leaves` (MasterModules + accessor `employee_name`/`shiftment_name`/`reason_name`/`approved_by_name`) + rute statis di `routes/web.php` (upload/process/recap didaftarkan sebelum rute `/{id}`)
- [x] Halaman upload CSV (attendance & overtime), proses bulanan, rekap dengan hari libur & akhir pekan (sidebar treeview menu Absensi/Lembur/Cuti)
- [x] `config/hris.php`: `attendance.closed_through` (+ env `HRIS_ATTENDANCE_CLOSED_THROUGH`)
- [x] Tes: 15 file unit domain (Attendance/Leave/Overtime) + `AttendanceWorkflowTest` feature (guest redirect, upload CSV, validasi error, proses bulanan, rekap, upload lembur) — 81 tes / 272 asersi
- [x] Integrasi Fase 4: rekap & summary memakai approved overtime (`AttendanceSummaryCalculator` filter `whereNotNull('approved_by_id')`, baris 105) — terverifikasi

### Catatan review Fase 3 (status: selesai)
- [x] `ValidateAttendance` diintegrasikan ke jalur store/update CRUD via hook `validator` per-modul (`MasterModules` modul `attendances` + `MasterDataController::assertModuleValid`): hadir tanpa jam atau absent tanpa reason ditolak — setara `ValidAttendanceValidator` di lapisan form Symfony (bukan model event, agar importer/processor internal tetap bisa menyimpan data mentah)
- [x] `OvertimeProcessor` (bulk "Proses Lembur") disamakan dgn asli: `save()` normal (bukan `saveQuietly`) sehingga observer berjalan — holiday detect + auto-approve + kalkulasi ulang, marker `PROCESSED#` transien di-strip (perilaku sama dgn `SEMART_VERSION#` asli)
- [x] `AttendanceController::process` & `OvertimeController::process`: `$period` di-`clone` per karyawan agar mutasi in-place `processPartialMonth` tidak menggeser periode ke karyawan berikutnya
- [x] Perbedaan kecil: lookup reason absent kini filter `type = ReasonType::ABSENT` + uppercase + sanitize (setara `findAbsentReasonByCode` asli)

## Fase 4 — Payroll, BPJS, Pajak (paling kritis)

> **Status**: inti workflow ter-port & teruji hijau (PayrollWorkflowTest + PayrollUiTest + PayrollValidationTest, 127 tes/456 asersi). Seluruh temuan review Fase 4 sudah ditindaklanjuti.

- [x] Model: `SalaryComponent`, `SalaryBenefit`, `SalaryBenefitHistory`, `SalaryAllowance`, `PayrollPeriod`, `Payroll`, `PayrollDetail`, `CompanyPayrollCost` (di-port sebagai `CompanyCost`, tabel `company_costs`), `Tax`, `TaxGroupHistory`
- [x] Port Tax calculator progresif 4 bracket (5/15/25/30%) — app/Domain/Tax + rantai `setPrevious` sudah di-wire
- [x] Port `Encryptor` RSA + `KeyLoader` — app/Domain/Encryptor
- [x] Port Salary Processor chain (Attendance → Overtime → Fixed Salary → BPJS → StoreAsCompanyCost; Tax & closing periode terpisah) — topologi sama dengan `services.yaml` asli; `Salary\Service\PayrollProcessor` membuat periode otomatis
- [x] `SalaryCast` Eloquent custom (enkripsi otomatis read/write) + fix get() (decode base64 → regex `#<40-hex>` key). Catatan: format penyimpanan **`#suffix`** berbeda dari asli (kolom `benefit_key`/`take_home_pay_key`/`tax_key` terpisah) → tidak byte-compatible dengan DB SemartHris lama, tapi konsisten internal
- [x] Aturan domain benefit/kontrak/history sudah di-wire: `ValidateBenefit::employeeHasPayroll` ke modul `salary-benefits`, `ValidateTaxHistory` ke modul `tax-group-histories`, `TaxGroupHistoryObserver` menangani `creating/created/updated`
- [x] Form tunjangan/potongan, proses payroll per periode & closing, detail gaji, beban gaji perusahaan — via modul CRUD (salary-benefits/allowances/company-costs), halaman Proses Payroll, Proses Pajak (closing), detail slip, rekap
- [x] Laporan: slip gaji (PDF per karyawan), rekap + export **Excel** (maatwebsite/excel) & **PDF** (barryvdh/laravel-dompdf); riwayat tersedia lewat modul `payrolls`/`taxes`
- [x] **Uji banding numerik multi-bracket**: `PayrollWorkflowTest` mencakup take home, PPh21 single-bracket (PKP 24 jt) + multi-bracket (PKP 306 jt, 3 bracket), BPJS company cost, idempotensi

### Temuan review Fase 4 (semua sudah ditindaklanjuti)
- [x] **HIGH — rantai bracket pajak tidak tersambung**: `AppServiceProvider` kini wire `Fourth→Third→Second→First` via `setPrevious()`. Tes `test_progressive_tax_spans_multiple_brackets` (PKP 306 jt, 3 bracket) verifikasi.
- [x] **HIGH/MEDIUM — risk ratio JKK berbeda dari asli**: didokumentasikan sebagai perbaikan disengaja di `RiskRatio` enum (asli punya bug `in_array` pada key float).
- [x] **MEDIUM — `TaxGroupHistoryObserver` hanya `creating/created`**: `updated()` ditambahkan, mengaplikasikan ulang nilai baru ke karyawan saat history diedit.
- [x] **MEDIUM — aturan belum di-wire**: `ValidateBenefit::employeeHasPayroll` di-wire ke modul `salary-benefits`, `ValidateTaxHistory` di-wire ke modul `tax-group-histories` via `validator` callback.
- [x] **LOW — `ChangeBenefit` saat edit history tanpa `new_benefit_value`**: guard `empty()` ditambahkan di `ChangeBenefit::apply()`.
- [x] **LOW — `TaxProcessor` menimpa `tax_group` tiap run**: sekarang hanya menetapkan `tax_group` saat row baru dibuat (bukan update). `ValidateStateType`/`ValidateTaxGroup` tidak diperlukan (enum + cast fungsional setara).

## Fase 5 — API & Notifikasi

- [x] Rute API per domain (Sanctum + `API Resource`), filter partial search (`q` atas `code/name/full_name`), pagination (`p`,`ep`) — `ApiModules` registry + `ApiController` generik (28 domain, 86 rute di `routes/api.php`)
- [x] Middleware role API (`CheckApiRole`, 403 JSON) & konteks perusahaan/karyawan (scope non-SUPER_ADMIN ke perusahaan sendiri)
- [x] Auth API: `login/logout/me` dengan Sanctum token ber-ability sesuai role; `Employee` memakai `HasApiTokens`
- [x] Notifikasi email approval: `OvertimeApprovedNotification` (saat auto-approved) & `PayrollProcessedNotification` (setelah proses penggajian)

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