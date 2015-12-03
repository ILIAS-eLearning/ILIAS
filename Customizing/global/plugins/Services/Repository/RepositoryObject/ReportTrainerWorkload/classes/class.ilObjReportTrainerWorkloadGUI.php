<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrainerWorkloadGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrainerWorkloadGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportTrainerWorkloadGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportTrainerWorkloadGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xrtw';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	protected function settingsForm($data = null) {
		$settings_form = parent::settingsForm($data);

		$is_online = new ilCheckboxInputGUI($this->gLng->txt('online'),'online');
		$is_online->setValue(1);
		if(isset($data["online"])) {
			$is_online->setChecked($data["online"]);
		}
		$settings_form->addItem($is_online);

		return $settings_form;
	}

	protected function getSettingsData() {
		$data = parent::getSettingsData();
		$data["online"] = $this->object->getOnline();
		return $data;
	}

	protected function saveSettingsData($data) {
		$this->object->setOnline($data["online"]);
		parent::saveSettingsData($data);
	}

	public static function transformResultRow($rec) {
		global $ilCtrl;
		foreach ($rec as &$value) {
			$value = number_format($value,2,',','.');
		}
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLS($rec) {
		$rec['odbd'] = $rec['org_unit_above1'];
		return parent::transformResultRow($rec);
	}
}