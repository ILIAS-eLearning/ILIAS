<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportDBVGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportDBVGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportDBVGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportDBVGUI extends ilObjReportBaseGUI {
	protected static $od_regexp;
	protected static $bd_regexp;

	protected function afterConstructor() {
		parent::afterConstructor();
	}

	public function getType() {
		return 'xrdv';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public static function transformResultRow($rec) {
		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )) {
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
			//$date = ilDatePresentation::formatPeriod($start,$end);
		} else {
			$date = '-';
		}
		$rec['date'] = $date;
		return parent::transformResultRow(self::setBD($rec));
	}

	public static function transformResultRowXLSX($rec) {
		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )) {
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
			//$date = ilDatePresentation::formatPeriod($start,$end);
		} else {
			$date = '-';
		}
		$rec['date'] = $date;
		return parent::transformResultRowXLSX(self::setBD($rec));
	}

	public static function setBD($rec) {
		if(!self::$bd_regexp ) {
			require_once './Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/config/od_bd_strings.php';
		}
		$orgu_above1 =  $rec['org_unit_above1'];
		$orgu_above2 =  $rec['org_unit_above2'];

		if (preg_match(self::$bd_regexp, $orgu_above1)) {
			$bd = $orgu_above1;
		} elseif(preg_match(self::$bd_regexp, $orgu_above2)) {
			$bd = $orgu_above2;
		} else {
			$bd = '-';
		}
		$rec['odbd'] = $bd;
		return $rec;
	}

	protected function renderTable() {
		$rendered_table = parent::renderTable();
		return $this->object->renderSumTable($this).$rendered_table;
	}
}