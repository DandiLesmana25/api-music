<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('users_name');
            $table->string('users_email')->unique();
            $table->string('users_password');
            $table->enum('users_role', ['admin', 'user', 'creator'])->default('user');
            $table->enum('users_req_upgrade', ['request', 'creator'])->nullable()->default(null);
            $table->timestamp('users_last_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
