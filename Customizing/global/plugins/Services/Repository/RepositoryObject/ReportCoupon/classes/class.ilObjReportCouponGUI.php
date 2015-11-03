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
		$desc = $this->object->getAdminMode() ? "gev_rep_coupon_desc_admin" : "gev_rep_coupon_desc";
		$this->title = catTitleGUI::create()
						->title("gev_rep_coupon_title")
						->subTitle($desc)
						->image("GEV_img/ico-head-edubio.png");
	}

		protected function settingsForm($data = null) {
		$settings_form = parent::settingsForm($data);

		$is_online = new ilCheckboxInputGUI('online','online');
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