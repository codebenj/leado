<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('customer_type');
            $table->string('house_type');
            $table->string('roof_preference');
            $table->string('commercial');
            $table->string('gutter_edge_meters');
            $table->string('valley_meters');
            $table->string('source');
            $table->string('sale_string');
            $table->float('sale');
            $table->string('staff_comments')->nullable();
            $table->string('comments')->nullable();
            $table->string('situation')->nullable();

            $table->unsignedBigInteger('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')
                  ->references('id')->on('customers')
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
        Schema::dropIfExists('leads');
    }
}
