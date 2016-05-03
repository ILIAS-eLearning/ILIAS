<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrDemandRetGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrDemandRetGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportTrDemandRetGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportTrDemandRetGUI extends ilObjReportBaseGUI {
	public function getType() {
		return 'xtdr';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public static function transformResultRow($rec) {
		if($rec['title'] !== null) {
			if(ilObject::_exists($rec['crs_id']) &&'crs' === ilObject::_lookupType($rec['crs_id'])) {
				$ref_id = current(ilObject::_getAllReferences($rec['crs_id']));
				global $ilAccess;
				if($ilAccess->checkAccess("write", "editInfo", $ref_id, "crs", $rec['crs_id'])) {
					global $ilCtrl;
					$ilCtrl->setParameterByClass("ilObjCourseGUI","ref_id",$ref_id);
					$link = $ilCtrl->getLinkTargetByClass(array("ilRepositoryGUI","ilObjCourseGUI"),"editInfo");
					$ilCtrl->setParameterByClass("ilObjCourseGUI","ref_id",null);
					$rec["title"] = '<a href = "'.$link.'">'.$rec['title'].'</a>';
				} elseif ($ilAccess->checkAccess("write_reduced_settings", "showSettings", $ref_id, "crs", $rec['crs_id'])) {
					global $ilCtrl;
					$ilCtrl->setParameterByClass("gevDecentralTrainingGUI","ref_id",$ref_id);
					$link = $ilCtrl->getLinkTargetByClass(array("gevDesktopGUI","gevDecentralTrainingGUI"),"showSettings");
					$ilCtrl->setParameterByClass("gevDecentralTrainingGUI","ref_id",null);
					$rec["title"] = '<a href = "'.$link.'">'.$rec['title'].'</a>';
				}
			}
			$rec['cancellation'] =
				$rec['cancellation'] === 'Ja'
					? $rec['cancellation'] : 'Nein';
			$rec['begin_date'] = date_format(date_create($rec['begin_date']),'d.m.Y')
					.' - '.date_format(date_create($rec['end_date']),'d.m.Y');
		} else {
			$rec = array(	'tpl_title' => $rec['tpl_title']);
		}
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		if($rec['title'] !== null) {
			$rec['cancellation'] =
				$rec['cancellation'] === 'Ja'
					? $rec['cancellation'] : 'Nein';
			$rec['begin_date'] = date_format(date_create($rec['begin_date']),'d.m.Y')
					.' - '.date_format(date_create($rec['end_date']),'d.m.Y');
		} else {
			$rec = array(	'tpl_title' => $rec['tpl_title']);
		}
		return parent::transformResultRow($rec);
	}
}