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

require_once("Services/GEV/Reports/classes/class.gevBasicReportGUI.php");

class gevBookingsByVenueGUI extends gevBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = array(
			'title' => 'gev_rep_bookings_by_venue_title',
			'desc' => 'gev_rep_bookings_by_venue_desc',
			'img' => 'GEV_img/ico-head-edubio.png'
		);

		$this->table_cols = array(
			array("gev_training_id", "custom_id"),
			array("title", "title"),
			array("gev_venue", "venue"),
			array("date", "date"),
			array("il_crs_tutor", "tutor"),
			array("no_members", "no_members"),
			array("no_accomodations", "no_accomodations"),
			array("list", "action"),
		);

		$this->table_row_template= array(
			"filename" => "tpl.gev_bookings_by_venue_row.html", 
			"path" => "Services/GEV/Reports"
		);
	}
	

	protected function executeCustomCommand($a_cmd) {
		switch ($a_cmd) {
			case "deliverMemberList":
				return $this->deliverMemberList();
			default:
				return null;
		}
	}
	

	protected function fetchData(){ 
		//fetch retrieves the data 
		$data = array();
			
		if(isset($_GET['_table_nav'])){
			$this->external_sorting = true; //set to false again, 
											//if the field is not relevant
			$table_nav_cmd = split(':', $_GET['_table_nav']);
			
			if ($table_nav_cmd[1] == "asc") {
				$direction = " ASC";
			}
			else {
				$direction = " DESC";
			}

			switch ($table_nav_cmd[0]) { //field
				case 'date':
					$direction = strtoupper($table_nav_cmd[1]);
					$sql_order_str = " ORDER BY crs.begin_date ";
					$sql_order_str .= $direction;
					break;
				
				//append more fields, simply for performance...

				default:
					$this->external_sorting = true;
					$sql_order_str = " ORDER BY ".$this->db->quoteIdentifier($table_nav_cmd[0])." ".$direction;
					//$sql_order_str = "";
					break;
			}
		}


		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/Accomodations/classes/class.ilAccomodations.php");

		
		//which venues?
		//require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		//$venu_names = gevOrgUnitUtils::getVenueNames();
		//$valid_venues = "'" .join(array_values($venu_names), "', '") ."'";


		$query = "SELECT DISTINCT
						 crs.crs_id, title, custom_id, tutor, 
						 begin_date, end_date, venue
					FROM 
						hist_course crs
					JOIN 
						crs_acco acco
					ON  
						crs.crs_id = acco.crs_id

					WHERE 
						crs.hist_historic=0
					AND
						crs.venue != '-empty-'
		";

		$query .= $this->queryWhen($this->start_date, $this->end_date);
		$query .= $sql_order_str;

		$res = $this->db->query($query);
		while($rec = $this->db->fetchAssoc($res)) {

			$lnk = $this->ctrl->getLinkTarget($this, "deliverMemberList");
			$lnk .= '&crs_id=' .$rec["crs_id"];
			$rec['action'] = '<a href="' . $lnk 
				.'"><img src="./Customizing/global/skin/genv/images/GEV_img/ico-table-eye.png"></a>';

			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
			$rec['date'] = $date;

			// get this from hist_usercoursestatus.overnights instead?
			//here, trainers are involved.
			$query_temp = "SELECT
						 		COUNT(acco.night) no_accomodations
						 	FROM
						 		crs_acco acco
						 	WHERE 
							 	crs_id =" .$rec['crs_id'];

			$res_temp = $this->db->query($query_temp);
			$rec_temp = $this->db->fetchAssoc($res_temp);
			$rec['no_accomodations'] = $rec_temp['no_accomodations'];

			$query_temp = "SELECT
						 		COUNT(DISTINCT usr_id) no_members
						 	FROM
						 		hist_usercoursestatus
						 	WHERE 
							 	crs_id =" .$rec['crs_id']
						."	AND
								hist_historic = 0
							AND (
									(function = 'Mitglied' 
										AND 
									booking_status = 'gebucht'
									)
									OR
									(
										function IN ('Trainer', 'Trainingsbetreuer')
									)
							)
						";

			$res_temp = $this->db->query($query_temp);
			$rec_temp = $this->db->fetchAssoc($res_temp);
			$rec['no_members'] = $rec_temp['no_members'];

			$data[] = $rec;

		}


		return $data;
	}




	protected function queryWhen(ilDate $start, ilDate $end) {
		if ($this->query_when === null) {
			$this->query_when =
					"   AND (crs.end_date >= ".$this->db->quote($start->get(IL_CAL_DATE), "date")
					."        OR crs.end_date = '-empty-' OR crs.end_date = '0000-00-00')"
					."   AND crs.begin_date <= ".$this->db->quote($end->get(IL_CAL_DATE), "date")
					;
		}
		
		return $this->query_when;
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

}

?>
