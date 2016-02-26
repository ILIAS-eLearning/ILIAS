<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilSelectInputGUI.php");
require_once("Services/Form/classes/class.ilHiddenInputGUI.php");

class catFilterOptionGUI {
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
		$select = new ilSelectInputGUI($this->filter->label(), "filter[$this->path]");
		$select->setInfo($this->filter->description());
		$select->setOptions(array("0"=>"Ja","1"=>"Nein"));
		$form->addItem($select);

		return $form;
	}
}