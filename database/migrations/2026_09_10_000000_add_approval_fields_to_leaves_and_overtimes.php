<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('amount');
            $table->foreignUuid('approved_by_id')->nullable()->after('status')->constrained('employees');
        });

        Schema::table('overtimes', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('overday');
        });

        // Kembalikan status historis: lembur yang sudah punya approver = disetujui.
        DB::table('overtimes')->whereNotNull('approved_by_id')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by_id');
            $table->dropColumn('status');
        });
    }
};
