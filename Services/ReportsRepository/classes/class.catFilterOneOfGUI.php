<?php
require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
require_once("Services/Form/classes/class.ilRadioOption.php");
require_once("Services/ReportsRepository/classes/class.catFilterGUI.php");

class catFilterOneOfGUI extends catFilterGUI {
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
					$duration = new ilDateDurationInputGUI("", "filter[$this->path][".$key."]");
					$duration->setShowDate(true);
					$duration->setShowTime(false);
					$option->addSubItem($duration);
					$option->setValue($key);
					break;
				case "CaT\Filter\Filters\Multiselect":
					require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
					$multi_select = new ilMultiSelectInputGUI("", "filter[$this->path][".$key."]");
					$opts = $sub_filter->options();
					asort($opts,  SORT_NATURAL | SORT_FLAG_CASE);
					$multi_select->setOptions($opts);
					$multi_select->setValue($sub_filter->default_choice());
					$option->addSubItem($multi_select);
					$option->setValue($key);
					break;
				case "CaT\Filter\Filters\Singleselect":
					require_once("Services/Form/classes/class.ilSelectInputGUI.php");
					$select = new ilSelectInputGUI("", "filter[$this->path][".$key."]");
					$opts = $sub_filter->options();
					asort($opts,  SORT_NATURAL | SORT_FLAG_CASE);
					$select->setOptions($opts);
					$select->setValue($sub_filter->default_choice());
					$option->addSubItem($multi_select);
					$option->setValue($key);
					break;
				case "CaT\Filter\Filters\Option":
					require_once("Services/Form/classes/class.ilSelectInputGUI.php");
					$select = new ilSelectInputGUI("", "filter[$this->path][".$key."]");
					$select->setOptions(array("1"=>"Ja","0"=>"Nein"));
					$option->addSubItem($select);
					$option->setValue($key);
					break;
				case "CaT\Filter\Filters\Text":
					require_once("Services/Form/classes/class.ilTextInputGUI.php");
					$input = new ilTextInputGUI("", "filter[$this->path][".$key."]");
					$option->addSubItem($input);
					$option->setValue($key);
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