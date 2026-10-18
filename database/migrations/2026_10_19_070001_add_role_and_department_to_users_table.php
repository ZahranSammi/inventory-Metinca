<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('karyawan')->after('email'); // karyawan, staff_accounting, manager, direktur_sdm
            $table->string('department')->default('Operasional')->after('role');
            $table->string('employee_id')->nullable()->after('department');
            $table->string('phone')->nullable()->after('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'department', 'employee_id', 'phone']);
        });
    }
};
