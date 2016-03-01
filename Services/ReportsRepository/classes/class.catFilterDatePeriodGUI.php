<?php
require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
require_once("Services/ReportsRepository/classes/class.catFilterGUI.php");

class catFilterDatePeriodGUI extends catFilterGUI {
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
		$duration = new ilDateDurationInputGUI($this->filter->label(), "filter[$this->path]");
		$duration->setInfo($this->filter->description());
		$duration->setShowDate(true);
		$duration->setShowTime(false);
		$duration->setStart(new ilDateTime('2008-06-12 08:00:00',IL_CAL_DATETIME));
		$form->addItem($duration);

		return $form;
	}
}