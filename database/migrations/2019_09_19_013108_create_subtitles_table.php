<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubtitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subtitles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('year');
            $table->string('url');
            $table->string('type');
            $table->string('episode')->nullable();
            $table->string('image')->nullable();
            $table->string('status');
            $table->bigInteger('author')->unsigned();
            $table->bigInteger('downloaded')->default(0);
            $table->foreign('author')->references('id')->on('users');
            $table->bigInteger('category')->unsigned();
            $table->foreign('category')->references('id')->on('categories');
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
        Schema::dropIfExists('subtitles');
    }
}
