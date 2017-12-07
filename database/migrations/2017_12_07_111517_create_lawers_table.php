<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLawersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lawers', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('placeId')->nullable();
            $t->bigInteger('uniqueId')->nullable();
            $t->string('name',255)->nullable();
            $t->string('phone',255)->nullable();
            $t->string('email',255)->nullable();
            $t->string('website',255)->nullable();
            $t->string('streetAddress',255)->nullable();
            $t->string('postCode',255)->nullable();
            $t->string('addressLocality',255)->nullable();
            $t->string('lat',40)->nullable();
            $t->string('lon',40)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lawers');
    }
}
