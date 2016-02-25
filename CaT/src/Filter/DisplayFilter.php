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
	protected $gui_factory;

	public function __construct(FilterGUIFactory $gui_factory) {
		$this->gui_factory = $gui_factory;
	}

	/**
	* get next filter
	*
	*/
	protected function getNextFilter(Navigator $navi) {
		if($next = $this->getNextRight($navi)) {
			return $next;
		} else {
			if($next = $this->getNextUpRight($navi)) {
				return $next;
			}
		}

		return false;
	}

	/**
	* get next filter gui
	*
	* @param $first_filter 		user filter at path 0 or not
	*/
	public function getNextFilterGUI(Filters\Sequence $sequence, array $post_values) {
		$navi = new Navigator($sequence);

		if(empty($post_values)) {
			$navi->go_to("0");
			$filter = $navi->current();
		} else {
			$last_path = $this->endKey($post_values);
			$navi->go_to($last_path);
			$filter = $this->getNextFilter($navi);
		}
		
		if(!$filter) {
			return false;
		}

		return $this->getNextGUI($filter, $navi);
	}

	public function getNextGUI($filter, Navigator $navi) {
		$filter_class = get_class($filter);

		switch($filter_class) {
			case "CaT\Filter\Filters\DatePeriod":
				return $this->gui_factory->dateperiod_gui($filter, $navi->path());
				break;
			case "CaT\Filter\Filters\Multiselect":
				return $this->gui_factory->multiselect_gui($filter, $navi->path());
				break;
			case "CaT\Filter\Filters\Option":
				return $this->gui_factory->option_gui($filter, $navi->path());
				break;
			case "CaT\Filter\Filters\Text":
				return $this->gui_factory->text_gui($filter, $navi->path());
				break;
			case "CaT\Filter\Filters\Sequence":
				try {
					$navi->enter();
					return $this->getNextGUI($navi->current(),$navi);
				} catch (\OutOfBoundsException $e) {
					return false;
				}
				break;
			case "CaT\Filter\Filters\OneOf":
				return $this->gui_factory->one_of_gui($filter, $navi->path());
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
	protected function getNextRight(Navigator $navi) {
		try{
			$navi->right();
			return $navi->current();
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
	protected function getNextUpRight(Navigator $navi) {
		while($this->getUp($navi)) {
			$tmp = $this->getNextRight($navi); 

			if($tmp) {
				return $tmp;
			}
		}

		return false;
	}

	/**
	* get node in tree one step up
	*
	* @return upper_filter || false
	*/
	protected function getUp(Navigator $navi) {
		try {
			$navi->up();
			return $navi->current();
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
	protected function getDatePeriod($filter) {
		
	}

	/**
	* render the Multiselect Filter
	*
	* @param $filter 
	*/
	protected function getMultiselect($filter) {
		require_once ("Services/ReportsRepository/classes/class.catFilterMultiselectGUI.php");
		$gui = new \catFilterMultiselectGUI($this->parent, $filter, $this->navi->path(), $this->post_values);
		return $gui;
	}

	/**
	* render the Option Filter
	*
	* @param $filter 
	*/
	protected function getOption($filter) {
		require_once ("Services/ReportsRepository/classes/class.catFilterOptionGUI.php");
		$gui = new \catFilterOptionGUI($this->parent, $filter, $this->navi->path(), $this->post_values);
		return $gui;
	}

	/**
	* render the Text Filter
	*
	* @param $filter 
	*/
	protected function getText($filter) {
		require_once ("Services/ReportsRepository/classes/class.catFilterTextGUI.php");
		$gui = new \catFilterTextGUI($this->parent, $filter, $this->navi->path(), $this->post_values);
		return $gui;
	}

	/**
	* render the OneOf Filter
	*
	* @param $filter
	*/
	protected function getOneOf($filter) {
		require_once ("Services/ReportsRepository/classes/class.catFilterOneOfGUI.php");
		$gui = new \catFilterOneOfGUI($this->parent, $filter, $this->navi->path(), $this->post_values);
		return $gui;
	}

	protected function endKey($post_values){
		end($post_values);
		return key($post_values);
	}
}