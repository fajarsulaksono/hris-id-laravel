# HRIS Indonesia (hris-id-laravel)

Human Resources Information System — Payroll, Attendance, BPJS & PPH21 for Indonesian Workforce.

A Laravel 12 port of the [SemartHris](https://github.com/SemartHris) (Symfony 6.4) application. Focused on Indonesian HR practices: salary processing, attendance, overtime, BPJS Ketenagakerjaan, BPJS Kesehatan, and PPH21 income tax.

## Table of Contents

- [Screenshots (per role)](#screenshots-per-role)
- [Features](#features)
- [Roles & Access](#roles--access)
- [Modules & Menus](#modules--menus)
- [Demo Users](#demo-users)
- [Architecture](#architecture)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Project Structure](#project-structure)
- [Customization & Support](#customization--support)
- [License](#license)

## Screenshots (per role)

Screenshots below were captured against a freshly seeded demo database (`php artisan migrate --seed` + `DummyDataSeeder`). Each role logs in with its own demo account, so the sidebar and pages reflect exactly what that role can open.

### Authentication

![Masuk — Layar Besar (3/4 + 1/4)](docs/screenshots/authentication/login.png)

![Masuk — Layar Sedang (2/3 + 1/3)](docs/screenshots/authentication/login-md.png)

### EMPLOYEE — Budi Santoso (`budi.santoso`)

> Regular employee: **Dashboard** plus **Data Saya** (self-service) — own profile, own attendance, leave request/history, and payslip download.

![EMPLOYEE — Dashboard (self-service only)](docs/screenshots/employee/dashboard.png)

![EMPLOYEE — Profil Saya](docs/screenshots/employee/data-saya-profil.png)

![EMPLOYEE — Absensi Saya](docs/screenshots/employee/data-saya-absensi.png)

![EMPLOYEE — Cuti & Izin Saya](docs/screenshots/employee/data-saya-cuti.png)

![EMPLOYEE — Slip Gaji Saya](docs/screenshots/employee/data-saya-slip-gaji.png)

### HRSTAFF — Sari Wulandari (`sari.wulandari`)

> Opens Master, Perusahaan, Karyawan, Alamat, Absensi, Lembur, Cuti and Manajemen User menus.

![HRSTAFF — Dashboard](docs/screenshots/hrstaff/dashboard.png)

![HRSTAFF — Karyawan list](docs/screenshots/hrstaff/karyawan.png)

![HRSTAFF — Absensi list](docs/screenshots/hrstaff/absensi.png)

![HRSTAFF — Data Keluarga](docs/screenshots/hrstaff/data-keluarga.png)

![HRSTAFF — Pendidikan](docs/screenshots/hrstaff/pendidikan.png)

![HRSTAFF — Keahlian](docs/screenshots/hrstaff/keahlian.png)

### HRSUPERVISOR — Dewi Lestari (`dewi.lestari`)

> HRSUPERVISOR is the lowest rank allowed into the **Penggajian** (payroll) group.

![HRSUPERVISOR — Dashboard](docs/screenshots/hr_supervisor/dashboard.png)

![HRSUPERVISOR — Riwayat Penggajian](docs/screenshots/hr_supervisor/riwayat-penggajian.png)

![HRSUPERVISOR — Proses Payroll](docs/screenshots/hr_supervisor/proses-payroll.png)

### HRMANAGER — Rina Kartika (`rina.kartika`)

![HRMANAGER — Dashboard](docs/screenshots/hr_manager/dashboard.png)

![HRMANAGER — Detail slip gaji (payslip breakdown)](docs/screenshots/hr_manager/detail-slip-gaji.png)

![HRMANAGER — Rekap Penggajian](docs/screenshots/hr_manager/rekap-penggajian.png)

### HRGENERAL_MANAGER — Andi Prasetyo (`andi.prasetyo`)

![HRGENERAL_MANAGER — Dashboard](docs/screenshots/hr_general_manager/dashboard.png)

![HRGENERAL_MANAGER — Proses Pajak PPH21](docs/screenshots/hr_general_manager/proses-pajak.png)

![HRGENERAL_MANAGER — Riwayat Pajak](docs/screenshots/hr_general_manager/riwayat-pajak.png)

### HRDIRECTOR — Sri Handayani (`sri.handayani`)

![HRDIRECTOR — Dashboard](docs/screenshots/hr_director/dashboard.png)

![HRDIRECTOR — Rekap Absensi](docs/screenshots/hr_director/rekap-absensi.png)

![HRDIRECTOR — Cuti list](docs/screenshots/hr_director/cuti.png)

### TOP_LEVEL_MANAGEMENT — Eko Wijaya (`eko.wijaya`)

![TOP_LEVEL_MANAGEMENT — Dashboard](docs/screenshots/top_level_management/dashboard.png)

![TOP_LEVEL_MANAGEMENT — Lembur list](docs/screenshots/top_level_management/lembur.png)

![TOP_LEVEL_MANAGEMENT — Komponen Gaji](docs/screenshots/top_level_management/komponen-gaji.png)

### SUPER_ADMIN — Agus Setiawan (`agus.setiawan`)

> Only SUPER_ADMIN can also open **Konfigurasi**.

![SUPER_ADMIN — Dashboard](docs/screenshots/super_admin/dashboard.png)

![SUPER_ADMIN — Profil Karyawan](docs/screenshots/super_admin/profil-karyawan.png)

![SUPER_ADMIN — Perusahaan list](docs/screenshots/super_admin/perusahaan.png)

## Features

- **Employee Management** — master data, contracts, mutation, family, education, and skill records (per employee), profile & promotion
- **Self-service (`Data Saya`)** — every employee views their own profile, attendance, leave request/history, and downloads their own payslip PDF
- **Attendance** – clock in/out, leave (`Cuti`), `Surat Izin`, `Surat Keterangan`, and overtime (`Lembur`)
- **Payroll** – salary components, BPJS contributions, PPH21 tax calculation, and payslip (HTML + PDF via `barryvdh/laravel-dompdf`)
- **Company Structure** – regions, cities, company, department, job level, and job title
- **CSV import** – bulk upload attendance & overtime (`maatwebsite/excel`), monthly processing & recap
- **Encrypted salary fields** – sensitive amounts stored encrypted (`Encryptor`, OpenSSL) and cast on read
- **Roles & Permissions** – 8 hierarchical roles via `spatie/laravel-permission` plus role-rank gates
  (`EMPLOYEE`, `HRSTAFF`, `HRSUPERVISOR`, `HRMANAGER`, `HRGENERAL_MANAGER`, `HRDIRECTOR`, `TOP_LEVEL_MANAGEMENT`, `SUPER_ADMIN`)

## Roles & Access

Access control is **rank-based** (see `config/hris.php` → `security` & `role_ranks`). Every menu group requires a minimum role rank; a higher role automatically inherits every lower menu. Employees themselves are the logins (`employees` table doubles as the `users` guard).

| Rank | Role                 | Sidebar groups available                                                          |
|------|----------------------|-----------------------------------------------------------------------------------|
| 0    | `EMPLOYEE`           | Dashboard + Data Saya (self-service)                                                |
| 1    | `HRSTAFF`            | + Master, Perusahaan, Karyawan, Alamat, Absensi, Lembur, Cuti, Manajemen User      |
| 2    | `HRSUPERVISOR`       | + Penggajian (payroll)                                                            |
| 3    | `HRMANAGER`          | same as HRSUPERVISOR (higher approval/scope rights via policies)                  |
| 4    | `HRGENERAL_MANAGER`  | same as HRSUPERVISOR                                                              |
| 5    | `HRDIRECTOR`         | same as HRSUPERVISOR                                                              |
| 6    | `TOP_LEVEL_MANAGEMENT` | same as HRSUPERVISOR                                                            |
| 7    | `SUPER_ADMIN`        | + Konfigurasi (all menus)                                                         |

Menu → minimum role mapping is defined in `config/hris.php` (`security.*_menu`, defaults below, overridable via `.env` `HRIS_SECURITY_*`):

| Menu key         | Default minimum role |
|------------------|----------------------|
| `master_menu`    | `HRSTAFF`            |
| `company_menu`   | `HRSTAFF`            |
| `employee_menu`  | `HRSTAFF`            |
| `address_menu`   | `HRSTAFF`            |
| `attendance_menu`| `HRSTAFF`            |
| `overtime_menu`  | `HRSTAFF`            |
| `leave_menu`     | `HRSTAFF`            |
| `user_menu`      | `HRSTAFF`            |
| `payroll_menu`   | `HRSUPERVISOR`       |
| `personal_menu`  | `EMPLOYEE`           |
| `config_menu`    | `SUPER_ADMIN`        |

All module registries live in `app/Support/MasterModules.php` (title, columns, searchable fields, route key, and validator hooks per module).

## Modules & Menus

Module list is driven from `MasterModules` and grouped by sidebar menu. Two additional admin areas sit outside the registry: **Manajemen User** (`/admin/users`) and **Konfigurasi** (`/admin/config`, read-only settings overview).

### Master (`/admin/master/…`)

| Module | Route key | Description |
|---|---|---|
| Lembaga Pendidikan | `educational-institutes` | Education institute master data |
| Gelar Pendidikan | `education-titles` | Academic degree titles |
| Kelompok Keahlian | `skill-groups` | Skill categories (hierarchical) |
| Keahlian | `skills` | Skills bound to a skill group |
| Propinsi | `regions` | Indonesian provinces |
| Kota | `cities` | Cities bound to a province |
| Hari Libur | `holidays` | Public / company holidays |
| Alasan Absen / Cuti | `reasons` | Absence & leave reasons (typed) |
| Kontrak | `contracts` | Contract templates (per type, date, tags) |

### Perusahaan (`/admin/company/…`)

| Module | Route key | Description |
|---|---|---|
| Perusahaan | `companies` | Company master data |
| Departemen | `departments` | Departments (hierarchical) |
| Departemen Perusahaan | `company-departments` | Company ↔ department relation |
| Level Jabatan | `job-levels` | Job level hierarchy |
| Jabatan | `job-titles` | Job titles bound to a level |

### Alamat (`/admin/address/…`)

| Module | Route key | Description |
|---|---|---|
| Alamat Karyawan | `employee-addresses` | Employee addresses (default flag) |
| Alamat Perusahaan | `company-addresses` | Company addresses (default flag) |

### Karyawan (`/admin/employee/…`)

| Module | Route key | Description |
|---|---|---|
| Karyawan | `employees` | Employee master data + **profile page** (`/profile`) & **promotion** (`/promotion`) |
| Data Keluarga | `employee-families` | Family members (parent / spouse / children) |
| Pendidikan | `employee-educations` | Education history per employee (institute & title) |
| Keahlian | `employee-skills` | Skills per employee with proficiency level |
| Penempatan | `placements` | Placement / assignment per company–dept–job title |
| Mutasi | `mutations` | Job mutation records |
| Riwayat Karir | `career-histories` | Career history timeline |

### Absensi (`/admin/attendance/…`)

| Module | Route key | Description |
|---|---|---|
| Absensi | `attendances` | Daily attendance (shift, in/out, absent) |
| Rekap Absensi | `attendance-summaries` | Monthly summary per employee |
| — (action) | `attendances/upload` | **Upload CSV** attendance import |
| — (action) | `attendances/process` | **Proses Bulanan** monthly processing |
| — (action) | `attendances/recap` | **Rekap** analytics recap |

### Lembur (`/admin/overtime/…`)

| Module | Route key | Description |
|---|---|---|
| Lembur | `overtimes` | Overtime records (start/end hour, holiday, overday) |
| — (action) | `overtimes/upload` | **Upload CSV** overtime import |
| — (action) | `overtimes/process` | **Proses Bulanan** monthly processing |

### Cuti (`/admin/leave/…`)

| Module | Route key | Description |
|---|---|---|
| Cuti | `leaves` | Leave records (reason, amount, description) |

### Data Saya (self-service, `/my/…`)

Available to every logged-in employee (own data only):

| Page | Route | Description |
|---|---|---|
| Profil Saya | `my.profile` | Own identity & organisation data |
| Absensi Saya | `my.attendance` | Own attendance history |
| Cuti & Izin | `my.leaves` | Submit & list own leave requests |
| Slip Gaji Saya | `my.payrolls` | Own payroll history + payslip PDF |

### Penggajian (`/admin/payroll/…`)

| Module | Route key | Description |
|---|---|---|
| Komponen Gaji | `salary-components` | Salary components (allowance/deduction, fixed flag) |
| Gaji Pokok & Tunjangan | `salary-benefits` | Fixed salary benefits per employee |
| Tunjangan & Potongan | `salary-allowances` | Monthly allowances & deductions |
| Riwayat Perubahan Gaji | `salary-benefit-histories` | Salary change history |
| Periode Penggajian | `payroll-periods` | Payroll periods (closed flag) |
| Riwayat Penggajian | `payrolls` | Processed payroll list + **detail slip** (`/{id}/detail`) & **PDF** (`/{id}/pdf`) |
| Rincian Gaji | `payroll-details` | Per-component payroll breakdown |
| Beban Perusahaan | `company-costs` | Company-paid contributions |
| Riwayat Pajak | `taxes` | PPH21 tax history (PTKP/PKP) |
| Riwayat Kelompok Pajak | `tax-group-histories` | Tax group / risk ratio history |
| — (action) | `payrolls/process` | **Proses Payroll** run payroll |
| — (action) | `payrolls/tax` | **Proses Pajak** calculate PPH21 |
| — (action) | `payrolls/recap` | **Rekap Penggajian** + export |

## Demo Users

Seeded by `DemoUserSeeder`. Password for all: `password123`.

| Code    | Name           | Username          | Email                   | Role                  |
|---------|----------------|-------------------|-------------------------|-----------------------|
| EMP001  | Budi Santoso   | budi.santoso      | budi.santoso@example.test | EMPLOYEE           |
| HR001   | Sari Wulandari | sari.wulandari    | sari.wulandari@example.test | HRSTAFF          |
| HR002   | Dewi Lestari   | dewi.lestari      | dewi.lestari@example.test | HRSUPERVISOR      |
| HR003   | Rina Kartika   | rina.kartika      | rina.kartika@example.test | HRMANAGER        |
| HR004   | Andi Prasetyo  | andi.prasetyo     | andi.prasetyo@example.test | HRGENERAL_MANAGER |
| DIR001  | Sri Handayani  | sri.handayani     | sri.handayani@example.test | HRDIRECTOR        |
| TOP001  | Eko Wijaya     | eko.wijaya        | eko.wijaya@example.test | TOP_LEVEL_MANAGEMENT |
| SA001   | Agus Setiawan  | agus.setiawan     | agus.setiawan@example.test | SUPER_ADMIN        |

Quick login: `php artisan serve` → open `/login` → use any username above with `password123`.

## Architecture

Laravel 12 (PHP 8.x) with domain-oriented code:

- **`app/Models/`** — grouped Eloquent models: `Master`, `Company`, `Employee`, `Attendance`, `Payroll`, `Tax`.
- **`app/Domain/`** — ported business logic as plain services/calculators (independent of HTTP layer):
  - `Attendance/` – workday/workshift calc, holiday checker, attendance & summary calculators, CSV importer, monthly processor
  - `Overtime/` – overtime calculator (incl. holiday multiplier), checker, importer, processor
  - `Salary/` & `Tax/` – payroll processors, PPH21 rate calculators (First–Fourth rate), tax services
  - `Employee/`, `Job/`, `Leave/`, `Contract/` – supervisor check, mutation applier, career service, contract uniqueness check
  - `Encryptor/` – OpenSSL encryptor + `SalaryCast` for encrypting salary columns at rest
  - `User/` – employee account manager & username generator
- **`app/Enums/`** – PHP enums (gender, marital status, identity type, contract type, tax group, risk ratio, salary state, etc.).
- **`app/Support/`** — `MasterModules` (module/menu registry), `Security` (rank-based RBAC), `DataTableServer`, `StringUtil`.
- **`app/Support/Concerns/Blameable`** – created/updated-by tracking trait.
- **`config/hris.php`** — central HRIS settings (format, currency, security ranks/abilities, attendance, overtime, bpjs, tax, seed flags).
- Views use **AdminLTE 4 + Bootstrap 5.3 + Tabler Icons** layout with **DataTables 2.x** server-side grids (CDN assets), Indonesian UI labels.

## Tech Stack

- Laravel 12
- `spatie/laravel-permission` – roles & permissions
- `spatie/laravel-medialibrary` – file/media storage (employee photos)
- `laravel/sanctum` – API authentication
- `barryvdh/laravel-dompdf` – payslip & recap PDF
- `maatwebsite/excel` – CSV/Excel attendance & overtime import
- DataTables 2.x + AdminLTE 4 (CDN)

## Requirements

- PHP 8.1+
- MySQL/MariaDB
- Composer

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Optional: generate dummy operational data (extra employees + attendance/overtime/leave + several closed payroll & tax periods) for a realistic demo:

```bash
php artisan db:seed --class=DummyDataSeeder
```

Run the app:

```bash
php artisan serve
```

## Project Structure

- `app/Models/{Master,Company,Employee,Attendance,Payroll,Tax}` – Eloquent models
- `app/Domain/` – ported business logic (calculators, encryptor, validators)
- `app/Enums/` – PHP 8.1 enums (gender, contract type, marital status, tax group, etc.)
- `app/Support/Concerns/` – reusable model traits (e.g., `Blameable`)
- `config/hris.php` – HRIS application settings (format, security, bpjs, tax, attendance, overtime)
- `docs/screenshots/<role>/` – UI screenshots per role (used in this README)

## Customization & Support

For customization, implementation, or consultation, feel free to reach out:

- Email: fajar.sulaksono@gmail.com
- Telegram: fajarsulaksono

## License

MIT
