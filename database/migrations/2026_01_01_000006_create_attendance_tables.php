<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shiftments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 7)->unique();
            $table->string('name');
            $table->time('start_hour');
            $table->time('end_hour');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['code', 'name'], 'shiftments_idx');
        });

        Schema::create('workshifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('shiftment_id')->nullable()->constrained('shiftments');
            $table->string('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['employee_id', 'shiftment_id', 'start_date', 'end_date'], 'workshifts_unique');
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('shiftment_id')->nullable()->constrained('shiftments');
            $table->date('attendance_date');
            $table->string('description')->nullable();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('early_in')->default(0);
            $table->integer('early_out')->default(0);
            $table->integer('late_in')->default(0);
            $table->integer('late_out')->default(0);
            $table->boolean('absent')->default(false);
            $table->foreignUuid('reason_id')->nullable()->constrained('absent_reasons');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['employee_id', 'attendance_date'], 'attendances_unique');
        });

        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->smallInteger('year');
            $table->smallInteger('month');
            $table->integer('total_workday');
            $table->integer('total_in');
            $table->integer('total_loyality');
            $table->integer('total_absent');
            $table->integer('total_overtime');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->index(['month', 'year'], 'attendance_summaries_idx');
        });

        Schema::create('overtimes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->foreignUuid('shiftment_id')->nullable()->constrained('shiftments');
            $table->date('overtime_date');
            $table->time('start_hour');
            $table->time('end_hour');
            $table->double('raw_value');
            $table->double('calculated_value');
            $table->boolean('holiday')->default(false);
            $table->boolean('overday')->default(false);
            $table->foreignUuid('approved_by_id')->nullable()->constrained('employees');
            $table->string('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['employee_id', 'overtime_date'], 'overtimes_unique');
        });

        Schema::create('leaves', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->date('leave_date');
            $table->foreignUuid('reason_id')->nullable()->constrained('absent_reasons');
            $table->smallInteger('amount')->default(1);
            $table->string('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['employee_id', 'leave_date'], 'leaves_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
        Schema::dropIfExists('overtimes');
        Schema::dropIfExists('attendance_summaries');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('workshifts');
        Schema::dropIfExists('shiftments');
    }
};