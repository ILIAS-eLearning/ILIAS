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

	protected static $dbv_report_ref = null;
	protected static $bd_regexp;
	protected static $od_regexp;
	protected function afterConstructor() {
		parent::afterConstructor();
		if( null !== $this->object && 'xrdv' === ilObject::_lookupType($this->object->settings['dbv_report_ref'], true)) {
			self::$dbv_report_ref = $this->object->settings['dbv_report_ref'];
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
		if(self::$dbv_report_ref) {
			$rec = self::addDbvReportLink($rec);
		}
		return parent::transformResultRow(self::setBD($rec));
	}

	public static function addDbvReportLink($rec) {
		global $ilCtrl;
		$ilCtrl->setParameterByClass("ilObjReportDBVGUI", "target_user_id", $rec["user_id"]);
		$ilCtrl->setParameterByClass("ilObjReportDBVGUI", "ref_id", self::$dbv_report_ref);
		$rec["dbv_report_link"] = $ilCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilObjReportDBVGUI"));
		$ilCtrl->setParameterByClass("ilObjReportDBVGUI", "target_user_id", null);
		$ilCtrl->setParameterByClass("ilObjReportDBVGUI", "ref_id",  null);
		return $rec;
	}

	public static function setBD($rec) {
		if(!self::$bd_regexp ) {
			require_once './Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/config/od_bd_strings.php';
		}
		$orgu_above1 =  $rec['org_unit_above1'];
		$orgu_above2 =  $rec['org_unit_above2'];

		if (preg_match(self::$bd_regexp, $orgu_above1)) {
			$bd = $orgu_above1;
		} elseif(preg_match(self::$bd_regexp, $orgu_above2)) {
			$bd = $orgu_above2;
		} else {
			$bd = '-';
		}
		$rec['odbd'] = $bd;
		return $rec;
	}

	protected function renderSettings() {
		parent::renderSettings();
		if('xrdv' !== ilObject::_lookupType($this->object->settings['dbv_report_ref'], true)) {
			ilUtil::sendInfo($this->object->settings['dbv_report_ref']." corresponds not to a DBV Report");
		}
	}

	public static function transformResultRowXLSX($rec) {
		$rec['odbd'] = $rec['org_unit_above1'];
		return parent::transformResultRow($rec);
	}
}