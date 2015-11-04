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

	protected function settingsForm($data = null) {
		$settings_form = parent::settingsForm($data);

		$is_online = new ilCheckboxInputGUI($this->gLng->txt('online'),'online');
		if(isset($data["online"])) {
			$is_online->setChecked($data["online"]);
		}
		$settings_form->addItem($is_online);

		$admin_mode = new ilCheckboxInputGUI($this->gLng->txt('gev_coupon_report_admin_mode'),'admin_mode');
		if(isset($data["admin_mode"])) {
			$admin_mode->setChecked($data["admin_mode"]);
		}
		$settings_form->addItem($admin_mode);

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
		$data["admin_mode"] = $this->object->getAdminMode();
		return $data;
	}


	protected function saveSettingsData($data) {
		$this->object->setOnline($data["online"]);
		$this->object->setAdminMode($data["admin_mode"]);
		parent::saveSettingsData($data);
	}

	public static function transformResultRow($a_rec) {
		$a_rec = parent::transformResultRow($a_rec);
		$a_rec["odbd"] = str_replace("-empty-/-empty-", "Generali", $a_rec["odbd"]);		
		$a_rec["odbd"] = str_replace("/-empty-", "/Generali", $a_rec["odbd"]);
		return $a_rec;
	}

	public static function transformResultRowXLS($a_rec) {
		$a_rec = parent::transformResultRowXLS($a_rec);
		$a_rec["odbd"] = str_replace("-empty-/-empty-", "Generali", $a_rec["odbd"]);		
		$a_rec["odbd"] = str_replace("/-empty-", "/Generali", $a_rec["odbd"]);
		return $a_rec;
	}
}