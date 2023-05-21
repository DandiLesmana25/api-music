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
            $table->string('judul');
            $table->string('artis');
            $table->date('tanggal_rilis')->nullable();
            $table->string('genre')->nullable();
            $table->string('cover')->nullable();
            $table->unsignedBigInteger('id_user'); // Tambahkan kolom id_user
            $table->string('status')->default('private'); // Tambahkan kolom status dengan nilai default 'pending'
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
