<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportEmplAttGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportEmplAttGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportEmplAttGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportEmplAttGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xrea';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}
}