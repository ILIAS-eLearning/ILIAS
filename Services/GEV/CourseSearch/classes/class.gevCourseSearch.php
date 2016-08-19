<?php
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
require_once("Modules/Course/classes/class.ilObjCourse.php");
require_once("Services/CourseBooking/classes/class.ilCourseBookings.php");
require_once("Modules/Course/classes/class.ilObjCourseAccess.php");

class gevCourseSearch {
	const TAB_ALL = "all";
	const TAB_PRAESENZ = "onside";
	const TAB_WEBINAR = "webinar";
	const TAB_SELF = "wbt";
	const TAB_VIRTUEL_TRAINING = "virt";
	const TAB_TO_SHOW_ADVICE = "all";
	const CSS_SELECTED_TAB = "tabactive";
	const CSS_NOT_SELECTED_TAB = "tabinactive";

	static protected $instances = array();

	public function __construct($a_usr_id) {
		global $ilDB, $ilUser, $ilCtrl;

		$this->usr_id = $a_usr_id;
		$this->usr_utils = gevUserUtils::getInstance($this->usr_id);
		$this->gev_set = gevSettings::getInstance();
		$this->gDB = $ilDB;
		$this->gUser = $ilUser;
		$this->gCtrl = $ilCtrl;

		$this->search_tabs = null;
		$this->tabs_count = null;
	}

	static public function getInstance($a_usr_id) {
		if(array_key_exists($a_usr_id, self::$instances)) {
			return self::$instances[$a_usr_id];
		}

		self::$instances[$a_usr_id] = new gevCourseSearch($a_usr_id);
		return self::$instances[$a_usr_id];
	}

	public function hasUserSelectorOnSearchGUI() {
		return $this->usr_utils->isSuperior() && count($this->getEmployeesForCourseSearch()) > 0;
	}

	public function getEmployeesForCourseSearch() {
		if ($this->employees_for_course_search) {
			return $this->employees_for_course_search;
		}
		
		$e_ids = $this->getEmployeeIdsForCourseSearch();
		
		$res = $this->gDB->query( "SELECT usr_id, firstname, lastname"
								." FROM usr_data "
								." WHERE ".$this->gDB->in("usr_id", $e_ids, false, "integer")
								." ORDER BY lastname, firstname ASC"
								);
		
		$this->employees_for_course_search = array();
		while($rec = $this->gDB->fetchAssoc($res)) {
			$this->employees_for_course_search[] = $rec;
		}
		
		$this->employees_for_course_search = $this->employees_for_course_search;
		
		return $this->employees_for_course_search;
	}

	public function getEmployeeIdsForCourseSearch() {
		if ($this->employee_ids_for_course_search) {
			return $this->employee_ids_for_course_search;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		// we need the employees in those ous
		$_d_ous = $this->usr_utils->getOrgUnitsWhereUserCanBookEmployees();
		// we need the employees in those ous and everyone in the ous
		// below those.
		$_r_ous = $this->usr_utils->getOrgUnitsWhereUserCanBookEmployeesRecursive();
		
		$e_ous = array_merge($_d_ous, $_r_ous);
		$a_ous = array();
		foreach(gevOrgUnitUtils::getAllChildren($_r_ous) as $val) {
			$a_ous[] = $val["ref_id"];
		}
		
		$e_ids = array_unique(array_merge( gevOrgUnitUtils::getEmployeesIn($e_ous)
										 , gevOrgUnitUtils::getAllPeopleIn($a_ous)
										 )
							 );
		
		$this->employee_ids_for_course_search = gevUserUtils::removeInactiveUsers($e_ids);
		return $this->employee_ids_for_course_search;
	}

	public function getPotentiallyBookableCourseIds($a_search_options) {
		$hash = md5(serialize($a_search_options));
		if ($this->potentiallyBookableCourses[$hash] !== null) {
			return $this->potentiallyBookableCourses[$hash];
		}
		
		$is_tmplt_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$start_date_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
		$type_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_TYPE);
		$bk_deadl_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_BOOKING_DEADLINE);
		$schedule_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_SCHEDULE);
		
		// include search options 
		$additional_join = "";
		$additional_where = "";
		
		if (array_key_exists("title", $a_search_options)) {
			$additional_join .= " LEFT JOIN object_data od ON cs.obj_id = od.obj_id\n";
			$additional_where .= " AND od.title LIKE ".$this->gDB->quote("%".$a_search_options["title"]."%", "text")."\n";
		}
		if (array_key_exists("custom_id", $a_search_options)) {
			$custom_id_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_CUSTOM_ID);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text custom_id\n".
				"   ON cs.obj_id = custom_id.obj_id\n".
				"   AND custom_id.field_id = ".$this->gDB->quote($custom_id_field_id, "integer")."\n"
				;
			$additional_where .=
				" AND custom_id.value LIKE ".$this->gDB->quote("%".$a_search_options["custom_id"]."%", "text")."\n";
		}
		if (array_key_exists("type", $a_search_options)) {
			$types = $a_search_options["type"];
			$is_prae = false;
			if(in_array("Präsenztraining", $types)) {
				$additional_where .= " AND (ltype.value LIKE 'Pr_senztraining'\n";
				unset($types["prae"]);
				$is_prae = true;
			}

			if(count($types) > 0){
				$close_bracked = "";
				if($is_prae) {
					$additional_where .= " OR ";
					$close_bracked = ")";
				} else {
					$additional_where .= " AND ";
				}
				$additional_where .=$this->gDB->in("ltype.value", $types, $negate = false, $a_type = "text").$close_bracked."\n";
			} else {
				$additional_where .= ")";
			}
			
		}
		if (array_key_exists("categorie", $a_search_options)) {
			$categorie_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_TOPIC);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text categorie\n".
				"   ON cs.obj_id = categorie.obj_id\n".
				"   AND categorie.field_id = ".$this->gDB->quote($categorie_field_id, "integer")."\n";
				;
			$additional_where .=
				" AND categorie.value LIKE ".$this->gDB->quote("%".$a_search_options["categorie"]."%", "text")."\n";
		}
		if (array_key_exists("target_group", $a_search_options)) {
			$target_group_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_TARGET_GROUP);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text target_group\n".
				"   ON cs.obj_id = target_group.obj_id\n".
				"   AND target_group.field_id = ".$this->gDB->quote($target_group_field_id, "integer")."\n";
				;
			$additional_where .=
				" AND target_group.value LIKE ".$this->gDB->quote("%".$a_search_options["target_group"]."%", "text")."\n";
		}
		if (array_key_exists("location", $a_search_options)) {
			$location_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_VENUE);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text location\n".
				"   ON cs.obj_id = location.obj_id\n".
				"   AND location.field_id = ".$this->gDB->quote($location_field_id, "integer")."\n"
				;
			$additional_where .=
				" AND location.value LIKE ".$this->gDB->quote("%".$a_search_options["location"]."%", "text")."\n";
		}
		if (array_key_exists("provider", $a_search_options)) {
			$provider_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_PROVIDER);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text provider\n".
				"   ON cs.obj_id = provider.obj_id\n".
				"   AND provider.field_id = ".$this->gDB->quote($provider_field_id, "integer")."\n"
				;
			$additional_where .=
				" AND provider.value LIKE ".$this->gDB->quote("%".$a_search_options["provider"]."%", "text")."\n";
		}
		if (array_key_exists("period", $a_search_options)) {
			$end_date_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_END_DATE);
			
			// this is knowledge from the course amd plugin!
			$additional_join .=
				" LEFT JOIN adv_md_values_date end_date\n".
				"   ON cs.obj_id = end_date.obj_id\n".
				"   AND end_date.field_id = ".$this->gDB->quote($end_date_field_id, "integer")."\n"
				;
			$additional_where .=
				" AND ( ( NOT start_date.value > ".$this->gDB->quote(date("Y-m-d", $a_search_options["period"]["end"]))." ) \n".
				"       OR ".$this->gDB->in("ltype.value", array("Selbstlernkurs"), false, "text").") \n".
				" AND ( ( NOT end_date.value < ".$this->gDB->quote(date("Y-m-d", $a_search_options["period"]["start"]))." ) \n".
				"       OR ".$this->gDB->in("ltype.value", array("Selbstlernkurs"), false, "text")." \n".
				"       OR (end_date.value IS NULL AND NOT start_date.value < ".$this->gDB->quote(date("Y-m-d", $a_search_options["period"]["start"]))."))\n"
				;
		}
		$hour = $this->gDB->quote(date("H"), "text");
		$minute = $this->gDB->quote(date("i"),"text");
		// try to narrow down the set as much as possible to avoid permission checks
		$query = "SELECT DISTINCT cs.obj_id \n".
				 " FROM crs_settings cs\n".
				 " LEFT JOIN object_reference oref\n".
				 "   ON cs.obj_id = oref.obj_id\n".
				 // this is knowledge from the course amd plugin!
				 " LEFT JOIN adv_md_values_text is_template\n".
				 "   ON cs.obj_id = is_template.obj_id \n".
				 "   AND is_template.field_id = ".$this->gDB->quote($is_tmplt_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " \nLEFT JOIN adv_md_values_date start_date\n".
				 "   ON cs.obj_id = start_date.obj_id \n".
				 "   AND start_date.field_id = ".$this->gDB->quote($start_date_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " \nLEFT JOIN adv_md_values_text ltype\n".
				 "   ON cs.obj_id = ltype.obj_id \n".
				 "   AND ltype.field_id = ".$this->gDB->quote($type_field_id, "integer").
				 // this is knowledge from the course amd plugin				 
				 " \nLEFT JOIN adv_md_values_text schedule\n".
				 "   ON cs.obj_id = schedule.obj_id \n".
				 "   AND schedule.field_id = ".$this->gDB->quote($schedule_field_id, "integer").
				
				 $additional_join.
				 " \nWHERE cs.activation_type = 1\n".
				 "   AND cs.activation_start < ".time().
				 "   \nAND cs.activation_end > ".time().
				 "   \nAND oref.deleted IS NULL\n".
				 "   AND is_template.value = ".$this->gDB->quote("Nein", "text").
				 "   \nAND (  ( (ltype.value LIKE 'Pr_senztraining' OR ltype.value = 'Webinar' OR ltype.value = 'Virtuelles Training')\n".
				 "            AND start_date.value > ".$this->gDB->quote(date("Y-m-d"), "text").
				 "\n		    )\n".
				 "		 OR (".$this->gDB->in("ltype.value", array("Selbstlernkurs"), false, "text").
				 "			\n)\n".
				 "\n		 OR (ltype.value = 'Webinar' AND start_date.value = ".$this->gDB->quote(date("Y-m-d"), "text").
			 	 "	\n		AND (\n".
				 "					(\n".
				 "						SUBSTRING(schedule.value,19,2)>=30 AND \n".
				 "					 	(	\n".
				 "					 		\nSUBSTRING(schedule.value,16,2) > ".$hour.
				 "					   		\nOR \n".
				 "					   		(\n".
				 "					   			SUBSTRING(schedule.value,16,2) = ".$hour.
				 "					   	  	  \n	AND \n".
				 "				       			SUBSTRING(schedule.value,19,2)-30 > ".$minute.
				 "		   		      	  	\n)\n".
				 "					 	)\n".
				 "					)	\n ".
				 "					OR\n".
				 "					(\n".
				 "						SUBSTRING(schedule.value,19,2)<30 AND\n".
				 "						(\n".
				 "							SUBSTRING(schedule.value,16,2) -1 > ".$hour.
				 "							\nOR\n".
				 "							(\n".
				 "								SUBSTRING(schedule.value,16,2) -1 = ".$hour.
				 "					   	  	  \n	AND \n".
				 "				       			SUBSTRING(schedule.value,19,2)+30 > ".$minute.
				 "					  	  	\n)\n".
				 "				  		)\n".
				 "			 		)\n".
				 "		 		)\n".
				 "			)\n".
				 "		)\n".
				 $additional_where.
				 "";
				 
		$res = $this->gDB->query($query);
		$crss = array();
		while($val = $this->gDB->fetchAssoc($res)) {
			$crs_utils = gevCourseUtils::getInstance($val["obj_id"]);

			if ( $this->gUser->getId() !== 0 && (
					!$crs_utils->canBookCourseForOther($this->gUser->getId(), $this->usr_id)
					|| in_array($crs_utils->getBookingStatusOf($this->usr_id)
							   , array(ilCourseBooking::STATUS_BOOKED, ilCourseBooking::STATUS_WAITING)
							   )
					|| $crs_utils->isMember($this->usr_id)
					|| !ilObjCourseAccess::_isActivated($val["obj_id"])
					)) {
				continue;
			}
			
			if (gevObjectUtils::checkAccessOfUser($this->usr_id, "visible",  "", $val["obj_id"], "crs")) {
				$crss[] = $val["obj_id"];
			}
		}

		$this->potentiallyBookableCourses[$hash] = $crss;
		return $crss;
	}
	
	public function getPotentiallyBookableCourseInformation($a_search_options, $a_offset, $a_limit, $a_order = "title", $a_direction = "desc") {
		if ($a_order == "") {
			$a_order = "title";
		}
		
		if (!in_array($a_order, array("title", "start_date", "end_date", "booking_date", "location"
									 , "points", "fee", "target_group", "goals", "content", "type"))) 
		{
			throw new Exception("gevUserUtils::getPotentiallyBookableCourseInformation: unknown order '".$a_order."'");
		}
		
		if ($a_direction !== "asc" && $a_direction !== "desc") {
			throw new Exception("gevUserUtils::getPotentiallyBookableCourseInformation: unknown direction '".$a_direction."'");
		}
		
		$crss = $this->getPotentiallyBookableCourseIds($a_search_options);
		
		$crs_amd = 
			array( gevSettings::CRS_AMD_START_DATE			=> "start_date"
				 , gevSettings::CRS_AMD_END_DATE 			=> "end_date"
				 , gevSettings::CRS_AMD_BOOKING_DEADLINE	=> "booking_date"
				 //, gevSettings::CRS_AMD_ => "title"
				 //, gevSettings::CRS_AMD_START_DATE => "status"
				 , gevSettings::CRS_AMD_TYPE 				=> "type"
				 , gevSettings::CRS_AMD_VENUE 				=> "location_id"
				 , gevSettings::CRS_AMD_CREDIT_POINTS 		=> "points"
				 , gevSettings::CRS_AMD_FEE					=> "fee"
				 , gevSettings::CRS_AMD_TARGET_GROUP_DESC	=> "target_group"
				 , gevSettings::CRS_AMD_TARGET_GROUP		=> "target_group_list"
				 , gevSettings::CRS_AMD_GOALS 				=> "goals"
				 , gevSettings::CRS_AMD_CONTENTS 			=> "content"
				 , gevSettings::CRS_AMD_MAX_PARTICIPANTS	=> "max_participants"
				 , gevSettings::CRS_AMD_CANCEL_DEADLINE		=> "cancel_date"
				 , gevSettings::CRS_AMD_SCHEDULE			=> "schedule"
			);
			
		$city_amd_id = $this->gev_set->getAMDFieldId(gevSettings::ORG_AMD_CITY);
		$amd_util = gevAMDUtils::getInstance();

		$info = $amd_util->getTable($crss, $crs_amd,
								array("CONCAT(od_city.title, IF(city.value IS NOT NULL , CONCAT(', ',city.value),'')) as location","if(type_sort.value = 'Selbstlernkurs',1,0) as tp_sort"), 
								array(" LEFT JOIN object_data od_city ".
									  "   ON od_city.obj_id = amd4.value "
									 ," LEFT JOIN adv_md_values_text city ".
									  "   ON city.field_id = ".$this->gDB->quote($city_amd_id, "integer").
									  "  AND city.obj_id = amd4.value "
									 ," LEFT JOIN adv_md_values_text type_sort ".
									 "    ON type_sort.field_id = ".$this->gDB->quote($amd_util->getFieldId(gevSettings::CRS_AMD_TYPE), "integer").
									 "    AND type_sort.obj_id = od.obj_id"
									 ),
								 "ORDER BY tp_sort, ".$a_order." ".$a_direction." ".
								 " LIMIT ".$a_limit." OFFSET ".$a_offset);

		foreach ($info as $key => $value) {
			// TODO: This surely could be tweaked to be faster if there was no need
			// to instantiate the course to get booking information about it.
			$crs_utils = gevCourseUtils::getInstance($value["obj_id"]);
			
			$list = "";
			if($info[$key]["target_group_list"]) {
				foreach ($info[$key]["target_group_list"] as $val) {
					$list .= "<li>".$val."</li>";
				}
			}
			
			$info[$key]["target_group"] = "<ul>".$list."</ul>".$info[$key]["target_group"];
			
			$info[$key]["booking_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																		, $value["booking_date"]
																		);
			$info[$key]["cancel_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																		, $value["cancel_date"]
																		);

			$info[$key]["free_places"] = $crs_utils->getFreePlaces();
			$info[$key]["waiting_list_active"] = $crs_utils->isWaitingListActivated();
			$info[$key]["waiting_list_full"] = $crs_utils->isWaitingListFull();
		}

		return $info;
	}

	public function addSearchForTypeByActiveTab($a_serach_opts, $a_active_tab) {
		$options = array();

		switch($a_active_tab) {
			case self::TAB_ALL:
				$options["prae"] = "Präsenztraining";
				$options["webinar"] = "Webinar";
				$options["virt"] = "Virtuelles Training";
				$options["self"] = "Selbstlernkurs";
				break;
			case self::TAB_PRAESENZ:
				$options["prae"] = "Präsenztraining";
				break;
			case self::TAB_WEBINAR:
				$options["webinar"] = "Webinar";
				break;
			case self::TAB_VIRTUEL_TRAINING:
				$options["virt"] = "Virtuelles Training";
				break;
			case self::TAB_SELF:
				$options["self"] = "Selbstlernkurs";
				break;
			default:
				//throw exception()
		}
		
		$a_serach_opts["type"] = $options;
		
		return $a_serach_opts;
	}

	public function getPossibleTabs() {
		if ($this->search_tabs === null) {
			$this->search_tabs = array();
			$this->gCtrl->setParameterByClass("gevCourseSearchGUI", "active_tab", "all");
			$this->search_tabs["all"] = array
				( "gev_crs_search_all"
				, $this->gCtrl->getLinkTargetByClass("gevCourseSearchGUI")
				);
			$this->gCtrl->setParameterByClass("gevCourseSearchGUI", "active_tab", "onside");
			$this->search_tabs["onside"] = array
				( "gev_crs_search_present"
				, $this->gCtrl->getLinkTargetByClass("gevCourseSearchGUI")
				);
			$this->gCtrl->setParameterByClass("gevCourseSearchGUI", "active_tab", "webinar");
			$this->search_tabs["webinar"] = array
				( "gev_crs_search_webinar"
				, $this->gCtrl->getLinkTargetByClass("gevCourseSearchGUI")
				);
			$this->gCtrl->setParameterByClass("gevCourseSearchGUI", "active_tab", "wbt");
			$this->search_tabs["wbt"] = array
				( "gev_crs_search_self_learn"
				, $this->gCtrl->getLinkTargetByClass("gevCourseSearchGUI")
				);
			$this->gCtrl->setParameterByClass("gevCourseSearchGUI", "active_tab",null);
		 }
		return $this->search_tabs;
	}

	public function getActiveTab() {
		return $_GET["active_tab"] ? $_GET["active_tab"] : gevCourseSearch::TAB_TO_SHOW_ADVICE;
	}

	public function getCourseCounting($a_serach_opts) {
		if ($this->tabs_count == null) {
			$tabs = $this->getPossibleTabs();
			$this->tabs_count = array();

			foreach (array_keys($tabs) as $key) {
				$a_serach_opts = $this->addSearchForTypeByActiveTab($a_serach_opts,$key);
				$this->tabs_count[$key] = count($this->getPotentiallyBookableCourseIds($a_serach_opts));
			}
		}
		return $this->tabs_count;
	}

	public function isActiveTabSelflearning($active_tab) {
		return self::TAB_SELF == $active_tab;
	}
}
