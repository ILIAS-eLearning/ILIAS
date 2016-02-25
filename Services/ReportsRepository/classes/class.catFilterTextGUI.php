<?php
require_once("Services/Form/classes/class.ilTextInputGUI.php");

class catFilterTextGUI {
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
		$input = new ilTextInputGUI($this->filter->label(), $this->path);
		$input->setInfo($this->filter->description());
		$form->addItem($input);

		return $form;
	}
}