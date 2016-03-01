<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';

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
		return array
			( "0" => "Trainingsvorlage 1"
			, "1" => "Trainingsvorlage 2"
			);
	}

	public function getOrguOptions() {
		return $this->user_utils->getOrgUnitNamesWhereUserCanViewEduBios();
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
				)
			, $f->dateperiod
				( $txt("dateperiod_choice_label")
				, $txt("dateperiod_choice_description")
				)
				->map_to_predicate
					( function(\DateTime $start, \DateTime $end) use ($f, $pf) {
						$pc = $f->dateperiod_overlaps_or_empty_predicate
							( "usrcrs.begin_date"
							, "usrcrs.end_date"
							);

						return $pc($start, $end);
					})
			, $f->one_of
				( $txt("person_choice_label")
				, $txt("person_choice_description")
				, $f->multiselect
					( $txt("orgu_choice_label")
					, $txt("orgu_choice_description")
					, $this->getOrguOptions()
					)
				, $f->multiselect
					( $txt("role_choice_label")
					, $txt("role_choice_description")
					, $this->getRoleOptions()
					)
				)
			)
			->map_raw(function($tpl_obj_id, $period_pred, $choice) {
				$ret = array( "template_obj_id" => $tpl_obj_id
							, "period_pred" => $period_pred
							);
				if ($choice[0] === 0) {
					$ret["orgu_ids"] = $choice[1];
				}
				else {
					$ret["role_ids"] = $choice[1];
				}
				return $ret;
			}, $tf->int())
			;
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
			$crs_ids = (int)$rec["crs_id"];
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
			$users = getAllPeopleIn($all_orgu_ref_ids);
		}
		else {
			require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
			$ru = gevRoleUtils::getInstance();
			$users = array();
			foreach ($settings["role_ids"] as $role_id) {
				$users = array_merge($ru->usersHavingRoleId($role_id), $users);
			}
		}
		$user = array_unique($users);

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
				 " WHERE ".$db->in("usr.usr_id", $usr_ids).
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

	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_rta ".
			"(id, is_online) VALUES (".
			$this->gIldb->quote($this->getId(), "integer").",".
			$this->gIldb->quote(0, "integer").
			")");
	}


	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_rta ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
		}
	}

	public function doUpdate() {
		$this->gIldb->manipulate($up = "UPDATE rep_robj_rta SET ".
			" is_online = ".$this->gIldb->quote($this->getOnline(), "integer").
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_rta WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}

	public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$new_obj->setOnline($this->getOnline());
		$new_obj->update();
	}


	public function setOnline($a_val) {
		$this->online = (int)$a_val;
	}

	public function getOnline() {
		return $this->online;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}