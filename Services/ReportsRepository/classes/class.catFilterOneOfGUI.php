<?php
require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
require_once("Services/Form/classes/class.ilRadioOption.php");

class catFilterOneOfGUI {
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
		//create radiogroup
		$group = new ilRadioGroupInputGUI($this->lng->txt("gev_course_type"), "g".$this->path);

		//create options
		foreach ($filter->subs() as $key => $sub_filter) {
			$option = new ilRadioOption($sub_filter->label(), "o".$this->path);
			$option->setInfo($sub_filter->description());
			$filter_class = get_class($sub_filter);

			switch($filter_class) {
				case "CaT\Filter\Filters\DatePeriod":
					$duration = new ilDateDurationInputGUI("", $this->path);
					$duration->setShowDate(true);
					$duration->setShowTime(false);
					$option->addSubItem($duration);
					break;
				case "CaT\Filter\Filters\Multiselect":
					$multi_select = new ilMultiSelectInputGUI("", $this->path);
					$multi_select->setOptions($sub_filter->options());
					$option->addSubItem($multi_select);
					break;
				case "CaT\Filter\Filters\Option":
					$select = new ilSelectInputGUI("", $this->path);
					$select->setOptions(array("0"=>"Ja","1"=>"Nein"));
					$option->addSubItem($select);
					break;
				case "CaT\Filter\Filters\Text":
					$input = new ilTextInputGUI("", $this->path);
					$option->addSubItem($input);
					break;
				default:
					throw new \Exception("Filter class not known");
			}
		}

		//add option to group
		$group->addOption($option);

		//add group to form
		$form->addItem($group);

		return $form;
	}
}