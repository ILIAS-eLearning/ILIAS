<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "Employee Edu Biographies" for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevEmployeeEduBiosGUI extends catBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = catTitleGUI::create()
						->title("gev_rep_employee_edu_bios_title")
						->subTitle("gev_rep_employee_edu_bios_desc")
						->image("GEV_img/ico-head-edubio.png")
						;

		$this->table = catReportTable::create()
						->column("lastname", "lastname")
						->column("firstname", "firstname")
						->column("adp_number", "gev_adp_number")
						->column("job_number", "gev_job_number")
						->column("od_bd", "gev_od_bd")
						->column("org_unit", "gev_org_unit_short")
						->column("position_key", "gev_agent_key")
						->column("cert_period", "gev_cert_period")
						->column("points_year1", "1", true)
						->column("points_year2", "2", true)
						->column("points_year3", "3", true)
						->column("points_year4", "4", true)
						->column("points_year5", "5", true)
						->column("points_sum", "gev_overall_points")
						->column("attention", "gev_attention")
						->template("tpl.gev_employee_edu_bios_row.html", "Services/GEV/Reports")
						;
		
		$this->order = catReportOrder::create($this->table)
						//->mapping("date", "crs.begin_date")
						->mapping("od_bd", array("org_unit_above1", "org_unit_above2"))
						->defaultOrder("lastname", "ASC")
						;
		
		$cert_year_sql = " YEAR( CURDATE( ) ) - YEAR( usr.begin_of_certification ) "
						."- ( DATE_FORMAT( CURDATE( ) , '%m%d' ) < DATE_FORMAT( usr.begin_of_certification, '%m%d' ) )"
						;
		$points_in_completed_cert_years 
						  =  "SUM( IF (     usrcrs.begin_date >= usr.begin_of_certification"
							."         AND usrcrs.begin_date < (usr.begin_of_certification + INTERVAL (".$cert_year_sql.") YEAR)"
							."         AND usrcrs.okz <> '-empty-'"
							."        , usrcrs.credit_points"
							."        , 0"
							."        )"
							."   )";
		
		$this->query = catReportQuery::create()
						->distinct()
						->select("usr.user_id")
						->select("usr.lastname")
						->select("usr.firstname")
						->select("usr.adp_number")
						->select("usr.job_number")
						->select("usr.org_unit_above1")
						->select("usr.org_unit_above2")
						->select("usr.org_unit")
						->select("usr.position_key")
						->select("usr.begin_of_certification")
						->select_raw("IF ( usr.begin_of_certification > '2013-12-31'"
									."   , usr.begin_of_certification"
									."   , '-')"
									." as cert_period"
									)
						->select_raw("IF ( usr.begin_of_certification > '2013-12-31'"
									."   , ".$this->points_in_cert_year_sql(1)
									."   , '-')"
									." as points_year1"
									)
						->select_raw("IF ( usr.begin_of_certification > '2013-12-31'"
									."   , ".$this->points_in_cert_year_sql(2)
									."   , '-')"
									." as points_year2"
									)
						->select_raw("IF ( usr.begin_of_certification > '2013-12-31'"
									."   , ".$this->points_in_cert_year_sql(3)
									."   , '-')"
									." as points_year3"
									)
						->select_raw("IF ( usr.begin_of_certification > '2013-12-31'"
									."   , ".$this->points_in_cert_year_sql(4)
									."   , '-')"
									." as points_year4"
									)
						->select_raw("IF ( usr.begin_of_certification > '2013-12-31'"
									."   , ".$this->points_in_cert_year_sql(5)
									."   , '-')"
									." as points_year5"
									)
						->select_raw("IF ( usr.begin_of_certification > '2013-12-31'"
									."   , SUM( IF (     usrcrs.begin_date >= usr.begin_of_certification"
									."               AND usrcrs.begin_date < ( usr.begin_of_certification "
									."                                       + INTERVAL (".$cert_year_sql.") YEAR"
									."                                       )"
									."               AND usrcrs.okz <> '-empty-'"
									."             , usrcrs.credit_points"
									."             , 0"
									."             )"
									."        )"
									."   , '-')"
									." as points_sum")
						->select_raw("CASE WHEN usr.begin_of_certification < '2013-12-31' THEN ''"
									."     WHEN ".$cert_year_sql." = 0 AND ".$points_in_completed_cert_years." < 40 THEN 'X'"
									."     WHEN ".$cert_year_sql." = 1 AND ".$points_in_completed_cert_years." < 80 THEN 'X'"
									."     WHEN ".$cert_year_sql." = 2 AND ".$points_in_completed_cert_years." < 120 THEN 'X'"
									."     WHEN ".$cert_year_sql." = 3 AND ".$points_in_completed_cert_years." < 160 THEN 'X'"
									."     WHEN ".$cert_year_sql." = 4 AND ".$points_in_completed_cert_years." < 200 THEN 'X'"
									."     ELSE ''"
									."END"
									." as attention"
									)
						->from("hist_user usr")
						->left_join("hist_usercoursestatus usrcrs")
							->on("     usr.user_id = usrcrs.usr_id"
								." AND usrcrs.hist_historic = 0 "
								." AND usrcrs.credit_points > 0"
								." AND usrcrs.participation_status = 'teilgenommen'")
						->group_by("user_id")
						->compile()
						;

		$this->allowed_user_ids = $this->user_utils->getEmployees();
		$this->filter = catFilter::create()
						->dateperiod( "period"
									, $this->lng->txt("gev_period")
									, $this->lng->txt("gev_until")
									, "usrcrs.begin_date"
									, "usrcrs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									, false
									, " OR usrcrs.hist_historic IS NULL"
									)
						->checkbox( "critical"
								  , $this->lng->txt("gev_rep_filter_show_critical_persons")
								  , "attention = 'X'"
								  , "TRUE"
								  , true
								  )
						->checkbox( "critical_year4"
								  , $this->lng->txt("gev_rep_filter_show_critical_persons_4th_year")
								  , "usr.begin_of_certification > '2013-12-31' AND ".
								    $cert_year_sql." = 4 AND attention = 'X'"
								  , "TRUE"
								  , true
								  )
						->static_condition($this->db->in("usr.user_id", $this->allowed_user_ids, false, "integer"))
						->static_condition(" usr.hist_historic = 0")
						->static_condition("(   usrcrs.hist_historic = 0"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("(   usrcrs.booking_status != '-empty-'"
										  ." OR usrcrs.participation_status != '-empty-'"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("(   usrcrs.booking_status != 'kostenfrei storniert'"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("(   usrcrs.function NOT IN ('Trainingsbetreuer', 'Trainer')"
										  ." OR usrcrs.hist_historic IS NULL )" )
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;
	}
	
	protected function points_in_cert_year_sql($year) {
		return   "SUM( IF (     usrcrs.begin_date >= usr.begin_of_certification + INTERVAL ".($year-1)." YEAR "
				."               AND usrcrs.begin_date < (usr.begin_of_certification + INTERVAL ".$year." YEAR)"
				."               AND usrcrs.okz <> '-empty-'"
				."             , usrcrs.credit_points"
				."             , 0"
				."             )"
				."        )";
	}
	
	protected function transformResultRow($rec) {
		// credit_points
/*		if ($rec["credit_points"] == -1) {
			$rec["credit_points"] = $this->lng->txt("gev_table_no_entry");
		}
*/		
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
		if ($rec['cert_period'] != "-") {
			$rec['cert_period'] = ilDatePresentation::formatDate(new ilDate($rec['cert_period'], IL_CAL_DATE));
		}

		// od_bd
		if ( $rec["org_unit_above2"] == "-empty-") {
			if ($rec["org_unit_above1"] == "-empty-") {
				$rec["od_bd"] = $this->lng->txt("gev_table_no_entry");
			}
			else {
				$rec["od_bd"] = $rec["org_unit_above1"];
			}
		}
		else {
			$rec["od_bd"] = $rec["org_unit_above2"]."/".$rec["org_unit_above1"];
		}
		
		$rec["edu_bio_link"] = gevUserUtils::getEduBioLinkFor($rec["user_id"]);
		
		return $this->replaceEmpty($rec);
	}
	
	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}
}

?>
