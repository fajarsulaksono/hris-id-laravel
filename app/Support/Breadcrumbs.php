<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class Breadcrumbs
{
    private const MENUS = [
        'master' => 'Master',
        'company' => 'Perusahaan',
        'employee' => 'Karyawan',
        'address' => 'Alamat',
        'attendance' => 'Absensi',
        'overtime' => 'Lembur',
        'leave' => 'Cuti',
        'payroll' => 'Penggajian',
    ];

    private const SELF_MAP = [
        'profile' => 'Profil Saya',
        'attendance' => 'Absensi Saya',
        'leaves' => 'Cuti & Izin',
        'payrolls' => 'Slip Gaji Saya',
    ];

    private const NORMAL_ACTIONS = [
        'create' => 'Tambah',
        'edit' => 'Ubah',
        'trash' => 'Sampah',
    ];

    /**
     * Jejak breadcrumb untuk route saat ini. Item terakhir berupa string (aktif),
     * item lain berupa array ['label' => ..., 'url' => ...].
     *
     * @return array<int, array{label: string, url: string}|string>
     */
    public static function crumbs(): array
    {
        $route = Route::current();
        $name = (string) $route?->getName();

        if ($name === '') {
            return [['label' => 'Home', 'url' => route('dashboard')]];
        }

        if (str_starts_with($name, 'my.')) {
            return self::selfCrumbs($name);
        }

        if (str_starts_with($name, 'admin.')) {
            return self::adminCrumbs($name);
        }

        return [['label' => 'Home', 'url' => route('dashboard')]];
    }

    protected static function selfCrumbs(string $name): array
    {
        $crumbs = [['label' => 'Home', 'url' => route('dashboard')]];
        $crumbs[] = ['label' => 'Data Saya', 'url' => route('my.profile')];

        $page = explode('.', $name)[1] ?? null;

        if ($page !== null && isset(self::SELF_MAP[$page])) {
            $crumbs[] = self::SELF_MAP[$page];
        }

        return $crumbs;
    }

    protected static function adminCrumbs(string $name): array
    {
        $parts = explode('.', $name);
        array_shift($parts);

        $crumbs = [['label' => 'Home', 'url' => route('dashboard')]];

        // Halaman statis: Manajemen User & Konfigurasi.
        if (($parts[0] ?? null) === 'users') {
            $crumbs[] = ['label' => 'Manajemen User', 'url' => route('admin.users.index')];
            if (($parts[1] ?? null) === 'edit') {
                $crumbs[] = 'Ubah';
            }

            return $crumbs;
        }

        if (($parts[0] ?? null) === 'config') {
            $crumbs[] = 'Konfigurasi';

            return $crumbs;
        }

        $menu = $parts[0] ?? null;
        $moduleKey = $parts[1] ?? null;
        $action = $parts[2] ?? 'index';

        $module = $moduleKey !== null ? MasterModules::get($moduleKey) : null;
        $menuLabel = $menu !== null && isset(self::MENUS[$menu]) ? self::MENUS[$menu] : null;

        if ($module === null || $menuLabel === null) {
            return $crumbs;
        }

        $moduleTitle = $module['title'];
        $duplicate = strcasecmp((string) $menuLabel, (string) $moduleTitle) === 0;

        // Halaman aksi khusus (upload/process/recap/detail/profil/dll).
        if (in_array($action, ['upload', 'process', 'recap', 'tax', 'profile', 'promotion', 'detail'], true)) {
            $crumbs[] = $menuLabel;
            $crumbs[] = self::actionLabel($moduleKey, $action);

            return $crumbs;
        }

        // Halaman CRUD standar (index/create/edit/trash/show).
        if (! $duplicate) {
            $crumbs[] = $menuLabel;
        }
        $crumbs[] = $moduleTitle;

        if (isset(self::NORMAL_ACTIONS[$action])) {
            $crumbs[] = self::NORMAL_ACTIONS[$action];
        }

        return $crumbs;
    }

    protected static function actionLabel(?string $moduleKey, string $action): string
    {
        return match ($action) {
            'upload' => 'Upload CSV',
            'process' => $moduleKey === 'payrolls' ? 'Proses Payroll' : 'Proses Bulanan',
            'recap' => 'Rekap',
            'tax' => 'Proses Pajak',
            'profile' => 'Profil',
            'promotion' => 'Promosi / Demosi',
            'detail' => 'Detail',
            default => ucfirst($action),
        };
    }
}
