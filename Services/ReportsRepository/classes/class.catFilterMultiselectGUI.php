<?php
require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");

class catFilterMultiselectGUI {
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
		$multi_select->setOptions($this->filter->options());
		$form->addItem($multi_select);

		return $form;
	}
}