<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

/**
* Decides which kind of Filter should be displayed and initialize GUI
*/
class DisplayFilter {
	protected $sequence;
	protected $post_values;

	public function __construct(Filters\Sequence $sequence, array $post_values = array(), $position = "") {
		assert(is_string($position));
		$this->sequence = $sequence;
		$this->post_values = (!empty($post_values)) ? unserialize($post_values) : $post_values;
		$this->position = ($position != "") ? unserialize($position) : array();
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
					return $this->workDatePeriod($value);
					break;
				case Filters\Multiselect:
					return $this->workMultiselect($value);
					break;
				case Filters\Option:
					return $this->workOption($value);
					break;
				case Filters\Text:
					return $this->workText($value);
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