<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportDBVSuperiorGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportDBVSuperiorGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportDBVSuperiorGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportDBVSuperiorGUI extends ilObjReportBaseGUI {

	protected static $year = 2015;

	protected function afterConstructor() {
		parent::afterConstructor();
		if( null !== $this->object) {
			self::$year = $this->object->settings['year'];
		}
	}

	public function getType() {
		return 'xrds';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}


	public static function transformResultRow($rec) {
		global $ilCtrl;
		$rec['odbd'] = $rec['org_unit_above1'];
		$ilCtrl->setParameterByClass("gevDBVReportGUI", "target_user_id", $rec["user_id"]);
		$ilCtrl->setParameterByClass("gevDBVReportGUI", "year", self::$year);
		$rec["dbv_report_link"] = $ilCtrl->getLinkTargetByClass(array("gevDesktopGUI","gevDBVReportGUI"));
		$ilCtrl->setParameterByClass("gevDBVReportGUI", "target_user_id", null);
		$ilCtrl->setParameterByClass("gevDBVReportGUI", "year",  null);
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		$rec['odbd'] = $rec['org_unit_above1'];
		return parent::transformResultRow($rec);
	}
}