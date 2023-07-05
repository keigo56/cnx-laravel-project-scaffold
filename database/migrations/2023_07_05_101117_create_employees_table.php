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
        Schema::create('employees', function (Blueprint $table) {
            $table->string('workday_id', 50)->primary();
            $table->string('name', 200)->nullable();
            $table->string('FirstName', 100)->nullable();
            $table->string('LastName', 100)->nullable();
            $table->string('MiddleName', 100)->nullable();
            $table->string('State', 100)->nullable();
            $table->string('City', 100)->nullable();
            $table->string('Country', 100)->nullable();
            $table->string('WorkCategory', 100)->nullable();
            $table->string('EmailAddress', 200)->nullable();
            $table->date('HireDate')->nullable();
            $table->string('Position', 100)->nullable();
            $table->string('MSAClient', 200)->nullable();
            $table->string('GradeLevel', 10)->nullable();
            $table->string('SiteLocation', 50)->nullable();
            $table->string('Account', 100)->nullable();
            $table->string('SupervisorID', 50)->nullable();
            $table->string('SupervisorName', 200)->nullable();
            $table->string('SupervisorEmail', 200)->nullable();
            $table->string('2ndLineSupervisorID', 50)->nullable();
            $table->string('2ndLineSupervisorName', 200)->nullable();
            $table->string('2ndLineSupervisorEmail', 200)->nullable();
            $table->string('Covid19_Work_Related_Status', 100)->nullable();
            $table->string('EmployeeStatus', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
