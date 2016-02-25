<?php
require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");

class catFilterDatePeriodGUI {
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
		$duration = new ilDateDurationInputGUI($this->filter->label(), $this->path);
		$duration->setInfo($this->filter->description());
		$duration->setShowDate(true);
		$duration->setShowTime(false);
		$form->addItem($duration);

		return $form;
	}
}