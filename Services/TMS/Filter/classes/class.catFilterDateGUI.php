<?php

require_once("Services/Form/classes/class.ilDateTimeInputGUI.php");
require_once 'Services/TMS/Filter/classes/class.catFilterGUI.php';

class catFilterDateGUI extends catFilterGUI {
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
		$date_input = new ilDateTimeInputGUI($this->filter->label(), "filter[$this->path]");
		$date_input->setInfo($this->filter->description());
		$date_input->setShowTime(false);
		
		if($this->val) {
			$date = \DateTime::createFromFormat('d.m.Y',$this->val)->format('Y-m-d');

			$date_input->setDate(new ilDateTime($date, IL_CAL_DATE));
		} else {
			$date_input->setDate(new ilDateTime($this->filter->default_date()->format("Y-m-d"),IL_CAL_DATE));
		}
		
		return $date_input;
	}

	public function setValue($val) {
		$this->val = $val;
	}
}