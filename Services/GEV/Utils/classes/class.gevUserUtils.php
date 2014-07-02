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
		
		$this->potentiallyBookableCourses = null;
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
	
	public function getNextCourse() {
		return 0;	// TODO: implement that properly
	}
	
	public function getLastCourse() {
		return 0;	// TODO: implement that properly
	}
	
	public function getEduBioLink() {
		return "http://www.google.de"; //TODO: implement this properly
	}
	
	public function getCourseHighlights() {
		require_once("Modules/Course/classes/class.ilObjCourse.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookings.php");
		
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
				 "            (   ltype.value = ".$this->db->quote("Präsenztraining", "text").
				 "            AND ADDDATE(start_date.value, -1 * bk_deadl.value) < ".
				 			     $this->db->quote(date("Y-m-d", time() + 14 * 24 * 60 * 60), "date").
				 "            )".
				 "       OR   (   ltype.value = ".$this->db->quote("Webinar", "text").
				 "            AND ADDDATE(start_date.value, -1 * bk_deadl.value) < ".	
				 				 $this->db->quote(date("Y-m-d", time() + 7 * 24 * 60 * 60), "date").
				 "            )".
				 "       )".
				 "";

		$res = $this->db->query($query);

		$ret = array();
		while($val = $this->db->fetchAssoc($res)) {
			$crs = new ilObjCourse($val["obj_id"], false);
			$crs_booking = ilCourseBookings::getInstance($crs);
			
			$crs_booking->getFreePlaces();
			
			// TODO: there need to be a check weather the user has met the preconditions here
			// to.
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
		
		
		$booked = $this->courseBookings->getBookedCourses();
		$booked_amd = gevAMDUtils::getInstance()->getTable($booked, $crs_amd);
		foreach ($booked_amd as $key => $value) {
			$booked_amd[$key]["status"] = ilCourseBooking::STATUS_BOOKED;
			$booked_amd[$key]["cancel_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																			 , $value["cancel_date"]
																			 );
			
			$orgu_utils = gevOrgUnitUtils::getInstance($value["location"]);
			$booked_amd[$key]["location"] = $orgu_utils->getLongTitle();
		}
		$waiting = $this->courseBookings->getWaitingCourses();
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
	
	public function getPotentiallyBookableCourseIds() {
		if ($this->potentiallyBookableCourses !== null) {
			return $this->potentiallyBookableCourses;
		}
		
		$is_tmplt_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$start_date_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
		
		// try to narrow down the set as much as possible to avoid permission checks
		$query = "SELECT DISTINCT cs.obj_id ".
				 " FROM crs_settings cs".
				 " LEFT JOIN object_reference oref".
				 "   ON cs.obj_id = oref.obj_id".
				 // this is knowledge from the course amd plugin!
				 " LEFT JOIN adv_md_values_text amd1".
				 "   ON cs.obj_id = amd1.obj_id ".
				 "   AND amd1.field_id = ".$this->db->quote($is_tmplt_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " LEFT JOIN adv_md_values_date amd2".
				 "   ON cs.obj_id = amd2.obj_id ".
				 "   AND amd2.field_id = ".$this->db->quote($start_date_field_id, "integer").
				 " WHERE cs.activation_type = 1".
				 "   AND cs.activation_start < ".time().
				 "   AND cs.activation_end > ".time().
				 "   AND oref.deleted IS NULL".
				 "   AND amd1.value = ".$this->db->quote("Nein", "text").
				 "   AND ( amd2.value > ".$this->db->quote(date("Y-m-d"), "date").
				 "       OR amd2.value IS NULL ".
				 "       )".
				 "";
		
		$res = $this->db->query($query);
		
		$crss = array();
		while($val = $this->db->fetchAssoc($res)) {
			// TODO: there need to be a check whether the user has met the preconditions here
			// too.
			if (gevObjectUtils::checkAccessOfUser($this->user_id, "view",  "", $val["obj_id"], "crs")) {
				$crss[] = $val["obj_id"];
			}
		}
		
		$this->potentiallyBookableCourses = $crss;
		return $crss;
	}
	
	public function getPotentiallyBookableCourseInformation($a_offset, $a_limit, $a_order = "title", $a_direction = "desc") {
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
		
		$crss = $this->getPotentiallyBookableCourseIds();
		
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
			$crs = new ilObjCourse($info["obj_id"], false);
			$crs_booking = ilCourseBookings::getInstance($crs);
			$crs_booking_perms = ilCourseBookingPermissions::getInstance($crs);
			$orgu_utils = gevOrgUnitUtils::getInstance($value["location"]);
			
			
			$info[$key]["location"] = $orgu_utils->getLongTitle();
			$info[$key]["booking_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																		, $value["booking_date"]
																		);
			$info[$key]["bookable"] = $crs_booking_perms->bookCourseForUser($this->user_id);
			$info[$key]["free_places"] = $crs_booking->getFreePlaces();
		}

		return $info;
	}
	
	public function hasUserSelectorOnSearchGUI() {
		return true; // TODO: Implement that properly.
	}
	
	public function getEmployeesForCourseSearch() {
		// TODO: Implement that properly
		global $ilDB;
		$res = $ilDB->query("SELECT usr_id, firstname, lastname FROM usr_data");
		$ret = array();
		while($val = $ilDB->fetchAssoc($res)) {
			$ret[] = $val;
		}
		return $ret;
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
	
	public function getOrgUnit() {
		//TODO: implement
		return 56;
	}
	
	public function getOrgUnitTitle() {
		// TODO: implement
		return "CaT";
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
		ilDatePresentation::setUseRelativeDates(false);
		$date = ilDatePresentation::formatDate($this->getBirthday());
		ilDatePresentation::setUseRelativeDates(true);
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
		$val = $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_STATUS);
	}
	
	public function setStatus($a_status) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_STATUS, $a_status);
	}
	
	public function getHPE() {
		$val = $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_HPE);
	}
	
	public function setHPE($a_hpe) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_HPE, $a_hpe);
	}

	
	// role assignment
	
	public function assignGlobalRole($a_role_title) {
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		gevRoleUtils::getInstance()->assignUserToGlobalRole($this->user_id, $a_role_title);
	}
	
	public function assignOrgRole($a_org_id, $a_role_title) {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$utils = gevOrgUnitUtils::getInstance($a_org_id);
		$utils->assignUser($this->user_id, $a_role_title);
	}
	
	
	// course specific stuff
	
	public function getFunctionAtCourse($a_crs_id) {
		// TODO: implement
		return "TBD";
	}
	
	public function getOvernightDetailsForCourse($a_crs_id) {
		// TODO: implement
		return "TBD";
	}
	
	
	
	// For IV-Import Process
	
	public function iv_isActivated() {
		global $ilDB;
		$res = $ilDB->query("SELECT * FROM gev_user_reg_tokens ".
						    " WHERE email = ".$ilDB->quote($this->getLogin(), "text").
							"   AND token_used IS NULL");

		if ($ilDB->fetchAssoc($res)) {
			return false;
		}
		return true;
	}
	
	public function iv_setActivated() {
		global $ilDB;
		
		$ilDB->manipulate("UPDATE gev_user_reg_tokens ".
						  "   SET token_used = NOW() ".
						  " WHERE email = ".$ilDB->quote($this->getLogin(), "text")
						);
	}
}

?>