<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_families', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->string('relation', 1)->default('c');
            $table->string('name');
            $table->string('gender', 1)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('identity_number', 27)->nullable();
            $table->string('job')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['employee_id', 'name'], 'employee_families_idx');
        });

        Schema::create('employee_educations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('education_institute_id')->nullable()->constrained('educational_institutes');
            $table->foreignUuid('education_title_id')->nullable()->constrained('education_titles');
            $table->year('year')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['employee_id', 'year'], 'employee_educations_idx');
        });

        Schema::create('employee_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('skill_id')->nullable()->constrained('skills');
            $table->string('level', 1)->default('b');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['employee_id', 'skill_id'], 'employee_skills_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('employee_educations');
        Schema::dropIfExists('employee_families');
    }
};
