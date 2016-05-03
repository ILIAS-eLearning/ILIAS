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

	public function __construct(FilterGUIFactory $gui_factory, TypeFactory $type_factory) {
		$this->gui_factory = $gui_factory;
		$this->type_factory = $type_factory;
	}

	/**
	* get next filter
	*
	* @param Navigator $navi
	*
	* @return Filter|null
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

		return null;
	}

	/**
	* get next filter gui
	*
	* @param $sequence 		sequence of filters
	* @param $post_values	array of values from pre filters
	*
	* @return FilterGUI|null
	*/
	public function getNextFilterGUI(Filters\Sequence $sequence, array $post_values) {
		$navi = new Navigator($sequence);

		if(empty($post_values)) {
			$navi->go_to("0");
			$filter = $navi->current();
		} else {
			$last_path = $this->firstKey($post_values);
			$navi->go_to($last_path);
			$filter = $this->getNextFilter($navi);
		}
		
		if(!$filter) {
			return null;
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

	/**
	*
	* @param $value
	*
	* @return array|int|string
	*/
	protected function unserializeValue($value) {
		if(is_string($value) && $uns = unserialize($value)) {
			return $uns;
		}
		
		return $value;
	}

	/**
	* flatten the post values
	*
	* @param Sequence 	$squence
	* @param array 		$post_values
	*
	* @return array
	*/
	public function buildFilterValues(\CaT\Filter\Filters\Sequence $sequence, array $post_values) {
		$navi = new \CaT\Filter\Navigator($sequence);
		$ret = array();

		while ($filter = $this->getNextFilter($navi)) {
			if($filter instanceof \CaT\Filter\Filters\Sequence) {
				$navi->enter();
				$filter = $navi->current();
			}

			$current_class = get_class($filter);
			$value = $post_values[$navi->path()];
			switch($current_class) {
				case "CaT\Filter\Filters\DatePeriod":
					$value = $this->unserializeValue($value);
					$start = $this->createDateTime($value["start"]["date"]);
					$end = $this->createDateTime($value["end"]["date"]);
					array_push($ret, $start);
					array_push($ret, $end);
					break;
				case "CaT\Filter\Filters\OneOf":
					$value = $this->unserializeValue($value);
					$choice = $value["option"];
					$value = $value[$choice];
					$tf = $this->type_factory;

					// TODO: this seams to be fishy... what about other filters besides
					// multiselects?
					if ($value === null) {
						$value = array();
					}
					// TODO: this seams to be fishy too... the content needs to be casted
					// to the correct type somewhere, but is this the correct location?
					$subs = $filter->subs();
					if ($subs[(int)$choice]->input_type() == $tf->lst($tf->int())) {
						$value = array_map(function($v) { return (int)$v; }, $value);
					}

					if ($subs[(int)$choice]->input_type() == $tf->bool()) {
						$value = (bool)$value;
					}

					if ($subs[(int)$choice]->input_type() == $tf->tuple($tf->cls("\\DateTime"), $tf->cls("\\DateTime"))) {
						$start = $this->createDateTime($value["start"]["date"]);
						$end = $this->createDateTime($value["end"]["date"]);
						$value = array($start, $end);
					}
					// End of fishy castings :(

					array_push($ret, (int)$choice);
					array_push($ret, $value);
					break;
				case "CaT\Filter\Filters\Multiselect":
					$value = $this->unserializeValue($value);
					// TODO: this seams to be fishy... what about other filters besides
					// multiselects?
					if ($value === null) {
						$value = array();
					}
					array_push($ret, $value);
					break;
				case "CaT\Filter\Filters\Singleselect":
					if ($filter->input_type() == $this->type_factory->int()) {
						$value = (int)$value;
					}
				case "CaT\Filter\Filters\Text":
					array_push($ret, $value);
					break;
				case "CaT\Filter\Filters\Option":
					array_push($ret, (bool)$value);
					break;
				default:
					throw new \Exception("Filter class not known");
			}
		}

		return $ret;
	}

	protected function createDateTime(array $date) {
		return new \DateTime($date["y"]."-"
						.str_pad($date["m"], 2, "0", STR_PAD_LEFT)
						."-"
						.str_pad($date["d"], 2, "0", STR_PAD_LEFT)
					);
	}
}