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
        Schema::create('quotes', function (Blueprint $table) {
            $table->increments('id');
            $table->text('quote');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('book_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->OnDelete('cascade');
            $table->foreign('book_id')->references('id')->on('library_books')->OnDelete('cascade');
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
        Schema::dropIfExists('quotes');
    }
};
