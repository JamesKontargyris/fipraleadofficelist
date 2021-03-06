<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCasesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cases', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name'); // acts as the background to the case study
			$table->integer('year');
			$table->string('challenges');
			$table->string('strategy');
			$table->string('result');
			$table->integer('unit_id')->unsigned()->index();
			$table->foreign('unit_id')->references('id')->on('units');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
			$table->integer('account_director_id')->unsigned()->index();
			$table->foreign('account_director_id')->references('id')->on('account_directors');
			$table->string('sector_id');
			$table->string('product_id');
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
		Schema::drop('cases');
	}

}
