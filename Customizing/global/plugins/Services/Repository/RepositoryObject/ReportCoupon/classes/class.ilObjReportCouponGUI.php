<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportCouponGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportCouponGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportCouponGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportCouponGUI extends ilObjReportBaseGUI {


	public function getType() {
		return 'xrcp';
	}

	protected function prepareTitle() {
		require_once 'Services/CaTUIComponents/classes/class.catTitleGUI.php';
		$this->title = catTitleGUI::create()
						->title("gev_rep_coupon_title")
						->subTitle("gev_rep_coupon_desc")
						->image("GEV_img/ico-head-edubio.png");
	}

		protected function settingsForm($data) {
		$settings_form = parent::settingsForm($data);

		$is_online = new ilCheckboxInputGUI('online','online');
		if(isset($data["online"])) {
			$is_online->setChecked($data["online"]);
		}
		$settings_form->addItem($is_online);

		$admin_mode = new ilCheckboxInputGUI('admin_mode','admin_mode');
		if(isset($data["admin_mode"])) {
			$admin_mode->setChecked($data["admin_mode"]);
		}
		$settings_form->addItem($admin_mode);

		return $settings_form;
	}

	protected function getSettingsData() {
		$data = parent::getSettingsData();
		$data["online"] = $this->object->getOnline();
		$data["admin_mode"] = $this->object->getAdminMode();
		return $data;
	}


	protected function saveSettingsData($data) {
		$this->object->setOnline($data["online"]);
		$this->object->setAdminMode($data["admin_mode"]);
		parent::saveSettingsData($data);
	}
}