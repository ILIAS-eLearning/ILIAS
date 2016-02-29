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
	* @param Navigator $navi
	*
	* @return Filter
	*/
	public function getNextFilter(Navigator $navi) {
		if($navi->path() === null) {
			$navi->go_to("0");
			return $navi->current();
		}

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
	* @param $sequence 		sequence of filters
	* @param $post_values	array of values from pre filters
	*
	* @return FilterGUI
	*/
	public function getNextFilterGUI(Filters\Sequence $sequence, array $post_values) {
		$navi = new Navigator($sequence);
//echo "gUI";var_dump($post_values);
		if(empty($post_values)) {
			echo 2;
			$navi->go_to("0");
			$filter = $navi->current();
		} else {
			$last_path = $this->firstKey($post_values);
			$navi->go_to($last_path);
			$filter = $this->getNextFilter($navi);
		}
		
		if(!$filter) {
			return false;
		}

		return $this->getNextGUI($filter, $navi);
	}

	/**
	* get next fui
	*
	* @param Filter 	$filter 	user filter
	* @param Navigator 	$navi 		Navigation for tree
	*
	* @return FilterGUI
	*/
	protected function getNextGUI($filter, Navigator $navi) {
		$filter_class = get_class($filter);
echo $filter_class."\n";
		switch($filter_class) {
			case "CaT\Filter\Filters\DatePeriod":
				return $this->gui_factory->dateperiod_gui($filter, $navi->path());
				break;
			case "CaT\Filter\Filters\Multiselect":
				return $this->gui_factory->multiselect_gui($filter, $navi->path());
				break;
			case "CaT\Filter\Filters\Singleselect":
				return $this->gui_factory->singleselect_gui($filter, $navi->path());
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
					return $this->getNextGUI($navi->current(), $navi);
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
	* @param Navigator 	$navi
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
	* @param Navigator 	$navi
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
	* @param Navigator 	$navi
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
	* get the key of the first element in array
	*
	* @param array
	*
	* @return string
	*/
	protected function firstKey(array $post_values) {
		return key($post_values);
	}
}