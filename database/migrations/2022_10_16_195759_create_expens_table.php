<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expens', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('amount');
            $table->string('description')->nullable();
            $table->date('expens_date')->nullable();
            $table->foreignId('category_id');
            $table->foreign('category_id')->references('id')->on('expens_categories');
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('expens');
    }
}
