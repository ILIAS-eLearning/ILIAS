<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportEduBioGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportEduBioGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportEduBioGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportEduBioGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xreb';
	}


	public function performCustomCommand($cmd) {
		switch ($cmd) {
			case "getCertificate":
				return $this->getCertificate();
			case "getBill":
				return $this->getBill();
			default:
				return false;
		}
	}

}