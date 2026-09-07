<?php

namespace Tests\Unit\Domain\Contract;

use App\Domain\Contract\CheckContract;
use App\Models\Employee\Employee;
use App\Models\Master\Contract;
use App\Rules\UniqueContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CheckContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_detects_contract_already_used_by_employee(): void
    {
        $contract = Contract::create([
            'type' => 'p',
            'letter_number' => 'KR/00001/P/2026',
            'subject' => 'KONTRAK KERJA',
            'start_date' => '2026-01-02',
            'signed_date' => '2026-01-01',
            'used' => true,
        ]);

        $this->employee($contract);

        $service = new CheckContract();

        $this->assertTrue($service->isAlreadyUsed($contract->getKey()));
    }

    public function test_it_allows_unused_contract(): void
    {
        $contract = Contract::create([
            'type' => 'p',
            'letter_number' => 'KR/00002/P/2026',
            'subject' => 'KONTRAK KERJA',
            'start_date' => '2026-01-02',
            'signed_date' => '2026-01-01',
        ]);

        $service = new CheckContract();

        $this->assertFalse($service->isAlreadyUsed($contract->getKey()));
    }

    public function test_it_ignores_current_entity_when_editing(): void
    {
        $contract = Contract::create([
            'type' => 'p',
            'letter_number' => 'KR/00003/P/2026',
            'subject' => 'KONTRAK KERJA',
            'start_date' => '2026-01-02',
            'signed_date' => '2026-01-01',
            'used' => true,
        ]);

        $employee = $this->employee($contract);

        $service = new CheckContract();

        $this->assertFalse($service->isAlreadyUsed($contract->getKey(), $employee->getKey()));
    }

    public function test_unique_contract_rule_passes_and_fails(): void
    {
        $used = Contract::create([
            'type' => 'p',
            'letter_number' => 'KR/00004/P/2026',
            'subject' => 'KONTRAK KERJA',
            'start_date' => '2026-01-02',
            'signed_date' => '2026-01-01',
            'used' => true,
        ]);

        $please = Contract::create([
            'type' => 'p',
            'letter_number' => 'KR/00005/P/2026',
            'subject' => 'KONTRAK KERJA',
            'start_date' => '2026-01-02',
            'signed_date' => '2026-01-01',
        ]);

        $this->employee($used);

        $this->assertTrue(Validator::make(
            ['contract_id' => $used->getKey()],
            ['contract_id' => new UniqueContract()]
        )->fails());

        $this->assertFalse(Validator::make(
            ['contract_id' => $please->getKey()],
            ['contract_id' => new UniqueContract()]
        )->fails());
    }

    private function employee(Contract $contract): Employee
    {
        return Employee::create([
            'code' => '001',
            'full_name' => 'Budi Santoso',
            'username' => 'budi.santoso',
            'email' => 'budi.santoso@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '3174012509900001',
            'contract_id' => $contract->getKey(),
        ]);
    }
}