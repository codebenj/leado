<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrganisationIdFieldInLeadEscalationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_escalations', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->unsigned()->nullable();
            $table->foreign('organisation_id')
                  ->references('id')->on('organisations')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_escalations', function (Blueprint $table) {
            $table->dropForeign('lead_escalations_organisation_id_foreign');
            $table->dropColumn('organisation_id');
        });
    }
}
