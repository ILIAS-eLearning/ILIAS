<?php
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");
require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php");
require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusPermissions.php");

class gevMyTrainingsAdmin {
	const FILTER_SESSION_VAR = "as_filter";
	const POST_SESSION_VAR = "as_post";

	const CLOSED = "closed";
	const WIP = "wip";
	const NOT_FINISHED = "not_closed";

	const PRAESENZ = "Prsenztraining";
	const WEBINAR = "Webinar";

	public function __construct($usr_id) {
		global $lng, $ilDB;

		$this->gLng = $lng;
		$this->gDb = $ilDB;
		$this->usr_id = $usr_id;
		$this->gev_settings = gevSettings::getInstance();

		$this->loadFilterSettings();
		$this->loadPost();
	}

	public function saveFilterInputs() {
		$filter = $this->filter();
		$display = new \CaT\Filter\DisplayFilter
						( new \CaT\Filter\FilterGUIFactory
						, new \CaT\Filter\TypeFactory
						);

		$post = $this->getPOST();
		if ($post["filter"] === null) {
			$post["filter"] = array();
		}

		$settings = $display->buildFilterValues($filter, $post["filter"]);
		$this->saveFilterSettings($settings);
		$this->savePostValues($post["filter"]);
	}

	public function loadFilterSettings() {
		if ($this->filter_settings !== null) {
			return $this->filter_settings;
		}

		$tmp = ilSession::get(self::FILTER_SESSION_VAR);
		if ($tmp !== null) {
			$this->filter_settings =  unserialize($tmp);
		}
		else {
			$this->filter_settings = array(0 => new \DateTime(date("Y")."-01-01")
				, 1 => new \DateTime(date("Y")."-12-31")
				, 2 => array_keys($this->getCourseStatus())
				, 3 => array_keys($this->getCourseTypes())
			);
		}

		return $this->filter_settings;
	}

	public function loadPost() {
		if ($this->filter_post_var !== null) {
			return $this->filter_post_var;
		}

		$tmp = ilSession::get(self::POST_SESSION_VAR);
		if ($tmp !== null) {
			$this->filter_post_var = unserialize($tmp);
		}
		else {
			$this->filter_post_var = null;
		}

		return $this->filter_post_var;
	}

	protected function getPOST() {
		return $_POST;
	}

	protected function saveFilterSettings($settings) {
		ilSession::set(self::FILTER_SESSION_VAR, serialize($settings));
		$this->filter_settings = $settings;
	}

	protected function savePostValues($post) {
		ilSession::set(self::POST_SESSION_VAR, serialize($post));
		$this->filter_post_var = $post;
	}

	public function filter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function($id) { return $this->gLng->txt($id); };

		return $f->sequence(
					$f->sequence(
						$f->dateperiod($txt("gev_period"),"")
						, $f->multiselect($txt("gev_training_status"), "", $this->getCourseStatus())
							->use_all_if_nothing(array_keys($this->getCourseStatus()) , $tf->lst($tf->string()))
						, $f->multiselect($txt("gev_course_type"), "", $this->getCourseTypes())
							->use_all_if_nothing(array_keys($this->getCourseTypes()) , $tf->lst($tf->string()))
					)
				)
				->map_raw(function($start, $end, $status, $types) use ($f) {
					$pc = $f->dateperiod_overlaps_predicate
								( "begin_date.value"
								, "end_date.value"
								);

					$ret = array( "period_pred" => $pc($start, $end)
								, "start" => $start
								, "end" => $end
								, "status" => $status
								, "types" => $types
								);
					return $ret;
				}, $tf->int());
	}

	public function displayFilter() {
		if(!$this->display) {
			$this->display = new \CaT\Filter\DisplayFilter
						( new \CaT\Filter\FilterGUIFactory
						, new \CaT\Filter\TypeFactory
						);
		}
		
		return $this->display;
	}

	protected function settings() {
		$settings = null;

		$to_sql = new \CaT\Filter\SqlPredicateInterpreter($this->gDb);
		$settings = call_user_func_array(array($this->filter(), "content"), $this->filter_settings);
		
		if(isset($settings["period_pred"])) {
			$settings["period_pred"] = $to_sql->interpret($settings["period_pred"]);
		}

		return $settings;
	}

	protected function getCourseTypes() {
		return array(self::PRAESENZ=>$this->gLng->txt("gev_training_admin_search_presence")
					, self::WEBINAR=>$this->gLng->txt("gev_training_admin_search_webinar")
				);
	}

	protected function getCourseStatus() {
		return array(self::CLOSED=>$this->gLng->txt("gev_training_admin_search_closed")
					,self::WIP=>$this->gLng->txt("gev_training_admin_search_wip")
					,self::NOT_FINISHED=>$this->gLng->txt("gev_training_admin_search_not_closed")
				);
	}

	public function getMyTrainingsAdminCourseInformation($a_order_field, $a_order_direction, array $roles) {
		if ((!$a_order_field && $a_order_direction) || ($a_order_field && !$a_order_direction)) {
			throw new Exception("gevUserUtils::getMyTrainingsAdminCourseInformation: ".
								"You need to set both: order_field and order_direction.");
		}
		
		if ($a_order_direction) {
			$a_order_direction = strtoupper($a_order_direction);
			if (!in_array($a_order_direction, array("ASC", "DESC"))) {
				throw new Exception("gevUserUtils::getMyTrainingsAdminCourseInformation: ".
									"order_direction must be ASC or DESC.");
			}
		}

			
			$crss = $this->getCourseIds($roles);
			$crss_ids = array_keys($crss);
			
			//do the amd-dance
			$crs_amd = 
			array( gevSettings::CRS_AMD_START_DATE			=> "start_date"
				 , gevSettings::CRS_AMD_END_DATE 			=> "end_date"
				 
				 , gevSettings::CRS_AMD_CUSTOM_ID			=> "custom_id"
				 , gevSettings::CRS_AMD_TYPE 				=> "type"
				 
				 , gevSettings::CRS_AMD_VENUE 				=> "location"
				 , gevSettings::CRS_AMD_VENUE_FREE_TEXT 	=> "location_free_text"

				 , gevSettings::CRS_AMD_MAX_PARTICIPANTS	=> "mbr_max"
				 , gevSettings::CRS_AMD_MIN_PARTICIPANTS	=> "mbr_min"
				 
				 , gevSettings::CRS_AMD_TARGET_GROUP		=> "target_group"
				 , gevSettings::CRS_AMD_TARGET_GROUP_DESC	=> "target_group_desc"
				 , gevSettings::CRS_AMD_GOALS 				=> "goals"
				 , gevSettings::CRS_AMD_CONTENTS 			=> "content"
				 , gevSettings::CRS_AMD_CREDIT_POINTS 		=> "credit_points"
			);
			
			if ($a_order_field) {
				$order_sql = " ORDER BY ".$this->gDb->quoteIdentifier($a_order_field)." ".$a_order_direction;
			}
			else {
				$order_sql = "";
			}
			
			$crss_amd = gevAMDUtils::getInstance()->getTable($crss_ids, $crs_amd);

			$ret = array();

			foreach ($crss_amd as $id => $entry) {
				$entry['crs_ref_id'] = $crss[$id]["ref_id"];
				$entry['status'] = $crss[$id]["status"];

				$crs_utils = gevCourseUtils::getInstance($id);
				$orgu_utils = gevOrgUnitUtils::getInstance($entry["location"]);
				$ps_helper = ilParticipationStatusHelper::getInstance($crs_utils->getCourse());
				$ps_permission = ilParticipationStatusPermissions::getInstance($crs_utils->getCourse(), $this->user_id);

				$entry["location"] = $orgu_utils->getLongTitle();

				$entry['mbr_booked_userids'] = $crs_utils->getParticipants();
				$entry['mbr_booked'] = count($entry['mbr_booked_userids']);
				$entry['mbr_waiting_userids'] = $crs_utils->getWaitingMembers($id);
				$entry['mbr_waiting'] = count($entry['mbr_waiting_userids']);
				$entry['tutor'] = $crs_utils->getTrainers(true);
				
				$entry['may_finalize'] = $crs_utils->canModifyParticipationStatus($this->user_id);

				$ret[$id] = $entry;
			}

			//sort?
			return $ret;
	}

	protected function getCourseIds(array $roles) {
		$tmplt_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$settings = $this->settings();
		$crs_type_query = $this->createCrsTypeQuery($settings["types"]);
		$period_query = $this->createPeriodQuery($settings["period_pred"]);
		$like_role = $this->createLikeRoleQuery($roles);

		$query = "(SELECT oref.obj_id, oref.ref_id, '{SELECT}' as status\n"
			."  FROM object_reference oref\n"
			."  JOIN object_data od ON od.type = 'role' AND ( ".$like_role ." )\n"
			."  JOIN rbac_fa fa ON fa.rol_id = od.obj_id\n"
			."  JOIN tree tr ON tr.child = fa.parent\n"
			."  JOIN rbac_ua ua ON ua.rol_id = od.obj_id\n"
			."  JOIN object_data od2 ON od2.obj_id = oref.obj_id\n"
			." LEFT JOIN adv_md_values_text is_template\n"
			."    ON oref.obj_id = is_template.obj_id\n"
			."   AND is_template.field_id = ".$this->gDb->quote($tmplt_field_id, "integer")."\n"
			.$period_query["join"]
			.$crs_type_query["join"]
			." {JOIN}"
			." WHERE oref.ref_id = tr.parent\n"
			."   AND ua.usr_id = ".$this->gDb->quote($this->usr_id, "integer")."\n"
			."   AND od2.type = 'crs'\n"
			."   AND oref.deleted IS NULL\n"
			."   AND is_template.value = 'Nein'\n"
			.$period_query["where"]
			.$crs_type_query["where"]
			." {WHERE})";

		$new_query = array();
		foreach ($settings["status"] as $status) {
			$status_query = $this->createCrsStatusQuery($status);
			$new_query_str = str_replace("{SELECT}", $status_query["select"], $query);
			$new_query_str = str_replace("{JOIN}", $status_query["join"], $new_query_str);
			$new_query_str = str_replace("{WHERE}", $status_query["where"], $new_query_str);
			$new_query[] = $new_query_str;
		}

		$exe_query = implode(" UNION ", $new_query);
die($exe_query);
		$res = $this->gDb->query($exe_query);
		$crs_ids = array();
		while($rec = $this->gDb->fetchAssoc($res)) {
			$crs_ids[$rec['obj_id']] = array("ref_id"=>$rec['ref_id'], "status"=>$rec["status"]);
		}

		return $crs_ids;
	}

	protected function createPeriodQuery($date_period) {
		$start_date_field_id = $this->gev_settings->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
		$end_date_field_id = $this->gev_settings->getAMDFieldId(gevSettings::CRS_AMD_END_DATE);

		$ret = array();
		$ret["join"] = " LEFT JOIN  adv_md_values_date end_date\n"
			."   ON oref.obj_id = end_date.obj_id\n"
			."   AND end_date.field_id = ".$this->gDb->quote($end_date_field_id, "integer")."\n"
			." LEFT JOIN adv_md_values_date begin_date\n"
			."   ON oref.obj_id = begin_date.obj_id\n"
			."   AND begin_date.field_id = ".$this->gDb->quote($start_date_field_id, "integer")."\n";

		$ret["where"] = "   AND ".$date_period."\n";

		return $ret;
	}

	protected function createCrsTypeQuery($types) {
		$crs_type_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_TYPE);
		$ret = array();
		$ret["join"] = " LEFT JOIN adv_md_values_text crs_type\n"
			."    ON oref.obj_id = crs_type.obj_id\n"
			."    AND crs_type.field_id = ".$this->gDb->quote($crs_type_field_id, "integer")."\n";

		$key = array_search(self::PRAESENZ, $types);
		if($key !== false) {
			$ret["where"] = "    AND (crs_type.value LIKE ".$this->gDb->quote("Pr%senztraining", "text")."\n";
			unset($types[$key]);
			$ret["where"] .= "    OR ".$this->gDb->in("crs_type.value", $types, false, "text").")\n";
		} else {
			$ret["where"] = "    AND ".$this->gDb->in("crs_type.value", $types, false, "text")."\n";
		}

		return $ret;
	}

	protected function createLikeRoleQuery($roles) {
		$like_role = array();
		foreach ($roles as $role) {
			$like_role[] = "od.title LIKE ".$this->gDb->quote($role);
		}
		return implode(" OR ", $like_role);
	}

	protected function createCrsStatusQuery($status) {
		$ret = array();
		$ret["select"] = $status;

		if($status == self::CLOSED) {
			$ret["join"] = " LEFT JOIN crs_pstatus_crs pstat_crs\n"
				."    ON oref.obj_id = pstat_crs.crs_id\n";

			$ret["where"] = "    AND pstat_crs.state = ".$this->gDb->quote("3", "integer")."\n";
		}

		if($status == self::WIP) {
			$end_date_field_id = $this->gev_settings->getAMDFieldId(gevSettings::CRS_AMD_END_DATE);
			$ret["join"] = " LEFT JOIN crs_pstatus_crs pstat_crs\n"
				."    ON oref.obj_id = pstat_crs.crs_id\n";

			$ret["join"] .= " LEFT JOIN  adv_md_values_date end_date_crs\n"
				."   ON oref.obj_id = end_date_crs.obj_id\n"
				."   AND end_date_crs.field_id = ".$this->gDb->quote($end_date_field_id, "integer")."\n";

			$ret["where"] = "    AND (pstat_crs.state < ".$this->gDb->quote("3", "integer")."\n"
							."    OR pstat_crs.state IS NULL)\n";

			$ret["where"] .= "    AND end_date_crs.value > ".$this->gDb->quote(date("Y-m-d"), "text")."\n";
		}

		if($status == self::NOT_FINISHED) {
			$end_date_field_id = $this->gev_settings->getAMDFieldId(gevSettings::CRS_AMD_END_DATE);
			$ret["join"] = " LEFT JOIN crs_pstatus_crs pstat_crs\n"
				."    ON oref.obj_id = pstat_crs.crs_id\n";

			$ret["join"] .= " LEFT JOIN  adv_md_values_date end_date_crs\n"
				."   ON oref.obj_id = end_date_crs.obj_id\n"
				."   AND end_date_crs.field_id = ".$this->gDb->quote($end_date_field_id, "integer")."\n";

			$ret["where"] = "    AND (pstat_crs.state < ".$this->gDb->quote("3", "integer")."\n"
							."    OR pstat_crs.state IS NULL)\n";

			$ret["where"] .= "    AND end_date_crs.value < ".$this->gDb->quote(date("Y-m-d"), "text")."\n";
		}

		return $ret;
	}
}