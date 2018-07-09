<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParsedWithGoogleAlternativeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lawers', function(Blueprint $t) {
            $t->boolean('parsedwithgooglealternative')->default(false)->nullable();
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
            $t->dropColumn('parsedwithgooglealternative');
        });
    }
}
