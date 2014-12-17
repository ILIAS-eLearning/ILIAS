<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "BookingsByVenue"
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*
*	Define title, table_cols and row_template.
*	Implement fetchData to retrieve the data you want
*
*	Add special _process_xls_XXX and _process_table_XXX methods
*	to modify certain entries after retrieving data.
*	Those methods must return a proper string.
*
*/

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");


class gevBookingsByVenueGUI extends catBasicReportGUI{
	public function __construct() {
		global $ilUser;
		
		parent::__construct();
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$user_utils = gevUserUtils::getInstance($ilUser->getId());
		
		$venue_names = gevOrgUnitUtils::getVenueNames();
		if (!$user_utils->isAdmin()) {
			$venues = $user_utils->getVenuesWhereUserIsMember();
			foreach($venue_names as $id => $name) {
				if (!in_array($id, $venues)) {
					unset($venue_names[$id]);
				}
			}
		}

		$this->title = catTitleGUI::create()
						->title("gev_rep_bookings_by_venue_title")
						->subTitle("gev_rep_bookings_by_venue_desc")
						->image("GEV_img/ico-head-edubio.png")
						;

		$this->table = catReportTable::create()
						->column("custom_id", "gev_training_id")
						->column("title", "title")
						->column("venue", "gev_venue")
						->column("date", "date")
						->column("tutor", "il_crs_tutor")
						->column("no_members", "no_members")
						->column("no_accomodations", "no_accomodations")
						->column("action", "list", false, "", true)
						->template("tpl.gev_bookings_by_venue_row.html", "Services/GEV/Reports")
						->order("date", "ASC")
						;
						
		$this->query = catReportQuery::create()
						->distinct()
						->select("crs.crs_id")
						->select("title")
						->select("custom_id")
						->select("tutor")
						->select("begin_date")
						->select("end_date")
						->select("venue")
						->from("hist_course crs")
						->join("object_reference oref")
							->on("oref.obj_id = crs.crs_id")
						->join("crs_settings cs")
							->on("cs.obj_id = crs.crs_id")
						->compile()
						;

		$this->filter = catFilter::create()
						->checkbox("show_past_events"
								  , $this->lng->txt("gev_rep_filter_show_past_events")
								  , null
								  , "crs.end_date > '".date("Y-m-d")."'"
								  )
						->dateperiod( "period"
									, $this->lng->txt("gev_period")
									, $this->lng->txt("gev_until")
									, "crs.begin_date"
									, "crs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									)
						->static_condition("crs.hist_historic = 0")
						->static_condition("crs.venue != '-empty-'")
						->static_condition("oref.deleted IS NULL")
						->static_condition("cs.activation_type = 1")
						->static_condition($this->db->in("venue", $venue_names, false, "text"))
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;
	}

	protected function executeCustomCommand($a_cmd) {
		switch ($a_cmd) {
			case "deliverMemberList":
				return $this->deliverMemberList();
			default:
				return null;
		}
	}

	protected function userIsPermitted () {
		return $this->user_utils->isAdmin() || $this->user_utils->hasRoleIn(array("Veranstalter"));
	}

	
	protected function transformResultRow($rec) {
		$lnk = $this->ctrl->getLinkTarget($this, "deliverMemberList");
		$lnk .= '&crs_id=' .$rec["crs_id"];
		$rec['action'] = '<a href="' . $lnk 
			.'"><img src="./Customizing/global/skin/genv/images/GEV_img/ico-table-eye.png"></a>';

		$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
		$end = new ilDate($rec["end_date"], IL_CAL_DATE);
		$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
		$rec['date'] = $date;

		// get this from hist_usercoursestatus.overnights instead?
		// here, trainers are involved.
		$query_temp = "SELECT
					 		night,
					 		COUNT(night) no_accomodations

					 	FROM
					 		crs_acco
					 	WHERE 
						 	crs_id =" .$rec['crs_id']
						 	
						." GROUP BY 
						 	night
						   ORDER BY
						   	night

						 ";


		$res_temp = $this->db->query($query_temp);

		$rec['no_accomodations'] = '';
		while($rec_temp = $this->db->fetchAssoc($res_temp)) {
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

		$query_temp = "SELECT
					 		COUNT(DISTINCT usr_id) no_members
					 	FROM
					 		hist_usercoursestatus
					 	WHERE 
						 	crs_id =" .$rec['crs_id']
					."	AND
							hist_historic = 0
						AND (
								(	
								function = 'Mitglied' 
								AND 
								booking_status = 'gebucht'
								)
							OR
								function = 'Trainer'
						)
				
					";

		$res_temp = $this->db->query($query_temp);
		$rec_temp = $this->db->fetchAssoc($res_temp);
		$rec['no_members'] = $rec_temp['no_members'];
		
		return $rec;
	}

	protected function deliverMemberList() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_id = $_GET["crs_id"];
		$cutils = gevCourseUtils::getInstance($crs_id);
		$cutils->deliverMemberList(gevCourseUtils::MEMBERLIST_HOTEL);
		return;
	}

	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}
	
	protected function _process_xls_no_accomodations($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		$val = str_replace('<b>', '', $val);
		$val = str_replace('</b>', '', $val);
		$val = str_replace('<br>', "\n", $val);
		$val = str_replace(' &nbsp; ', " - ", $val);
		return $val;
	}
	protected function _process_xls_action($val) {
		return '';
	}

}

?>
