<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->unsigned()->nullable();
            $table->integer('meters_gutter_edge')->default(0);
            $table->integer('meters_valley')->default(0);
            $table->char('comments', 200)->nullable();
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
        Schema::dropIfExists('lead_jobs');
    }
}
