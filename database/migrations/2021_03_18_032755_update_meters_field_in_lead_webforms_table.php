<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMetersFieldInLeadWebformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_webforms', function (Blueprint $table) {
            $table->string('gutter_edge_meters')->nullable()->change();
            $table->string('valley_meters')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_webforms', function (Blueprint $table) {
            //
        });
    }
}
