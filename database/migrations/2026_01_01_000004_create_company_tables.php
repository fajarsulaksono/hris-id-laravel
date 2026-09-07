<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('code', 7)->unique();
            $table->string('name');
            $table->date('birth_day');
            $table->string('email');
            $table->string('tax_number');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['code', 'name'], 'companies_idx');
        });

        Schema::create('company_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('address');
            $table->foreignUuid('region_id')->nullable()->constrained('regions')->cascadeOnDelete();
            $table->foreignUuid('city_id')->nullable()->constrained('cities')->cascadeOnDelete();
            $table->string('postal_code', 5);
            $table->string('phone_number', 17);
            $table->string('fax_number', 11);
            $table->boolean('default_address')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('departments')->cascadeOnDelete();
            $table->string('code', 7)->unique();
            $table->string('name');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['code', 'name'], 'departments_idx');
        });

        Schema::create('company_departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->cascadeOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['department_id', 'company_id']);
        });

        Schema::create('job_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('job_levels')->cascadeOnDelete();
            $table->string('code', 7)->unique();
            $table->string('name');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['code', 'name'], 'job_levels_idx');
        });

        Schema::create('job_titles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_level_id')->nullable()->constrained('job_levels')->cascadeOnDelete();
            $table->string('code', 9)->unique();
            $table->string('name');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['code', 'name'], 'job_titles_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_titles');
        Schema::dropIfExists('job_levels');
        Schema::dropIfExists('company_departments');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('company_addresses');
        Schema::dropIfExists('companies');
    }
};