<?php
require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
require_once 'Services/TMS/Filter/classes/class.catFilterGUI.php';

class catFilterDatePeriodGUI extends catFilterGUI {
	protected $filter;
	protected $path;
	protected $val;

	public function __construct($filter, $path) {
		global $lng;

		$this->filter = $filter;
		$this->path = $path;
		$this->gLng = $lng;
	}
	
	/**
	 * @inheritdoc
	 */
	public function formElement() {
		$duration = new ilDateDurationInputGUI($this->filter->label(), "filter[$this->path]");
		$duration->setInfo($this->filter->description());
		$duration->setShowTime(false);
		$duration->setStartText($this->gLng->txt("filter_period_from"));
		$duration->setEndText($this->gLng->txt("filter_period_to"));
		
		if($this->val) {
			$start_date = \DateTime::createFromFormat('d.m.Y',$this->val["start"])->format('Y-m-d');
			$end_date = \DateTime::createFromFormat('d.m.Y',$this->val["end"])->format('Y-m-d');
			$duration->setStart(new ilDateTime($start_date, IL_CAL_DATE));
			$duration->setEnd(new ilDateTime($end_date, IL_CAL_DATE));
		} else {
			$duration->setStart(new ilDateTime($this->filter->default_begin()->format("Y-m-d 00:00:00"),IL_CAL_DATETIME));
			$duration->setEnd(new ilDateTime($this->filter->default_end()->format("Y-m-d 00:00:00"),IL_CAL_DATETIME));
		}
		
		$duration->setStartYear($this->filter->period_min()->format("Y"));

		return $duration;
	}

	public function setValue($val) {
		$this->val = $val;
	}
}
