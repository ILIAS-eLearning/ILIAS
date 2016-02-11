<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrDemandRetGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrDemandRetGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
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