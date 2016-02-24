<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

/**
* Decides which kind of Filter should be displayed and initialize GUI
*/
class DisplayFilter {
	const START_PATH = "0";

	protected $sequence;
	protected $post_values;

	public function __construct(Filters\Sequence $sequence, array $post_values = array(), $position = "0") {
		assert(is_string($position));

		$this->sequence = $sequence;
		$this->post_values = (!empty($post_values)) ? unserialize($post_values) : $post_values;
		$this->position = $position;

		$this->navi = new Navigator($this->sequence, $this->position);
	}

	/**
	* saves data from current FilterGUI into $post_array
	*/
	public function saveFilter() {
		$this->post_values[$this->navi->path()] = $_POST;
		$this->renderNextFilterGUI(false);
	}

	/**
	* render next filter gui
	*
	* @param $first_filter 		user filter at position 0 or not
	*/
	public function renderNextFilterGUI($first_filter = true) {
		if($first_filter) {
			$this->navi->select("0");
			$this->position = "0";
			$this->filterGUI($this->navi->current());
		}

		if($next = $this->getNextRight()) {
			$this->filterGUI($next);
		} else {
			if($next = $this->getNextUpRight()) {
				$this->filterGUI($next);
			}
		}

		return true;
	}

	protected function filterGUI($filter) {
		$filter_class = get_class($filter);

		switch($filter_class) {
			case Filters\DatePeriod:
				$this->renderDatePeriod($filter);
				break;
			case Filters\Multiselect:
				$this->renderMultiselect($filter);
				break;
			case Filters\Option:
				$this->renderOption($filter);
				break;
			case Filters\Text:
				$this->renderText($filter);
				break;
			case Filters\Sequence:
				$this->navi->enter();
				$this->filterGUI($this->navi->current());
				break;
			case Filters\OneOf:
				$this->renderOnOf($filter);
				break;
			default:
				throw new Exception("Filter class not known");
		}
	}

	/**
	* get next right node on limb
	*
	* @return current_filter || false
	*/
	protected function getNextRight() {
		try{
			$this->navi->right();
			return $this->navi->current();
		} catch (\OutOfBoundsException $e) {
			//end of limb
			return false;
		}

		return false;
	}

	/**
	* get the next right node at any upper node
	*
	* @return current_filter || false
	*/
	protected function getNextUpRight() {
		while($this->getUp()) {
			$tmp = $this->getNextRight(); 

			if($tmp) {
				return $tmp;
			}
		}
	}

	/**
	* get node in tree one step up
	*
	* @return upper_filter || false
	*/
	protected function getUp() {
		try {
			$this->navi->up();
			return $this->navi->current();
		} catch (\OutOfBoundsException $e) {
			//top of tree reached
			return false;
		}

		return false;
	}

	/**
	* render the DatePeriod Filter
	*
	* @param $filter 
	*/
	protected function renderDatePeriod($filter) {

	}

	/**
	* render the Multiselect Filter
	*
	* @param $filter 
	*/
	protected function renderMultiselect($filter) {

	}

	/**
	* render the Option Filter
	*
	* @param $filter 
	*/
	protected function renderOption($filter) {

	}

	/**
	* render the Text Filter
	*
	* @param $filter 
	*/
	protected function renderText($filter) {

	}

	/**
	* render the OneOf Filter
	*
	* @param $filter
	*/
	protected function renderOnOf($filter) {

	}
}