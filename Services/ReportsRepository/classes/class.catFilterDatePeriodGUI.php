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
	
	/**
	 * @inheritdoc
	 */
	public function formElement() {
		$duration = new ilDateDurationInputGUI($this->filter->label(), "filter[$this->path]");
		$duration->setInfo($this->filter->description());
		$duration->setShowDate(true);
		$duration->setShowTime(false);
		$duration->setStart(new ilDateTime($this->filter->default_begin()->format("Y-m-d 00:00:00"),IL_CAL_DATETIME));
		$duration->setEnd(new ilDateTime($this->filter->default_end()->format("Y-m-d 00:00:00"),IL_CAL_DATETIME));
		$duration->setStartYear($this->filter->period_min()->format("Y"));

		return $duration;
	}
}