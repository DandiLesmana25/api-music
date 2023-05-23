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

        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('albums_title');
            $table->string('albums_artist');
            $table->date('albums_release_date')->nullable();
            $table->string('albums_genre')->nullable();
            $table->string('albums_cover')->nullable();
            $table->unsignedBigInteger('users_id'); // Tambahkan kolom id_user
            $table->string('albums_status')->default('private'); // Tambahkan kolom status dengan nilai default 'pending'
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
        Schema::dropIfExists('albums');
    }
};
