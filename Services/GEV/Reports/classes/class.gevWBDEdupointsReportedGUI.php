<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "WBD-EduPoints"
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/GEV/Reports/classes/class.gevBasicReportGUI.php");

class gevWBDEdupointsReportedGUI extends gevBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = array(
			'title' => 'gev_rep_wbd_edupoints',
			'desc' => 'gev_rep_wbd_edupoints_desc',
			'img' => 'GEV_img/ico-head-rep-billing.png'
		);

		$this->table_cols = array
			( array("firstname", "firstname")
			, array("lastname", "lastname")
			, array("birthday", "birthday")
			, array("gev_bwv_id", "bwv_id")
			, array("wbd_service_type", "wbd_type")
			, array("title", "trainingstitle")
			, array("begin_date", "begin_date")
			, array("end_date", "end_date")
			, array("gev_credit_points", "credit_points")
			, array("wbd_booking_id", "wbd_booking_id")
			);

		$this->table_row_template= array(
			"filename" => "tpl.gev_wbd_edupoints_row.html", 
			"path" => "Services/GEV/Reports"
		);
	}
	
	protected function userIsPermitted () {
		return $this->user_utils->isAdmin();
	}

	protected function executeCustomCommand($a_cmd) {
		switch ($a_cmd) {
			default:
				return null;
		}
	}
	
	protected function fetchData(){ 
		//fetch retrieves the data 
		//require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$no_entry = $this->lng->txt("gev_table_no_entry");
		//$user_utils = gevUserUtils::getInstance($this->target_user_id);
		$data = array();


		//when ordering the table, watch out for date!
		//_table_nav=date:asc:0
		//btw, what is the third parameter?
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
					break;
			}
		}



		$query =  " SELECT 
						hist_user.firstname,
						hist_user.lastname,
						hist_user.birthday,
						hist_user.bwv_id,
						hist_user.wbd_type,
						hist_course.title as trainingstitle,
						hist_course.begin_date,
						hist_course.end_date,
						hist_usercoursestatus.credit_points,
						hist_usercoursestatus.wbd_booking_id
						FROM 
							hist_usercoursestatus
						INNER JOIN
							hist_course
						ON
							hist_usercoursestatus.crs_id = hist_course.crs_id
						INNER JOIN
							hist_user
						ON
							hist_usercoursestatus.usr_id = hist_user.user_id
						WHERE 
							hist_usercoursestatus.wbd_booking_id IS NOT NULL
						AND hist_user.hist_historic = 0
						AND hist_course.hist_historic =0"


					. $this->queryWhen($this->start_date, $this->end_date)
					. $sql_order_str
					;





		$bill_link_icon = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-get_bill.png").'" />';

		$res = $this->db->query($query);

		while($rec = $this->db->fetchAssoc($res)) {
			/*	
				modify record-entries here.
			*/			
			foreach ($rec as $key => $value) {
				
				if ($value == '-empty-' || $value == -1) {
					$rec[$key] = $no_entry;
					continue;
				}

				//date
				if( $rec["begin_date"] && $rec["end_date"] 
					&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
					){
					$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
					$end = new ilDate($rec["end_date"], IL_CAL_DATE);
					$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
					//$date = ilDatePresentation::formatPeriod($start,$end);
				} else {
					$date = '-';
				}
				$rec['date'] = $date;
			}
			
			$this->ctrl->setParameter($this, "billnumber", $rec["billnumber"]);
			$target = $this->ctrl->getLinkTarget($this, "deliverBillPDF");
			$this->ctrl->clearParameters($this);
			$rec["bill_link"] = "<a href=\"".$target."\">".$bill_link_icon."</a>";
			
			$data[] = $rec;
		}

		return $data;
	}

	protected function queryWhen(ilDate $start, ilDate $end) {
		if ($this->query_when === null) {
			$this->query_when =
					"    AND ( hist_course.end_date >= ".$this->db->quote($start->get(IL_CAL_DATE), "date")
					."        OR hist_course.end_date = '-empty-' OR hist_usercoursestatus.end_date = '0000-00-00')"
					."   AND hist_course.begin_date <= ".$this->db->quote($end->get(IL_CAL_DATE), "date")
					;
		}
		
		return $this->query_when;
	}
	
	

	//_process_ will modify record entries
	// xls means: only for Excel-Export
	// date is the key in data-array 
	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}	

	protected function _process_table_wbd_type($val) {
		$val = substr($val, 4);
		return $val;
	}
}

?>
