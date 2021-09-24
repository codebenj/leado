<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyOrgIdToOrgLocatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('org_locators', function (Blueprint $table) {
            //make mistake on first migration for this table. $table->bigInteger('org_id')->nullable(0);
            $table->bigInteger('org_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('org_locators', function (Blueprint $table) {
            //
        });
    }
}
