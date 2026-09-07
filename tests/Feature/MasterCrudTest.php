<?php

namespace Tests\Feature;

use App\Models\Company\Company;
use App\Models\Employee\Employee;
use App\Models\Master\City;
use App\Models\Master\Region;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasterCrudTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_region_full_crud_flow_and_uppercase_transform(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->post(route('admin.master.regions.store'), ['code' => 'jkt', 'name' => 'jakarta'])
            ->assertRedirect(route('admin.master.regions.index'));

        $region = Region::where('code', 'JKT')->first();

        $this->assertNotNull($region);
        $this->assertSame('JAKARTA', $region->name);

        $this->actingAs($staff)
            ->get(route('admin.master.regions.show', $region))
            ->assertOk()
            ->assertSee('JAKARTA');

        $this->actingAs($staff)
            ->get(route('admin.master.regions.edit', $region))
            ->assertOk();

        $this->actingAs($staff)
            ->put(route('admin.master.regions.update', $region), ['code' => 'jkt', 'name' => 'DKI jakarta'])
            ->assertRedirect(route('admin.master.regions.index'));

        $this->assertSame('DKI JAKARTA', $region->fresh()->name);
    }

    public function test_datatables_endpoint_returns_server_side_payload(): void
    {
        Region::create(['code' => 'R01', 'name' => 'DKI JAKARTA']);
        Region::create(['code' => 'R02', 'name' => 'JAWA BARAT']);
        Region::create(['code' => 'R03', 'name' => 'JAWA TIMUR']);

        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->get(route('admin.master.regions.data', ['draw' => 1, 'start' => 0, 'length' => 10]))
            ->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data'])
            ->assertJson(['draw' => 1, 'recordsTotal' => 3, 'recordsFiltered' => 3]);

        $this->actingAs($staff)
            ->get(route('admin.master.regions.data', ['draw' => 2, 'search' => ['value' => 'KARTA']]))
            ->assertJson(['recordsTotal' => 3, 'recordsFiltered' => 1]);
    }

    public function test_soft_delete_trash_and_restore_flow(): void
    {
        $region = Region::create(['code' => 'R01', 'name' => 'DKI JAKARTA']);

        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->delete(route('admin.master.regions.destroy', $region))
            ->assertRedirect(route('admin.master.regions.index'));

        $this->assertSoftDeleted('regions', ['id' => $region->getKey()]);

        $this->actingAs($staff)
            ->get(route('admin.master.regions.data', ['trashed' => 1]))
            ->assertJson(['recordsTotal' => 1]);

        $this->actingAs($staff)
            ->post(route('admin.master.regions.restore', $region))
            ->assertRedirect();

        $this->assertDatabaseHas('regions', ['id' => $region->getKey(), 'deleted_at' => null]);
    }

    public function test_force_delete_removes_row_permanently(): void
    {
        $region = Region::create(['code' => 'R01', 'name' => 'DKI JAKARTA']);
        $region->delete();

        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->delete(route('admin.master.regions.force-destroy', $region))
            ->assertRedirect();

        $this->assertDatabaseMissing('regions', ['id' => $region->getKey()]);
    }

    public function test_unique_and_composite_unique_validation(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)->post(route('admin.master.regions.store'), ['code' => 'R01', 'name' => 'DKI JAKARTA']);
        $this->actingAs($staff)
            ->post(route('admin.master.regions.store'), ['code' => 'R01', 'name' => 'JAWA BARAT'])
            ->assertSessionHasErrors('code');

        $this->actingAs($staff)->post(route('admin.master.reasons.store'), [
            'type' => 'a', 'code' => 'S', 'name' => 'SAKIT',
        ]);
        $this->actingAs($staff)
            ->post(route('admin.master.reasons.store'), [
                'type' => 'a', 'code' => 'S', 'name' => 'SAKIT LAGI',
            ])
            ->assertSessionHasErrors('code');

        $this->actingAs($staff)
            ->post(route('admin.master.regions.store'), ['code' => 'R02'])
            ->assertSessionHasErrors('name');
    }

    public function test_contract_store_transforms_taglist(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->post(route('admin.master.contracts.store'), [
                'type' => 'p',
                'letter_number' => 'KR/00001/P/2026',
                'subject' => 'kontrak kerja permanent',
                'description' => '',
                'start_date' => '2026-01-02',
                'end_date' => '',
                'signed_date' => '2026-01-01',
                'tags' => 'KPI, Payroll',
            ])
            ->assertRedirect(route('admin.master.contracts.index'));

        $contract = \App\Models\Master\Contract::where('letter_number', 'KR/00001/P/2026')->first();

        $this->assertNotNull($contract);
        $this->assertSame('KONTRAK KERJA PERMANENT', $contract->subject);
        $this->assertSame(['KPI', 'PAYROLL'], $contract->tags);
    }

    public function test_dependency_options_filters_city_by_region(): void
    {
        $region = Region::create(['code' => 'R01', 'name' => 'DKI JAKARTA']);
        $other = Region::create(['code' => 'R02', 'name' => 'JAWA BARAT']);

        City::create(['code' => 'JK01', 'name' => 'KOTA JAKARTA BARAT', 'region_id' => $region->getKey()]);
        City::create(['code' => 'JK02', 'name' => 'KOTA JAKARTA TIMUR', 'region_id' => $region->getKey()]);
        City::create(['code' => 'JB01', 'name' => 'KOTA BANDUNG', 'region_id' => $other->getKey()]);

        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $payload = $this->actingAs($staff)
            ->getJson(route('admin.api.options', ['type' => 'city']).'?parent_id='.$region->getKey())
            ->assertOk()
            ->json();

        $this->assertCount(2, $payload);
        $this->assertSame('JK01 - KOTA JAKARTA BARAT', $payload[0]['text']);
        $this->assertSame('JK02 - KOTA JAKARTA TIMUR', $payload[1]['text']);
    }

    public function test_company_module_is_gated_by_company_menu(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $this->actingAs($employee)
            ->get(route('admin.company.companies.index'))
            ->assertForbidden();

        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $this->actingAs($staff)
            ->get(route('admin.company.companies.index'))
            ->assertOk();
    }

    public function test_company_index_works_under_company_context(): void
    {
        Company::create([
            'code' => 'C001',
            'name' => 'PT SEMART SOLUTIONS',
            'birth_day' => '2015-03-15',
            'email' => 'hr@semart.id',
            'tax_number' => '01.234.567.8-042.000',
        ]);

        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->get(route('admin.company.companies.index'))
            ->assertOk();
    }

    private function employee(string $username, string $role): Employee
    {
        $sequence = ++$this->sequence;

        $employee = Employee::create([
            'code' => Str::upper(str_pad((string) $sequence, 3, '0', STR_PAD_LEFT)),
            'full_name' => Str::title(str_replace('.', ' ', $username)),
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '31740125099000'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
        ]);

        $employee->assignRole($role);

        return $employee;
    }
}