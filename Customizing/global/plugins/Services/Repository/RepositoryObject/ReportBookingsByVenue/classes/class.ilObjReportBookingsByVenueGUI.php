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

	public static function transformResultRowXLSX($rec) {
		global $ilDB;
		$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
		$end = new ilDate($rec["end_date"], IL_CAL_DATE);
		$date = ilDatePresentation::formatPeriod($start,$end);
		$rec['date'] = $date;

		// get this from hist_usercoursestatus.overnights instead?
		// here, trainers are involved.
		$query_temp = 	"SELECT acco.night, COUNT(acco.night) no_accomodations"
						."	FROM crs_acco acco"
						."	LEFT JOIN hist_usercoursestatus usrcrs"
						."		ON usrcrs.crs_id = acco.crs_id "
						."			AND usrcrs.usr_id = acco.user_id"
						."			AND usrcrs.hist_historic = 0"
						."	WHERE acco.crs_id = ".$rec['crs_id']
						."		AND ((usrcrs.function = 'Mitglied' AND usrcrs.booking_status = 'gebucht')"
						."		OR  usrcrs.function = 'Trainer')"
						."	GROUP BY acco.night"
						."	ORDER BY acco.night";


		$res_temp = $ilDB->query($query_temp);

		$rec['no_accomodations'] = '';
		while($rec_temp = $ilDB->fetchAssoc($res_temp)) {
			$night = new ilDate($rec_temp['night'], IL_CAL_DATE);
			$night = ilDatePresentation::formatDate($night);
			$rec['no_accomodations'] .=
				$night
				.' : '
				.$rec_temp['no_accomodations'].'; ';
		}

		//this is how the xls-list is generated:
		//$user_ids = $this->getCourse()->getMembersObject()->getMembers();
		//$tutor_ids = $this->getCourse()->getMembersObject()->getTutors();

		$query_temp =	"SELECT COUNT(DISTINCT usr_id) no_members"
						."	FROM hist_usercoursestatus"
					 	."	WHERE crs_id =" .$rec['crs_id']
						."		AND hist_historic = 0"
						."		AND (( function = 'Mitglied' AND booking_status = 'gebucht')"
						."		OR function = 'Trainer')";

		$res_temp = $ilDB->query($query_temp);
		$rec_temp = $ilDB->fetchAssoc($res_temp);
		$rec['no_members'] = $rec_temp['no_members'];
		return parent::transformResultRowXLSX($rec);
	}

	public static function transformResultRow($rec) {
		global $ilDB, $ilCtrl;
		$lnk = $ilCtrl->getLinkTargetByClass('ilObjReportBookingsByVenueGUI', "deliverMemberList");
		$lnk .= '&crs_id=' .$rec["crs_id"];
		$rec['action'] = '<a href="' . $lnk
			.'"><img src="./Customizing/global/skin/genv/images/GEV_img/ico-table-eye.png"></a>';

		$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
		$end = new ilDate($rec["end_date"], IL_CAL_DATE);
		$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
		$rec['date'] = $date;

		// get this from hist_usercoursestatus.overnights instead?
		// here, trainers are involved.
		$query_temp =	"SELECT acco.night, COUNT(acco.night) no_accomodations"
						."	FROM crs_acco acco"
						."	LEFT JOIN hist_usercoursestatus usrcrs"
						."		ON usrcrs.crs_id = acco.crs_id "
						."			AND usrcrs.usr_id = acco.user_id"
						."			AND usrcrs.hist_historic = 0"
						."	WHERE acco.crs_id = ".$rec['crs_id']
						."		AND ((usrcrs.function = 'Mitglied' AND usrcrs.booking_status = 'gebucht')"
						."		OR  usrcrs.function = 'Trainer')"
						."	GROUP BY acco.night"
						."	ORDER BY acco.night";


		$res_temp = $ilDB->query($query_temp);

		$rec['no_accomodations'] = '';
		while($rec_temp = $ilDB->fetchAssoc($res_temp)) {
			$night = new ilDate($rec_temp['night'], IL_CAL_DATE);
			$night = ilDatePresentation::formatDate($night);
			$rec['no_accomodations'] .= '<nobr>'
				.$night
				.' &nbsp; <b>'
				.$rec_temp['no_accomodations']
				.'</b>'
				.'</nobr><br>';
		}

		//this is how the xls-list is generated:
		//$user_ids = $this->getCourse()->getMembersObject()->getMembers();
		//$tutor_ids = $this->getCourse()->getMembersObject()->getTutors();
		$query_temp =	"SELECT COUNT(DISTINCT usr_id) no_members"
				."	FROM hist_usercoursestatus"
				."	WHERE crs_id =" .$rec['crs_id']
				."		AND hist_historic = 0"
				."		AND (( function = 'Mitglied' AND booking_status = 'gebucht')"
				."		OR function = 'Trainer')";


		$res_temp = $ilDB->query($query_temp);
		$rec_temp = $ilDB->fetchAssoc($res_temp);
		$rec['no_members'] = $rec_temp['no_members'];
		
		return parent::transformResultRow($rec);
	}

	protected function deliverMemberList() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_id = $_GET["crs_id"];
		$cutils = gevCourseUtils::getInstance($crs_id);
		$cutils->deliverMemberList(gevCourseUtils::MEMBERLIST_HOTEL);
		return;
	}

	public function performCustomCommand($cmd) {
		switch ($cmd) {
			case "deliverMemberList":
				$this->deliverMemberList();
				break;
			default:
				return parent::performCustomCommand($cmd);
		}
	}
}