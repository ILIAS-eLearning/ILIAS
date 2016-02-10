<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrDemandAdvGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrDemandAdvGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
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
		var_dump($rec);
		echo "<br>";
		if($rec['title'] !== null) {
			$rec['min_part_achived'] = 
				((string)$rec['min_participants'] === "0" 
					|| $rec['min_participants'] === null 
					|| $rec['bookings'] >=  $rec['min_participants'])
					? 'Ja' : 'Nein';
			$rec['bookings_left'] 
				= ((string)$rec['max_participants'] === "-1" || $rec['max_participants'] === null)
					? 'keine Beschränkung'
					: (string)((int)$rec['max_participants'] - (int)$rec['bookings']);
			$rec['waitinglist'] = $rec['waitinglist_active'] === 'Ja' 
											? $rec['bookings_wl'] : 'inaktiv';

			$rec['date'] = date_format(date_create($rec['begin_date']),'d.m.Y')
					.' - '.date_format(date_create($rec['end_date']),'d.m.Y');
			$rec['booking_dl'] = date_format(date_create($rec['booking_dl']),'d.m.Y');
		} else {
			$rec = array('tpl_title' => $rec['tpl_title']);
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