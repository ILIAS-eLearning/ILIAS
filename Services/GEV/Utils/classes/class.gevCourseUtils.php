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

class gevCourseUtils {
	static $instances = array();
	
	protected function __construct($a_crs_id) {
		global $ilDB;
		
		$this->db = &$ilDB;
		
		$this->crs_id = $a_crs_id;
		$this->gev_settings = gevSettings::getInstance();
		$this->amd = gevAMDUtils::getInstance();
	}
	
	static public function getInstance($a_crs_id) {
		if (array_key_exists($a_crs_id, self::$instances)) {
			return self::$instances[$a_crs_id];
		}

		self::$instances[$a_crs_id] = new gevCourseUtils($a_crs_id);
		return self::$instances[$a_crs_id];
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
		return "NYI!"; // TODO: Implement this!
	}

	static public function mkDeadlineDate($a_start_date, $a_deadline) {
		if (!$a_start_date || !$a_deadline) {
			return null;
		}
		
		$date = new ilDate($a_start_date->get(IL_CAL_DATE), IL_CAL_DATE);
		// ILIAS idiosyncracy. Why does it destroy the date, when i increment by 0?
		if ($a_deadline == 0) {
			return $date;
		}
		$date->increment($a_deadline * -1, IL_CAL_DAY);
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
		$temp = extract("-", $a_custom_id);
		return $temp[1];
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

	public function getLink() {
		return self::getLinkTo($this->crs_id);
	}

	public function getCustomId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CUSTOM_ID);
	}
	
	public function setCustomId($a_id) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_CUSTOM_ID, $a_id);
	}
	
	public function getTemplateTitle() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TEMPLATE_TITLE);
	}
	
	public function setTemplateTitle($a_title) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_TEMPLATE_TITLE, $a_title);
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
	
	public function getStartDate() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_START_DATE);
	}
	
	public function setStartDate($a_date) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_START_DATE, $a_date);
	}
	
	public function getEndDate() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_END_DATE);
	}
	
	public function setEndDate($a_date) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_END_DATE, $a_date);
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
	
	public function getMinParticipants() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_MIN_PARTICIPANTS);
	}
	
	public function getCancelDeadline() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CANCEL_DEADLINE);
	}
	
	public function getBookingDeadline() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_BOOKING_DEADLINE);
	}
	
	public function getCancelWaitingList() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CANCEL_WAITING);
	}
	
	public function getProvider() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_PROVIDER);
	}
	
	public function getVenue() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VENUE);
	}
	
	public function getAccomodation() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_ACCOMODATION);
	}
}

?>