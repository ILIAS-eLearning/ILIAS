<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';

/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportExampleGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportExampleGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportExampleGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportExampleGUI extends ilObjReportBaseGUI {


	public function getType() {
		return 'xrts';
	}
	protected function prepareTitle() {
		require_once 'Services/CaTUIComponents/classes/class.catTitleGUI.php';
		$this->title = catTitleGUI::create()
						->title("gev_rep_coupon_title")
						->subTitle("gev_rep_coupon_desc")
						->image("GEV_img/ico-head-edubio.png");
	}

	protected function settingsForm($data = null) {
		$settings_form = parent::settingsForm($data);

		$is_online = new ilCheckboxInputGUI('online','online');
		$is_online->setValue(1);
		if(isset($data["online"])) {
			$is_online->setChecked($data["online"]);
		}
		$settings_form->addItem($is_online);

		$show_filter = new ilCheckboxInputGUI('filter','filter');
		$show_filter->setValue(1);
		if(isset($data["filter"])) {
			$show_filter->setChecked($data["filter"]);
		}
		$settings_form->addItem($show_filter);

		return $settings_form;
	}

	protected function getSettingsData() {
		$data = parent::getSettingsData();
		$data["online"] = $this->object->getOnline();
		$data["filter"] = $this->object->getShowFilter();
		return $data;
	}

	protected function saveSettingsData($data) {
		$this->object->setOnline($data["online"]);
		$this->object->setShowFilter($data["filter"]);
		parent::saveSettingsData($data);
	}
}