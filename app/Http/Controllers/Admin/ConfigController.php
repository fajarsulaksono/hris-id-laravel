<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ConfigController extends Controller
{
    public function index(): View
    {
        $groups = [
            'Umum' => [
                'Nama Aplikasi' => config('hris.name'),
                'Password Default Karyawan Baru' => '••••••••••',
                'Baris per Halaman' => config('hris.record_per_page'),
                'Hari Kerja per Minggu' => config('hris.workday_per_week'),
                'Akhir Pekan' => implode(', ', (array) config('hris.offday_per_week')),
            ],
            'Akses Menu (Role Minimal)' => $this->menuAccess(),
            'Kehadiran' => [
                'Cut-off Tanggal' => config('hris.attendance.cut_off_date'),
                'Kode Alasan Absen Default' => config('hris.attendance.default_absent_reason_code'),
                'Periode Tertutup Sampai (YYYY-MM)' => config('hris.attendance.closed_through') ?? '-',
            ],
            'Lembur' => [
                'Auto-approve' => config('hris.overtime.auto_approved') ? 'Ya' : 'Tidak',
                'Kode Komponen Benefit' => config('hris.overtime.benefit_code'),
            ],
            'Currency' => [
                'Prefix' => config('hris.currency.prefix'),
                'Presisi Desimal' => config('hris.currency.decimal_precision'),
                'Pemisah Ribuan' => config('hris.currency.thousand_separator'),
            ],
            'BPJS' => $this->bpjs(),
            'Pajak' => [
                'Kode Komponen Pajak (+) PPH21' => config('hris.tax.plus_code'),
                'Kode Komponen Pajak (-) PPH21' => config('hris.tax.minus_code'),
            ],
        ];

        return view('admin.config.index', compact('groups'));
    }

    protected function menuAccess(): array
    {
        $labels = [
            'config_menu' => 'Konfigurasi',
            'user_menu' => 'Manajemen User',
            'master_menu' => 'Master Data',
            'company_menu' => 'Perusahaan',
            'employee_menu' => 'Karyawan',
            'personal_menu' => 'Data Pribadi (self-service)',
            'address_menu' => 'Alamat',
            'attendance_menu' => 'Absensi',
            'overtime_menu' => 'Lembur',
            'leave_menu' => 'Cuti',
            'payroll_menu' => 'Penggajian',
        ];

        $rows = [];

        foreach ($labels as $key => $label) {
            $role = config("hris.security.{$key}");

            if ($role !== null) {
                $rows[$label] = $role;
            }
        }

        return $rows;
    }

    protected function bpjs(): array
    {
        return [
            'JKK' => config('hris.bpjs.jkk_code'),
            'JKM' => config('hris.bpjs.jkm_code'),
            'JHT Karyawan' => config('hris.bpjs.jht_minus_code'),
            'JHT Plus (Perusahaan)' => config('hris.bpjs.jht_plus_code'),
            'JHT Perusahaan (Beban)' => config('hris.bpjs.jht_company_code'),
            'JP Karyawan' => config('hris.bpjs.jp_minus_code'),
            'JP Plus (Perusahaan)' => config('hris.bpjs.jp_plus_code'),
            'JP Perusahaan (Beban)' => config('hris.bpjs.jp_company_code'),
        ];
    }
}
