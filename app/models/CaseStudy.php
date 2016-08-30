<?php

class CaseStudy extends \BaseModel {

	protected $table = 'cases';
	protected $fillable = [
		'name',
		'background',
		'challenges',
		'strategy',
		'result',
		'unit_id',
		'user_id',
		'location_id',
		'account_director_id',
		'sector_id',
		'product_id'
	];

	public function add( $case ) {
		$this->name                = $case->name;
		$this->year                = $case->year;
		$this->background          = $case->background;
		$this->challenges          = $case->challenges;
		$this->strategy            = $case->strategy;
		$this->unit_id             = $case->unit_id;
		$this->user_id             = $case->user_id;
		$this->location_id         = $case->location_id;
		$this->account_director_id = $case->account_director_id;
		$this->sector_id           = $case->sector_id;
		$this->product_id          = $case->product_id;
		$this->save();

		return $this;
	}

	public function edit( $case ) {
		$update_case                      = $this->find( $case->id );
		$update_case->name                = $case->name;
		$update_case->year                = $case->year;
		$update_case->background          = $case->background;
		$update_case->challenges          = $case->challenges;
		$update_case->strategy            = $case->strategy;
		$update_case->unit_id             = $case->unit_id;
		$update_case->user_id             = $case->user_id;
		$update_case->location_id         = $case->location_id;
		$update_case->account_director_id = $case->account_director_id;
		$update_case->sector_id           = $case->sector_id;
		$update_case->product_id          = $case->product_id;
		$update_case->save();

		return $update_case;
	}
}