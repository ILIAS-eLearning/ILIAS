<?php
require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
require_once("Services/Form/classes/class.ilRadioOption.php");
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterGUI.php';

class catFilterOneOfGUI extends catFilterGUI {
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
		//create radiogroup
		$group = new ilRadioGroupInputGUI($this->filter->label(), "filter[$this->path][option]");

		if($this->val) {
			$group->setValue($this->val["option"]);
		}

		//create options
		foreach ($this->filter->subs() as $key => $sub_filter) {
			$option = new ilRadioOption($sub_filter->label());
			$option->setInfo($sub_filter->description());
			$filter_class = get_class($sub_filter);

			switch($filter_class) {
				case "CaT\Filter\Filters\DatePeriod":
					require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
					$duration = new ilDateDurationInputGUI("", "filter[$this->path][".$key."]");
					$duration->setShowDate(true);
					$duration->setShowTime(false);
					$duration->setStartText($this->gLng->txt("gev_filter_period_from"));
					$duration->setEndText($this->gLng->txt("gev_filter_period_to"));

					if($this->val && $key == $this->val["option"]) {
						$val = $this->val[$this->val["option"]];
						$start_date = $val["start"]["date"]["y"]."-".str_pad($val["start"]["date"]["m"], 2, "0", STR_PAD_LEFT)
								."-".str_pad($val["start"]["date"]["d"], 2, "0", STR_PAD_LEFT)." 00:00:00";
						$end_date = $val["end"]["date"]["y"]."-".str_pad($val["end"]["date"]["m"], 2, "0", STR_PAD_LEFT)
								."-".str_pad($val["end"]["date"]["d"], 2, "0", STR_PAD_LEFT)." 00:00:00";

						$duration->setStart(new ilDateTime($start_date, IL_CAL_DATETIME));
						$duration->setEnd(new ilDateTime($end_date, IL_CAL_DATETIME));
					} else {
						$duration->setStart(new ilDateTime($sub_filter->default_begin()->format("Y-m-d 00:00:00"),IL_CAL_DATETIME));
						$duration->setEnd(new ilDateTime($sub_filter->default_end()->format("Y-m-d 00:00:00"),IL_CAL_DATETIME));
					}

					$option->addSubItem($duration);
					$option->setValue($key);
					break;
				case "CaT\Filter\Filters\Multiselect":
					require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
					$multi_select = new ilMultiSelectInputGUI("", "filter[$this->path][".$key."]");
					$opts = $sub_filter->options();
					asort($opts,  SORT_NATURAL | SORT_FLAG_CASE);
					$multi_select->setOptions($opts);

					if($this->val && $key == $this->val["option"]) {
						$multi_select->setValue($this->val[$this->val["option"]]);
					} else {
						$multi_select->setValue($sub_filter->default_choice());
					}

					$multi_select->setWidth(250);
					$option->addSubItem($multi_select);
					$option->setValue($key);
					break;
				case "CaT\Filter\Filters\Singleselect":
					require_once("Services/Form/classes/class.ilSelectInputGUI.php");
					$select = new ilSelectInputGUI("", "filter[$this->path][".$key."]");
					$opts = $sub_filter->options();
					asort($opts,  SORT_NATURAL | SORT_FLAG_CASE);
					$select->setOptions($opts);

					if($this->val && $key == $this->val["option"]) {
						$select->setValue($this->val[$this->val["option"]]);
					} else {
						$select->setValue($sub_filter->default_choice());
					}

					$option->addSubItem($multi_select);
					$option->setValue($key);
					break;
				case "CaT\Filter\Filters\Option":
					require_once("Services/Form/classes/class.ilSelectInputGUI.php");
					$select = new ilSelectInputGUI("", "filter[$this->path][".$key."]");
					$select->setOptions(array("1"=>"Ja","0"=>"Nein"));

					if($this->val && $key == $this->val["option"]) {
						$select->setValue($this->val[$this->val["option"]]);
					}

					$option->addSubItem($select);
					$option->setValue($key);
					break;
				case "CaT\Filter\Filters\Text":
					require_once("Services/Form/classes/class.ilTextInputGUI.php");
					$input = new ilTextInputGUI("", "filter[$this->path][".$key."]");

					if($this->val && $key == $this->val["option"]) {
						$input->setValue($this->val[$this->val["option"]]);
					}

					$option->addSubItem($input);
					$option->setValue($key);
					break;
				default:
					throw new \Exception("Filter class not known");
			}
			
			//add option to group
			$group->addOption($option);
		}

		return $group;
	}

	public function setValue($val) {
		$this->val = $val;
	}
}