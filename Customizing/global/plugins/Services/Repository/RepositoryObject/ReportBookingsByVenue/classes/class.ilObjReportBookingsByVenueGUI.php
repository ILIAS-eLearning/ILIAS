<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportBookingsByVenueGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportBookingsByVenueGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportBookingsByVenueGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportBookingsByVenueGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xbbv';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}


	public static function transformResultRow($rec) {
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		return parent::transformResultRow($rec);
	}
}