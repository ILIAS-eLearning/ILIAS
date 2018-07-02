<?php
require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
require_once 'Services/TMS/Filter/classes/class.catFilterGUI.php';

class catFilterMultiselectGUI extends catFilterGUI {
	protected $filter;
	protected $path;
	protected $val;

	public function __construct($filter, $path) {
		$this->filter = $filter;
		$this->path = $path;
	}

	/**
	 * @inheritdoc
	 */
	public function formElement() {
		$multi_select = new ilMultiSelectInputGUI($this->filter->label(), "filter[$this->path]");
		$multi_select->setInfo($this->filter->description());
		$opts = $this->filter->options();
		asort($opts,  SORT_NATURAL | SORT_FLAG_CASE);
		$multi_select->setOptions($opts);
		
		if($this->val) {
			$multi_select->setValue($this->val);
		} else {
			$multi_select->setValue($this->filter->default_choice());
		}

		$multi_select->setWidth(250);

		return $multi_select;
	}

	public function setValue($val) {
		$this->val = $val;
	}
}