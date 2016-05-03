<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseListGUI.php';

/**
* {@inheritdoc}
*/
class ilObjReportBookingsByTplListGUI extends ilObjReportBaseListGUI {

	/**
	* Init type
	*/
	public function initType() {
		$this->setType("xrbt");
		parent::initType();
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass() {
		return "ilObjReportBookingsByTplGUI";
	}

	public function getProperties() {
		$props = array();

		$this->plugin->includeClass("class.ilObjReportBookingsByTplAccess.php");
		if (!ilObjReportBookingsByTplAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
			"value" => $this->lng->txt("offline"));
		}
		 
		return $props;
	}
}