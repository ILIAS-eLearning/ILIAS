<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* @ilCtrl_isCalledBy ilObjReportASTDGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportASTDGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportASTDGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportASTDGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xatd';
	}


	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image('GEV_img/ico-head-rep-billing.png');
		return $a_title;
	}


	public static function transformResultRow($rec) {
		global $lng;
		foreach ($rec as $key => &$value) {
			if($key != 'astd_category') {
				$value = $rec['astd_category'] == 'astd_participators' ? number_format($value, 0, ',', '.') : number_format($value, 2, ',', '.'); 
			}
		}
		$rec['astd_category'] = $lng->txt($rec['astd_category']);
		return $rec;
	}

	public static function transformResultRowXLSX($rec) {
		return self::transformResultRow($rec);
	}
}