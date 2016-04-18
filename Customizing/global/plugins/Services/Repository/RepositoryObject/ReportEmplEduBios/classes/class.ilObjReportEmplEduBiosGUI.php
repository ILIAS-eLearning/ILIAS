<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportEmplEduBiosGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportEmplEduBiosGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI,
* @ilCtrl_Calls ilObjReportEmplEduBiosGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportEmplEduBiosGUI extends ilObjReportBaseGUI {
	protected $relevant_parameters = array();

	public function getType() {
		return 'xeeb';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		$a_title->setVideoLink($this->object->getVideoLink());
		return $a_title;
	}

	public static function transformResultRow($rec) {
		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
			){
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
		} else {
			$date = '-';
		}
		if ($rec['cert_period'] != "-") {
			$rec['cert_period'] = ilDatePresentation::formatDate(new ilDate($rec['cert_period'], IL_CAL_DATE));
		}


		$rec["od_bd"] = $rec["org_unit_above2"]."/".$rec["org_unit_above1"];
		
		$rec["edu_bio_link"] = self::getEduBioLinkFor($rec["user_id"]);
		
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
			){
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = ilDatePresentation::formatPeriod($start,$end) ;
		} else {
			$date = '-';
		}
		if ($rec['cert_period'] != "-") {
			$rec['cert_period'] = ilDatePresentation::formatDate(new ilDate($rec['cert_period'], IL_CAL_DATE));
		}


		$rec["od_bd"] = $rec["org_unit_above2"]."/".$rec["org_unit_above1"];		
		return parent::transformResultRow($rec);
	}

	protected function getSettingsData() {
		$data = parent::getSettingsData();
		return $data;
	}

	protected function saveSettingsData($data) {
		parent::saveSettingsData($data);
	}

	public static function getEduBioLinkFor($a_user_id) {
		global $ilCtrl;
		$ilCtrl->setParameterByClass("gevEduBiographyGUI", "target_user_id", $a_target_user_id);
		$link = $ilCtrl->getLinkTargetByClass(array("gevDesktopGUI","gevEduBiographyGUI"), "view");
		$ilCtrl->clearParametersByClass("gevEduBiographyGUI");
		return $link;
	}
}