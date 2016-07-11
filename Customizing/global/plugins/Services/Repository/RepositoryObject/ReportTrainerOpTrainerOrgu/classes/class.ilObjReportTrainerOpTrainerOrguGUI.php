<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrainerOpTrainerOrguGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrainerOpTrainerOrguGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportTrainerOpTrainerOrguGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportTrainerOpTrainerOrguGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xoto';
	}

	protected function afterConstructor() {
		parent::afterConstructor();
		if($this->object->plugin) {
			$this->tpl->addCSS($this->object->plugin->getStylesheetLocation('css/report.css'));
		}
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public static function transformResultRow($rec) {
		return $rec;
	}

	public static function transformResultRowXLSX($rec) {
		$rec["title"] =  preg_replace("#&[a-z0-9]{2,8};#i","",strip_tags($rec['title']));
		return $rec;
	}
}