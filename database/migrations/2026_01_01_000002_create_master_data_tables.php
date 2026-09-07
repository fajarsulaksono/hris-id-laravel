<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educational_institutes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['name'], 'educational_institutes_idx');
        });

        Schema::create('education_titles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('short_name', 5)->unique();
            $table->string('name');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['short_name', 'name'], 'education_titles_idx');
        });

        Schema::create('skill_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('skill_groups')->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['name'], 'skill_groups_idx');
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('skill_group_id')->nullable()->constrained('skill_groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['name'], 'skills_idx');
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('holiday_date');
            $table->string('name');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['name'], 'holidays_idx');
        });

        Schema::create('absent_reasons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 1);
            $table->string('code', 7);
            $table->string('name');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['type', 'code']);
            $table->index(['code', 'name'], 'absent_reasons_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absent_reasons');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_groups');
        Schema::dropIfExists('education_titles');
        Schema::dropIfExists('educational_institutes');
    }
};