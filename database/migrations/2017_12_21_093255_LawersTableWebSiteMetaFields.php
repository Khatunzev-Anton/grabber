<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LawersTableWebSiteMetaFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lawers', function(Blueprint $t) {
            $t->text('googlequerystring')->nullable();
            $t->text('googlewebsitemetadescription')->nullable();
            $t->text('googlewebsitemetakeywords')->nullable();
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
            $t->dropColumn('googlequerystring');
            $t->dropColumn('googlewebsitemetadescription');
            $t->dropColumn('googlewebsitemetakeywords');
        });
    }
}
