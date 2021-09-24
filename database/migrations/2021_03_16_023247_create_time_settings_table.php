<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // recurring or one-time
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('stop_date')->nullable();
            $table->time('stop_time')->nullable();
            $table->string('start_day')->nullable();
            $table->string('stop_day')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->json('metadata')->nullable();
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
        Schema::dropIfExists('time_settings');
    }
}
