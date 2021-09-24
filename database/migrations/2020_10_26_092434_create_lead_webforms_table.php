<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadWebformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_webforms', function (Blueprint $table) {
            $table->id();

            //lead type
            $table->string('customer_type', 20);

            //customer information
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('address', 100);
            $table->string('city', 30);
            $table->string('suburb', 20);
            $table->string('postcode', 10);
            $table->string('state', 20);
            $table->string('country', 30);
            $table->string('email', 100);
            $table->string('contact_number', 20);

            //building/house detail
            $table->string('house_type', 50)->nullable();
            $table->string('roof_preference', 20)->nullable();
            $table->string('source', 50)->nullable();
            $table->string('comments')->nullable();
            $table->string('gutter_edge_meters', 10)->nullable();
            $table->string('valley_meters', 10)->nullable();
            $table->string('commercial', 20)->nullable();
            $table->string('situation', 100)->nullable();
            $table->tinyInteger('is_uploaded')->default(0);

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
        Schema::dropIfExists('lead_webforms');
    }
}
