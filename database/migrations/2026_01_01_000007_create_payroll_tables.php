<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 7)->unique();
            $table->string('name');
            $table->string('state', 1)->default('p');
            $table->boolean('fixed')->default(false);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['code', 'name'], 'salary_components_idx');
        });

        Schema::create('salary_benefits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('component_id')->nullable()->constrained('salary_components');
            $table->text('benefit_value')->nullable();
            $table->string('benefit_key')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['employee_id', 'component_id'], 'salary_benefits_unique');
        });

        Schema::create('salary_benefit_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('component_id')->nullable()->constrained('salary_components');
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts');
            $table->text('new_benefit_value')->nullable();
            $table->text('old_benefit_value')->nullable();
            $table->string('benefit_key')->nullable();
            $table->string('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['employee_id', 'component_id'], 'salary_benefit_histories_unique');
            $table->unique('contract_id');
        });

        Schema::create('salary_allowances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('component_id')->nullable()->constrained('salary_components');
            $table->smallInteger('year');
            $table->smallInteger('month');
            $table->text('benefit_value')->nullable();
            $table->string('benefit_key')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['employee_id', 'component_id', 'year', 'month'], 'salary_allowances_unique');
        });

        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->nullable()->constrained('companies');
            $table->smallInteger('year');
            $table->smallInteger('month');
            $table->boolean('closed')->default(false);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['month', 'year'], 'payroll_periods_idx');
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('period_id')->nullable()->constrained('payroll_periods');
            $table->text('take_home_pay')->nullable();
            $table->string('take_home_pay_key')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['employee_id', 'period_id'], 'payrolls_unique');
        });

        Schema::create('payroll_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_id')->nullable()->constrained('payrolls');
            $table->foreignUuid('component_id')->nullable()->constrained('salary_components');
            $table->text('benefit_value');
            $table->string('benefit_key')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('company_costs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_id')->nullable()->constrained('payrolls');
            $table->foreignUuid('component_id')->nullable()->constrained('salary_components');
            $table->text('benefit_value');
            $table->string('benefit_key')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('taxs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('period_id')->nullable()->constrained('payroll_periods');
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->string('tax_group', 3);
            $table->text('untaxable')->nullable();
            $table->text('taxable')->nullable();
            $table->text('tax_value')->nullable();
            $table->string('tax_key')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxs');
        Schema::dropIfExists('company_costs');
        Schema::dropIfExists('payroll_details');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('salary_allowances');
        Schema::dropIfExists('salary_benefit_histories');
        Schema::dropIfExists('salary_benefits');
        Schema::dropIfExists('salary_components');
    }
};