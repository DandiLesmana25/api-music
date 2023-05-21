<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('cover');
            $table->text('lagu');
            $table->date('tanggal_rilis');
            $table->enum('status', ['pending', 'published', 'unpublished']);
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_album')->nullable()->default(null);
            $table->string('mood')->nullable();
            $table->string('genre')->nullable();
            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_album')->references('id')->on('albums')->onDelete('set null');
            $table->timestamps();
        });

        Schema::table('songs', function (Blueprint $table) {
            $table->index('id_user');
            $table->index('id_album');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('songs');
    }
}
