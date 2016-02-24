<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

/**
* Decides which kind of Filter should be displayed and initialize GUI
*/
class DisplayFilter {
	const START_PATH = "0";

	protected $sequence;
	protected $parent;
	protected $post_values;

	public function __construct(Filters\Sequence $sequence, $parent, array $post_values = array(), $position = "0") {
		assert(is_string($position));

		$this->sequence = $sequence;
		$this->parent = $parent;
		$this->post_values = (!empty($post_values)) ? unserialize($post_values) : $post_values;
		$this->position = $position;

		$this->navi = (new Navigator($this->sequence))->go_to($position);
	}

	/**
	* saves data from current FilterGUI into $post_array
	*/
	public function saveFilter() {
		$this->post_values[$this->navi->path()] = "Hallo";
		return $this->getNextFilterGUI(false);
	}

	/**
	* render next filter gui
	*
	* @param $first_filter 		user filter at position 0 or not
	*/
	public function getNextFilterGUI($first_filter = true) {
		if($first_filter) {
			$this->navi->go_to("0");
			$this->position = "0";
			return $this->filterGUI($this->navi->current());
		}

		if($next = $this->getNextRight()) {
			return $this->filterGUI($next);
		} else {
			if($next = $this->getNextUpRight()) {
				return $this->filterGUI($next);
			}
		}

		return true;
	}

	protected function filterGUI($filter) {
		$filter_class = get_class($filter);

		switch($filter_class) {
			case "CaT\Filter\Filters\DatePeriod":
				return $this->renderDatePeriod($filter);
				break;
			case "CaT\Filter\Filters\Multiselect":
				return $this->renderMultiselect($filter);
				break;
			case "CaT\Filter\Filters\Option":
				return $this->renderOption($filter);
				break;
			case "CaT\Filter\Filters\Text":
				return $this->renderText($filter);
				break;
			case "CaT\Filter\Filters\Sequence":
				$this->navi->enter();
				return $this->filterGUI($this->navi->current());
				break;
			case "CaT\Filter\Filters\OneOf":
				return $this->renderOneOf($filter);
				break;
			default:
				throw new \Exception("Filter class not known");
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
		require_once ("Services/ReportsRepository/classes/class.catFilterDatePeriodGUI.php");
		$gui = new \catFilterDatePeriodGUI($this->parent, $filter, $this->navi->path(), $this->post_values);
		return $gui;
	}

	/**
	* render the Multiselect Filter
	*
	* @param $filter 
	*/
	protected function renderMultiselect($filter) {
		require_once ("Services/ReportsRepository/classes/class.catFilterMultiselectGUI.php");
		$gui = new \catFilterMultiselectGUI($this->parent, $filter, $this->navi->path(), $this->post_values);
		return $gui;
	}

	/**
	* render the Option Filter
	*
	* @param $filter 
	*/
	protected function renderOption($filter) {
		require_once ("Services/ReportsRepository/classes/class.catFilterOptionGUI.php");
		$gui = new \catFilterOptionGUI($this->parent, $filter, $this->navi->path(), $this->post_values);
		return $gui;
	}

	/**
	* render the Text Filter
	*
	* @param $filter 
	*/
	protected function renderText($filter) {
		require_once ("Services/ReportsRepository/classes/class.catFilterTextGUI.php");
		$gui = new \catFilterTextGUI($this->parent, $filter, $this->navi->path(), $this->post_values);
		return $gui;
	}

	/**
	* render the OneOf Filter
	*
	* @param $filter
	*/
	protected function renderOneOf($filter) {
		require_once ("Services/ReportsRepository/classes/class.catFilterOneOfGUI.php");
		$gui = new \catFilterOneOfGUI($this->parent, $filter, $this->navi->path(), $this->post_values);
		return $gui;
	}
}