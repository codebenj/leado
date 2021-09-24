<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('street_address');
            $table->text('suburb');
            $table->text('state')->nullable();
            $table->text('postcode');
            $table->text('phone_number');
            $table->text('contact')->nullable();
            $table->text('keep_stock')->nullable();
            $table->text('code');
            $table->double('last_year_sales', 8, 2)->nullable();
            $table->double('year_to_date_sales', 8, 2)->nullable();
            $table->text('pricing_book')->nullable();
            $table->text('priority')->nullable();
            $table->text('stock_kits')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('stores');
    }
}
