<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrainerOpTepCatGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrainerOpTepCatGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportTrainerOpTepCatGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportTrainerOpTepCatGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xttc';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	protected function render() {
		$this->tpl->addCSS('Services/ReportsRepository/templates/css/report.css');
		return parent::render();
	}
}