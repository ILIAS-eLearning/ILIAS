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
		$group = new ilRadioGroupInputGUI($this->filter->label(), "filter[$this->path][option]");

		//create options
		foreach ($this->filter->subs() as $key => $sub_filter) {
			$option = new ilRadioOption($sub_filter->label());
			$option->setInfo($sub_filter->description());
			$filter_class = get_class($sub_filter);

			switch($filter_class) {
				case "CaT\Filter\Filters\DatePeriod":
					require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
					$duration = new ilDateDurationInputGUI("", "filter[$this->path][date]");
					$duration->setShowDate(true);
					$duration->setShowTime(false);
					$option->addSubItem($duration);
					$option->setValue("date");
					break;
				case "CaT\Filter\Filters\Multiselect":
					require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
					$multi_select = new ilMultiSelectInputGUI("", "filter[$this->path][multi]");
					$multi_select->setOptions($sub_filter->options());
					$option->addSubItem($multi_select);
					$option->setValue("mulit");
					break;
				case "CaT\Filter\Filters\Option":
					require_once("Services/Form/classes/class.ilSelectInputGUI.php");
					$select = new ilSelectInputGUI("", "filter[$this->path][select]");
					$select->setOptions(array("0"=>"Ja","1"=>"Nein"));
					$option->addSubItem($select);
					$option->setValue("select");
					break;
				case "CaT\Filter\Filters\Text":
					require_once("Services/Form/classes/class.ilTextInputGUI.php");
					$input = new ilTextInputGUI("", "filter[$this->path][text]");
					$option->addSubItem($input);
					$option->setValue("text");
					break;
				default:
					throw new \Exception("Filter class not known");
			}
			
			//add option to group
			$group->addOption($option);
		}

		//add group to form
		$form->addItem($group);

		return $form;
	}
}