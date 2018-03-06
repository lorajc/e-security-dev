<?php namespace Esecurity\Projects\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('esecurity_projects_products', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('category_id');
            $table->integer('group');
            $table->string('name');
            $table->string('slug');
            $table->string('summary')->nullable();
            $table->text('description')->nullable();
            $table->boolean('published')->default(false);
            $table->double('price', 10, 2)->default(0);
            $table->double('cost', 10, 2)->default(0);
            $table->integer('items')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('esecurity_projects_products');
    }
}
