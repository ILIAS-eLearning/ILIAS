<?php
require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
require_once("Services/ReportsRepository/classes/class.catFilterGUI.php");

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
		$duration->setShowDate(true);
		$duration->setShowTime(false);
		$duration->setStartText($this->gLng->txt("gev_filter_period_from"));
		$duration->setEndText($this->gLng->txt("gev_filter_period_to"));
		
		if($this->val) {
			$start_date = $this->val["start"]["date"]["y"]."-".str_pad($this->val["start"]["date"]["m"], 2, "0", STR_PAD_LEFT)
							."-".str_pad($this->val["start"]["date"]["d"], 2, "0", STR_PAD_LEFT)." 00:00:00";
			$end_date = $this->val["end"]["date"]["y"]."-".str_pad($this->val["end"]["date"]["m"], 2, "0", STR_PAD_LEFT)
							."-".str_pad($this->val["end"]["date"]["d"], 2, "0", STR_PAD_LEFT)." 00:00:00";

			$duration->setStart(new ilDateTime($start_date, IL_CAL_DATETIME));
			$duration->setEnd(new ilDateTime($end_date, IL_CAL_DATETIME));
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