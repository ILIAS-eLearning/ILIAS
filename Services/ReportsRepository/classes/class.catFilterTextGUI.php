<?php
require_once("Services/Form/classes/class.ilTextInputGUI.php");
require_once("Services/ReportsRepository/classes/class.catFilterGUI.php");

class catFilterTextGUI extends catFilterGUI {
	protected $filter;
	protected $path;

	public function __construct($filter, $path) {
		$this->filter = $filter;
		$this->path = $path;
	}

	/**
	 * @inheritdoc
	 */
	public function formElement() {
		$input = new ilTextInputGUI($this->filter->label(), "filter[$this->path]");
		$input->setInfo($this->filter->description());

		return $input;
	}
}