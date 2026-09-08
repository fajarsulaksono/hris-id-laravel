<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DemoUserSeeder::class,
            MasterDataSeeder::class,
            SalaryComponentSeeder::class,
        ]);

        // Data dummy (karyawan + absensi/lembur/cuti + payroll beberapa bulan
        // terakhir) hanya dibuat jika HRIS_SEED_DUMMY=true. Untuk menjalankannya
        // sekali, gunakan: php artisan db:seed --class=DummyDataSeeder
        if ((bool) config('hris.seed_dummy.enabled')) {
            $this->call(DummyDataSeeder::class);
        }
    }
}
