<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/Calendar/classes/class.ilDate.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Modules/Course/classes/class.ilObjCourse.php");

class gevCourseUtils {
	static $instances = array();
	
	protected function __construct($a_crs_id) {
		global $ilDB, $ilLog, $lng, $ilCtrl;
		
		$this->db = &$ilDB;
		$this->log = &$ilLog;
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		
		$this->lng->loadLanguageModule("crs");
		
		$this->crs_id = $a_crs_id;
		$this->crs_obj = null;
		$this->crs_booking_permissions = null;
		$this->crs_participations = null;
		$this->gev_settings = gevSettings::getInstance();
		$this->amd = gevAMDUtils::getInstance();
		$this->local_roles = null;
		
		$this->membership = null;
		$this->main_trainer = null;
		$this->main_admin = null;
	}
	
	static public function getInstance($a_crs_id) {
		if (!is_int($a_crs_id) && !is_numeric($a_crs_id)) {
			throw new Exception("gevCourseUtils::getInstance: no integer crs_id given: '".$a_crs_id."'");
		}
		
		if (array_key_exists($a_crs_id, self::$instances)) {
			return self::$instances[$a_crs_id];
		}

		self::$instances[$a_crs_id] = new gevCourseUtils($a_crs_id);
		return self::$instances[$a_crs_id];
	}
	
	static public function getInstanceByObj(ilObjCourse $a_crs) {
		$inst = gevCourseUtils::getInstance($a_crs->getId());
		$inst->crs_obj = $a_crs;
		$inst->crs_obj->setRefId(gevObjectUtils::getRefId($inst->crs_id));
		return $inst;
	}
	
	static public function getInstanceByObjOrId($a_course) {
		if (is_int($a_course) || is_numeric($a_course)) {
			return self::getInstance((int)$a_course);
		}
		else {
			return self::getInstanceByObj($a_course);
		}
	}

	static public  function getLinkTo($a_crs_id) {
		return "goto.php?target=crs_".gevObjectUtils::getRefId($a_crs_id)	;
	}
	
	static public function getCancelLinkTo($a_crs_id, $a_usr_id) {
		global $ilCtrl;
		$ilCtrl->setParameterByClass("gevMyCoursesGUI", "crs_id", $a_crs_id);
		$ilCtrl->setParameterByClass("gevMyCoursesGUI", "usr_id", $a_user_id);
		$link = $ilCtrl->getLinkTargetByClass("gevMyCoursesGUI", "cancelBooking");
		$ilCtrl->clearParametersByClass("gevMyCoursesGUI");
		return $link;
	}
	
	static public function getBookingLinkTo($a_crs_id, $a_usr_id) {
		global $ilCtrl;
		$ilCtrl->setParameterByClass("gevBookingGUI", "user_id", $a_usr_id);
		$ilCtrl->setParameterByClass("gevBookingGUI", "crs_id", $a_crs_id);
		$lnk = $ilCtrl->getLinkTargetByClass("gevBookingGUI", "book");
		$ilCtrl->clearParametersByClass("gevBookingGUI");
		return $lnk;
	}
	
	static public function gotoBooking($a_crs_id) {
		global $ilCtrl;
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmd=toBooking&crs_id=".$a_crs_id);
	}

	static public function mkDeadlineDate($a_start_date, $a_deadline) {
		if (!$a_start_date || $a_deadline === null) {
			return null;
		}
		
		$date = new ilDate($a_start_date->get(IL_CAL_DATE), IL_CAL_DATE);
		// ILIAS idiosyncracy. Why does it destroy the date, when i increment by 0?
		if ($a_deadline == 0) {
			return $date;
		}
		$date->increment(IL_CAL_DAY, $a_deadline * -1);
		return $date;
	}
	
	// CUSTOM ID LOGIC
	
	/**
	 * Every course template should have a custom id. This id is used to create
	 * an id for a concrete course. The new custom ids have the form $year-$tmplt-$num
	 * where $year is the current year, $tmplt is the custom id from the course template
	 * and $num is a consecutive number of the courses with the same $year-$tmpl part of
	 * the custom id.
	 **/
	static public function createNewCustomId($a_tmplt) {
		global $ilDB;
		$gev_settings = gevSettings::getInstance();
		
		$year = date("Y");
		$head = $year."-".$a_tmplt."-";
		
		$field_id = $gev_settings->getAMDFieldId(gevSettings::CRS_AMD_CUSTOM_ID);
		
		// This query requires knowledge from CourseAMD-Plugin!!
		$res = $ilDB->query("SELECT MAX(value) as m".
							" FROM adv_md_values_text".
							" WHERE value LIKE ".$ilDB->quote($head."%", "text").
							"   AND field_id = ".$ilDB->quote($field_id, "integer")
							);

		if ($val = $ilDB->fetchAssoc($res)) {
			$temp = explode("-", $val["m"]);
			$num = intval($temp[2]) + 1;
		}
		else {
			$num = 1;
		}
		$num = sprintf("%03d", $num);
		return $head.$num;
	}
	
	static public function extractCustomId($a_custom_id) {
		$temp = explode("-", $a_custom_id);
		return $temp[1];
	}
	
	/**
	 * Every course template has an unique id (e.g. SL10001) from a block of
	 * ids (e.g. SL10000). This function creates a fresh unique id from a 
	 * block of ids.
	 *
	 * WARNING: The assumption here is, that an block (= $a_tmplt) is always
	 * constructed from two alphanums, 2 digits that should be used to identify
	 * the block and 3 zeros that will be filled with subsequent numbers for
	 * the template.
	 **/
	static public function createNewTemplateCustomId($a_tmplt) {
		global $ilDB, $ilLog;
		$gev_settings = gevSettings::getInstance();
		$field_id = $gev_settings->getAMDFieldId(gevSettings::CRS_AMD_CUSTOM_ID);
		
		$pre = substr($a_tmplt, 0, 4);
		
		$res = $ilDB->query("SELECT MAX(value) as m "
						   ."  FROM adv_md_values_text "
						   ." WHERE value LIKE ".$ilDB->quote($pre."%", "text")
						   ."   AND field_id = ".$ilDB->quote($field_id, "integer")
						   );
		
		if ($val = $ilDB->fetchAssoc($res)) {
			$num = intval(substr($val["m"], 4)) + 1;
		}
		else {
			$num = 1;
		}
		$num = sprintf("%03d", $num);
		return $pre.$num;
	}
	

	/**
	 * Get custom roles assigned to a course.
	 */
	static public function getCustomRoles($crs_id) {
		global $rbacreview;
		
		$all_roles = $rbacreview->getParentRoleIds(gevObjectUtils::getRefId($crs_id));
		$custom_roles = array();
		
		foreach($all_roles as $role) {
			if ($role["role_type"] == "global"
			||  $role["role_type"] == "linked"
			|| substr($role["title"], 0, 6) == "il_crs") {
				continue;
			}
			
			$custom_roles[] = $role;
		}
		
		return $custom_roles;
	}
	
	
	public function getCourse() {
		require_once("Modules/Course/classes/class.ilObjCourse.php");
		
		if ($this->crs_obj === null) {
			$this->crs_obj = new ilObjCourse($this->crs_id, false);
			$this->crs_obj->setRefId(gevObjectUtils::getRefId($this->crs_id));
		}
		
		return $this->crs_obj;
	}
	
	public function getBookings() {
		require_once("Services/CourseBooking/classes/class.ilCourseBookings.php");
		return ilCourseBookings::getInstance($this->getCourse());
	}
	
	public function getBookingPermissions($a_user_id) {
		require_once("Services/CourseBooking/classes/class.ilCourseBookingPermissions.php");
		return ilCourseBookingPermissions::getInstance($this->getCourse(), $a_user_id);
	}
	
	public function getBookingHelper() {
		require_once("Services/CourseBooking/classes/class.ilCourseBookingHelper.php");
		return ilCourseBookingHelper::getInstance($this->getCourse());
	}
	public function getParticipations() {
		if ($this->crs_participations === null) {
			require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");
			$this->crs_participations = ilParticipationStatus::getInstance($this->getCourse());
		}
		
		return $this->crs_participations;
	}
	
	public function getLocalRoles() {
		if ($this->local_roles === null) {
			require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
			$this->local_roles = gevRoleUtils::getInstance()->getLocalRoleIdsAndTitles($this->crs_id);
			
			// rewrite names of member, tutor and admin roles
			foreach ($this->local_roles as $id => $title) {
				$pref = substr($title, 0, 8);
				if ($pref == "il_crs_m") {
					$this->local_roles[$id] = $this->lng->txt("crs_member");
				}
				else if ($pref == "il_crs_t") {
					$this->local_roles[$id] = $this->lng->txt("crs_tutor");
				}
				else if ($pref == "il_crs_a") {
					$this->local_roles[$id] = $this->lng->txt("crs_admin");
				}
			}
		}
		return $this->local_roles;
	}
	
	//
	
	
	public function getId() {
		return $this->crs_id;
	}
	
	public function getTitle() {
		return $this->getCourse()->getTitle();
	}
	
	public function getSubtitle() {
		return $this->getCourse()->getDescription();
	}
	
	public function getLink() {
		return self::getLinkTo($this->crs_id);
	}
	
	public function getCustomId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CUSTOM_ID);
	}
	
	public function setCustomId($a_id) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_CUSTOM_ID, $a_id);
	}
	
	public function getTemplateCustomId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CUSTOM_ID_TEMPLATE);
	}
	
	public function getTemplateTitle() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TEMPLATE_TITLE);
	}
	
	public function setTemplateTitle($a_title) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_TEMPLATE_TITLE, $a_title);
	}
	
	public function getTemplateRefId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TEMPLATE_REF_ID);
	}
	
	public function setTemplateRefId($a_ref_id) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_TEMPLATE_REF_ID, $a_ref_id);
	}
	
	public function isTemplate() {
		return "Ja" == $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_IS_TEMPLATE);
	}
	
	public function setIsTemplate($a_val) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_IS_TEMPLATE, ($a_val === true)? "Ja" : "Nein" );
	}
	
	public function getType() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TYPE);
	}
	
	public function isPraesenztraining() {
		return preg_match("/.*senztraining/", $this->getType());
	}
	
	public function isWebinar() {
		return $this->getType() == "Webinar";
	}
	
	public function getStartDate() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_START_DATE);
	}
	
	public function getFormattedStartDate() {
		$d = $this->getStartDate();
		if (!$d) {
			return null;
		}
		$val = ilDatePresentation::formatDate($d);
		return $val;
	}
	
	public function setStartDate($a_date) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_START_DATE, $a_date);
	}
	
	public function getEndDate() {
		$val = $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_END_DATE);
		if ($val) {
			return $val;
		}
		return $this->getStartDate(); //#537
	}
	
	public function getFormattedEndDate() {
		$d = $this->getEndDate();
		if (!$d) {
			return null;
		}
		$val = ilDatePresentation::formatDate($d);
		return $val;
	}
	
	public function setEndDate($a_date) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_END_DATE, $a_date);
	}
	
	public function getSchedule() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_SCHEDULE);
	}
	
	public function setSchedule($a_value) {
		return $this->amd->setField($this->crs_id, gevSettings::CRS_AMD_SCHEDULE, $a_value);
	}
	
	public function getFormattedSchedule($a_line_break = "<br />") {
		$schedule = $this->getSchedule();
		$counter = 1;
		foreach ($schedule as $key => $value) {
			$schedule[$key] = "Tag ".$counter.": ".$value;
			$counter += 1;
		}
		return implode($a_line_break, $schedule);
	}
	
	public function getFormattedStartTime() {
		$schedule = $this->getSchedule();
		if (count($schedule) == 0) {
			return "";
		}
		
		$spl = explode("-", $schedule[0]);
		return $spl[0];
	}
	
	public function getFormattedEndTime() {
		$schedule = $this->getSchedule();
		if (count($schedule) == 0) {
			return "";
		}
		
		$spl = explode("-", $schedule[count($schedule) - 1]);
		return $spl[1];
	}
	
	public function getFormattedAppointment() {
		$start = $this->getStartDate();
		$end = $this->getEndDate();
		if ($start && $end) {
			$val = ilDatePresentation::formatPeriod($start, $end);
			return $val;
		}
		return "";
	}
	
	public function getFormattedBookingDeadlineDate() {
		$dl = $this->getBookingDeadlineDate();
		if (!$dl) {
			return "";
		}
		$val = ilDatePresentation::formatDate($dl);
		return $val;
	}

	public function getFormattedCancelDeadlineDate() {
		$dl = $this->getCancelDeadlineDate();
		if (!$dl) {
			return "";
		}
		$val = ilDatePresentation::formatDate($dl);
		return $val;
	}


	public function getAmountHours() {
		$type = $this->getType();
		if ( $type === null
		  || in_array($type, array("POT-Termin", "Selbstlernkurs"))) {
			return null;
		}
		$schedule = $this->getSchedule();
		$hours = 0;
		foreach ($schedule as $day) {
			$spl = split("-", $day);
			$spl[0] = split(":", $spl[0]);
			$spl[1] = split(":", $spl[1]);
			$hours += $spl[1][0] - $spl[0][0] + ($spl[1][1] - $spl[0][1])/60.0;
		}
		return round($hours);
	}
	
	public function getTopics() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TOPIC);
	}
	
	public function getContents() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CONTENTS);
	}
	
	public function getGoals() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_GOALS);
	}
	
	public function getMethods() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_METHODS);
	}
	
	public function getMedia() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_MEDIA);
	}
	
	public function getEduProgramm() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_EDU_PROGRAMM);
	}
	
	public function getTargetGroup() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TARGET_GROUP);
	}
	
	public function getTargetGroupDesc() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TARGET_GROUP_DESC);
	}
	
	public function getIsExpertTraining() {
		return "Ja" == $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_EXPERT_TRAINING);
	}
	
	public function getCreditPoints() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CREDIT_POINTS);
	}
	
	public function getFee() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_FEE);
	}
	
	public function getFormattedFee() {
		$fee = $this->getFee();
		if ($fee) {
			return gevCourseUtils::formatFee($fee);
		}
	}
	
	static public function formatFee($a_fee) {
		require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");
		return gevBillingUtils::formatPrize($a_fee);
	}
	
	public function getMiceId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_MICE_ID);
	}
	
	public function getMinParticipants() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_MIN_PARTICIPANTS);
	}
	
	public function setMinParticipants($a_min) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_MIN_PARTICIPANTS, $a_min);
	}
	
	public function getMaxParticipants() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_MAX_PARTICIPANTS);
	}
	
	public function setMaxParticipants($a_min, $a_update_course = true) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_MAX_PARTICIPANTS, $a_min);
		
		if ($a_update_course) {
			$this->getCourse()->setSubscriptionMaxMembers($a_min);
			$this->getCourse()->update();
		}
	}

	public function getWaitingListActive() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_WAITING_LIST_ACTIVE) == "Ja";
	}

	public function setWaitingListActive($a_active, $a_update_course = true) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_WAITING_LIST_ACTIVE, $a_active ? "Ja" : "Nein");
		
		if ($a_update_course) {
			$this->getCourse()->enableSubscriptionMembershipLimitation(true);
			$this->getCourse()->enableWaitingList(true);
			$this->getCourse()->update();
		}
	}

	public function getCancelDeadline() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CANCEL_DEADLINE);
	}
	
	public function setCancelDeadline($a_dl) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_CANCEL_DEADLINE, $a_dl);
	}
	
	public function getCancelDeadlineDate() {
		return self::mkDeadlineDate($this->getStartDate(), $this->getCancelDeadline());
	}
	
	public function getFormattedCancelDeadline() {
		$dl = $this->getCancelDeadlineDate();
		if (!$dl) {
			return "";
		}
		$val = ilDatePresentation::formatDate($dl);
		return $val;
	}
	
	public function isCancelDeadlineExpired() {
		$dl = $this->getCancelDeadlineDate();
		
		if (!$dl) {
			return false;
		}
		
		$now = new ilDateTime(time(), IL_CAL_UNIX);
		return ilDateTime::_before($dl, $now);
	}
	
	public function getBookingDeadline() {
		$val = $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_BOOKING_DEADLINE);
		if (!$val) {
			$val = 0;
		}
		return $val;
	}
	
	public function setBookingDeadline($a_dl) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_BOOKING_DEADLINE, $a_dl);
	}
	
	public function getBookingDeadlineDate() {
		return self::mkDeadlineDate($this->getStartDate(), $this->getBookingDeadline());
	}
	
	public function isBookingDeadlineExpired() {
		$bdl = $this->getBookingDeadlineDate();
		if (!$bdl) {
			return false;
		}
		
		$now = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		return ($bdl->get(IL_CAL_DATE) < $now->get(IL_CAL_DATE));
	}
	
	public function getCancelWaitingList() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CANCEL_WAITING);
	}
	
	public function getCancelWaitingListDate() {
		return self::mkDeadlineDate($this->getStartDate(), $this->getCancelWaitingList());
	}
	
	public function getFreePlaces() {
		return $this->getBookings()->getFreePlaces();
	}
	
	public function getProviderId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_PROVIDER);
	}
	
	public function setProviderId($a_provider) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_PROVIDER, $a_provider);
	}
	
	public function getProvider() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$id = $this->getProviderId();
		if ($id === null) {
			return null;
		}
		return gevOrgUnitUtils::getInstance($id);
	}
	
	public function getProviderTitle() {
		$prv = $this->getProvider();
		if ($prv === null) {
			return "";
		}
		
		return $prv->getLongTitle();
	}
	
	// Venue Info
	
	public function getVenueId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VENUE);
	}
	
	public function setVenueId($a_venue) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_VENUE, $a_venue);
	}
	
	public function getVenue() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$id = $this->getVenueId();
		if ($id === null) {
			return null;
		}
		return gevOrgUnitUtils::getInstance($id);
	}
	
	public function getVenueTitle() {
		$ven = $this->getVenue();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getLongTitle();
	}
	
	public function getVenueStreet() {
		$ven = $this->getVenue();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getStreet();
	}
	
	public function getVenueHouseNumber() {
		$ven = $this->getVenue();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getHouseNumber();
	}
	
	public function getVenueZipcode() {
		$ven = $this->getVenue();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getZipcode();
	}
	
	public function getVenueCity() {
		$ven = $this->getVenue();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getCity();
	}
	
	public function getVenuePhone() {
		$ven = $this->getVenue();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getContactPhone();
	}
	
	public function getVenueEmail() {
		$ven = $this->getVenue();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getContactEmail();
	}
	
	// Accomodation Info
	
	public function getAccomodationId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_ACCOMODATION);
	}
	
	public function setAccomodationId($a_accom) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_ACCOMODATION, $a_accom);
	}
	
	public function getAccomodation() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$id = $this->getAccomodationId();
		if ($id === null) {
			return null;
		}
		return gevOrgUnitUtils::getInstance($id);	
	}
	
	public function getAccomodationTitle() {
		$ven = $this->getAccomodation();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getLongTitle();
	}
	
	public function getAccomodationStreet() {
		$ven = $this->getAccomodation();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getStreet();
	}
	
	public function getAccomodationHouseNumber() {
		$ven = $this->getAccomodation();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getHouseNumber();
	}
	
	public function getAccomodationZipcode() {
		$ven = $this->getAccomodation();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getZipcode();
	}
	
	public function getAccomodationCity() {
		$ven = $this->getAccomodation();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getCity();
	}
	
	public function getAccomodationPhone() {
		$ven = $this->getAccomodation();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getContactPhone();
	}
	
	public function getAccomodationEmail() {
		$ven = $this->getAccomodation();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getContactEmail();
	}
	
	public function isWithAccomodations() {
		return $this->getAccomodation() 
			&& ($this->getStartDate() !== null) 
			&& ($this->getEndDate() !== null);
	}
	
	public function getWebExLink() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_WEBEX_LINK);
	}
	
	public function setWebExLink($a_value) {
		return $this->amd->setField($this->crs_id, gevSettings::CRS_AMD_WEBEX_LINK, $a_value);
	}
	
	public function getWebExPassword() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_WEBEX_PASSWORD);
	}
	
	public function setWebExPassword($a_value) {
		return $this->amd->setField($this->crs_id, gevSettings::CRS_AMD_WEBEX_PASSWORD, $a_value);
	}
	
	/*public function getCSNLink() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CSN_LINK);
	}*/
	
	public function getFormattedPreconditions() {
		// TODO: implement this!
		return "NYI!";
	}
	
	public function getOrgaInfo() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_ORGA);
	}
	
	public function setOrgaInfo($a_orga) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_ORGA, $a_orga);
	}
	
	// options for course search
	
	public static function getTypeOptions() {
		global $lng;
		$all = $lng->txt("gev_crs_srch_all");
		$pt = "Präsenztraining";
		//$wb = "Webinar";
		$wb = "Virtuelles Training";
		$sk = "Selbstlernkurs";
/*		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		$arr = gevAMDUtils::getInstance()->getOptions(gevSettings::CRS_AMD_TYPE);
		return array_merge(array($all => $all), $arr);*/
		return array( $all => $all
					, $pt => $pt
					, $wb => $wb
					, $sk => $sk
					);
	}
	
	public static function getCategorieOptions() {
		global $lng;
		$all = $lng->txt("gev_crs_srch_all");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		$arr = gevAMDUtils::getInstance()->getOptions(gevSettings::CRS_AMD_TOPIC);
		return array_merge(array($all => $all), $arr);
	}
	
	public static function getTargetGroupOptions() {
		global $lng;
		$all = $lng->txt("gev_crs_srch_all");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		$arr = gevAMDUtils::getInstance()->getOptions(gevSettings::CRS_AMD_TARGET_GROUP);
		return array_merge(array($all => $all), $arr);
	}
	
	public static function getLocationOptions() {
		global $lng;
		$all = $lng->txt("gev_crs_srch_all");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$arr = gevOrgUnitUtils::getVenueNames();
		
		foreach($arr as $id => $name) {
			if (!in_array($name, array( "Generali Akademie GmbH, Bernried"
									  , "Generali Versicherung AG, München"
									  , "Online – An einem PC Ihrer Wahl, "
									  ))
				) {
				unset($arr[$id]);
			}
		}
		
		return array($all => $all) + $arr;
	}
	
	public static function getProviderOptions() {
		global $lng;
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$arr = gevOrgUnitUtils::getProviderNames();
		$all = $lng->txt("gev_crs_srch_all");
		return array($all => $all) + $arr;
	}
	

	public static function getEducationProgramOptions() {
		global $lng;
		$all = $lng->txt("gev_crs_srch_all");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		$arr = gevAMDUtils::getInstance()->getOptions(gevSettings::CRS_AMD_EDU_PROGRAMM);
		return array_merge(array($all => $all), $arr);
	}
	




	// derived courses for templates
	
	public function getDerivedCourseIds() {
		if (!$this->isTemplate()) {
			throw new Exception("gevCourseUtils::getDerivedCourseIds: this course is no template and thus has no derived courses.");
		}
		
		$ref_id_field = $this->amd->getFieldId(gevSettings::CRS_AMD_TEMPLATE_REF_ID);
		
		$ref_ids = gevObjectUtils::getAllRefIds($this->crs_id);
		
		$res = $this->db->query( "SELECT obj_id FROM adv_md_values_int"
								." WHERE field_id = ".$this->db->quote($ref_id_field, "integer")
								."  AND ".$this->db->in("value", $ref_ids, false, "integer")
								);
		$obj_ids = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			$obj_ids[] = $rec["obj_id"];
		}
		
		return $obj_ids;
	}
	
	public function updateDerivedCourses() {
		if (!$this->isTemplate()) {
			throw new Exception("gevCourseUtils::updateDerivedCourses: this course is no template and thus has no derived courses.");
		}

		$obj_ids = $this->getDerivedCourseIds();
		
		$tmplt_title_field = $this->amd->getFieldId(gevSettings::CRS_AMD_TEMPLATE_TITLE);
		
		$this->db->manipulate( "UPDATE adv_md_values_text "
							  ."   SET value = ".$this->db->quote($this->getTitle(), "text")
							  ." WHERE ".$this->db->in("obj_id", $obj_ids, false, "integer")
							  ."   AND field_id = ".$this->db->quote($tmplt_title_field, "integer")
							 );
	}
	
	// Participants, Trainers and other members
	
	public function getMembership() {
		return $this->getCourse()->getMembersObject();
	}
	
	public function isMember($a_user_id) {
		return $this->getMembership()->isAssigned($a_user_id);
	}
	
	public function getMembersExceptForAdmins() {
		$ms = $this->getMembership();
		return array_merge($ms->getMembers(), $ms->getTutors());
	}
	
	public function getParticipants() {
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$role = $this->getCourse()->getDefaultMemberRole();
		return gevRoleUtils::getInstance()->getRbacReview()->assignedUsers($role);
	}
	
	public function getTrainers() {
		return $this->getMembership()->getTutors();
	}
	
	public function hasTrainer($trainer_id) {
		return in_array($trainer_id, $this->getTrainers());
	}
	
	
	public function getAdmins() {
		return $this->getMembership()->getAdmins();
	}
	
	public function getMembers() {
		return array_merge($this->getMembership()->getMembers(), $this->getTrainers(), $this->getAdmins());
	}
	
	public function getSpecialMembers() {		
		return array_diff( $this->getMembers()
						 , $this->getParticipants()
						 , $this->getAdmins()
						 , $this->getTrainers()
						 );
	}
	
	public function getMainTrainer() {
		if ($this->main_trainer === null) {
			$tutors = $this->getTrainers();
			sort($tutors);
			if(count($tutors) != 0) {
				$this->main_trainer = new ilObjUser($tutors[0]);
			}
		}
		
		return $this->main_trainer;
	}
	
	public function getMainAdmin() {
		if ($this->main_admin === null) {
			$admins = $this->getAdmins();
			sort($admins);
			if (count($admins) != 0) {
				$this->main_admin = new ilObjUser($admins[0]);
			}
		}
		
		return $this->main_admin;
	}
	
	public function getCancelledMembers() {
		return $this->getBookings()->getCancelledUsers();
	}
	
	public function getCancelledWithCostsMembers() {
		return $this->getBookings()->getCancelledWithCostsUsers();
	}
	
	public function getCancelledWithoutCostsMembers() {
		return $this->getBookings()->getCancelledWithoutCostsUsers();
	}
	
	public function getWaitingMembers() {
		return $this->getBookings()->getWaitingUsers();
	}
	
	public function getSuccessfullParticipants() {
		return $this->getParticipations()->getSuccessfullUsers();
	}
	
	public function getAbsentParticipants() {
		return $this->getParticipations()->getAbsentNotExcusedUsers();
	}
	
	public function getExcusedParticipants() {
		return $this->getParticipations()->getAbsentExcusedUsers();
	}
	
	// Training Officer Info (Themenverantwortlicher)
	
	public function getTrainingOfficerName() {
		return $this->getCourse()->getContactName();
	}
	
	public function getTrainingOfficerEMail() {
		return $this->getCourse()->getContactEmail();
	}
	
	public function getTrainingOfficerPhone() {
		return $this->getCourse()->getContactPhone();
	}
	
	public function getTrainingOfficerContactInfo() {
		$name = $this->getTrainingOfficerName();
		$phone = $this->getTrainingOfficerPhone();
		$email = $this->getTrainingOfficerEmail();
		
		if ($phone && $email) {
			$contact = $phone.", ".$email;
		}
		else if ($phone) {
			$contact = $phone;
		}
		else if($email) {
			$contact = $email;
		}
		else {
			$contact = "";
		}
		
		if ($name && $contact) {
			return $name. " (".$contact.")";
		}
		if ($name) {
			return $name;
		}
		return $contact;
	}
	
	// Main Trainer Info
	
	public function getMainTrainerFirstname() {
		$tr = $this->getMainTrainer();
		if ($tr !== null) {
			return $tr->getFirstname();
		}
		return "";
	}
	
	public function getMainTrainerLastname() {
		$tr = $this->getMainTrainer();
		if ($tr !== null) {
			return $this->getMainTrainer()->getLastname();
		}
		return "";
	}
	
	public function getMainTrainerName() {
		$tr = $this->getMainTrainer();
		if ($tr !== null) {
			return $this->getMainTrainerFirstname()." ".$this->getMainTrainerLastname();
		}
		return "";
	}
	
	public function getMainTrainerPhone() {
		$tr = $this->getMainTrainer();
		if ($tr !== null) {
			return $this->getMainTrainer()->getPhoneOffice();
		}
		return "";
	}
	
	public function getMainTrainerEMail() {
		$tr = $this->getMainTrainer();
		if ($tr !== null) {
			return $this->getMainTrainer()->getEmail();
		}
		return "";
	}
	
	// Main Admin info
	
	public function getMainAdminFirstname() {
		$tr = $this->getMainAdmin();
		if ($tr !== null) {
			return $tr->getFirstname();
		}
		return "";
	}
	
	public function getMainAdminLastname() {
		$tr = $this->getMainAdmin();
		if ($tr !== null) {
			return $tr->getLastname();
		}
		return "";
	}
	
	public function getMainAdminName() {
		$tr = $this->getMainAdmin();
		if ($tr !== null) {
			return $this->getMainAdminFirstname()." ".$this->getMainAdminLastname();
		}
		return "";
	}
	
	public function getMainAdminPhone() {
		$tr = $this->getMainAdmin();
		if ($tr !== null) {
			return $tr->getPhoneOffice();
		}
		return "";
	}
	
	public function getMainAdminEMail() {
		$tr = $this->getMainAdmin();
		if ($tr !== null) {
			return $tr->getEmail();
		}
		return "";
	}
	
	public function getMainAdminContactInfo() {
		$email = $this->getMainAdminEMail();
		$phone = $this->getMainAdminPhone();
		if ($phone && $email) {
			return $phone.", ".$email;
		}
		if ($phone) {
			return $phone;
		}
		if ($email) {
			return $email;
		}
		return "";
	}
	
	
	public function getInvitationMailPreview() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$am = new gevCrsAutoMails($this->getId());
		return $am->getPreview("invitation");
	}
	
	// Memberlist creation
	
	const MEMBERLIST_TRAINER = 0;
	const MEMBERLIST_HOTEL = 1;
	const MEMBERLIST_PARTICIPANT = 2;
	
	public function deliverMemberList($a_type) {
		$this->buildMemberList(true, null, $a_type);
	}
	
	public function buildMemberList($a_send, $a_filename, $a_type) {
		if (!in_array($a_type, array(self::MEMBERLIST_TRAINER, self::MEMBERLIST_HOTEL, self::MEMBERLIST_PARTICIPANT))) {
			throw new Exception ("Unknown type for memberlist: ".$a_type);
		}

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		
		global $lng;
		
		$lng->loadLanguageModule("common");
		$lng->loadLanguageModule("gev");

		if ($a_filename === null) {
			if(!$a_send)
			{
				$a_filename = ilUtil::ilTempnam();
			}
			else
			{
				$a_filename = "list.xls";
			}
		}

		include_once "./Services/Excel/classes/class.ilExcelUtils.php";
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter($a_filename, $a_send);
		$workbook = $adapter->getWorkbook();
		$worksheet = $workbook->addWorksheet();
		$worksheet->setLandscape();

		// what is this good for
		//$txt = array();

		$columns = array( $lng->txt("gender")
						, $lng->txt("firstname")
						, $lng->txt("lastname")
						, $lng->txt("gev_org_unit_short")
						);

		$worksheet->setColumn(0, 0, 16);		// gender
		$worksheet->setColumn(1, 1, 20); 	// firstname
		$worksheet->setColumn(2, 2, 20);	// lastname
		$worksheet->setColumn(3, 3, 20);	// org-unit
		
		if($a_type == self::MEMBERLIST_HOTEL)
		{
			$columns[] = $lng->txt("gev_crs_book_overnight_details"); // #3764

			$worksheet->setColumn(4, 4, 50); // #4481
		}
		else if ($a_type == self::MEMBERLIST_PARTICIPANT) {
			$columns[] = $lng->txt("status");
			
			$worksheet->setColumn(4, 4, 20);
		}
		else
		{
			$columns[] = $lng->txt("status");
			$columns[] = $lng->txt("birthday");
			$columns[] = $lng->txt("gev_signature");
			
			$worksheet->setColumn(4, 4, 20);
			$worksheet->setColumn(5, 5, 25);
			$worksheet->setColumn(6, 6, 20);
		}

		$row = $this->buildListMeta( $workbook
							   , $worksheet
							   , $lng->txt("gev_excel_member_title")." ".
										( (!($a_type == self::MEMBERLIST_HOTEL))
										? $lng->txt("obj_crs") 
										: $lng->txt("gev_hotel")
										)
							   , $lng->txt("gev_excel_member_row_title")
							   , $columns
							   );

		$user_ids = $this->getCourse()->getMembersObject()->getMembers();
		$tutor_ids = $this->getCourse()->getMembersObject()->getTutors();

		$user_ids = array_merge($user_ids, $tutor_ids);

		if($user_ids)
		{
			$format_wrap = $workbook->addFormat();
			$format_wrap->setTextWrap();

			foreach($user_ids as $user_id)
			{
				$row++;
				//$txt[] = "";
				$user_utils = gevUserUtils::getInstance($user_id);


				//$txt[] = $lng->txt("name").": ".$user_data["name"];
				//$txt[] = $lng->txt("phone_office").": ".$user_data["fon"];
				//$txt[] = $lng->txt("vofue_org_unit_short").": ". $user_data["ounit"];

				$worksheet->write($row, 0, $user_utils->getGender(), $format_wrap);
				$worksheet->writeString($row, 1, $user_utils->getFirstname(), $format_wrap);
				$worksheet->write($row, 2, $user_utils->getLastname(), $format_wrap);
				$worksheet->write($row, 3, $user_utils->getOrgUnitTitle(), $format_wrap);
				
				if($a_type == self::MEMBERLIST_HOTEL)
				{
					// vfstep3.1
					$worksheet->write($row, 4, $user_utils->getFormattedOvernightDetailsForCourse($this->getCourse()), $format_wrap);

					//$txt[] = $lng->txt("vofue_crs_book_overnight_details").": ".$user_data["ov"];
				}
				else if ($a_type == self::MEMBERLIST_PARTICIPANT) {
					$worksheet->write($row, 4, $user_utils->getFunctionAtCourse($this->crs_id), $format_wrap);
				}
				else
				{
					$worksheet->write($row, 4, $user_utils->getFunctionAtCourse($this->crs_id), $format_wrap);
					$worksheet->write($row, 5, $user_utils->getFormattedBirthday(), $format_wrap);
					$worksheet->write($row, 6, "", $format_wrap);
					
					//$txt[] = $lng->txt("vofue_udf_join_date").": ".$user_data["jdate"];
					//$txt[] = $lng->txt("birthday").": ".$user_data["bdate"];
					//$txt[] = $lng->txt("vofue_crs_function").": ".$user_data["func"];
					//$txt[] = $lng->txt("vofue_udf_adp_number").": ". $user_data["adp"];
					//$txt[] = $lng->txt("vofue_crs_book_goals").": ".$user_data["goals"];
				}
			}
		}

		$workbook->close();

		if($a_send)
		{
			exit();
		}

		return array($filename, "Teilnehmer.xls");//, implode("\n", $txt));
	}
	
	protected function buildListMeta($workbook, $worksheet, $title, $row_title, array $column_titles)
	{
		global $lng;

		$num_cols = sizeof($column_titles);

		$format_bold = $workbook->addFormat(array("bold" => 1));
		$format_title = $workbook->addFormat(array("bold" => 1, "size" => 14));
		$format_subtitle = $workbook->addFormat(array("bold" => 1, "bottom" => 6));

		$worksheet->writeString(0, 0, $title, $format_title);
		$worksheet->mergeCells(0, 0, 0, $num_cols-1);
		$worksheet->mergeCells(1, 0, 1, $num_cols-1);

		$worksheet->writeString(2, 0, $lng->txt("gev_excel_course_title"), $format_subtitle);
		for($loop = 1; $loop < $num_cols; $loop++)
		{
			$worksheet->writeString(2, $loop, "", $format_subtitle);
		}
		$worksheet->mergeCells(2, 0, 2, $num_cols-1);
		$worksheet->mergeCells(3, 0, 3, $num_cols-1);

		// course info
		$row = 4;
		foreach($this->getListMetaData() as $caption => $value)
		{
			$worksheet->writeString($row, 0, $caption, $format_bold);

			if(!is_array($value))
			{
				$worksheet->writeString($row, 1, $value);
				$worksheet->mergeCells($row, 1, $row, $num_cols-1);
			}
			else
			{
				$first = array_shift($value);
				$worksheet->writeString($row, 1, $first);
				$worksheet->mergeCells($row, 1, $row, $num_cols-1);

				foreach($value as $line)
				{
					if(trim($line))
					{
						$row++;
						$worksheet->write($row, 0, "");
						$worksheet->writeString($row, 1, $line);
						$worksheet->mergeCells($row, 1, $row, $num_cols-1);
					}
				}
			}

			$row++;
		}

		// empty row
		$worksheet->mergeCells($row, 0, $row, $num_cols-1);
		$row++;
		$worksheet->mergeCells($row, 0, $row, $num_cols-1);
		$row++;

		// row_title
		$worksheet->writeString($row, 0, $row_title, $format_subtitle);
		for($loop = 1; $loop < $num_cols; $loop++)
		{
			$worksheet->writeString($row, $loop, "", $format_subtitle);
		}
		$worksheet->mergeCells($row, 0, $row, $num_cols-1);
		$row++;
		$worksheet->mergeCells($row, 0, $row, $num_cols-1);
		$row++;

		// title row
		for($loop = 0; $loop < $num_cols; $loop++)
		{
			$worksheet->writeString($row, $loop, $column_titles[$loop], $format_bold);
		}

		return $row;
	}
	
	protected function getListMetaData() {
		$start_date = $this->getStartDate();
		$end_date = $this->getEndDate();
		$arr = array("Titel" => $this->getTitle()
					, "Untertitel" => $this->getSubtitle()
					, "Nummer der Maßnahme" => $this->getCustomId()
					, "Datum" => ($start_date !== null && $end_date !== null)
								 ? ilDatePresentation::formatPeriod($this->getStartDate(), $this->getEndDate())
								 : ""
					, "Veranstaltungsort" => $this->getVenueTitle()
					, "Bildungspunkte" => $this->getCreditPoints()
					, "Trainer" => ($this->getMainTrainer() !== null)
								   ?$this->getMainTrainerLastname().", ".$this->getMainTrainerFirstname()
								   :""
					);
		if ($this->isPraesenztraining()) {
			$arr["Bei Rückfragen"] = "Ad-Schulung.de@generali.com";
		}
		return $arr;
	}
	
	
	// CSV for CSN
	
	protected function encodeForWindows($a_str) {
		return mb_convert_encoding($a_str, "ISO-8859-1", "UTF-8");
	}
	
	public function deliverCSVForCSN() {
		header("Pragma: ");
		header("Cache-Control: ");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Cache-Control: private");
		header("Content-Type: application/csv; charset=UTF-8");
		header("Content-Disposition:attachment; filename=\"csn_".$this->crs_id.".csv\"");
		
		echo $this->encodeForWindows('"Kurzname";"Telefon1 (geschäftlich)"'."\n");
		
		$users = array_merge($this->getTrainers(), $this->getParticipants());
		foreach ($users as $uid) {
			$user = new ilObjUser($uid);
			echo $this->encodeForWindows('"'.$user->getFullname().'";"'.$user->getPhoneOffice().'"'."\n");
		}
		
		exit();
	}
	
	
	// Desk Display creation
	
	public function canBuildDeskDisplays() {
		return count($this->getMembersExceptForAdmins()) > 0;
	}
	
	public function buildDeskDisplays($a_path = null) {
		require_once("Services/DeskDisplays/classes/class.ilDeskDisplay.php");
		$dd = new ilDeskDisplay($this->db, $this->log);
		
		// Generali-Konzept, Kapitel "Tischaufsteller"
		$dd->setLine1Font("Arial", 48, false, false);
		$dd->setLine1Color(120, 120, 150);
		$dd->setLine2Font("Arial", 86, false, false);
		$dd->setLine2Color(0, 0, 0);
		$dd->setSpaceLeft(2);
		$dd->setSpaceBottom1(12.0);
		$dd->setSpaceBottom2(8.5);
		
		$dd->setUsers($this->getMembersExcepxfForAdmins());
		if ($a_path === null) {
			$dd->deliver();
		}
		else {
			$dd->build($a_path);
		}
	}

	// Booking
	
	public function bookUser($a_user_id) {
		return $this->getBookings()->join($a_user_id);
	}
	
	public function getBookingStatusOf($a_user_id) {
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		return ilCourseBooking::getUserStatus($this->crs_id, $a_user_id);
	}
	
	public function getBookingStatusLabelOf($a_user_id) {
		$status = $this->getBookingStatusOf($a_user_id);
		switch ($status) {
			case ilCourseBooking::STATUS_BOOKED:
				return "gebucht";
			case ilCourseBooking::STATUS_WAITING:
				return "auf Warteliste";
			case ilCourseBooking::STATUS_CANCELLED_WITH_COSTS:
				return "kostenpflichtig storniert";
			case ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS:
				return "kostenfrei storniert";
			default:
				return "";
		}
	}
	
	public function isWaitingListActivated() {
		return $this->getBookings()->isWaitingListActivated();
	}
	
	public function canBookCourseForOther($a_user_id, $a_other_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$utils = gevUserUtils::getInstance($a_user_id);
		return    $this->getBookingPermissions($a_user_id)->bookCourseForUser($a_other_id)
			   || in_array($a_other_id, $utils->getEmployeeIdsForCourseSearch())
			   ;
	}
	
	public function canViewBookings($a_user_id) {
		return $this->getBookingPermissions($a_user_id)->viewOtherBookings();
	}
	
	public function canCancelCourseForOther($a_user_id, $a_other_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$utils = gevUserUtils::getInstance($a_user_id);
		return    $this->getBookingPermissions($a_user_id)->cancelCourseForUser($a_other_id)
			   || in_array($a_other_id, $utils->getEmployeeIdsForBookingCancellations())
			   ;
	}
	
	public function isBookableFor($a_user) {
		return $this->getBookingHelper($a_user_id)->isBookable($a_user);
	}
	
	public function cancelBookingOf($a_user_id) {
		return $this->getBookings()->cancel($a_user_id);
	}
	
	public function fillFreePlacesFromWaitingList() {
		return $this->getBookings()->fillFreePlaces();
	}
	
	public function cleanWaitingList() {
		$ws = $this->getBookings()->cleanWaitingList();
	}
	
	// Participation
	
	public function getParticipationStatusOf($a_user_id) {
		$sp = $this->getParticipations()->getStatusAndPoints($a_user_id);
		$status = $sp["status"];
		
		if ($status === null && $this->getBookingStatusOf($a_user_id) == ilCourseBooking::STATUS_BOOKED) {
			return ilParticipationStatus::STATUS_NOT_SET;
		}
		return $status;
	}
	
	public function getParticipationStatusLabelOf($a_user_id) {
		$status = $this->getParticipationStatusOf($a_user_id);
		switch ($status) {
			case ilParticipationStatus::STATUS_NOT_SET:
				return "nicht gesetzt";
			case ilParticipationStatus::STATUS_SUCCESSFUL:
				return "teilgenommen";
			case ilParticipationStatus::STATUS_ABSENT_EXCUSED:
				return "fehlt entschuldigt";
			case ilParticipationStatus::STATUS_ABSENT_NOT_EXCUSED:
				return "fehlt ohne Absage";
			default:
				return "";
		}
	}
	
	public function allParticipationStatusSet() {
		return $this->getParticipations()->allStatusSet(); 
	}
	
	public function getFunctionOfUser($a_user_id) {
		//this is a check for ROLES, not for function.
		//i.e. member has canceled, but is still member of course...
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$utils = gevRoleUtils::getInstance();
		$roles = $this->getLocalRoles();
		$res = $this->db->query( "SELECT rol_id FROM rbac_ua "
								." WHERE usr_id = ".$this->db->quote($a_user_id)
								."   AND ".$this->db->in("rol_id", array_keys($roles), false, "integer"));
		if ($rec = $this->db->fetchAssoc($res)) {
			return $roles[$rec["rol_id"]];
		}
		return null;
	}
	
	public function getCreditPointsOf($a_user_id) {
		$sp = $this->getParticipations()->getStatusAndPoints($a_user_id);
		if ($sp["status"] == ilParticipationStatus::STATUS_NOT_SET) {
			return $this->getCreditPoints();
		}
		if ($sp["status"] == ilParticipationStatus::STATUS_SUCCESSFUL) {
			if ($sp["points"] !== null) {
				return $sp["points"];
			}
			return $this->getCreditPoints();
		}
		return 0;
	}


	public function getWBDTopic(){
		//CRS_AMD_GDV_TOPIC
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_GDV_TOPIC);
	}

	// Common gui-elements for a course
	
	public function renderCancellationForm($a_gui, $a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");
		
		global $ilUser;
		
		$user_utils = gevUserUtils::getInstance($a_user_id);
		$bill_utils = gevBillingUtils::getInstance();
		$bill = $bill_utils->getNonFinalizedBillForCourseAndUser($this->crs_id, $a_user_id);
		$status = $this->getBookingStatusOf($a_user_id);
		
		if ( $user_utils->paysFees() 
		   && $this->getFee() 
		   && $status != ilCourseBooking::STATUS_WAITING 
		   && $this->isCancelDeadlineExpired()) {
			$action = $this->lng->txt("gev_costly_cancellation_action");
		}
		else {
			$action = $this->lng->txt("gev_free_cancellation_action");
		}
		
		$title = new catTitleGUI("gev_cancellation_title", "gev_cancellation_subtitle", "GEV_img/ico-head-trash.png");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->getTitle());
		$this->ctrl->setParameter($a_gui, "crs_id", $this->crs_id);
		$form->setFormAction($this->ctrl->getFormAction($a_gui));
		$this->ctrl->clearParameters($a_gui, "crs_id", $this->crs_id);
		$form->addCommandButton("view", $this->lng->txt("cancel"));
		$form->addCommandButton("finalizeCancellation", $action);
		
		$officer_contact = $this->getTrainingOfficerContactInfo();

		$vals = array(
			  array( $this->lng->txt("gev_course_id")
				   , true
				   , $this->getCustomId()
				   )
			, array( $this->lng->txt("gev_course_type")
				   , true
				   , implode(", ", $this->getType())
				   )
			, array( $this->lng->txt("appointment")
				   , true
				   , $this->getFormattedAppointment()
				   )
			, array( $this->lng->txt("gev_provider")
				   , $prv?true:false
				   , $prv?$prv->getTitle():""
				   )
			, array( $this->lng->txt("gev_venue")
				   , $ven?true:false
				   , $ven?$ven->getTitle():""
				   )
			, array( $this->lng->txt("gev_instructor")
				   , true
				   , $this->getMainTrainerName()
				   )
			, array( $this->lng->txt("gev_free_cancellation_until")
				   , $status == ilCourseBooking::STATUS_BOOKED
				   , $this->getFormattedCancelDeadline()
				   )
			, array( $this->lng->txt("gev_free_places")
				   , true
				   , $this->getFreePlaces()
				   )
			, array( $this->lng->txt("gev_training_contact")
				   , $officer_contact
				   , $officer_contact
				   )
			, array( $this->lng->txt("gev_overall_prize")
				   , ($bill !== null)
				   , $bill_utils->formatPrize(
				   			$bill !== null?$bill->getAmount():0
				   		)." &euro;"
				   	)
			, array( $this->lng->txt("gev_credit_points")
				   , true
				   , $this->getCreditPoints()
				   )
			);
		
		foreach ($vals as $val) {
			if (!$val[1] or !$val[2]) {
				continue;
			}
		
			$field = new ilNonEditableValueGUI($val[0], "", true);
			$field->setValue($val[2]);
			$form->addItem($field);
		}

		if ($ilUser->getId() !== $a_user_id) {
			require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
			require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
			require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
			
			$spacer = new catHSpacerGUI();
			
			$form2 = new catPropertyFormGUI();
			$form2->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
			$field = new ilNonEditableValueGUI($this->lng->txt("gev_cancellation_for"), "", true);
			$field->setValue($user_utils->getFullName());
			$form2->addItem($field);
			
			$employee = $spacer->render()
					  . $form2->getContent()
					  . $spacer->render()
					  ;

		}
		else {
			$employee = "";
		}

		return $title->render() . $employee . $form->getHTML();
	}

	// Over historizing course tables

	static $hist_edu_programs = null;

	static function getEduProgramsFromHisto() {
		if (self::$hist_edu_programs !== null) {
			return self::$hist_edu_programs;
		}

		global $ilDB;

		$res = $ilDB->query("SELECT DISTINCT edu_program FROM hist_course WHERE edu_program != '-empty-'");
		self::$hist_edu_programs = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			self::$hist_edu_programs[] = $rec["edu_program"];
		}
		return self::$hist_edu_programs;
	}

	static $hist_course_types = null;

	static function getLearningTypesFromHisto() {
		if (self::$hist_course_types !== null) {
			return self::$hist_course_types;
		}

		global $ilDB;
		
		$res = $ilDB->query("SELECT DISTINCT type FROM hist_course WHERE type != '-empty-'");
		self::$hist_course_types = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			self::$hist_course_types[] = $rec["type"];
		}
		return self::$hist_course_types;
	}
	
	
	static $hist_course_template_title = null;

	static function getTemplateTitleFromHisto() {
		if (self::$hist_course_template_title !== null) {
			return self::$hist_course_template_title;
		}

		global $ilDB;

		$res = $ilDB->query("SELECT DISTINCT template_title FROM hist_course WHERE template_title != '-empty-'");
		self::$hist_course_template_title = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			self::$hist_course_template_title[] = $rec["template_title"];
		}
		return self::$hist_course_template_title;
	}

	static $hist_participation_status = null;

	static function getParticipationStatusFromHisto() {
		if (self::$hist_participation_status !== null) {
			return self::$hist_participation_status;
		}

		global $ilDB;

		$res = $ilDB->query("SELECT DISTINCT participation_status FROM hist_usercoursestatus WHERE participation_status != '-empty-'");
		self::$hist_participation_status = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			self::$hist_participation_status[] = $rec["participation_status"];
		}
		return self::$hist_participation_status;
	}





	public function searchCourses($a_search_options, $a_offset, 
								$a_limit, $a_order = "title", 
								$a_direction = "desc") {
		
		global $ilDB;
		global $ilUser;
		//global $ilCtrl;
		
		$gev_set = gevSettings::getInstance();
		$db = &$ilDB;

		if ($a_order == "") {
			$a_order = "title";
		}

		if ($a_direction !== "asc" && $a_direction !== "desc") {
			throw new Exception("gevCourseUtils::searchCourses: unknown direction '".$a_direction."'");
		}
		
		/*if (!in_array($a_order, array("title", "start_date", "end_date", "booking_date", "location"
									 , "points", "fee", "target_group", "goals", "content", "type"))) 
		{
			throw new Exception("gevUserUtils::getPotentiallyBookableCourseInformation: unknown order '".$a_order."'");
		}
		*/

		/*
		$hash = md5(serialize($a_search_options));
		if ($this->potentiallyBookableCourses[$hash] !== null) {
			return $this->potentiallyBookableCourses[$hash];
		}
		*/
		
		$is_tmplt_field_id = $gev_set->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$start_date_field_id = $gev_set->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
		$type_field_id = $gev_set->getAMDFieldId(gevSettings::CRS_AMD_TYPE);
		
		// include search options 
		$additional_join = "";
		$additional_where = "";
		

		if (array_key_exists("title", $a_search_options)) {
			$additional_join .= " LEFT JOIN object_data od ON cs.obj_id = od.obj_id ";
			$additional_where .= " AND od.title LIKE ".$db->quote("%".$a_search_options["title"]."%", "text");
		}

		if (array_key_exists("custom_id", $a_search_options)) {
			$custom_id_field_id = $gev_set->getAMDFieldId(gevSettings::CRS_AMD_CUSTOM_ID);
			
			// this is knowledge from the course amd plugin!

			$additional_join .= 
				" LEFT JOIN adv_md_values_text custom_id".
				"   ON cs.obj_id = custom_id.obj_id ".
				"   AND custom_id.field_id = ".$db->quote($custom_id_field_id, "integer")
				;
			$additional_where .=
				" AND custom_id.value LIKE ".$db->quote("%".$a_search_options["custom_id"]."%", "text");
		}

		if (array_key_exists("type", $a_search_options)) {
			$additional_where .=
				" AND ltype.value LIKE ".$db->quote("%".$a_search_options["type"]."%", "text");
		}


		if (array_key_exists("program", $a_search_options)) {
			$custom_id_field_id = $gev_set->getAMDFieldId(gevSettings::CRS_AMD_EDU_PROGRAMM);
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text edu_program".
				"   ON cs.obj_id = edu_program.obj_id ".
				"   AND edu_program.field_id = ".$db->quote($custom_id_field_id, "integer")
				;
			$additional_where .=
				" AND edu_program.value LIKE ".$db->quote("%".$a_search_options["program"]."%", "text");
		}


		if (array_key_exists("location", $a_search_options)) {
			$location_field_id = $gev_set->getAMDFieldId(gevSettings::CRS_AMD_VENUE);
			
			// this is knowledge from the course amd plugin!
			$additional_join .= 
				" LEFT JOIN adv_md_values_text location".
				"   ON cs.obj_id = location.obj_id ".
				"   AND location.field_id = ".$db->quote($location_field_id, "integer")
				;
			$additional_where .=
				" AND location.value LIKE ".$db->quote("%".$a_search_options["location"]."%", "text");
		}

	
		if (array_key_exists("period", $a_search_options)) {
			$end_date_field_id = $gev_set->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
			
			// this is knowledge from the course amd plugin!
			$additional_join .=
				" LEFT JOIN adv_md_values_date end_date".
				"   ON cs.obj_id = end_date.obj_id ".
				"   AND end_date.field_id = ".$db->quote($end_date_field_id, "integer")
				;
			$additional_where .=
				" AND ((NOT start_date.value > ".$db->quote(date("Y-m-d", $a_search_options["period"]["end"]))." ) ".
				" AND (NOT end_date.value < ".$db->quote(date("Y-m-d", $a_search_options["period"]["start"]))." ) ".
				" OR (end_date.value IS NULL ".
				" AND NOT start_date.value < ".$db->quote(date("Y-m-d", $a_search_options["period"]["start"])).")".
				" OR (end_date.value IS NULL AND start_date.value IS NULL))"
				;

		}

		if (array_key_exists("online_status", $a_search_options)) {
			$online_status = $a_search_options['online_status'];
			if($online_status == 2){ //offline
				$online_status = 0;
			}
			$additional_where .=" AND cs.activation_type = " .$db->quote($online_status, 'integer');
			//print $additional_where;
		}

		
		// try to narrow down the set as much as possible to avoid permission checks
		$query = "SELECT DISTINCT cs.obj_id ".
				 " FROM crs_settings cs".
				 " LEFT JOIN object_reference oref".
				 "   ON cs.obj_id = oref.obj_id".
				 // this is knowledge from the course amd plugin!
				 " LEFT JOIN adv_md_values_text is_template".
				 "   ON cs.obj_id = is_template.obj_id ".
				 "   AND is_template.field_id = ".$db->quote($is_tmplt_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " LEFT JOIN adv_md_values_date start_date".
				 "   ON cs.obj_id = start_date.obj_id ".
				 "   AND start_date.field_id = ".$db->quote($start_date_field_id, "integer").
				 // this is knowledge from the course amd plugin
				 " LEFT JOIN adv_md_values_text ltype".
				 "   ON cs.obj_id = ltype.obj_id ".
				 "   AND ltype.field_id = ".$db->quote($type_field_id, "integer").

				

				 $additional_join.
				 " WHERE ".

/*				 "	 cs.activation_type = 1".
				 "   AND cs.activation_start < ".time().
				 "   AND cs.activation_end > ".time().
				 "   AND oref.deleted IS NULL".
*/
				 "   oref.deleted IS NULL".
				 "   AND is_template.value = ".$db->quote("Nein", "text").

				 $additional_where;




		$res = $db->query($query);
		$crss = array();
		while($val = $db->fetchAssoc($res)) {
			$crss[] = $val["obj_id"];
		}
	
		$crs_amd = 
			array( gevSettings::CRS_AMD_CUSTOM_ID			=> "custom_id"
				 , gevSettings::CRS_AMD_TYPE 				=> "type"
				 , gevSettings::CRS_AMD_VENUE 				=> "location"
				 , gevSettings::CRS_AMD_START_DATE			=> "start_date"
				 , gevSettings::CRS_AMD_END_DATE 			=> "end_date"
				 //trainer
				 , gevSettings::CRS_AMD_CREDIT_POINTS 		=> "points"
				 , gevSettings::CRS_AMD_FEE					=> "fee"
				 //status (online/offline)
				 , gevSettings::CRS_AMD_MIN_PARTICIPANTS	=> "min_participants"
				 , gevSettings::CRS_AMD_MAX_PARTICIPANTS	=> "max_participants"

				 //memberlist (link)
			
			);

		$addsql = "ORDER BY ".$a_order." ".$a_direction; //." LIMIT ".$a_limit." OFFSET ".$a_offset;

		$info = gevAMDUtils::getInstance()->getTable(
				$crss, 
				$crs_amd, 
				array(), 
				array(),
				$addsql			
			);


		foreach ($info as $key => $value) {
			// TODO: This surely could be tweaked to be faster if there was no need
			// to instantiate the course to get booking information about it.
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			$crs_utils = gevCourseUtils::getInstance($value["obj_id"]);
			$orgu_utils = gevOrgUnitUtils::getInstance($value["location"]);
			$crs_ref = gevObjectUtils::getRefId($crs_utils->getCourse()->getId());
			
			$edit_lnk = "ilias.php?cmdClass=ilobjcoursegui&cmd=editInfo&baseClass=ilRepositoryGUI&ref_id=" .$crs_ref;

			$info[$key]["title"] = '<a href="'
									.$edit_lnk
									.'">'
									.$info[$key]["title"]
									.'</a>';

			$orgu_utils->getLongTitle();


			
			$info[$key]["location"] = $orgu_utils->getLongTitle();
			$trainer = $crs_utils->getMainTrainer();
			if($trainer){
				$info[$key]["trainer"] = $trainer->getFullName();
			} else {
				$info[$key]["trainer"] = '-';
			}

			$ms = $crs_utils->getMembership();
			
			$mbr_booked_userids = $ms->getMembers();
			$mbr_waiting_userids = $crs_utils->getWaitingMembers($id);

			$mbr_booked = count($mbr_booked_userids);
			$mbr_waiting = count($mbr_waiting_userids);

			$info[$key]["members"] = $mbr_booked .' (' .$mbr_waiting .')'
									.' / ' .$info[$key]["min_participants"] .'-' .$info[$key]["max_participants"];
			
			$info[$key]["date"] = $info[$key]["start_date"] .'-' .$info[$key]["end_date"];
			
			$info[$key]["status"] = ($crs_utils->getCourse()->isActivated()) ? 'online' : 'offline';

			$memberlist_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-table-eye.png").'" />';
			//http://localhost/4_4_generali2/ilias.php?ref_id=80&cmd=trainer&cmdClass=gevmemberlistdeliverygui&cmdNode=ei&baseClass=gevmemberlistdeliverygui
			//$memberlist_link = $ilCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "trainer");
			$memberlist_lnk = "ilias.php?cmd=trainer&cmdClass=gevmemberlistdeliverygui&cmdNode=ei&baseClass=gevmemberlistdeliverygui&ref_id=" .$crs_ref;
			$action = '<a href="'
					.$memberlist_lnk
					.'">'
					.$memberlist_img
					.'</a>';
			$info[$key]["action"] = $action;

		}

		return $info;
	}

}

?>