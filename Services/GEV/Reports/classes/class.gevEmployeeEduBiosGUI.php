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
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");

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
						->column("points_sum", "gev_overall_points")
						->column("cert_period", "gev_cert_period")
						->column("login", "login")
						->column("adp_number", "gev_adp_number")
						->column("job_number", "gev_job_number")
						->column("od_bd", "gev_od_bd")
						->column("org_unit", "gev_org_unit_short")
						->column("roles", "gev_rep_roles")
						->column("points_year1", "1", true)
						->column("points_year2", "2", true)
						->column("points_year3", "3", true)
						->column("points_year4", "4", true)
						->column("points_year5", "5", true)
						->column("attention", "gev_attention")
						->template("tpl.gev_employee_edu_bios_row.html", "Services/GEV/Reports");
		
		$this->order = catReportOrder::create($this->table)
						//->mapping("date", "crs.begin_date")
						->mapping("od_bd", array("org_unit_above1", "org_unit_above2"))
						->defaultOrder("lastname", "ASC")
						;
		
		$cert_year_sql = " YEAR( CURDATE( ) ) - YEAR( usr.begin_of_certification ) "
						."- ( DATE_FORMAT( CURDATE( ) , '%m%d' ) < DATE_FORMAT( usr.begin_of_certification, '%m%d' ) )"
						;
		$points_in_current_period
						  =  "SUM( IF (     usrcrs.begin_date >= usr.begin_of_certification"
							."         AND usrcrs.begin_date < (usr.begin_of_certification + INTERVAL 5 YEAR)"
							."         AND usrcrs.okz <> '-empty-'"
							."        , usrcrs.credit_points"
							."        , 0"
							."        )"
							."   )";

		$this->allowed_user_ids = $this->user_utils->getEmployeesWhereUserCanViewEduBios();
		$orgu_filter = new recursiveOrguFilter("org_unit","orgu_id",true,true);
		$orgu_refs = $this->user_utils->getOrgUnitsWhereUserCanViewEduBios();
		require_once "Services/GEV/Utils/classes/class.gevObjectUtils.php";
		$orgus = array_map(function ($ref_id) {return gevObjectUtils::getObjId($ref_id);},$orgu_refs);
		$orgu_filter->setFilterOptionsByArray($orgus);
		$services = array(gevWBD::WBD_EDU_PROVIDER,gevWBD::WBD_TP_BASIS,gevWBD::WBD_TP_SERVICE);
		$no_tp_service_condition =
			"(roles.num_tp_service_roles = 0"
			."	AND ".$this->db->in("usr.wbd_type",$services,true,"text")
			.")";
		$wbd_relevant_condition =
			" (roles.num_wbd_roles > 0 "
			."		OR usr.okz != ".$this->db->quote("-empty-",'text').")";

		$this->filter = catFilter::create()
						->checkbox( "critical"
								  , $this->lng->txt("gev_rep_filter_show_critical_persons")
								  , "attention = 'X'"
								  , "TRUE"
								  , true
								  )
						->checkbox( "critical_year4"
								  , $this->lng->txt("gev_rep_filter_show_critical_persons_4th_year")
								  , "usr.begin_of_certification >= '$earliest_possible_cert_period_begin' AND ".
								    $cert_year_sql." = 4 AND attention = 'X'"
								  , "TRUE"
								  , true
								  )
						->checkbox( "possibly_wbd_relevant"
								  , $this->lng->txt("gev_filter_wbd_relevant_only")
								  , $wbd_relevant_condition
								  , "TRUE"
								  );
		$orgu_filter->addToFilter($this->filter);
		$this->filter	->textinput( "lastname"
								   , $this->lng->txt("gev_lastname_filter")
								   , "usr.lastname"
								   )
						->static_condition($this->db->in("usr.user_id", $this->allowed_user_ids, false, "integer"))
						->static_condition(" usr.hist_historic = 0")		
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;

		$this->relevant_parameters = array(
				$this->filter->getGETName() => $this->filter->encodeSearchParamsForGET()
			);
		
		$this->filtered_orgus = $this->filter->get('org_unit');
		$earliest_possible_cert_period_begin = "2013-09-01";
		$this->orgu_filter = "SELECT usr_id, GROUP_CONCAT(DISTINCT orgu_title SEPARATOR ', ') AS org_unit, "
							."		GROUP_CONCAT(DISTINCT org_unit_above1 SEPARATOR ', ') AS org_unit_above1, "
							."		GROUP_CONCAT(DISTINCT org_unit_above2 SEPARATOR ', ') AS org_unit_above2 " 
							."		FROM hist_userorgu huo "
							."		WHERE  huo.`action` >= 0 AND huo.hist_historic = 0 "
							."		AND ".$orgu_filter->deliverQuery()
							."		GROUP BY huo.usr_id ";						
		$this->query = catReportQuery::create()
						->select("usr.user_id")
						->select("usr.lastname")
						->select("usr.firstname")
						->select("usrd.login")
						->select("usr.adp_number")
						->select("usr.job_number")
						->select_raw("orgu.org_unit")
						->select("orgu.org_unit_above1")
						->select("orgu.org_unit_above2")
						->select("roles.roles")
						->select("usr.begin_of_certification")
						->select_raw("IF ( usr.begin_of_certification >= '$earliest_possible_cert_period_begin'"
									."   , usr.begin_of_certification"
									."   , '-')"
									." as cert_period"
									)
						->select_raw("IF ( usr.begin_of_certification >= '$earliest_possible_cert_period_begin'"
									."   , ".$this->points_in_cert_year_sql(1)
									."   , '-')"
									." as points_year1"
									)
						->select_raw("IF ( usr.begin_of_certification >= '$earliest_possible_cert_period_begin'"
									."   , ".$this->points_in_cert_year_sql(2)
									."   , '-')"
									." as points_year2"
									)
						->select_raw("IF ( usr.begin_of_certification >= '$earliest_possible_cert_period_begin'"
									."   , ".$this->points_in_cert_year_sql(3)
									."   , '-')"
									." as points_year3"
									)
						->select_raw("IF ( usr.begin_of_certification >= '$earliest_possible_cert_period_begin'"
									."   , ".$this->points_in_cert_year_sql(4)
									."   , '-')"
									." as points_year4"
									)
						->select_raw("IF ( usr.begin_of_certification >= '$earliest_possible_cert_period_begin'"
									."   , ".$this->points_in_cert_year_sql(5)
									."   , '-')"
									." as points_year5"
									)
						->select_raw($points_in_current_period." as points_sum")
						->select_raw("CASE 	WHEN ".$no_tp_service_condition." THEN ''"
									."		WHEN usr.begin_of_certification <= '$earliest_possible_cert_period_begin' THEN ''"
									."		WHEN ".$cert_year_sql." = 1 AND ".$points_in_current_period." < 40 THEN 'X'"
									."		WHEN ".$cert_year_sql." = 2 AND ".$points_in_current_period." < 80 THEN 'X'"
									."		WHEN ".$cert_year_sql." = 3 AND ".$points_in_current_period." < 120 THEN 'X'"
									."		WHEN ".$cert_year_sql." = 4 AND ".$points_in_current_period." < 160 THEN 'X'"
									."     ELSE ''"
									."END"
									." as attention"
									)
						->from("hist_user usr")
						->join("usr_data usrd")
							->on(" usr.user_id = usrd.usr_id")
						->raw_join("JOIN (".$this->orgu_filter
									.") as orgu ON orgu.usr_id = usr.user_id")
						->raw_join("JOIN ( SELECT usr_id"
									."	,SUM(IF(".$this->db->in("rol_id",$this->getWbdRelevantRoleIds(),false,"integer")
									."		,1,0)) AS num_wbd_roles"
									."	,SUM(IF(".$this->db->in("rol_id",$this->getTpServiceRoleIds(),false,"integer")
									."		,1,0)) AS num_tp_service_roles"
									."	,GROUP_CONCAT(DISTINCT rol_title ORDER BY rol_title ASC SEPARATOR ', ') AS roles "
									."		FROM hist_userrole "
									."		WHERE action >= 0 AND hist_historic = 0 "
									."			AND ".$this->db->in("usr_id", $this->allowed_user_ids, false, "integer")
									."		GROUP BY usr_id "
									."		) AS roles ON roles.usr_id = usr.user_id")
						->left_join("hist_usercoursestatus usrcrs")
							->on("     usr.user_id = usrcrs.usr_id"
								." AND usrcrs.hist_historic = 0 "
								." AND usrcrs.credit_points > 0"
								." AND usrcrs.participation_status = 'teilgenommen'"
								." AND usrcrs.booking_status = 'gebucht'"
								." AND usrcrs.okz <> '-empty-'"
								)
						->group_by("user_id")
						->compile();
	}
	
	protected function points_in_cert_year_sql($year) {
		return   "SUM( IF (     usrcrs.begin_date >= usr.begin_of_certification + INTERVAL ".($year-1)." YEAR "
				."               AND usrcrs.begin_date < (usr.begin_of_certification + INTERVAL ".$year." YEAR)"
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

	protected function getWbdRelevantRoleIds() {
		return $this->getRoleIdsForRoleTitles(gevWBD::$wbd_relevant_roles);
	}

	protected function getTpServiceRoleIds() {
		return $this->getRoleIdsForRoleTitles(gevWBD::$wbd_tp_service_roles);
	}

	protected function getRoleIdsForRoleTitles(array $titles) {
		$query = 'SELECT obj_id FROM object_data '
				.'	WHERE '.$this->db->in('title',$titles,false,'text')
				.'		AND type = '.$this->db->quote('role','text');
		$res = $this->db->query($query);
		$return = array();
		while($rec = $this->db->fetchAssoc($res)) {
			echo "1";
			echo $rec['obj_id']."<br>";
			$return[] = $rec['obj_id'];
		}
		return $return;
	}
}