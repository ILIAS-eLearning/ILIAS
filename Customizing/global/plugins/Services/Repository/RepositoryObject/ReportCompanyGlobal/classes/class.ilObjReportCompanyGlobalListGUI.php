<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseListGUI.php';

/**
* {@inheritdoc}
*/
class ilObjReportCompanyGlobalListGUI extends ilObjReportBaseListGUI {

	/**
	* Init type
	*/
	public function initType() {
		$this->setType("xrcg");
		parent::initType();
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass() {
		return "ilObjReportCompanyGlobalGUI";
	}

	public function getProperties() {
		$props = array();

		$this->plugin->includeClass("class.ilObjReportCompanyGlobalAccess.php");
		if (!ilObjReportCompanyGlobalAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
			"value" => $this->lng->txt("offline"));
		}
		 
		return $props;
	}
}