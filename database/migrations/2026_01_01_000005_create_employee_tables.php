<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('join_date');
            $table->string('employee_status', 1)->default('t');
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts');
            $table->foreignUuid('company_id')->nullable()->constrained('companies');
            $table->foreignUuid('department_id')->nullable()->constrained('departments');
            $table->foreignUuid('job_level_id')->nullable()->constrained('job_levels');
            $table->foreignUuid('job_title_id')->nullable()->constrained('job_titles');
            $table->foreignUuid('supervisor_id')->nullable()->constrained('employees');
            $table->string('code', 17)->unique();
            $table->string('full_name');
            $table->string('gender', 1)->default('m');
            $table->foreignUuid('region_of_birth_id')->nullable()->constrained('regions');
            $table->foreignUuid('city_of_birth_id')->nullable()->constrained('cities');
            $table->date('date_of_birth');
            $table->string('identity_number', 27)->unique();
            $table->string('identity_type', 1)->default('k');
            $table->string('marital_status', 1)->default('s');
            $table->string('email')->unique();
            $table->integer('leave_balance')->nullable()->default(12);
            $table->string('tax_group', 3)->default('tk0');
            $table->date('resign_date')->nullable();
            $table->boolean('have_overtime_benefit')->default(false);
            $table->string('risk_ratio', 3)->default('vlr');
            $table->string('username')->unique();
            $table->string('password');
            $table->json('roles')->nullable();
            $table->string('profile_image')->nullable();
            $table->integer('profile_size')->nullable();
            $table->rememberToken();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['code', 'full_name', 'username'], 'employees_idx');
        });

        Schema::create('employee_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->string('address');
            $table->foreignUuid('region_id')->nullable()->constrained('regions');
            $table->foreignUuid('city_id')->nullable()->constrained('cities');
            $table->string('postal_code', 5);
            $table->string('phone_number', 17);
            $table->string('fax_number', 11)->nullable();
            $table->boolean('default_address')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignUuid('address_id')->nullable()->after('email')->constrained('employee_addresses');
        });

        Schema::create('job_placements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('company_id')->nullable()->constrained('companies');
            $table->foreignUuid('department_id')->nullable()->constrained('departments');
            $table->foreignUuid('job_level_id')->nullable()->constrained('job_levels');
            $table->foreignUuid('job_title_id')->nullable()->constrained('job_titles');
            $table->foreignUuid('supervisor_id')->nullable()->constrained('employees');
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts');
            $table->boolean('active')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('job_mutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 1)->default('m');
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('old_company_id')->nullable()->constrained('companies');
            $table->foreignUuid('old_department_id')->nullable()->constrained('departments');
            $table->foreignUuid('old_job_level_id')->nullable()->constrained('job_levels');
            $table->foreignUuid('old_job_title_id')->nullable()->constrained('job_titles');
            $table->foreignUuid('old_supervisor_id')->nullable()->constrained('employees');
            $table->foreignUuid('new_company_id')->nullable()->constrained('companies');
            $table->foreignUuid('new_department_id')->nullable()->constrained('departments');
            $table->foreignUuid('new_job_level_id')->nullable()->constrained('job_levels');
            $table->foreignUuid('new_job_title_id')->nullable()->constrained('job_titles');
            $table->foreignUuid('new_supervisor_id')->nullable()->constrained('employees');
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('career_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('company_id')->nullable()->constrained('companies');
            $table->foreignUuid('department_id')->nullable()->constrained('departments');
            $table->foreignUuid('job_level_id')->nullable()->constrained('job_levels');
            $table->foreignUuid('job_title_id')->nullable()->constrained('job_titles');
            $table->foreignUuid('supervisor_id')->nullable()->constrained('employees');
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts');
            $table->string('description', 11);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['employee_id', 'company_id', 'department_id', 'job_level_id', 'job_title_id', 'supervisor_id'], 'career_histories_unique');
            $table->unique('contract_id');
        });

        Schema::create('tax_group_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->string('old_tax_group', 3);
            $table->string('new_tax_group', 3)->nullable();
            $table->string('old_risk_ratio', 3);
            $table->string('new_risk_ratio', 3)->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
        });

        Schema::dropIfExists('tax_group_history');
        Schema::dropIfExists('career_histories');
        Schema::dropIfExists('job_mutations');
        Schema::dropIfExists('job_placements');
        Schema::dropIfExists('employee_addresses');
        Schema::dropIfExists('employees');
    }
};