<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganisationPostcodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisation_postcodes', function (Blueprint $table) {
            $table->id();

            $table->string('postcode');

            $table->unsignedBigInteger('organisation_id')->unsigned()->nullable();
            $table->foreign('organisation_id')
                  ->references('id')->on('organisations')
                  ->onDelete('cascade');

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
        Schema::dropIfExists('organisation_postcodes');
    }
}
