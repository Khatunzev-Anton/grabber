<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQueryStringFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lawers', function(Blueprint $t) {
            $t->text('googlequerystring1')->nullable();
            $t->text('googlequerystring2')->nullable();
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
            $t->dropColumn('googlequerystring1');
            $t->dropColumn('googlequerystring2');
        });
    }
}
