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
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('cover');
            $table->text('lagu');
            // $table->time('duration');
            $table->date('tanggal_rilis');
            $table->enum('status', ['pending', 'published', 'unpublished']);
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_label')->nullable()->default(null);
            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_label')->references('id')->on('labels')->onDelete('set null');
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
        Schema::dropIfExists('songs');
    }
};
