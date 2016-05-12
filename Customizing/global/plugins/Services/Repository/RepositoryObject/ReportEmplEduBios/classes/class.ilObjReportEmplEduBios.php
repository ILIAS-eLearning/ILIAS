<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
class ilObjReportEmplEduBios extends ilObjReportBase {
	
	protected $relevant_parameters = array();

	public function initType() {
		 $this->setType("xeeb");
	}

	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		global $lng;
		$this->gLng = $lng;
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_reeb')
				->addSetting($this->s_f
								->settingBool('truncate_orgu_filter', $this->plugin->txt('truncate_orgu_filter'))
							);
	}


	protected function points_in_cert_year_sql($year) {
		return   "SUM( IF (     usrcrs.begin_date >= usr.begin_of_certification + INTERVAL ".($year-1)." YEAR "
				."               AND usrcrs.begin_date < (usr.begin_of_certification + INTERVAL ".$year." YEAR)"
				."             , usrcrs.credit_points"
				."             , 0"
				."             )"
				."        )";
	}

	protected function getRoleIdsForRoleTitles(array $titles) {
		$query = 'SELECT obj_id FROM object_data '
				.'	WHERE '.$this->gIldb->in('title',$titles,false,'text')
				.'		AND type = '.$this->gIldb->quote('role','text');
		$res = $this->gIldb->query($query);
		$return = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec['obj_id'];
		}
		return $return;
	}

	protected function getWbdRelevantRoleIds() {
		return $this->getRoleIdsForRoleTitles(gevWBD::$wbd_relevant_roles);
	}

	protected function getTpServiceRoleIds() {
		return $this->getRoleIdsForRoleTitles(gevWBD::$wbd_tp_service_roles);
	}

	protected function buildQuery($query) {
		$points_in_current_period
						  =  "SUM( IF (     usrcrs.begin_date >= usr.begin_of_certification"
							."         AND usrcrs.begin_date < (usr.begin_of_certification + INTERVAL 5 YEAR)"
							."         AND usrcrs.okz <> '-empty-'"
							."        , usrcrs.credit_points"
							."        , 0"
							."        )"
							."   )";
		
		$no_tp_service_condition =
			"(roles.num_tp_service_roles = 0"
			."	AND ".$this->gIldb->in("usr.wbd_type",$services,true,"text")
			.")";
		$tp_service_condition =
			"(roles.num_tp_service_roles > 0"
			."	OR ".$this->gIldb->in("usr.wbd_type",$services,false,"text")
			.")";

		$earliest_possible_cert_period_begin = "2013-09-01"; 
		$cert_year_sql = " YEAR( CURDATE( ) ) - YEAR( begin_of_certification ) "
						."- ( DATE_FORMAT( CURDATE( ) , '%m%d' ) < DATE_FORMAT( begin_of_certification, '%m%d' ) )";
		$query	->select("usr.user_id")
				->select("usr.lastname")
				->select("usr.firstname")
				->select("usrd.login")
				->select("usr.adp_number")
				->select("usr.job_number")
				->select_raw("orgu_all.org_unit")
				->select("orgu_all.org_unit_above1")
				->select("orgu_all.org_unit_above2")
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
				->select_raw("CASE "
							."		WHEN ".$no_tp_service_condition
							."			 AND usr.begin_of_certification <= '$earliest_possible_cert_period_begin' THEN ''"
							."		WHEN ".$tp_service_condition
							."			 AND usr.begin_of_certification <= '$earliest_possible_cert_period_begin' THEN 'X'"
							."		WHEN ".$cert_year_sql." = 1 AND ".$points_in_current_period." < 40 THEN 'X'"
							."		WHEN ".$cert_year_sql." = 2 AND ".$points_in_current_period." < 80 THEN 'X'"
							."		WHEN ".$cert_year_sql." = 3 AND ".$points_in_current_period." < 120 THEN 'X'"
							."		WHEN ".$cert_year_sql." = 4 AND ".$points_in_current_period." < 160 THEN 'X'"
							."		ELSE ''"
							."END"
							." as attention"
							)
				->from("hist_user usr")
				->join("usr_data usrd")
					->on(" usr.user_id = usrd.usr_id")
				->raw_join("JOIN ( SELECT usr_id"
							."	,SUM(IF(".$this->gIldb->in("rol_id",$this->getWbdRelevantRoleIds(),false,"integer")
							."		,1,0)) AS num_wbd_roles"
							."	,SUM(IF(".$this->gIldb->in("rol_id",$this->getTpServiceRoleIds(),false,"integer")
							."		,1,0)) AS num_tp_service_roles"
							."	,GROUP_CONCAT(DISTINCT rol_title ORDER BY rol_title ASC SEPARATOR ', ') AS roles "
							."		FROM hist_userrole "
							."		WHERE action >= 0 AND hist_historic = 0 "
							."			AND ".$this->gIldb->in("usr_id", $this->allowed_user_ids, false, "integer")
							."		GROUP BY usr_id "
							."		) AS roles ON roles.usr_id = usr.user_id")
				->raw_join($this->getAllOrgusForUsersJoin())
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

				return $query;
	}
	
	protected function buildFilter($filter) {
		$earliest_possible_cert_period_begin = "2013-09-01"; 
		$cert_year_sql = " YEAR( CURDATE( ) ) - YEAR( begin_of_certification ) "
						."- ( DATE_FORMAT( CURDATE( ) , '%m%d' ) < DATE_FORMAT( begin_of_certification, '%m%d' ) )"
						;
		$wbd_relevant_condition =
			" (roles.num_wbd_roles > 0 "
			."		OR usr.okz != ".$this->gIldb->quote("-empty-",'text').")";
		$this->allowed_user_ids = $this->user_utils->getEmployeesWhereUserCanViewEduBios();

		//add recursive ORguFilter
		$this->orgu_filter = new recursiveOrguFilter("org_unit","orgu_id",true,true);

		$orgu_refs = $this->user_utils->getOrgUnitsWhereUserCanViewEduBios();
		require_once "Services/GEV/Utils/classes/class.gevObjectUtils.php";
		$orgus = array_map(function ($ref_id) {return gevObjectUtils::getObjId($ref_id);},$orgu_refs);
		$this->orgu_filter->setFilterOptionsByArray($orgus);

		//only truncate orgu filter settings if set
		if((bool)$this->getSettingsDataFor("truncate_orgu_filter")) {
			$this->orgu_filter->setPreSelect(array_map(function($v) { return $v["obj_id"];},$this->user_utils->getOrgUnitsWhereUserIsDirectSuperior()));
			$this->orgu_filter->uncheckRecursiveSearch();
		}

		$this->orgu_filter->addToFilter($filter);
		//end

		$filter	->checkbox( "critical"
						  , $this->plugin->txt("show_critical_persons")
						  , "attention = 'X'"
						  , "TRUE"
						  , true
						  )
				->checkbox( "critical_year4"
						  , $this->plugin->txt("show_critical_persons_4th_year")
						  , "begin_of_certification >= '$earliest_possible_cert_period_begin' AND ".
						    $cert_year_sql." = 4 AND attention = 'X'"
						  , "TRUE"
						  , true
						  )
				->checkbox( "possibly_wbd_relevant"
						  , $this->plugin->txt("wbd_relevant_only")
						  , $wbd_relevant_condition
						  , "TRUE"
						  );
		$filter	->textinput( "lastname"
								   , $this->plugin->txt("lastname_filter")
								   , "usr.lastname"
								   )
				->static_condition($this->gIldb->in("usr.user_id", $this->allowed_user_ids, false, "integer"))
				->static_condition(" usr.hist_historic = 0")		
				->action($this->filter_action)
				->compile();
		return $filter;
	}

	protected function buildTable($table) {
		$table
						->column("lastname", $this->plugin->txt("lastname") ,true)
						->column("firstname", $this->plugin->txt("firstname") ,true)
						->column("cert_period", $this->plugin->txt("cert_period") ,true)
						->column("points_sum", $this->plugin->txt("overall_points_cert_period") ,true)
						->column("login", $this->plugin->txt("login") ,true)
						->column("adp_number", $this->plugin->txt("adp_number") ,true)
						->column("job_number", $this->plugin->txt("job_number") ,true)
						->column("od_bd", $this->plugin->txt("od_bd") ,true)
						->column("org_unit", $this->plugin->txt("orgu_short") ,true)
						->column("roles", $this->plugin->txt("roles") ,true)
						->column("points_year1", "1", true)
						->column("points_year2", "2", true)
						->column("points_year3", "3", true)
						->column("points_year4", "4", true)
						->column("points_year5", "5", true)
						->column("attention", $this->plugin->txt("critical"),true);
		return parent::buildTable($table);
	}

	public function buildOrder($order) {
		$order->mapping("date", "crs.begin_date")
				->mapping("od_bd", array("org_unit_above1", "org_unit_above2"))
				->defaultOrder("lastname", "ASC")
				;
		return $order;
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_employee_edu_bios_row.html";
	}

	protected function getAllOrgusForUsersJoin() {
		$query = 	"JOIN ("
					."	SELECT usr_id, GROUP_CONCAT(DISTINCT orgu_title SEPARATOR ', ') as org_unit".PHP_EOL
					."		, GROUP_CONCAT(DISTINCT org_unit_above1 SEPARATOR ', ') as org_unit_above1".PHP_EOL
					."		, GROUP_CONCAT(DISTINCT org_unit_above1 SEPARATOR ', ') as org_unit_above2".PHP_EOL
					."	FROM hist_userorgu".PHP_EOL
					."	WHERE ".$this->gIldb->in("usr_id", array_intersect($this->allowed_user_ids,$this->getUsersFilteredByOrguFilter()), false, "integer").PHP_EOL
					."		AND action >= 0 AND hist_historic = 0".PHP_EOL
					."	GROUP BY usr_id".PHP_EOL
					.") as orgu_all ON orgu_all.usr_id = usr.user_id ".PHP_EOL;
		return $query;
	}

	protected function getUsersFilteredByOrguFilter() {
		$orgu_filter = "SELECT usr_id FROM hist_userorgu ".PHP_EOL
						."		WHERE `action` >= 0 AND hist_historic = 0 ".PHP_EOL
						."		AND ".$this->orgu_filter->deliverQuery().PHP_EOL
						."		GROUP BY usr_id ".PHP_EOL;
		$res = $this->gIldb->query($orgu_filter);
		$return = array();
		while( $rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec['usr_id'];
		}
		return $return;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}