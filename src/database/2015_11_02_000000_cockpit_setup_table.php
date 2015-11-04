<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CockpitSetupTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("widgets", function (Blueprint $table) {
            $table->increments('id');
            $table->string( "name" );
            $table->string( "metric");
            $table->string( "submetric" );

            $table->text( "filters");

            $table->enum( "charttype", ['ColumnChart', 'LineChart', 'PieChart'] );
            $table->enum( "timegroup", ['DAY', 'WEEK', 'MONTH', 'YEAR'] );

            $table->integer( "col" )->unsigned();
            $table->integer( "row" )->unsigned();
            $table->integer( "size_x" )->unsigned();
            $table->integer( "size_y" )->unsigned();

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

        Schema::drop("widgets");

    }
}