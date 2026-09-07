# HRIS Indonesia (hris-id-laravel)

Human Resources Information System - Payroll, Attendance, BPJS & PPH21 for Indonesian Workforce.

A Laravel 12 port of the SemartHris (Symfony 6.4) application. Focused on Indonesian HR practices including salary processing, attendance, overtime, BPJS Ketenagakerjaan, BPJS Kesehatan, and PPH21 income tax.

## Features

- **Employee Management** – master data, contracts, mutation, family, education, and skill records
- **Attendance** – clock in/out, leave (`Cuti`), `Surat Izin`, `Surat Keterangan`, and overtime (`Lembur`)
- **Payroll** – salary components, BPJS contributions, PPH21 tax calculation, and payslip
- **Company Structure** – regions, cities, company, department, job level, and job title
- **Roles & Permissions** – 8 roles via `spatie/laravel-permission`:
  `EMPLOYEE`, `HRSTAFF`, `HRSUPERVISOR`, `HRMANAGER`, `HRGENERAL_MANAGER`, `HRDIRECTOR`, `TOP_LEVEL_MANAGEMENT`, `SUPER_ADMIN`

## Requirements

- PHP 8.1+
- MySQL (default connection at `192.168.10.106`)
- Composer

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Default demo users (password: `password123`):

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

## Tech Stack

- Laravel 12
- `spatie/laravel-permission` – roles & permissions
- `spatie/laravel-medialibrary` – file/media storage
- `laravel/sanctum` – API authentication
- DataTables 2.x (CDN)
- AdminLTE (CDN)

## Project Structure

- `app/Models/{Master,Company,Employee,Attendance,Payroll,Tax}` – Eloquent models
- `app/Domain/` – ported business logic (calculators, encryptor, validators)
- `app/Enums/` – PHP 8.1 enums (gender, contract type, marital status, tax group, etc.)
- `app/Support/Concerns/` – reusable model traits (e.g., `Blameable`)
- `config/hris.php` – HRIS application settings (format, security, bpjs, tax, attendance, overtime)

## License

MIT
