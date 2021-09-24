<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsInLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('house_type')->nullable()->change();
            $table->string('roof_preference')->nullable()->change();
            $table->string('commercial')->nullable()->change();
            $table->string('gutter_edge_meters')->nullable()->change();
            $table->string('valley_meters')->nullable()->change();
            $table->string('source')->nullable()->change();
            $table->string('sale_string')->nullable()->change();
            $table->float('sale')->nullable()->change();

            $table->string('enquirer_message')->nullable();
            $table->string('received_via')->nullable();
            $table->tinyInteger('notify_enquirer')->default(0);
            $table->string('use_for')->nullable();
            $table->string('source_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
}
