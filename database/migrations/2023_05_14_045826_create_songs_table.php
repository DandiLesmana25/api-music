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
            $table->string('songs_title');
            $table->text('songs_cover');
            $table->text('songs_song');
            $table->date('songs_release_date');
            $table->enum('songs_status', ['pending', 'published', 'unpublished']);
            $table->unsignedBigInteger('users_id');
            $table->unsignedBigInteger('albums_id')->nullable()->default(null);
            $table->string('songs_mood')->nullable();
            $table->string('songs_genre')->nullable();
            $table->foreign('users_id')->references('id')->on('users');
            $table->foreign('albums_id')->references('id')->on('albums')->onDelete('set null');
            $table->timestamps();
        });

        Schema::table('songs', function (Blueprint $table) {
            $table->index('users_id');
            $table->index('albums_id');
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
