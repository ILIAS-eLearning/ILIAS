<?php
require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
require_once("Services/ReportsRepository/classes/class.catFilterGUI.php");

class catFilterMultiselectGUI extends catFilterGUI {
	protected $filter;
	protected $path;

	public function __construct($filter, $path) {
		$this->filter = $filter;
		$this->path = $path;
	}

	public function path() {
		return $this->path;
	}

	public function fillForm(ilPropertyFormGUI $form) {
		$multi_select = new ilMultiSelectInputGUI($this->filter->label(), "filter[$this->path]");
		$multi_select->setInfo($this->filter->description());
		$opts = $this->filter->options();
		print_r($opts);
		asort($opts,  SORT_NATURAL | SORT_FLAG_CASE);
		print_r($opts);
		$multi_select->setOptions($opts);
		$multi_select->setValue($this->filter->default_choice());
		$form->addItem($multi_select);

		return $form;
	}
}