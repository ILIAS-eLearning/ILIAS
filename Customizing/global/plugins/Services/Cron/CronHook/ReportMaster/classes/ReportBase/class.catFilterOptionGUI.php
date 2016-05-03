<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilSelectInputGUI.php");
require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterGUI.php';

class catFilterOptionGUI extends catFilterGUI {
	protected $filter;
	protected $path;
	protected $val;

	public function __construct($filter, $path) {
		$this->filter = $filter;
		$this->path = $path;
	}

	/**
	 * @inheritdoc
	 */
	public function formElement() {
		$select = new ilSelectInputGUI($this->filter->label(), "filter[$this->path]");
		$select->setInfo($this->filter->description());
		$select->setOptions(array("1"=>"Ja","0"=>"Nein"));

		if($this->val !== null) {
			$select->setValue($this->val);
		}

		return $select;
	}

	public function setValue($val) {
		$this->val = $val;
	}
}