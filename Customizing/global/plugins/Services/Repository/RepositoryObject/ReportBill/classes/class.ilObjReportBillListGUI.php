<?php
require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseListGUI.php';
  /**
* ListGUI implementation for Example object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*
* PLEASE do not create instances of larger classes here. Use the
* ...Access class to get DB data and keep it small.
*/
class ilObjReportBillListGUI extends ilObjReportBaseListGUI {

	/**
	* Init type
	*/
	public function initType() {
		$this->setType("xrbi");
		parent::initType();
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass() {
		return "ilObjReportBillGUI";
	}

	/**
	* Get commands
	*/
	public function initCommands() {
		return array(
			array(
				"permission" => "read",
				"cmd" => "showContent",
				"txt" => "show",
				"default" => true),
			array(
				"permission" => "write",
				"cmd" => "settings",
				"txt" => $this->lng->txt("edit"),
				"default" => false)
		);
	}

	public function getProperties() {
		$props = array();

		$this->plugin->includeClass("class.ilObjReportBillAccess.php");
		if (!ilObjReportBillAccess::checkOnline($this->obj_id))
		{
		$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
		"value" => $this->lng->txt("offline"));
		}
		 
		return $props;
	}
}