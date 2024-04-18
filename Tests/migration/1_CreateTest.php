<?php
use SLiMS\Migration\Migration;
use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;

class CreateTest extends Migration
{
    function up()
    {
        Schema::create('test_table', function(Blueprint $table) {
            $table->autoIncrement('id');
            $table->string('name', 64)->notNull();
            $table->tinynumber('age', 3)->default(0);
            $table->text('bui')->notNull();
            $table->timestamps();
            $table->index('name');
            $table->engine = 'MyISAM';
        });
    }

    function down()
    {
        Schema::drop('test_table');
    }
}