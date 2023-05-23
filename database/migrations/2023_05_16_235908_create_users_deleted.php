<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_deleted', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('users_deleted_name');
            $table->string('users_deleted_email')->unique();
            $table->timestamp('users_deleted_deleted_at')->nullable();
            $table->unsignedBigInteger('users_deleted_deleted_by')->nullable();
            $table->foreign('users_deleted_deleted_by')->references('id')->on('users')->onDelete('SET NULL');
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
        Schema::dropIfExists('users_deleted');
    }
};
