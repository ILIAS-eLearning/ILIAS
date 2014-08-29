<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for generali users.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/Calendar/classes/class.ilDate.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
require_once("Services/CourseBooking/classes/class.ilUserCourseBookings.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");

class gevUserUtils {
	static protected $instances = array();

	protected function __construct($a_user_id) {
		global $ilDB;
		global $ilAccess;
		
		$this->user_id = $a_user_id;
		$this->courseBookings = ilUserCourseBookings::getInstance($a_user_id);
		$this->gev_set = gevSettings::getInstance();
		$this->udf_utils = gevUDFUtils::getInstance();
		$this->db = &$ilDB;
		$this->access = &$ilAccess;
		$this->user_obj = null;
		$this->org_id = null;
		
		$this->potentiallyBookableCourses = array();
		$this->users_who_booked_at_course = array();
	}
	
	public function getUser() {
		require_once("Services/User/classes/class.ilObjUser.php");
		
		if ($this->user_obj === null) {
			$this->user_obj = new ilObjUser($this->user_id);
		}
		
		return $this->user_obj;
	}
	
	static public function getInstance($a_user_id) {
		if (array_key_exists($a_user_id, self::$instances)) {
			return self::$instances[$a_user_id];
		}
		
		self::$instances[$a_user_id] = new gevUserUtils($a_user_id);
		return self::$instances[$a_user_id];
	}
	
	static public function getInstanceByObj(ilObjUser $a_user_obj) {
		$inst = self::getInstance($a_user_obj->getId());
		$inst->user_obj = $a_user_obj;
		return $inst;
	}
	
	static public function getInstanceByObjOrId($a_user) {
		if (is_int($a_user) || is_numeric($a_user)) {
			return self::getInstance((int)$a_user);
		}
		else {
			return self::getInstanceByObj($a_user);
		}
	}
	
	public function getNextCourseId() {
		$now = date("Y-m-d");
		$crss = $this->getBookedAndWaitingCourses();
		$amd = array( gevSettings::CRS_AMD_START_DATE => "start_date");
		$info = gevAMDUtils::getInstance()->getTable($crss, $amd, array(), array(),
													 " AND amd0.value >= ".$this->db->quote($now, "text").
													 " ORDER BY start_date ASC".
													 " LIMIT 1 OFFSET 0");
		if (count($info) > 0) {
			$val = array_pop($info);
			return $val["obj_id"];
		}
		else {
			return null;
		}
	}
	
	public function getLastCourseId() {
		$now = date("Y-m-d");
		$crss = $this->getBookedCourses();
		$amd = array( gevSettings::CRS_AMD_START_DATE => "start_date");
		$info = gevAMDUtils::getInstance()->getTable($crss, $amd, array(), array(),
													 " AND amd0.value < ".$this->db->quote($now, "text").
													 " ORDER BY start_date DESC".
													 " LIMIT 1 OFFSET 0");
		if (count($info) > 0) {
			$val = array_pop($info);
			return $val["obj_id"];
		}
		else {
			return null;
		}
	}
	
	public function getEduBioLink() {
		global $ilCtrl;
		$ilCtrl->setParameterByClass("gevEduBiographyGUI", "target_user_id", $this->user_id);
		$link = $ilCtrl->getLinkTargetByClass("gevEduBiographyGUI", "view");
		$ilCtrl->clearParametersByClass("gevEduBiographyGUI");
		return $link;
	}
	
	public function getCourseHighlights() {
		require_once("Modules/Course/classes/class.ilObjCourse.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookings.php");
		
		global $ilUser;
		
		$is_tmplt_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$start_date_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
		$type_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_TYPE);
		$bk_deadl_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_BOOKING_DEADLINE);
		
		$query = "SELECT DISTINCT cs.obj_id ".
				 " FROM crs_settings cs".
				 " LEFT JOIN object_reference oref".
				 "   ON cs.obj_id = oref.obj_id".
				 // this is knowledge from the course amd plugin!
				 " LEFT JOIN adv_md_values_text tmplt".
				 "   ON cs.obj_id = tmplt.obj_id ".
				 "   AND tmplt.field_id = ".$this->db->quote($is_tmplt_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " LEFT JOIN adv_md_values_date start_date".
				 "   ON cs.obj_id = start_date.obj_id ".
				 "   AND start_date.field_id = ".$this->db->quote($start_date_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " LEFT JOIN adv_md_values_text ltype".
				 "   ON cs.obj_id = ltype.obj_id ".
				 "   AND ltype.field_id = ".$this->db->quote($type_field_id, "integer").
				 " LEFT JOIN adv_md_values_int bk_deadl ".
				 "   ON cs.obj_id = bk_deadl.obj_id ".
				 "   AND bk_deadl.field_id = ".$this->db->quote($bk_deadl_field_id, "integer").
				 " WHERE cs.activation_type = 1".
				 "   AND cs.activation_start < ".time().
				 "   AND cs.activation_end > ".time().
				 "   AND oref.deleted IS NULL".
				 "   AND tmplt.value = ".$this->db->quote("Nein", "text").
				 "   AND start_date.value > ".$this->db->quote(date("Y-m-d"), "date").
				 "   AND NOT start_date.value IS NULL ".
				 // generali konzept "Trainingsbewerbung"
				 "   AND (".
				 "            (   ltype.value LIKE 'Pr_senztraining' ".
				 "            AND ADDDATE(start_date.value, -1 * bk_deadl.value) < ".
				 			     $this->db->quote(date("Y-m-d", time() + 14 * 24 * 60 * 60), "date").
				 "            )".
				 "       OR   (   ltype.value = ".$this->db->quote("Webinar", "text").
				 "            AND ADDDATE(start_date.value, -1 * bk_deadl.value) < ".	
				 				 $this->db->quote(date("Y-m-d", time() + 7 * 24 * 60 * 60), "date").
				 "            )".
				 "       )".
				 "   AND ADDDATE(start_date.value, -1 * bk_deadl.value) >= ".$this->db->quote(date("Y-m-d"), "text").
				 "";

		$res = $this->db->query($query);

		$ret = array();
		while($val = $this->db->fetchAssoc($res)) {
			$crs = new ilObjCourse($val["obj_id"], false);
			$crs_utils = gevCourseUtils::getInstanceByObj($crs);
			$crs_booking = ilCourseBookings::getInstance($crs);
			
			$crs_booking->getFreePlaces();
			
			if (!$crs_utils->canBookCourseForOther($ilUser->getId(), $this->user_id)) {
				continue;
			}
			
			if (gevObjectUtils::checkAccessOfUser($this->user_id, "visible",  "", $val["obj_id"], "crs")
			&& $crs_booking->getFreePlaces() > 4) {
				$ret[] = $val["obj_id"];
			}
		}

		return $ret;
	}
	
	public function getBookedAndWaitingCourseInformation() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		$crs_amd = 
			array( gevSettings::CRS_AMD_START_DATE			=> "start_date"
				 , gevSettings::CRS_AMD_END_DATE 			=> "end_date"
				 , gevSettings::CRS_AMD_CANCEL_DEADLINE		=> "cancel_date"
				 , gevSettings::CRS_AMD_SCHEDULED_FOR		=> "scheduled_for"
				 //, gevSettings::CRS_AMD_ => "title"
				 //, gevSettings::CRS_AMD_START_DATE => "status"
				 , gevSettings::CRS_AMD_TYPE 				=> "type"
				 , gevSettings::CRS_AMD_VENUE 				=> "location"
				 , gevSettings::CRS_AMD_CREDIT_POINTS 		=> "credit_points"
				 , gevSettings::CRS_AMD_FEE					=> "fee"
				 , gevSettings::CRS_AMD_TARGET_GROUP_DESC	=> "target_group"
				 , gevSettings::CRS_AMD_GOALS 				=> "goals"
				 , gevSettings::CRS_AMD_CONTENTS 			=> "content"
			);
		
		
		$booked = $this->getBookedCourses();
		$booked_amd = gevAMDUtils::getInstance()->getTable($booked, $crs_amd);
		foreach ($booked_amd as $key => $value) {
			$booked_amd[$key]["status"] = ilCourseBooking::STATUS_BOOKED;
			$booked_amd[$key]["cancel_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																			 , $value["cancel_date"]
																			 );
			// TODO: Push this to SQL-Statement.
			$orgu_utils = gevOrgUnitUtils::getInstance($value["location"]);
			$booked_amd[$key]["location"] = $orgu_utils->getLongTitle();
		}
		$waiting = $this->getWaitingCourses();
		$waiting_amd = gevAMDUtils::getInstance()->getTable($waiting, $crs_amd);
		foreach ($waiting_amd as $key => $value) {
			$waiting_amd[$key]["status"] = ilCourseBooking::STATUS_WAITING;
			$waiting_amd[$key]["cancel_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																			  , $value["cancel_date"]
																			  );
			
			$orgu_utils = gevOrgUnitUtils::getInstance($value["location"]);
			$waiting_amd[$key]["location"] = $orgu_utils->getLongTitle();
		}
		
		return array_merge($booked_amd, $waiting_amd);
	}
	
	public function getPotentiallyBookableCourseIds($a_search_options) {
		global $ilUser;
		$hash = md5(serialize($a_search_options));
		if ($this->potentiallyBookableCourses[$hash] !== null) {
			return $this->potentiallyBookableCourses[$hash];
		}
		
		$is_tmplt_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$start_date_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
		$type_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_TYPE);
		$bk_deadl_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_BOOKING_DEADLINE);
		
		
		// include search options 
		$additional_join = "";
		$additional_where = "";
		
		if (array_key_exists("title", $a_search_options)) {
			$additional_join .= " LEFT JOIN object_data od ON cs.obj_id = od.obj_id ";
			$additional_where .= " AND od.title LIKE ".$this->db->quote("%".$a_search_options["title"]."%", "text");
		}
		if (array_key_exists("custom_id", $a_search_options)) {
			$custom_id_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_CUSTOM_ID);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text custom_id".
				"   ON cs.obj_id = custom_id.obj_id ".
				"   AND custom_id.field_id = ".$this->db->quote($custom_id_field_id, "integer")
				;
			$additional_where .=
				" AND custom_id.value LIKE ".$this->db->quote("%".$a_search_options["custom_id"]."%", "text");
		}
		if (array_key_exists("type", $a_search_options)) {
			$additional_where .=
				" AND ltype.value LIKE ".$this->db->quote("%".$a_search_options["type"]."%", "text");
		}
		if (array_key_exists("categorie", $a_search_options)) {
			$categorie_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_TOPIC);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text categorie".
				"   ON cs.obj_id = categorie.obj_id ".
				"   AND categorie.field_id = ".$this->db->quote($categorie_field_id, "integer")
				;
			$additional_where .=
				" AND categorie.value LIKE ".$this->db->quote("%".$a_search_options["categorie"]."%", "text");
		}
		if (array_key_exists("target_group", $a_search_options)) {
			$target_group_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_TARGET_GROUP);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text target_group".
				"   ON cs.obj_id = target_group.obj_id ".
				"   AND target_group.field_id = ".$this->db->quote($target_group_field_id, "integer")
				;
			$additional_where .=
				" AND target_group.value LIKE ".$this->db->quote("%".$a_search_options["target_group"]."%", "text");
		}
		if (array_key_exists("location", $a_search_options)) {
			$location_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_VENUE);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text location".
				"   ON cs.obj_id = location.obj_id ".
				"   AND location.field_id = ".$this->db->quote($location_field_id, "integer")
				;
			$additional_where .=
				" AND location.value LIKE ".$this->db->quote("%".$a_search_options["location"]."%", "text");
		}
		if (array_key_exists("provider", $a_search_options)) {
			$provider_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_PROVIDER);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text provider".
				"   ON cs.obj_id = provider.obj_id ".
				"   AND provider.field_id = ".$this->db->quote($provider_field_id, "integer")
				;
			$additional_where .=
				" AND provider.value LIKE ".$this->db->quote("%".$a_search_options["provider"]."%", "text");
		}
		if (array_key_exists("period", $a_search_options)) {
			$end_date_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
			
			// this is knowledge from the course amd plugin!
			$additional_join .=
				" LEFT JOIN adv_md_values_date end_date".
				"   ON cs.obj_id = end_date.obj_id ".
				"   AND end_date.field_id = ".$this->db->quote($end_date_field_id, "integer")
				;
			$additional_where .=
				" AND ( ( NOT start_date.value >= ".$this->db->quote(date("Y-m-d", $a_search_options["period"]["end"]))." ) ".
				"       OR ".$this->db->in("ltype.value", array("Selbstlernkurs"), false, "text").") ".
				" AND ( ( NOT end_date.value <= ".$this->db->quote(date("Y-m-d", $a_search_options["period"]["end"]))." ) ".
				"       OR ".$this->db->in("ltype.value", array("Selbstlernkurs"), false, "text").") "
				;
		}
		
		// try to narrow down the set as much as possible to avoid permission checks
		$query = "SELECT DISTINCT cs.obj_id ".
				 " FROM crs_settings cs".
				 " LEFT JOIN object_reference oref".
				 "   ON cs.obj_id = oref.obj_id".
				 // this is knowledge from the course amd plugin!
				 " LEFT JOIN adv_md_values_text is_template".
				 "   ON cs.obj_id = is_template.obj_id ".
				 "   AND is_template.field_id = ".$this->db->quote($is_tmplt_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " LEFT JOIN adv_md_values_date start_date".
				 "   ON cs.obj_id = start_date.obj_id ".
				 "   AND start_date.field_id = ".$this->db->quote($start_date_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " LEFT JOIN adv_md_values_text ltype".
				 "   ON cs.obj_id = ltype.obj_id ".
				 "   AND ltype.field_id = ".$this->db->quote($type_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " LEFT JOIN adv_md_values_int bk_deadl ".
				 "   ON cs.obj_id = bk_deadl.obj_id ".
				 "   AND bk_deadl.field_id = ".$this->db->quote($bk_deadl_field_id, "integer").
				 $additional_join.
				 " WHERE cs.activation_type = 1".
				 "   AND cs.activation_start < ".time().
				 "   AND cs.activation_end > ".time().
				 "   AND oref.deleted IS NULL".
				 "   AND is_template.value = ".$this->db->quote("Nein", "text").
				 "   AND (   ( (ltype.value LIKE 'Pr_senztraining' OR ltype.value = 'Webinar')".
				 "            AND ADDDATE(start_date.value, -1 * bk_deadl.value) >= ".$this->db->quote(date("Y-m-d"), "text").
				 "		     )".
				 "		  OR (".$this->db->in("ltype.value", array("Selbstlernkurs"), false, "text").
				 "			 )".
				 "		 )".
				 $additional_where.
				 "";	
		
		$res = $this->db->query($query);
		
		$crss = array();
		while($val = $this->db->fetchAssoc($res)) {
			$crs_utils = gevCourseUtils::getInstance($val["obj_id"]);
			
			if (!$crs_utils->canBookCourseForOther($ilUser->getId(), $this->user_id)) {
				continue;
			}
			
			if (gevObjectUtils::checkAccessOfUser($this->user_id, "visible",  "", $val["obj_id"], "crs")) {
				$crss[] = $val["obj_id"];
			}
		}
		
		$this->potentiallyBookableCourses[$hash] = $crss;
		return $crss;
	}
	
	public function getPotentiallyBookableCourseInformation($a_search_options, $a_offset, $a_limit, $a_order = "title", $a_direction = "desc") {
		require_once("Modules/Course/classes/class.ilObjCourse.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookings.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookingPermissions.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

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
				 , gevSettings::CRS_AMD_VENUE 				=> "location"
				 , gevSettings::CRS_AMD_CREDIT_POINTS 		=> "points"
				 , gevSettings::CRS_AMD_FEE					=> "fee"
				 , gevSettings::CRS_AMD_TARGET_GROUP_DESC	=> "target_group"
				 , gevSettings::CRS_AMD_TARGET_GROUP		=> "target_group_list"
				 , gevSettings::CRS_AMD_GOALS 				=> "goals"
				 , gevSettings::CRS_AMD_CONTENTS 			=> "content"
			);
			
		$info = gevAMDUtils::getInstance()->getTable($crss, $crs_amd, array(), array(),
													 "ORDER BY ".$a_order." ".$a_direction." ".
													 " LIMIT ".$a_limit." OFFSET ".$a_offset);

		global $ilUser;

		foreach ($info as $key => $value) {
			// TODO: This surely could be tweaked to be faster if there was no need
			// to instantiate the course to get booking information about it.
			$crs_utils = gevCourseUtils::getInstance($value["obj_id"]);
/*			$crs = new ilObjCourse($info["obj_id"], false);
			$crs_booking = ilCourseBookings::getInstance($crs);
			$crs_booking_perms = ilCourseBookingPermissions::getInstance($crs);*/
			$orgu_utils = gevOrgUnitUtils::getInstance($value["location"]);
			
			if (!$crs_utils->canBookCourseForOther($ilUser->getId(), $this->user_id)) {
				unset($info[$key]);
				continue;
			}
			
			$list = "";
			foreach ($info[$key]["target_group_list"] as $val) {
				$list .= "<li>".$val."</li>";
			}
			$info[$key]["target_group"] = "<ul>".$list."</ul>".$info[$key]["target_group"];
			
			$info[$key]["location"] = $orgu_utils->getLongTitle();
			$info[$key]["booking_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																		, $value["booking_date"]
																		);
			$info[$key]["free_places"] = $crs_utils->getFreePlaces();
			$info[$key]["bookable"] = $info[$key]["free_places"] === null 
									|| $info[$key]["free_places"] > 0
									|| $crs_utils->isWaitingListActivated();
		}

		return $info;
	}
	
	public function hasUserSelectorOnSearchGUI() {
		return false; // TODO: Implement that properly.
	}
	
	public function getEmployeesForCourseSearch() {
		// TODO: Implement that properly
		return array();
		global $ilDB;
		$res = $ilDB->query("SELECT usr_id, firstname, lastname FROM usr_data");
		$ret = array();
		while($val = $ilDB->fetchAssoc($res)) {
			$ret[] = $val;
		}
		return $ret;
	}

	public function isProfileComplete() {
		require_once("Services/GEV/Desktop/classes/class.gevUserProfileGUI.php");
		$birthplace = $this->getBirthplace();
		$birthname = $this->getBirthname();
		$email = $this->getPrivateEmail();
		$mobile = $this->getPrivatePhone();
	
		return $birthplace && $birthname && $email && $mobile && preg_match(gevUserProfileGUI::$telno_regexp, $mobile);
	}
	
	
	public function getLogin() {
		return $this->getUser()->getLogin();
	}
	
	public function getGender() {
		return $this->getUser()->getGender();
	}
	
	public function getFirstname() {
		return $this->getUser()->getFirstname();
	}
	
	public function getLastname() {
		return $this->getUser()->getLastname();
	}
	
	public function getFullName() {
		return $this->getLastname().", ".$this->getFirstname();
	}
	
	public function getEMail() {
		return $this->getUser()->getEmail();
	}
	
	public function getOrgUnitId() {
		if ($this->orgu_id === null) {
			$query = "SELECT oref.obj_id FROM object_data od "
					." JOIN rbac_ua ua ON od.obj_id = ua.rol_id "
					." JOIN object_reference oref ON oref.ref_id = SUBSTR(od.title, 18) "
					." WHERE od.type = 'role' " 
					." AND ua.usr_id = ".$this->db->quote($this->user_id, "integer")
					." AND od.title LIKE 'il_orgu_employee_%' "
					." ORDER BY obj_id ASC LIMIT 1 OFFSET 0";
			
			$res = $this->db->query($query);
			if ($rec = $this->db->fetchAssoc($res)) {
				$this->orgu_id = $rec["obj_id"];
			}
			else {
				// Ok, so he is no employee. Maybe he's a superior?
				$query = "SELECT oref.obj_id FROM object_data od "
						." JOIN rbac_ua ua ON od.obj_id = ua.rol_id "
						." JOIN object_reference oref ON oref.ref_id = SUBSTR(od.title, 18) "
						." WHERE od.type = 'role' " 
						." AND ua.usr_id = ".$this->db->quote($this->user_id, "integer")
						." AND od.title LIKE 'il_orgu_superior_%' "
						." ORDER BY obj_id ASC LIMIT 1 OFFSET 0";
				$res = $this->db->query($query);
				if ($rec = $this->db->fetchAssoc($res)) {
					return $rec["obj_id"];
				}
				else {
					// Oh no, he's not assigned anywhere....
					$this->orgu_id = null;
				}
			}
		}
		return $this->orgu_id;
	}
	
	public function getOrgUnitTitle() {
		$orgu_id = $this->getOrgUnitId();
		if ($orgu_id === null) {
			return "";
		}
		else {
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			return gevOrgUnitUtils::getInstance($orgu_id)->getTitle();
		}
	}
	
	public function getBirthday() {
		require_once("Services/Calendar/classes/class.ilDate.php");
		$bd = $this->getUser()->getBirthday();
		if (!is_a($bd, "ilDate")) {
			$bd = new ilDate($bd, IL_CAL_DATE);
		}
		return $bd;
	}
	
	public function getFormattedBirthday() {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		$date = ilDatePresentation::formatDate($this->getBirthday());
		return $date;
	}
	
	public function getADPNumber() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_ADP_NUMBER);
	}
	
	public function setADPNumber($a_adp) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_ADP_NUMBER, $a_adp);
	}
	
	public function getJobNumber() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_JOB_NUMMER);
	}
	
	public function setJobNumber($a_number) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_JOB_NUMMER, $a_number);
	}
	
	public function getBirthplace() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_BIRTHPLACE);
	}
	
	public function setBirthplace($a_place) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_BIRTHPLACE, $a_place);
	}
	
	public function getBirthname() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_BIRTHNAME);
	}
	
	public function setBirthname($a_name) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_BIRTHNAME, $a_name);
	}
	
	public function getIHKNumber() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_IHK_NUMBER);
	}
	
	public function setIHKNumber($a_number) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_IHK_NUMBER, $a_number);
	}
	
	public function getADTitle() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_AD_TITLE);
	}
	
	public function setADTitle($a_title) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_AD_TITLE, $a_title);
	}
	
	public function getAgentKey() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_AGENT_KEY);
	}
	
	public function setAgentKey($a_key) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_AGENT_KEY, $a_key);
	}
	
	public function getCompanyTitle() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_COMPANY_TITLE);
	}
	
	public function setCompanyTitle($a_title) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_COMPANY_TITLE, $a_title);
	}
	
	public function getPrivateEmail() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_EMAIL);
	}
	
	public function setPrivateEmail($a_email) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_EMAIL, $a_email);
	}
	
	public function getPrivateStreet() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_STREET);
	}
	
	public function setPrivateStreet($a_street) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_STREET, $a_street);
	}
	
	public function getPrivateCity() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_CITY);
	}
	
	public function setPrivateCity($a_city) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_CITY, $a_city);
	}
	
	public function getPrivateZipcode() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_ZIPCODE);
	}
	
	public function setPrivateZipcode($a_zipcode) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_ZIPCODE, $a_zipcode);
	}
	
	public function getPrivateState() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_STATE);
	}
	
	public function setPrivateState($a_state) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_STATE, $a_state);
	}
	
	public function getPrivatePhone() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_PHONE);
	}
	
	public function setPrivatePhone($a_phone) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_PHONE, $a_phone);
	}
	
	public function getPrivateFax() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_FAX);
	}
	
	public function setPrivateFax($a_fax) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_FAX, $a_fax);
	}
	
	public function getEntryDate() {
		$val = $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_ENTRY_DATE);
		if (!trim($val)) {
			return null;
		}
		try {
			return new ilDate($val, IL_CAL_DATE);
		}
		catch (Exception $e) {
			return null;
		}
	}
	
	public function setEntryDate(ilDate $a_date) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_ENTRY_DATE, $a_date->get(IL_CAL_DATE));
	}
	
	public function getExitDate() {
		$val = $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_EXIT_DATE);
		if (!trim($val)) {
			return null;
		}
		try {
			return new ilDate($val, IL_CAL_DATE);
		}
		catch (Exception $e) {
			return null;
		}
	}
	
	public function setExitDate(ilDate $a_date) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_EXIT_DATE, $a_date->get(IL_CAL_DATE));
	}
	
	public function getStatus() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_STATUS);
	}
	
	public function setStatus($a_status) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_STATUS, $a_status);
	}
	
	public function getHPE() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_HPE);
	}
	
	public function setHPE($a_hpe) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_HPE, $a_hpe);
	}

	
	// role assignment
	
	public function assignGlobalRole($a_role_title) {
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		gevRoleUtils::getInstance()->assignUserToGlobalRole($this->user_id, $a_role_title);
	}
	
	public function deassignGlobalRole($a_role_title) {
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		gevRoleUtils::getInstance()->deassignUserFromGlobalRole($this->user_id, $a_role_title);
	}
	
	public function assignOrgRole($a_org_id, $a_role_title) {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$utils = gevOrgUnitUtils::getInstance($a_org_id);
		$utils->assignUser($this->user_id, $a_role_title);
	}
	
	public function deassignOrgRole($a_org_id, $a_role_title) {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$utils = gevOrgUnitUtils::getInstance($a_org_id);
		$utils->deassignUser($this->user_id, $a_role_title);
	}
	
	
	public function paysFees() {
		return !$this->hasRoleIn(gevSettings::$NO_PAYMENT_ROLES);
	}
	
	public function isAdmin() {
		// root
		if ($this->user_id == 6) {
			return true;
		}
		
		return $this->hasRoleIn(gevSettings::$ADMIN_ROLES);
	}
	
	protected function hasRoleIn($a_roles) {
		$roles = gevRoleUtils::getInstance()->getGlobalRolesOf($this->user_id);

		foreach($roles as $key => $value) {
			$roles[$key] = ilObject::_lookupTitle($value);
		}

		foreach ($a_roles as $role) {
			if (in_array($role, $roles)) {
				return true;
			}
		}
		
		return false;
	}
	
	// course specific stuff
	
	public function getFunctionAtCourse($a_crs_id) {
		return gevCourseUtils::getInstance($a_crs_id)->getFunctionOfUser($this->user_id);
	}
	
	public function getOvernightDetailsForCourse(ilObjCourse $a_crs) {
		require_once("Services/Accomodations/classes/class.ilAccomodations.php");
		return ilAccomodations::getInstance($a_crs)
							  ->getAccomodationsOfUser($this->user_id);
	}
	
	public function getFormattedOvernightDetailsForCourse(ilObjCourse $a_crs) {
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		$ovs = $this->getOvernightDetailsForCourse($a_crs);
		
		// will contain arrays with start and end of consecutive sequences
		// of overnights, where 0 => start and 1 => null|end
		$ovs_cons = array();
		// the consecutive sequence we are currently working on
		$ovs_cur = array();
		// the last checked overnight
		$ov_cur = null;
		
		// search for consecutive sequences of overnights
		foreach ($ovs as $ov) {
			if ($ov_cur === null) {
				$ovs_cur[0] = $ov;
				$ov_cur = $ov;
				continue;
			}
			
			$cur_p1 = new ilDate($ov_cur->get(IL_CAL_DATE), IL_CAL_DATE);
			$cur_p1->increment(ilDateTime::DAY, 1);
			if ($ov->get(IL_CAL_DATE) == $cur_p1->get(IL_CAL_DATE)) {
				$ovs_cur[1] = $ov;
			}
			else {
				$ovs_cons[] = $ovs_cur;
				$ovs_cur = array($ov);
				$ov_cur = $ov;
			}
		}
		
		$ovs_cons[] = $ovs_cur;
		
		// adjust the sequences. since convention in Accomodations package is
		// to give the starting day for an overnight, the enddates of the
		// consecutive sequences must be adopted accordingly.
		foreach ($ovs_cons as $key => $ovs) {
			if (count($ovs) == 0) {
				continue;
			}
			if (count($ovs) == 1) {
				$end = new ilDate($ovs[0]->get(IL_CAL_DATE), IL_CAL_DATE);
				$end->increment(ilDateTime::DAY, 1);
				$ovs[1] = $end;
			}
			else {
				$ovs[1]->increment(ilDateTime::DAY, 1);
			}
			$ov_cons[$key] = ilDatePresentation::formatPeriod($ovs[0], $ovs[1]);
		}
		
		return implode(", ", $ov_cons);
	}
	
	public function getOvernightAmountForCourse(ilObjCourse $a_crs) {
		return count($this->getOvernightDetailsForCourse($a_crs));
	}
	
	public function getUserWhoBookedAtCourse($a_crs_id) {
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		if (!array_key_exists($a_crs_id, $this->users_who_booked_at_course)) {
			$bk_info = ilCourseBooking::getUserData($a_crs_id, $this->user_id);
			$this->users_who_booked_at_course[$a_crs_id] 
				= new ilObjUser($bk_info["status_changed_by"]);
		}
		
		return $this->users_who_booked_at_course[$a_crs_id];
	}
	
	public function getFirstnameOfUserWhoBookedAtCourse($a_crs_id) {
		return $this->getUserWhoBookedAtCourse($a_crs_id)->getFirstname();
	}
	
	public function getLastnameOfUserWhoBookedAtCourse($a_crs_id) {
		return $this->getUserWhoBookedAtCourse($a_crs_id)->getLastname();
	}
	
	public function getBookingStatusAtCourse($a_course_id) {
		return gevCourseUtils::getInstance($a_course_id)->getBookingStatusOf($this->user_id);
	}
	
	public function getBookedCourses() {
		return $this->courseBookings->getBookedCourses();
	}
	
	public function getWaitingCourses() {
		return $this->courseBookings->getWaitingCourses();
	}
	
	public function getBookedAndWaitingCourses() {
		return array_merge($this->getBookedCourses(), $this->getWaitingCourses());
	}
	
	public function canBookCourseDerivedFromTemplate($a_tmplt_ref_id) {
		if ($a_tmplt_ref_id == 0) {
			return true;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");
		$field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_TEMPLATE_REF_ID);
		
		$sql =  "SELECT COUNT(*) cnt "
			   ."  FROM adv_md_values_int amd "
			   ."  JOIN crs_book cb ON cb.crs_id = amd.obj_id AND cb.user_id = ".$this->db->quote($this->user_id, "integer")
			   ."  LEFT JOIN crs_pstatus_usr ps ON ps.crs_id = amd.obj_id AND ps.user_id = ".$this->db->quote($this->user_id, "integer")
			   ." WHERE amd.field_id = ".$this->db->quote($field_id, "integer")
			   ."   AND amd.value = ".$this->db->quote($a_tmplt_ref_id, "integer")
			   ."   
			   		AND (    ".$this->db->in("cb.status"
			   								, array(ilCourseBooking::STATUS_BOOKED, ilCourseBooking::STATUS_WAITING)
			   								, false, "integer")
			   ."          AND ( ps.status = ".$this->db->quote(ilParticipationStatus::STATUS_NOT_SET, "integer")
			   ."               OR ps.status IS NULL"
			   ."              )"
			   ."       )"
			   ;
			
		$res = $this->db->query($sql);
		if ($rec = $this->db->fetchAssoc($res)) {
			return $rec["cnt"] == 0;
		}
	
		return true;
	}
	
	// For IV-Import Process
	
	public function iv_isActivated() {
		global $ilDB;
		$res = $this->db->query("SELECT * FROM gev_user_reg_tokens ".
								" WHERE username = ".$ilDB->quote($this->getLogin(), "text").
								"   AND password_changed IS NULL");

		if ($this->db->fetchAssoc($res)) {
			return false;
		}
		return true;
	}
	
	public function iv_setActivated() {
		$this->db->manipulate("UPDATE gev_user_reg_tokens ".
							  "   SET password_changed = NOW() ".
							  " WHERE username = ".$this->db->quote($this->getLogin(), "text")
							  );
	}
	
	// superiors/employees
	
	public function isSuperiorOf($a_user_id) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		// propably faster then checking the employees of this->user
		return in_array($this->user_id, $tree->getSuperiorsOfUser($a_user_id));
	}
	
	public function getDirectSuperiors() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		$sups = $tree->getSuperiorsOfUser($this->user_id, false);
		if (count($sups) > 0) {
			return $sups;
		}
		// ok, so there are no superiors in any org-unit where the user is employee
		// we need to find the superiors by ourselves
		$sups = array();
		$orgus = $tree->getOrgUnitOfUser($this->user_id);
		$parents = array();
		
		while (count ($sups) == 0) {
			foreach ($orgus as $ref) {
				$parents = $tree->getParent($ref);
			}
			if (count($parents) == 0) {
				return array();
			}
			$parents = array_unique($parents);
			foreach ($parents as $ref) {
				$sups = array_merge($sups, $this->getSuperiors($ref, false));
			}
			$orgus = $parents;
		}
		return $sups;
	}
	
	public function isEmployeeOf($a_user_id) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		// propably faster then checking the employees of this->user
		return in_array($a_user_id, $tree->getSuperiorsOfUser($this->user_id));
	}
	
	// billing info
	
	public function getLastBillingDataMaybe() {
		$res = $this->db->query( "SELECT bill_recipient_name, bill_recipient_street, bill_recipient_zip"
								."   , bill_recipient_hnr, bill_recipient_city, bill_recipient_email, bill_cost_center "
								."  FROM bill "
								." WHERE bill_usr_id = ".$this->db->quote($this->user_id, "integer")
								." ORDER BY bill_pk DESC LIMIT 1"
								);
		
		if ($rec = $this->db->fetchAssoc($res)) {
			$spl = explode(",", $rec["bill_recipient_name"]);
			return array( "recipient" => trim($spl[1])
						, "agency" => trim($spl[0])
						, "street" => $rec["bill_recipient_street"]
						, "housenumber" => $rec["bill_recipient_hnr"]
						, "zipcode" => $rec["bill_recipient_zip"]
						, "city" => $rec["bill_recipient_city"]
						, "costcenter" => $rec["bill_cost_center"]
						, "email" => $rec["bill_recipient_email"]
						);
		}
		else {
			return null;
		}
	}

	// wbd stuff
	
	const WBD_NO_SERVICE 		= "0 - kein Service";
	const WBD_EDU_PROVIDER		= "1 - Bildungsdienstleister";
	const WBD_TP_BASIS			= "2 - TP-Basis";
	const WBD_TP_SERVICE		= "3 - TP-Service";
	
	const WBD_OKZ_FROM_POSITION	= "0 - aus Stellung";
	const WBD_OKZ1				= "1 - OKZ1";
	const WBD_OKZ2				= "2 - OKZ2";
	const WBD_OKZ3				= "3 - OKZ3";
	const WBD_NO_OKZ			= "4 - keine Zuordnung";
	
	static $wbd_relevant_roles	= array( "OD/LD/BD/VD/VTWL"
									   , "DBV-VL-EVG"
									   , "DBV-UVG"
									   , "AVL"
									   , "HA"
									   , "BA"
									   , "NA"
									   , "VP"
									   );
	
	static $wbd_tp_service_roles= array( "OD/LD/BD/VD/VTWL"
									   , "DBV-VL-EVG"
									   , "DBV-UVG"
									   , "AVL"
									   , "HA"
									   , "BA"
									   , "NA"
									   );

	public function getWBDTPType() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_TP_TYPE);
	}
	
	public function setWBDTPType($a_type) {
		if (!in_array($a_type, array( self::WBD_NO_SERVICE, self::WBD_EDU_PROVIDER
									, self::WBD_TP_BASIS, self::WBD_TP_SERVICE))
			) {
			throw new Exception("gevUserUtils::setWBDTPType: ".$a_type." is no valid type.");
		}

		$this->udf_utils->setField($this->user_id, gevSettings::USR_TP_TYPE, $a_type);
	}
	
	public function getWBDBWVId() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_BWV_ID);
	}
	
	public function setWBDBWVId($a_id) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_BWV_ID, $a_id);
	}
	
	public function getRawWBDOKZ() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_WBD_OKZ);
	}
	
	public function setRawWBDOKZ($a_okz) {
		if (!in_array($a_okz, array( self::WBD_OKZ_FROM_POSITION, self::WBD_NO_OKZ
								   , self::WBD_OKZ1, self::WBD_OKZ2, self::WBD_OKZ3))
		   ) {
			throw new Exception("gevUserUtils::setRawWBDOKZ: ".$a_okz." is no valid okz.");
		}
		
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_WBD_OKZ, $a_okz);
	}
	
	public function getWBDOKZ() {
		$okz = $this->getRawWBDOKZ();
		
		if ($okz == WBD_NO_OKZ) {
			return null;
		}
		
		if (in_array($okz, array(self::WBD_OKZ1, self::WBD_OKZ2, self::WBD_OKZ3))) {
			$spl = explode("-", $okz);
			return trim($spl[1]);
		}
		
		
		// Everyone who has a wbd relevant role also has okz1
		if ($this->hasWBDRelevantRole()) {
			return "OKZ1";
		}
		
		return;
	}
	
	public function transferPointsToWBD() {
		return (   in_array($this->getWBDOKZ(), 
							array("OKZ1", "OKZ2", "OKZ3"))
				&& in_array($this->getWBDTPType(), 
							array(self::WBD_EDU_PROVIDER, self::WBD_TP_BASIS, self::WBD_TP_SERVICE))
				&& $this->getWBDBWVId()
				);
	}
	
	public function transferPointsFromWBD() {
		return (   in_array($this->getWBDOKZ(), 
							array("OKZ1", "OKZ2", "OKZ3"))
				&& $this->getWBDTPType() == self::WBD_TP_SERVICE
				&& $this->getWBDBWVId()
				);
	}
	
	public function getWBDFirstCertificationPeriodBegin() {
		$date = $this->udf_utils->getField($this->user_id, gevSettings::USR_WBD_CERT_PERIOD_BEGIN);
		return new ilDate($date, IL_CAL_DATE);
	}
	
	public function setWBDFirstCertificationPeriodBegin(ilDate $a_start) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_WBD_CERT_PERIOD_BEGIN, $a_start->get(IL_CAL_DATE));
	}
	
	public function getStartOfCurrentCertificationPeriod() {
		return $this->getStartOfCurrentCertificationX(5);
	}
	
	public function getStartOfCurrentCertificationYear() {
		return $this->getStartOfCurrentCertificationX(1);
	}
	
	protected function getStartOfCurrentCertificationX($a_year_step) {
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		
		$now = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		$start = $this->getWBDFirstCertificationPeriodBegin();
		
		while(ilDateTime::_before($start, $now)) {
			$start->increment(ilDateTime::YEAR, $a_year_step);
		}
		$start->increment(ilDateTime::YEAR, -1 * $a_year_step);
		
		return $start;
	}
	
	public function hasWBDRelevantRole() {
		$query = "SELECT COUNT(*) cnt "
				."  FROM rbac_ua ua "
				."  JOIN object_data od ON od.obj_id = ua.rol_id "
				." WHERE ua.usr_id = ".$this->db->quote($this->user_id, "integer")
				."   AND od.type = 'role' "
				."   AND ".$this->db->in("od.title", self::$wbd_relevant_roles, false, "text")
				;

		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			return $rec["cnt"] > 0;
		}
		return false;
	}
	
	public function hasDoneWBDRegistration() {
		return ($this->udf_utils->getField($this->user_id, gevSettings::USR_WBD_DID_REGISTRATION) == "1 - Ja");
	}
	
	public function setWBDRegistrationDone() {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_WBD_DID_REGISTRATION, "1 - Ja");
	}
	
	public function canBeRegisteredAsTPService() {
		$query = "SELECT COUNT(*) cnt "
				."  FROM rbac_ua ua "
				."  JOIN object_data od ON od.obj_id = ua.rol_id "
				." WHERE ua.usr_id = ".$this->db->quote($this->user_id, "integer")
				."   AND od.type = 'role' "
				."   AND ".$this->db->in("od.title", self::$wbd_tp_service_roles, false, "text")
				;

		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			return $rec["cnt"] > 0;
		}
		return false;
	}
	
	public function getWBDCommunicationEmail() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_WBD_COM_EMAIL);
	}
	
	public function setWBDCommunicationEmail($a_email) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_WBD_COM_EMAIL, $a_email);
	}
}

?>