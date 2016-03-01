<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrDemandAdvGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrDemandAdvGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportTrDemandAdvGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportTrDemandAdvGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xtda';
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
			$rec['min_part_achived'] = 
				(	(string)$rec['min_part_achived'] === "1"
					|| $rec['min_participants'] === null 
					|| (string)$rec['min_participants'] === '-1')
						? 'Ja' : 'Nein';
			$rec['bookings_left'] =
				(	(string)$rec['max_participants'] === "0"
					||(string)$rec['max_participants'] === "-1"
					|| $rec['max_participants'] === null)
						? 'keine Beschränkung' : $rec['bookings_left'];
			$rec['booked_wl'] = $rec['waitinglist_active'] === 'Ja'
						? $rec['booked_wl'] : 'inaktiv';

			$rec['begin_date'] = date_format(date_create($rec['begin_date']),'d.m.Y')
					.' - '.date_format(date_create($rec['end_date']),'d.m.Y');
			$rec['booking_dl'] = date_format(date_create($rec['booking_dl']),'d.m.Y');
		} else {
			$rec = array('tpl_title' => $rec['tpl_title']);
		}
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLS($rec) {
		if($rec['title'] !== null) {
			$rec['min_part_achived'] = 
				(	(string)$rec['min_part_achived'] === "1" 
					|| $rec['min_participants'] === null 
					|| (string)$rec['min_participants'] === '-1' )
						? 'Ja' : 'Nein';
			$rec['bookings_left'] =
				(	(string)$rec['max_participants'] === "0"
					||(string)$rec['max_participants'] === "-1"
					|| $rec['max_participants'] === null)
						? 'keine Beschränkung' : $rec['bookings_left'];
			$rec['booked_wl'] = $rec['waitinglist_active'] === 'Ja' 
						? $rec['booked_wl'] : 'inaktiv';

			$rec['begin_date'] = date_format(date_create($rec['begin_date']),'d.m.Y')
					.' - '.date_format(date_create($rec['end_date']),'d.m.Y');
			$rec['booking_dl'] = date_format(date_create($rec['booking_dl']),'d.m.Y');
		} else {
			$rec = array(	'tpl_title' => $rec['tpl_title']);
		}
		return parent::transformResultRow($rec);
	}


	protected function settingsForm($data = null) {
		$settings_form = parent::settingsForm($data);
		$is_local = new ilCheckboxInputGUI($this->object->plugin->txt('report_is_local'),'is_local');
		$is_local->setValue(1);
		if(isset($data["is_local"])) {
			$is_local->setChecked($data["is_local"]);
		}
		$settings_form->addItem($is_local);
		return $settings_form;
	}

	protected function getSettingsData() {
		$data = parent::getSettingsData();
		$data['is_local'] = $this->object->getIsLocal();
		return $data;
	}

	protected function saveSettingsData($data) {
		$this->object->setIsLocal($data['is_local']);
		parent::saveSettingsData($data);
	}
}