<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrainerWorkloadGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrainerWorkloadGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportTrainerWorkloadGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportOrguAttGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xroa';
	}
}