<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
/**
* @ilCtrl_isCalledBy ilObjReportASTDGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportASTDGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportASTDGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportASTDGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xatd';
	}

	protected function settingsForm($data = null) {
		$settings_form = parent::settingsForm($data);

		$is_online = new ilCheckboxInputGUI($this->gLng->txt('online'),'online');
		if(isset($data['online'])) {
			$is_online->setChecked($data['online']);
		}
		$settings_form->addItem($is_online);

		$accomodation_cost = new ilNumberInputGUI($this->gLng->txt('astd_accomodation_cost_per_day_person'),'accomodation_cost');
		$accomodation_cost->allowDecimals(true);
		if(isset($data['accomodation_cost'])) {
			$accomodation_cost->setValue(number_format($data['accomodation_cost'],2,',','.'));
		}
		$settings_form->addItem($accomodation_cost);

		return $settings_form;
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image('GEV_img/ico-head-rep-billing.png');
		return $a_title;
	}

	protected function getSettingsData() {
		$data = parent::getSettingsData();
		$data['online'] = $this->object->getOnline();
		$data['accomodation_cost'] = $this->object->getAccomodationCost();
		return $data;
	}

	protected function saveSettingsData($data) {
		$this->object->setOnline($data['online']);
		$this->object->setAccomodationCost($data['accomodation_cost']);
		parent::saveSettingsData($data);
	}

	public static function transformResultRow($rec) {
		global $lng;
		foreach ($rec as $key => &$value) {
			if($key != 'astd_category') {
				$value = $rec['astd_category'] == 'astd_participators' ? number_format($value, 0) : number_format($value, 2, ',', '.'); 
			}
		}
		$rec['astd_category'] = $lng->txt($rec['astd_category']);
		return $rec;
	}
}