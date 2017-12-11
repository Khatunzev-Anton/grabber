<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LawersTableAddGoogleFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lawers', function(Blueprint $t) {
            $t->boolean('parsedwithgoogle')->default(false)->nullable();
            $t->string('googlename',255)->nullable();
            $t->string('googleaddress',255)->nullable();
            $t->string('googlephone',255)->nullable();
            $t->string('googlewebsite',255)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lawers', function(Blueprint $t) {
            $t->dropColumn('parsedwithgoogle');
            $t->dropColumn('googlename');
            $t->dropColumn('googleaddress');
            $t->dropColumn('googlephone');
            $t->dropColumn('googlewebsite');

        });

    }
}
