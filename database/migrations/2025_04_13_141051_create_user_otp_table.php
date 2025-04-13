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
        Schema::create('users_otp', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('user_email');
            $table->integer('otp');
            $table->timestamp('otp_generated_at');
            $table->string('login_authorization_id');
            $table->timestamp('last_login_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_otp');
    }
};
