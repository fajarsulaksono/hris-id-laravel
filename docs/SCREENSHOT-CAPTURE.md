# Regenerasi Screenshot UI per Role (hris-id-laravel)

Dokumen ini menjelaskan alur yang dipakai untuk membuat/memperbarui screenshot di
`docs/screenshots/<role>/` yang ditampilkan di `README.md` (galeri per role).

## Latar belakang

AdminLTE 4 merender `.app-sidebar` dengan `position: sticky; max-height: 100vh`.
Akibatnya, jika halaman di-capture sebagai *full-page*, area konten yang lebih tinggi dari
viewport membuat bagian bawah kolom sidebar berwarna **putih** (sidebar tidak ikut
menutupi sisa dokumen). Solusinya:

1. muat halaman dengan viewport lebar **1440px**;
2. pastikan sidebar dalam keadaan **expanded** (tanpa class `sidebar-collapse`, lebar ≥ 200px);
3. ukur tinggi konten (`document.documentElement.scrollHeight`);
4. set tinggi viewport = `max(tinggiKonten, 1000)` sehingga `100vh` sidebar menutupi
   seluruh dokumen;
5. capture **non-full-page** pada viewport tersebut.

Hasilnya: sidebar gelap menjangkau sampai paling bawah gambar, tanpa pita putih, dan
seluruh konten halaman terlihat (tinggi gambar ≈ tinggi konten).

## Prasyarat

- Database demo ter-seed: `php artisan migrate --seed` (+ `DemoUserSeeder`,
  `DummyDataSeeder`) — akun demo ada di `database/seeders/DemoUserSeeder.php`
  (password `password123`).
- Aplikasi berjalan: `php artisan serve --host=127.0.0.1 --port=9100`
  (sesuai `APP_URL`).
- Google Chrome terpasang (`/usr/bin/google-chrome`).
- `playwright-core` tersedia untuk Node (cukup instalasi lokal sementara,
  mis. `npm i playwright-core` di folder kerja).

## Alur capture

1. Login sekali per role dengan akun demo-nya (pakai *context* browser terpisah per role
   agar sesi antar role tidak tercampur).
2. Navigasi ke tiap URL halaman, tunggu hingga tenang (`networkidle`) dan (bila ada)
   DataTables `#master-datatable` selesai render baris.
3. Pastikan sidebar expanded.
4. Capture seperti dijelaskan di atas.

Login (`/login`) sengaja **tidak** di-capture ulang oleh skrip ini.

## Peta role → akun → halaman

| Folder screenshots | Akun demo | File → URL |
|---|---|---|
| `employee` | `budi.santoso` | `dashboard` `/`; `data-saya-profil` `/my/profile`; `data-saya-absensi` `/my/attendance`; `data-saya-cuti` `/my/leaves`; `data-saya-slip-gaji` `/my/payrolls` |
| `hrstaff` | `sari.wulandari` | `dashboard`; `karyawan` `/admin/employee/employees`; `absensi` `/admin/attendance/attendances`; `data-keluarga` `/admin/employee/employee-families`; `pendidikan` `/admin/employee/employee-educations`; `keahlian` `/admin/employee/employee-skills` |
| `hr_supervisor` | `dewi.lestari` | `dashboard`; `riwayat-penggajian` `/admin/payroll/payrolls`; `proses-payroll` `/admin/payroll/payrolls/process` |
| `hr_manager` | `rina.kartika` | `dashboard`; `detail-slip-gaji` `/admin/payroll/payrolls/{payroll}/detail`; `rekap-penggajian` `/admin/payroll/payrolls/recap` |
| `hr_general_manager` | `andi.prasetyo` | `dashboard`; `proses-pajak` `/admin/payroll/payrolls/tax`; `riwayat-pajak` `/admin/payroll/taxes` |
| `hr_director` | `sri.handayani` | `dashboard`; `rekap-absensi` `/admin/attendance/attendance-summaries`; `cuti` `/admin/leave/leaves` |
| `top_level_management` | `eko.wijaya` | `dashboard`; `lembur` `/admin/overtime/overtimes`; `komponen-gaji` `/admin/payroll/salary-components` |
| `super_admin` | `agus.setiawan` | `dashboard`; `profil-karyawan` `/admin/employee/employees/{employee}/profile`; `perusahaan` `/admin/company/companies` |

Halaman berbasis record (`detail-slip-gaji`, `profil-karyawan`) memakai salah satu id
record demo yang ada di DB (ambil via `php artisan tinker` bila data di-reseed).

## Skrip

```js
const { chromium } = require('playwright-core');
const fs = require('fs');

const BASE = process.env.BASE || 'http://localhost:9100';
const OUT = process.env.OUT || '<repo>/docs/screenshots';
const MIN_HEIGHT = parseInt(process.env.MIN_HEIGHT || '1000', 10);
const WIDTH = parseInt(process.env.WIDTH || '1440', 10);
const ONLY_ROLE = process.env.ONLY_ROLE || '';
const PASSWORD = 'password123';

const TASKS = {
  employee: { user: 'budi.santoso', shots: [
    ['dashboard', '/'],
    ['data-saya-profil', '/my/profile'],
    ['data-saya-absensi', '/my/attendance'],
    ['data-saya-cuti', '/my/leaves'],
    ['data-saya-slip-gaji', '/my/payrolls'],
  ]},
  hrstaff: { user: 'sari.wulandari', shots: [
    ['dashboard', '/'],
    ['karyawan', '/admin/employee/employees'],
    ['absensi', '/admin/attendance/attendances'],
    ['data-keluarga', '/admin/employee/employee-families'],
    ['pendidikan', '/admin/employee/employee-educations'],
    ['keahlian', '/admin/employee/employee-skills'],
  ]},
  hr_supervisor: { user: 'dewi.lestari', shots: [
    ['dashboard', '/'],
    ['riwayat-penggajian', '/admin/payroll/payrolls'],
    ['proses-payroll', '/admin/payroll/payrolls/process'],
  ]},
  hr_manager: { user: 'rina.kartika', shots: [
    ['dashboard', '/'],
    ['detail-slip-gaji', '/admin/payroll/payrolls/{PAYROLL_ID}/detail'],
    ['rekap-penggajian', '/admin/payroll/payrolls/recap'],
  ]},
  hr_general_manager: { user: 'andi.prasetyo', shots: [
    ['dashboard', '/'],
    ['proses-pajak', '/admin/payroll/payrolls/tax'],
    ['riwayat-pajak', '/admin/payroll/taxes'],
  ]},
  hr_director: { user: 'sri.handayani', shots: [
    ['dashboard', '/'],
    ['rekap-absensi', '/admin/attendance/attendance-summaries'],
    ['cuti', '/admin/leave/leaves'],
  ]},
  top_level_management: { user: 'eko.wijaya', shots: [
    ['dashboard', '/'],
    ['lembur', '/admin/overtime/overtimes'],
    ['komponen-gaji', '/admin/payroll/salary-components'],
  ]},
  super_admin: { user: 'agus.setiawan', shots: [
    ['dashboard', '/'],
    ['profil-karyawan', '/admin/employee/employees/{EMPLOYEE_ID}/profile'],
    ['perusahaan', '/admin/company/companies'],
  ]},
};

async function login(page, user) {
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.fill('#username', user);
  await page.fill('#password', PASSWORD);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.click('button[type="submit"]'),
  ]);
}

async function sidebarInfo(page) {
  return page.evaluate(() => {
    const body = document.body;
    const aside = document.querySelector('.app-sidebar');
    return {
      collapsed: body.classList.contains('sidebar-collapse'),
      sidebarWidth: aside ? Math.round(aside.getBoundingClientRect().width) : 0,
      docH: document.documentElement.scrollHeight,
      asideBottom: aside ? Math.round(aside.getBoundingClientRect().bottom) : 0,
    };
  });
}

async function ensureSidebarExpanded(page) {
  const st = await sidebarInfo(page);
  if (st.collapsed || st.sidebarWidth < 200) {
    const btn = page.locator('[data-lte-toggle="sidebar"]').first();
    if (await btn.count()) { await btn.click(); await page.waitForTimeout(500); }
  }
}

async function settle(page) {
  await page.waitForLoadState('networkidle').catch(() => {});
  await page.waitForTimeout(900);
  try { await page.waitForSelector('#master-datatable tbody tr', { timeout: 8000 }); } catch (e) {}
}

async function capture(page, file) {
  const info = await sidebarInfo(page);
  await page.setViewportSize({ width: WIDTH, height: Math.max(info.docH, MIN_HEIGHT) });
  await page.waitForTimeout(600);
  fs.mkdirSync(file.substring(0, file.lastIndexOf('/')), { recursive: true });
  await page.screenshot({ path: file, fullPage: false }); // non-fullPage!
}

(async () => {
  const browser = await chromium.launch({
    executablePath: '/usr/bin/google-chrome',
    headless: true,
    args: ['--no-sandbox', '--disable-gpu', '--force-device-scale-factor=1'],
  });

  for (const [role, cfg] of Object.entries(TASKS)) {
    if (ONLY_ROLE && role !== ONLY_ROLE) continue;
    console.log(`[${role}] login ${cfg.user}`);
    const context = await browser.newContext({ viewport: { width: WIDTH, height: 900 }, deviceScaleFactor: 1 });
    const page = await context.newPage();
    await login(page, cfg.user);

    for (const [name, path] of cfg.shots) {
      const resp = await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle' });
      if (!resp || !resp.ok()) { console.log(`  SKIP ${name} (http ${resp && resp.status()})`); continue; }
      await ensureSidebarExpanded(page);
      await settle(page);
      await page.setViewportSize({ width: WIDTH, height: 900 }); // reset tinggi antar halaman
      const final = await sidebarInfo(page);
      console.log(`  ${name} docH=${final.docH} asideBottom=${final.asideBottom} collapsed=${final.collapsed}`);
      await capture(page, `${OUT}/${role}/${name}.png`);
    }
    await context.close();
  }
  await browser.close();
})();
```

> Catatan kecil pada alur di atas: reset viewport ke `900px` sebelum tiap halaman penting,
> agar tinggi viewport dari halaman sebelumnya tidak "terbawa" (menggelembungkan) halaman
> berikutnya.

## Validasi hasil

Setelah capture, cek tiap file PNG:

- lebar `1440px`;
- di log skrip: `collapsed=false` dan `asideBottom == docH` (sidebar menutup penuh,
  tidak ada pita putih di bawah sidebar);
- tidak ada halaman error (skrip me-skip bila HTTP bukan 2xx / redirect ke `/login`);
- `authentication/login.png` tidak ikut tertimpa.
