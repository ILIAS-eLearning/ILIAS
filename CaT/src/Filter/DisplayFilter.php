<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

/**
* Decides which kind of Filter should be displayed and initialize GUI
*/
class DislpayFilter {
	protected $sequence;
	protected $position;

	public function __construct(Filters\Sequence $sequence, $position = 0) {
		$this->sequence = $sequence;
		$this->position = $position;
	}

	/**
	* starts working the sequence
	*/
	public function start() {
		$this->workSequence($this->sequence);
	}

	/**
	* works the sequence Step By Step
	*
	* @param $sequence
	*/
	protected function workSequence($sequence) {
		foreach ($sequence as $key => $value) {

			$filter_class = get_class($value);
			switch($filter_class) {
				case Filters\Sequence:
					$this->workSequence($value);
					break;
				case Filters\DatePeriod:
					$this->workDatePeriod($value);
					break;
				case Filters\Multiselect:
					$this->workMultiselect($value);
					break;
				case Filters\Option:
					$this->workOption($value);
					break;
				case Filters\Text:
					$this->workText($value);
					break;
				default:
					throw new Exception("No Known Filter");
			}
		}
	}

	/**
	* works the DatePeriod Filter
	*
	* @param $filter 
	*/
	protected function workDatePeriod($filter) {

	}

	/**
	* works the Multiselect Filter
	*
	* @param $filter 
	*/
	protected function workMultiselect($filter) {

	}

	/**
	* works the Option Filter
	*
	* @param $filter 
	*/
	protected function workOption($filter) {

	}

	/**
	* works the Text Filter
	*
	* @param $filter 
	*/
	protected function workText($filter) {

	}
}