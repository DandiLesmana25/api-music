<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreatorRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('creator_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('users_id');
            $table->timestamp('request_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('creator_requests');
    }
}
