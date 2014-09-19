<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "AttendanceByEmployees"
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

class gevBillingReportGUI extends gevBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = array(
			'title' => 'gev_rep_billing_title',
			'desc' => 'gev_rep_biling_desc',
			'img' => 'GEV_img/ico-head-rep-billing.png'
		);


		$this->table_cols = array
			( array("gev_bill_number", "billnumber")
			, array("gev_participation_status", "participation_status")
			, array("gev_training_fee_pretax", "fee_pretax")
			, array("gev_tax", "fee_tax")
			, array("gev_training_fee_posttax", "fee_posttax")
			, array("gev_coupon_pretax", "coupon_pretax")
			, array("gev_tax", "coupon_tax")
			, array("gev_coupon_posttax", "coupon_posttax")
			, array("gev_bill_amount_pretax", "amount_pretax")
			, array("gev_tax", "amount_tax")
			, array("gev_bill_amount_posttax", "amount_posttax")
			, array("gev_charged_agency", "cost_center")
			, array("lastname", "lastname")
			, array("firstname", "firstname")
			, array("gender", "gender")
			, array("gev_org_unit_short", "org_unit")
			, array("gev_event_title", "title")
			, array("gev_number_of_measure", "custom_id")
			, array("date", "date")
			, array("gev_venue", "venue")
			);

		$this->table_row_template= array(
			"filename" => "tpl.gev_billing_row.html", 
			"path" => "Services/GEV/Reports"
		);
	}
	
	protected function fetchData(){ 
		//fetch retrieves the data 
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$no_entry = $this->lng->txt("gev_table_no_entry");
		$user_utils = gevUserUtils::getInstance($this->target_user_id);
		$data = array();


		//when ordering the table, watch out for date!
		//_table_nav=date:asc:0
		//btw, what is the third parameter?
		/*if(isset($_GET['_table_nav'])){
			$this->external_sorting = true; //set to false again, 
											//if the field is not relevant

			$table_nav_cmd = split(':', $_GET['_table_nav']);
			switch ($table_nav_cmd[0]) { //field
				case 'date':
					$direction = strtoupper($table_nav_cmd[1]);
					$sql_order_str = " ORDER BY crs.begin_date ";
					$sql_order_str .= $direction;
					break;
				
				//append more fields, simply for performance...

				default:
					$this->external_sorting = false;
					$sql_order_str = " ORDER BY usr.lastname ASC";
					break;
			}
			
		}*/

		$query = 	 "SELECT  bill.bill_number as billnumber"
					."		, usrcrs.participation_status as participation_status"
					."		, ROUND(SUM(IF(item.billitem_context_id = bill.bill_context_id, item.billitem_pta, 0)), 2) as fee_pretax"
					."		, ROUND(SUM(IF(item.billitem_context_id = bill.bill_context_id, item.billitem_pta, 0)) * bill.bill_vat/100, 2) as fee_tax"
					."		, ROUND(SUM(IF(item.billitem_context_id = bill.bill_context_id, item.billitem_pta, 0)) * (1 + bill.bill_vat/100), 2) as fee_posttax"
					."		, ROUND(SUM(IF(item.billitem_context_id IS NULL, item.billitem_pta, 0)), 2) as coupon_pretax"
					."		, ROUND(SUM(IF(item.billitem_context_id IS NULL, item.billitem_pta, 0)) * bill.bill_vat/100, 2) as coupon_tax"
					."		, ROUND(SUM(IF(item.billitem_context_id IS NULL, item.billitem_pta, 0)) * (1 + bill.bill_vat/100), 2) as coupon_posttax"
					."		, ROUND(SUM(item.billitem_pta), 2) as amount_pretax"
					."		, ROUND(SUM(item.billitem_pta) * bill.bill_vat/100, 2) as amount_tax"
					."		, ROUND(SUM(item.billitem_pta) * (1 + bill.bill_vat/100), 2) as amount_posttax"
					."		, bill.bill_cost_center as cost_center"
					."		, usr.firstname as lastname"
					."		, usr.lastname as firstname"
					." 		, usr.gender as gender"
					."		, usr.org_unit as org_unit"
					."		, crs.title as title"
					."		, crs.custom_id as custom_id"
					."		, crs.begin_date as start_date"
					."		, crs.end_date as end_date"
					."		, crs.venue as venue"
					." FROM  bill "
					." INNER JOIN hist_course crs ON crs.crs_id = bill.bill_context_id AND crs.hist_historic = 0"
					." INNER JOIN hist_user usr ON usr.user_id = bill.bill_usr_id AND usr.hist_historic = 0"
					." INNER JOIN hist_usercoursestatus usrcrs ON usrcrs.usr_id = bill.bill_usr_id AND usrcrs.crs_id = bill.bill_context_id AND usrcrs.hist_historic = 0"
					." RIGHT JOIN billitem item ON bill.bill_pk = item.bill_fk"
					." WHERE bill.bill_final = 1"
					." GROUP BY bill.bill_number"
					;
					
		die($query);

		//get data
		$query =	 "SELECT usrcrs.usr_id, usrcrs.crs_id, "
					."		 usrcrs.booking_status, usrcrs.participation_status, usrcrs.okz, usrcrs.org_unit,"
					."		 usr.firstname, usr.lastname, usr.gender, usr.bwv_id, usr.position_key,"
					."		 crs.custom_id, crs.title, crs.type, crs.venue, crs.provider, crs.begin_date, crs.end_date "

 					."  FROM hist_usercoursestatus usrcrs "
					."  JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
					."  JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0"

					."  WHERE ("
					."		(usrcrs.booking_status != '-empty-' OR usrcrs.participation_status != '-empty-')"
					."  	AND usrcrs.function NOT IN ('Trainingsbetreuer', 'Trainer')"
					."  )"
					
					. $this->queryWhen($this->start_date, $this->end_date)
					. $this->queryAllowedUsers()
					
					//."  ORDER BY usr.lastname ASC";
					.$sql_order_str;


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
			
			$data[] = $rec;
		}

		return $data;
	}

	protected function queryWhen(ilDate $start, ilDate $end) {
		if ($this->query_when === null) {
			$this->query_when =
					 //" WHERE usr.user_id = ".$this->db->quote($this->target_user_id, "integer")
					//"  WHERE ".$this->db->in("usrcrs.function", array("Mitglied", "Teilnehmer", "Member"), false, "text")
					//."   AND ".$this->db->in("usrcrs.booking_status", array("gebucht", "kostenpflichtig storniert", "kostenfrei storniert"), false, "text")
					"   AND usrcrs.hist_historic = 0 "
					."   AND ( usrcrs.end_date >= ".$this->db->quote($start->get(IL_CAL_DATE), "date")
					."        OR usrcrs.end_date = '-empty-' OR usrcrs.end_date = '0000-00-00')"
					."   AND usrcrs.begin_date <= ".$this->db->quote($end->get(IL_CAL_DATE), "date")
					;
		}
		
		return $this->query_when;
	}
	
	protected function queryAllowedUsers() {
		
		//get all users the current user is superior of:
		$allowed_user_ids = $this->user_utils->getEmployees();
		$query = " AND ".$this->db->in("usr.user_id", $allowed_user_ids, false, "integer");
	
		return $query;
	}

	//_process_ will modify record entries
	// xls means: only for Excel-Export
	// date is the key in data-array 
	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}
}

?>
