<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLawyersGoogleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lawyersgoogle', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('placeid')->nullable();
            $t->string('uniqueid',50)->nullable();
            $t->string('name',255)->nullable();
            $t->string('phone',255)->nullable();
            $t->string('email',255)->nullable();
            $t->string('website',255)->nullable();
            $t->string('streetaddress',255)->nullable();
            $t->decimal('rating',10,4)->nullable();
            $t->integer('reviews')->nullable();
            $t->text('websitemetadescription')->nullable();
            $t->text('websitemetakeywords')->nullable();
            $t->integer('websitepagescount')->nullable();
            $t->string('websiteemail',255)->nullable();
            $t->string('websitephone',255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lawyersgoogle');
    }
}
