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
        Schema::create('detail_playlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detail_playlist_playlists_id');
            $table->unsignedBigInteger('detail_playlist_song_id');
            $table->foreign('detail_playlist_playlists_id')->references('id')->on('playlists')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('detail_playlist_song_id')->references('id')->on('songs')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('detail_playlist');
    }
};
