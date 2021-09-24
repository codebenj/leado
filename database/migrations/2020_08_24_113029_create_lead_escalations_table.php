<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadEscalationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_escalations', function (Blueprint $table) {
            $table->id();

            $table->string('escalation_level');
            $table->string('escalation_status');
            $table->string('color');
            $table->string('progress_period_date')->nullable();
            $table->string('gutter_edge_meters')->nullable();
            $table->string('valley_meters')->nullable();
            $table->string('installed_date')->nullable();
            $table->boolean('is_notified')->default(false);
            $table->boolean('is_active')->default(false);
            $table->text('reason')->nullable();
            $table->text('comments')->nullable();
            $table->json('metadata')->nullable();

            $table->unsignedBigInteger('lead_id')->unsigned()->nullable();
            $table->foreign('lead_id')
                  ->references('id')->on('leads')
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
        Schema::dropIfExists('lead_escalations');
    }
}
