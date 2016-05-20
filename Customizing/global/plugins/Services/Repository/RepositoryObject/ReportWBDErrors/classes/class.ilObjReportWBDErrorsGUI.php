<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportWBDErrorsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportWBDErrorsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportWBDErrorsGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportWBDErrorsGUI extends ilObjReportBaseGUI {
	public function getType() {
		return 'xwbe';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public function performCustomCommand($cmd) {
		switch ($cmd) {
			case 'resolve':
				$err_id = $_GET['err_id'];
				require_once("Services/WBDData/classes/class.wbdErrorLog.php");
				$errlog = new wbdErrorLog();
				$errlog->resolveWBDErrorById($err_id);
				$this->object->setFilterAction("showContent");
				$this->object->prepareReport();
				$this->enableRelevantParametersCtrl();
				$this->gCtrl->redirect($this, "showContent");
				break;
			default:
				return false;
		}
	}
}