<?php

//require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseListGUI.php';
require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';

/**
* ListGUI implementation for Example object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*
* PLEASE do not create instances of larger classes here. Use the
* ...Access class to get DB data and keep it small.
*/

class ilObjReportTrainingAttendanceListGUI extends ilObjectPluginListGUI {
	/**
	* Init type
	*/
	public function initType() {
		$this->setType("xrta");
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass() {
		return "ilObjReportTrainingAttendanceGUI";
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
				"txt" => "edit",
				"default" => false)
		);
	}
}