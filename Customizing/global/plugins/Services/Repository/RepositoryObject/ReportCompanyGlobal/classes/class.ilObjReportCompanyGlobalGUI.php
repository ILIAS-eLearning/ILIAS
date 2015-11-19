<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportCompanyGlobalGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportCompanyGlobalGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI,
* @ilCtrl_Calls ilObjReportCompanyGlobalGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportCompanyGlobalGUI extends ilObjReportBaseGUI {
	public function getType() {
		return 'xrcg';
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

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-rep-billing.png");
		return $a_title;
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
}