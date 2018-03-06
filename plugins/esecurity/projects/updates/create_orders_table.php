<?php namespace Esecurity\Projects\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('esecurity_projects_orders', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('product_id');
            $table->string('costumer_name');
            $table->string('costumer_email');
            $table->string('costumer_address');
            $table->string('costumer_zip');
            $table->string('costumer_phone');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('esecurity_projects_orders');
    }
}
