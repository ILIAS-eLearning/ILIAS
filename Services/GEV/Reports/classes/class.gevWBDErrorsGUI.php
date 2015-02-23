<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "WBDErrors"
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");

class gevWBDErrorsGUI extends catBasicReportGUI{
	public function __construct() {
		
		parent::__construct();


		$this->title = catTitleGUI::create()
						->title("gev_rep_wbd_errors")
						->subTitle("gev_rep_wbd_errors_desc")
						->image("GEV_img/ico-head-rep-billing.png")
						;


		$this->table = catReportTable::create()
						->column("ts", "ts")
						->column("action", "action")
						->column("internal", "internal")
						->column("user_id", "usr_id")
						->column("course_id", "crs_id")
						->column("firstname", "firstname")
						->column("lastname", "lastname")
						->column("title", "title")
						->column("begin_date", "begin_date")
						->column("end_date", "end_date")
						->column("reason", "reason")
						->column("reason_full", "text")
						->column("resolve", "resolve")

						->template("tpl.gev_wbd_errors_row.html", "Services/GEV/Reports")
						;


		$this->filter = catFilter::create()
/*
						->checkbox( "too_old"
								  , $this->lng->txt("gev_wbd_errors_show_too_old")
								  , "TRUE"
								  , "reason != 'TOO_OLD'"
								  , true
								  )
						->checkbox( "critical_year4"
								  , $this->lng->txt("gev_rep_filter_show_critical_persons_4th_year")
								  , "usr.begin_of_certification >= '$earliest_possible_cert_period_begin' AND ".
								    $cert_year_sql." = 4 AND attention = 'X'"
								  , "TRUE"
								  , true
								  )
						->textinput( "lastname"
								   , $this->lng->txt("gev_lastname_filter")
								   , "usr.lastname"
								   )
						->multiselect("org_unit"
									 , $this->lng->txt("gev_org_unit")
									 , array("usr.org_unit", "usr.org_unit_above1", "usr.org_unit_above2")
									 , $ous
									 , array()
									 )
						->static_condition($this->db->in("usr.user_id", $this->allowed_user_ids, false, "integer"))
						->static_condition(" usr.hist_historic = 0")

*/						
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;

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
		//require_once("./Services/WBDData/classes/class.wbdErrorLog.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$no_entry = $this->lng->txt("gev_table_no_entry");
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
				case 'ts':
					$direction = strtoupper($table_nav_cmd[1]);
					$sql_order_str = " ORDER BY ts ";
					$sql_order_str .= $direction;
					break;
				
				//append more fields, simply for performance...

				default:
					$this->external_sorting = true;
					$sql_order_str = " ORDER BY ".$this->db->quoteIdentifier($table_nav_cmd[0])." ".$direction;
					break;
			}
		}



		$query =  " SELECT * FROM wbd_errors 
					INNER JOIN hist_user
					ON wbd_errors.usr_id = hist_user.user_id
					WHERE  wbd_errors.resolved = 0
					AND hist_user.hist_historic = 0
					"


//					. $this->queryWhen($this->start_date, $this->end_date)
					. $sql_order_str
					;





		$res = $this->db->query($query);

		while($rec = $this->db->fetchAssoc($res)) {
			/*	
				modify record-entries here.
			*/			

			if($rec['crs_id'] != 0) {
				$sql = "SELECT * FROM hist_course 
					WHERE hist_historic=0
					AND crs_id = " . $rec['crs_id'];

				$res_tmp = $this->db->query($sql);
				$rec_tmp = $this->db->fetchAssoc($res_tmp);

				$rec['begin_date'] = $rec_tmp['begin_date'];
				$rec['end_date'] = $rec_tmp['end_date'];
				$rec['title'] = $rec_tmp['title'];
			}




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
