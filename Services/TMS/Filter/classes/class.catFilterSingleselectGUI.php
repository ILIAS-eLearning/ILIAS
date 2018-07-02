<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilSelectInputGUI.php");
require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
require_once 'Services/TMS/Filter/classes/class.catFilterGUI.php';

class catFilterSingleselectGUI extends catFilterGUI {
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
		$select = new ilSelectInputGUI($this->filter->label(), "filter[$this->path]");
		$select->setInfo($this->filter->description());
		$opts = $this->filter->options();
		asort($opts,  SORT_NATURAL | SORT_FLAG_CASE);
		$select->setOptions($opts);
		
		if($this->val) {
			$select->setValue($this->val);
		} else {
			$select->setValue($this->filter->default_choice());
		}

		return $select;
	}

	public function setValue($val) {
		$this->val = $val;
	}
}