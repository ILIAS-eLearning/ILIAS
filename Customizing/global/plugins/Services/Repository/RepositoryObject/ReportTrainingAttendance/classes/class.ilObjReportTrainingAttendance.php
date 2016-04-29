<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

class ilObjReportTrainingAttendance extends ilObjReportBase {
	
	protected $online;
	protected $relevant_parameters = array();

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
	}

	public function initType() {
		 $this->setType("xrta");
	}

	public function getRowTemplateTitle() {
		return "tpl.training_attendance_row.tpl";
	}

	protected function buildQuery($query) {
		return $query;
	}
	
	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rta')
					->addSetting($this->s_f->settingBool('is_local',$this->plugin->txt('is_local')));
	}

	// TODO: Those are not really used, as we use the new filter logic
	// in this report. Remove em!
	protected function buildFilter($filter) {
		return null;
	}

	public function deliverFilter() {
		return null;
	}
	//

	// As is don't use a regular filter, i also don't need its params...
	protected function addFilterToRelevantParameters() {
	}

	public function getTrainingTemplateOptions() {
		// TODO: implement this properly
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

		return $this->settings['is_local'] ? $this->getSubtreeCourseTemplates() : gevCourseUtils::getAllTemplates();
	}

	public function getOrguOptions() {
		return $this->user_utils->getOrgUnitNamesWhereUserCanViewEduBios(true);
	}

	public function getRoleOptions() {
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		return gevRoleUtils::getInstance()->getGlobalRoles();
	}

	public function filter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function($id) { return $this->plugin->txt($id); };

		return $f->sequence
			( $f->singleselect
				( $txt("template_choice_label")
				, $txt("template_choice_description")
				, $this->getTrainingTemplateOptions()
				)->map(function($tpl_obj_id) {return $tpl_obj_id;},$tf->int())
			, $f->dateperiod
				( $txt("dateperiod_choice_label")
				, $txt("dateperiod_choice_description")
				)->map(function($start,$end) use ($f) {
						$pc = $f->dateperiod_overlaps_or_empty_predicate
							( "usrcrs.begin_date"
							, "usrcrs.end_date"
							);
						return array("date_period_predicate" => $pc($start,$end)
							,"start" => $start
							,"end" => $end);
						},$tf->dict(array(
							"date_period_predicate" => $tf->cls("CaT\Filter\Predicates\Predicate")
							,"start" => $tf->cls("DateTime")
							,"end" => $tf->cls("DateTime")
						)))
				, $f->one_of
				( $txt("person_choice_label")
				, $txt("person_choice_description")
				, $f->multiselect
					( $txt("orgu_choice_label")
					, $txt("orgu_choice_description")
					, $this->getOrguOptions()
					)->map(function($id_s) {return $id_s;}
						,$tf->lst($tf->int()))
				, $f->multiselect
					( $txt("role_choice_label")
					, $txt("role_choice_description")
					, $this->getRoleOptions()
					)->map(function($id_s) {return $id_s;}
						,$tf->lst($tf->int()))
				)->map( function($choice,$id_s) {return array($choice,$id_s);}
				,$tf->tuple($tf->int(),$tf->lst($tf->int())))
			)->map(function($tpl_obj_id,$date_period_predicate,$start, $end, $choice, $id_s) {
						return array( "template_obj_id" => $tpl_obj_id
							, "period_pred" => $date_period_predicate
							, "start" => $start
							, "end" => $end
							, "choice" => $choice
							, "ids" => $id_s
							);}
						, $tf->dict(array("template_obj_id" => $tf->int()
							,"period_pred" => $tf->cls("CaT\Filter\Predicates\Predicate")
							,"start" => $tf->cls("DateTime")
							,"end" => $tf->cls("DateTime")
							,"choice" => $tf->int()
							,"ids"=> $tf->lst($tf->int()))));

	}

	protected function fetchData(callable $callback) {
		$db = $this->gIldb;
		$to_sql = new \CaT\Filter\SqlPredicateInterpreter($this->gIldb);
		$filter = $this->filter();
		$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);

		$dt_query = $to_sql->interpret($settings["period_pred"]);

		$res = $db->query("SELECT DISTINCT crs_id ".
						  "  FROM hist_course".
						  " WHERE hist_historic = 0".
						  "   AND template_obj_id = ".$db->quote($settings["template_obj_id"], "integer")
						);
		$crs_ids = array();
		while($rec = $db->fetchAssoc($res)) {
			$crs_ids[] = (int)$rec["crs_id"];
		}
		if($settings["choice"] === 0 ){
			$settings["orgu_ids"] = $settings["ids"];
		} elseif($settings["choice"] === 1) {
			$settings["role_ids"] = $settings["ids"];
		}

		if (array_key_exists("orgu_ids", $settings)) {
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
			$org_ref_ids = array_map(function($obj_id) {
				return gevObjectUtils::getRefId($obj_id);
			}, $settings["orgu_ids"]);
			$all_orgu_ref_ids = array_map(function($rec) {
				return $rec["ref_id"];
			}, gevOrgUnitUtils::getAllChildren($org_ref_ids));
			$all_orgu_ref_ids = array_merge($org_ref_ids, $all_orgu_ref_ids);
			$users = gevOrgUnitUtils::getAllPeopleIn($all_orgu_ref_ids);
		}
		else {
			require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
			$ru = gevRoleUtils::getInstance();
			$users = array();
			foreach ($settings["role_ids"] as $role_id) {
				$users = array_merge($ru->usersHavingRoleId($role_id), $users);
			}
		}
		$users = array_unique($users);

		$usr_ids = array_intersect( $this->user_utils->getEmployeesWhereUserCanViewEduBios()
								  , $users
								  );

		$query = "SELECT usr.lastname, usr.firstname, usr.email, usr.login, ".
				 " GROUP_CONCAT(DISTINCT usrorg.orgu_title SEPARATOR ', ') as orgu, ".
				 " IF((NOT usrcrs.participation_status IS NULL) AND usrcrs.participation_status = 'teilgenommen','Ja','Nein') as participated, ".
				 " usrcrs.begin_date as begin_date, usrcrs.end_date as end_date, ".
				 " IF((NOT usrcrs.booking_status IS NULL) AND usrcrs.booking_status = 'teilgenommen','Ja','Nein') as booked".
				 " FROM usr_data usr ".
				 " JOIN hist_userorgu usrorg ON usrorg.usr_id = usr.usr_id AND usrorg.hist_historic = 0 AND usrorg.action >= 0".
				 " LEFT JOIN hist_usercoursestatus usrcrs ON usr.usr_id = usrcrs.usr_id AND usrcrs.hist_historic = 0 AND ".$dt_query.
				 " WHERE ".$db->in("usr.usr_id", array_values($usr_ids), false, "integer").
				 "		AND ".$db->in("usrcrs.crs_id", $crs_ids, false, "integer").
				 " GROUP BY usr.usr_id ".
				 " ORDER BY usr.lastname, usr.firstname"
				 ;

		$res = $this->gIldb->query($query);
		$data = array();
		
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$data[] = call_user_func($callback,$rec);
		}

		return $data;
	}

	protected function buildTable($table) {
		$table
			->column("lastname",$this->plugin->txt("lastname"), true, "", false, false)
			->column("firstname",$this->plugin->txt("firstname"), true, "", false, false)
			->column("email",$this->plugin->txt("email"), true, "", false, false)
			->column("login",$this->plugin->txt("login"), true, "", false, false)
			->column("orgu",$this->plugin->txt("orgu"), true, "", false, false)
			//->column("training_type",$this->plugin->txt("training_type"), true, "", false, false)
			->column("participated",$this->plugin->txt("participated"), true, "", false, false)
			->column("participated_date",$this->plugin->txt("participated_date"), true, "", false, false)
			->column("booked",$this->plugin->txt("booked"), true, "", false, false)
			->column("booked_for_date",$this->plugin->txt("booked_for_date"), true, "", false, false)
			;
		return parent::buildTable($table);
	}

	protected function buildOrder($order) {
		return $order;
	}

	protected function getObjectTypes() {
		$sql = "SELECT DISTINCT type FROM object_data";
		$res = $this->gIldb->query($sql);
		$return = array();
		while( $rec = $this->gIldb->fetchAssoc($res) ) {
			$return[] = $rec["type"];
		}
		return $return;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}


	protected function getSubtreeCourseTemplates() {
		$query = 	'SELECT amd_val.obj_id,od.title FROM adv_md_values_text amd_val '
					.'	JOIN object_data od ON amd_val.obj_id = od.obj_id'
					.'	WHERE '.$this->gIldb->in('od.obj_id',
							$this->getSubtreeTypeIdsBelowParentType('crs','cat'),false,'integer')
					.'		AND amd_val.field_id = '.$this->gIldb->quote(
												gevSettings::getInstance()
													->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE)
												,'integer')
					.'		AND amd_val.value = '.$this->gIldb->quote('Ja','text');
		$return = array();
		$res = $this->gIldb->query($query);
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec['obj_id']] = $rec['title'];
		}
		return $return;
	}
}