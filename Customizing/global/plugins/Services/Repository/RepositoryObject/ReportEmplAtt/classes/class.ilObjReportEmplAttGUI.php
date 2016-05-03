<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportEmplAttGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportEmplAttGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI,
* @ilCtrl_Calls ilObjReportEmplAttGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportEmplAttGUI extends ilObjReportBaseGUI {

	static $od_regexp;
	static $bd_regexp;

	public function getType() {
		return 'xrea';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public static function transformResultRow($rec) {
		global $lng;
		// credit_points
		if ($rec["credit_points"] == -1) {
			$rec["credit_points"] = $lng->txt("gev_table_no_entry");
		}

		//date
		if( $rec["begin_date"] && $rec["end_date"]
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
			){
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
		} else {
			$date = '-';
		}
		$rec['date'] = $date;

		// od_bd
		if(!self::$od_regexp || !self::$bd_regexp ) {
			require_once './Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/config/od_bd_strings.php';
		}
		$orgu_above1 =  $rec['org_unit_above1'];
		$orgu_above2 =  $rec['org_unit_above2'];
		if (preg_match(self::$od_regexp, $orgu_above1)) {
			$od = $orgu_above1;
		} elseif(preg_match(self::$od_regexp, $orgu_above2)) {
			$od = $orgu_above2;
		} else {
			$od = '-';
		}
		if (preg_match(self::$bd_regexp, $orgu_above1)) {
			$bd = $orgu_above1;
		} elseif(preg_match(self::$bd_regexp, $orgu_above2)) {
			$bd = $orgu_above2;
		} else {
			$bd = '-';
		}
		$rec['od_bd'] = $od .'/' .$bd;
		if($rec["participation_status"] == "nicht gesetzt") {
			$rec["participation_status"] = "gebucht, noch nicht abgeschlossen";
		}

		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		global $lng;
		// credit_points
		if ($rec["credit_points"] == -1) {
			$rec["credit_points"] = $lng->txt("gev_table_no_entry");
		}

		//date
		if( $rec["begin_date"] && $rec["end_date"]
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
			){
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
		} else {
			$date = '-';
		}
		$rec['date'] = $date;

		// od_bd
		if(!self::$od_regexp || !self::$bd_regexp ) {
			require_once './Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/config/od_bd_strings.php';
		}
		$orgu_above1 =  $rec['org_unit_above1'];
		$orgu_above2 =  $rec['org_unit_above2'];
		if (preg_match(self::$od_regexp, $orgu_above1)) {
			$od = $orgu_above1;
		} elseif(preg_match(self::$od_regexp, $orgu_above2)) {
			$od = $orgu_above2;
		} else {
			$od = '-';
		}
		if (preg_match(self::$bd_regexp, $orgu_above1)) {
			$bd = $orgu_above1;
		} elseif(preg_match(self::$bd_regexp, $orgu_above2)) {
			$bd = $orgu_above2;
		} else {
			$bd = '-';
		}
		$rec['od_bd'] = $od .'/' .$bd;
		if($rec["participation_status"] == "nicht gesetzt") {
			$rec["participation_status"] = "gebucht, noch nicht abgeschlossen";
		}

		return parent::transformResultRow($rec);
	}
}
