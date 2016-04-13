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
require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
require_once("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");

class gevCourseUtils {
	static $instances = array();
	const CREATOR_ROLE_TITLE = "Pool Trainingsersteller";
	const RECIPIENT_MEMBER = "Mitglied";
	const RECIPIENT_STANDARD = "standard";
	
	protected function __construct($a_crs_id) {
		global $ilDB, $ilLog, $lng, $ilCtrl, $rbacreview, $rbacadmin, $rbacsystem, $tree;
		
		$this->gIldb = $ilDB;
		$this->gLog = $ilLog;
		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gTree = $tree;
		
		$this->gLng->loadLanguageModule("crs");
		
		$this->crs_id = $a_crs_id;
		$this->crs_obj = null;
		$this->crs_booking_permissions = null;
		$this->crs_participations = null;
		$this->gev_settings = gevSettings::getInstance();
		$this->amd = gevAMDUtils::getInstance();
		$this->local_roles = null;
		
		$this->gRbacreview = $rbacreview;
		$this->gRbacadmin = $rbacadmin;
		$this->gRbacsystem = $rbacsystem;
		
		$this->membership = null;
		$this->main_trainer = null;
		$this->main_admin = null;
		$this->main_training_creator = null;
	
		$this->material_list = null;
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
		global $ilCtrl,$ilUser;
		// This is for the booking per express login.
		if (!$ilUser->getId() || gevSettings::getInstance()->getAgentOfferUserId() == $ilUser->getId() ) {
			$ilCtrl->setParameterByClass("gevExpressRegistrationGUI", "crs_id", $a_crs_id);
			$lnk = $ilCtrl->getLinkTargetByClass("gevExpressRegistrationGUI", "startRegistration");
			$ilCtrl->clearParametersByClass("gevExpressRegistrationGUI");
			return $lnk;
		}
		
		$ilCtrl->setParameterByClass("gevBookingGUI", "user_id", $a_usr_id);
		$ilCtrl->setParameterByClass("gevBookingGUI", "crs_id", $a_crs_id);
		$lnk = $ilCtrl->getLinkTargetByClass("gevBookingGUI", "book");
		$ilCtrl->clearParametersByClass("gevBookingGUI");
		return $lnk;
	}
	
	public function getPermanentBookingLink() {
		include_once('./Services/Link/classes/class.ilLink.php');
		return ilLink::_getStaticLink($this->crs_id, "gevcrsbooking",true, "");
	}
	
	public function getPermanentBookingLinkGUI() {
		include_once 'Services/PermanentLink/classes/class.ilPermanentLinkGUI.php';
		
		if ($this->isDecentralTraining()) {
			$type = "gevcrsbookingexpress";
		}
		else {
			$type = "gevcrsbooking";
		}
		
		$bl = new ilPermanentLinkGUI($type,  $this->getId());
		$bl->setIncludePermanentLinkText(false);
		$bl->setAlignCenter(false);
		return $bl;
	}
	
	static public function gotoBooking($a_crs_id) {
		global $ilCtrl;
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmd=toBooking&crs_id=".$a_crs_id);
	}
	
	static public function gotoExpressBooking($a_crs_id) {
		require_once("Services/Utilities/classes/class.ilUtil.php");
		ilUtil::redirect("makler.php?baseClass=gevexpressregistrationgui&cmd=startRegistration&crs_id=".$a_crs_id);
	}
	
	static public function gotoBookingTrainer($a_crs_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$crs_ref_id = gevObjectUtils::getRefId($a_crs_id);
		ilUtil::redirect("ilias.php?ref_id=$crs_ref_id&cmdClass=ilcoursebookingadmingui&baseClass=ilcoursebookinggui");
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
			$ind = count($temp) - 1;
			$num = intval($temp[$ind]) + 1;
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
	
	public function refreshCourse() {
		$this->crs_obj = new ilObjCourse($this->crs_id, false);
		$this->crs_obj->setRefId(gevObjectUtils::getRefId($this->crs_id));
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

	public function setParticipationStatusAndPoints($user_id, $state, $cpoints) {
		if(!is_numeric($state)) {
			throw new Exception("gevCourseUtils::setParticipationStatusAndPoints:state is not an integer");
		}

		if(!is_numeric($cpoints)) {
			throw new Exception("gevCourseUtils::setParticipationStatusAndPoints:cpoints is not an integer");
		}

		$sql = "UPDATE crs_pstatus_usr"
				." SET cpoints = ".$this->gIldb->quote($cpoints,"integer")
					." , status = ".$this->gIldb->quote($state,"integer")
				." WHERE user_id = ".$this->gIldb->quote($user_id,"integer")
					." AND crs_id = ".$this->gIldb->quote($this->getId(),"integer");
		
		$this->gIldb->manipulate($sql);
	}
	
	public function getLocalRoles() {
		if ($this->local_roles === null) {
			require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
			$this->local_roles = gevRoleUtils::getInstance()->getLocalRoleIdsAndTitles($this->crs_id);
			
			// rewrite names of member, tutor and admin roles
			foreach ($this->local_roles as $id => $title) {
				$pref = substr($title, 0, 8);
				if ($pref == "il_crs_m") {
					$this->local_roles[$id] = $this->gLng->txt("crs_member");
				}
				else if ($pref == "il_crs_t") {
					$this->local_roles[$id] = $this->gLng->txt("crs_tutor");
				}
				else if ($pref == "il_crs_a") {
					$this->local_roles[$id] = $this->gLng->txt("crs_admin");
				}
			}
		}
		return $this->local_roles;
	}
	
	//
	
	
	public function getId() {
		return $this->crs_id;
	}

	public function getRefId() {
		return gevObjectUtils::getRefId($this->crs_id);
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
	
	public function getIsCancelled() {
		return "Ja" == $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_IS_CANCELLED);
	}

	public function setIsCancelled($a_val) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_IS_CANCELLED, ($a_val === true) ? "Ja" : "Nein" );
	}

	public function getType() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TYPE);
	}
	
	public function isPraesenztraining() {
		return preg_match("/.*senztraining/", $this->getType());
	}
	
	public function isSelflearning() {
		return $this->getType() == "Selbstlernkurs";
	}

	public function isWebinar() {
		return $this->getType() == "Webinar";
	}

	public function isVirtualTraining(){
		return $this->getType() == "Virtuelles Training";
	}
	
	public function isDecentralTraining() {
		return $this->getEduProgramm() == "dezentrales Training";
	}

	public function isFlexibleDecentrallTraining() {
		$tpl_ref_id = $this->getTemplateRefId();
		
		if($tpl_ref_id === null) {
			return false;
		}
		
		if(gevSettings::getInstance()->getDctTplFlexPresenceId() == $tpl_ref_id) {
			return true;
		}

		if(gevSettings::getInstance()->getDctTplFlexWebinarId() == $tpl_ref_id) {
			return true;
		}

		return false;
	}

	public function isStartAndEndDateSet(){
		if($this->getStartDate() !== null && $this->getEndDate() !== null){
			return true;
		}

		return false;
	}

	public function hasStartOrEndDateChangedToVCAssign() {
		require_once("Services/VCPool/classes/class.ilVCPool.php");
		$vc_pool = ilVCPool::getInstance();
		$assigns = $vc_pool->getVCAssignmentsByObjId($this->crs_id);
		
		if(empty($assigns)) {
			return true;
		}

		$start_datetime = $this->getStartDate()->get(IL_CAL_DATE)." ".$this->getFormattedStartTime().":00";
		$end_datetime = $this->getEndDate()->get(IL_CAL_DATE)." ".$this->getFormattedEndTime().":00";

		foreach($assigns as $assign) {
			$ass_start_date = $assign->getStart()->get(IL_CAL_DATETIME);
			$ass_end_date = $assign->getEnd()->get(IL_CAL_DATETIME);

			if ($ass_start_date != $start_datetime || $ass_end_date != $end_datetime) {
				return true;
			}
		}

		return false;
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
	
	public function setStartDate(ilDate $a_date) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_START_DATE, $a_date);
	}

	public function getStartDateTime() {
		$start_date = $this->getStartDate()->get(IL_CAL_DATE);
		$start_time = $this->getFormattedStartTime().":00";

		return $start_date." ".$start_time;
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
	
	public function setEndDate(ilDate $a_date) {
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
	
	public function getDBVHotTopic() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_DBV_HOT_TOPIC);
	}

	public function setDBVHotTopic($a_topic) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_DBV_HOT_TOPIC, $a_topic);
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
			$this->getCourse()->enableSubscriptionMembershipLimitation($a_active);
			$this->getCourse()->enableWaitingList($a_active);
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


	public function getAbsoluteCancelDeadline() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_ABSOLUTE_CANCEL_DEADLINE);
	}
	
	public function setAbsoluteCancelDeadline($a_dl) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_ABSOLUTE_CANCEL_DEADLINE, $a_dl);
	}
	
	public function getAbsoluteCancelDeadlineDate() {
		return self::mkDeadlineDate($this->getStartDate(), $this->getAbsoluteCancelDeadline());
	}
	
	public function getFormattedAbsoluteCancelDeadline() {
		$dl = $this->getAbsoluteCancelDeadlineDate();
		if (!$dl) {
			return "";
		}
		$val = ilDatePresentation::formatDate($dl);
		return $val;
	}
	
	public function isAbsoluteCancelDeadlineExpired() {
		$dl = $this->getAbsoluteCancelDeadlineDate();
		
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
	
	public function setVirtualClassType($a_vc_type) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_VC_CLASS_TYPE, $a_vc_type);
	}

	public function getVirtualClassType() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VC_CLASS_TYPE);
	}

	public function setTrainingCategory(array $a_training_category) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_TOPIC, $a_training_category);
	}

	public function setTargetGroup(array $a_target_group) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_TARGET_GROUP, $a_target_group);
	}

	public function setGDVTopic($a_gdv_topic) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_GDV_TOPIC, $a_gdv_topic);
	}

	// Venue Info
	
	public function getVenueId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VENUE);
	}
	
	public function setVenueId($a_venue) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_VENUE, $a_venue);
	}

	public function setVenueFreeText($a_venue_free_text) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_VENUE_FREE_TEXT,$a_venue_free_text);
	}

	public function getVenueFreeText() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VENUE_FREE_TEXT);
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
	
	public function getVenueHomepage() {
		$ven = $this->getVenue();
		if ($ven === null) {
			return "";
		}
		
		return $ven->getHomepage();
	}

	//Training Creatot
	public function setTrainingCreatorLogin($user_login) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_TRAINING_CREATOR, $user_login);
	}

	public function getTrainingCreatorLogin() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TRAINING_CREATOR);
	}

	
	// Accomodation
	
	public function getAccomodationId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_ACCOMODATION);
	}
	
	public function setAccomodationId($a_accom) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_ACCOMODATION, $a_accom);
	}
	
	public function getAccomodations() {
		require_once("Services/Accomodations/classes/class.ilAccomodations.php");
		return ilAccomodations::getInstance($this->getCourse());
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
	
	// Checks whether the start and end date of the course were modified regarding the
	// last histo entry and moves all accomodations so they have the same relation to
	// the new date. If some the overnights exceed the end date they are removed.
	public function moveAccomodations() {
		// Get the last histo entry.
		$res = $this->gIldb->query( "SELECT begin_date, end_date"
								."  FROM hist_course"
								." WHERE crs_id = ".$this->gIldb->quote($this->crs_id, "integer")
								."   AND hist_historic = 1"
								."   AND begin_date != '0000-00-00'"
								."   AND end_date != '0000-00-00'"
								." ORDER BY hist_version DESC"
								." LIMIT 1"
								);
		
		$start_date = $this->getStartDate();
		$end_date = $this->getEndDate();
		if (($rec = $this->gIldb->fetchAssoc($res)) && $start_date && $end_date) {
			$start_hist = $rec["begin_date"];
			$start_cur = $start_date->get(IL_CAL_DATE);
			$end_hist = $rec["end_date"];
			$end_cur = $end_date->get(IL_CAL_DATE);
			$duration_cur = floor((strtotime($start_cur) - strtotime($end_cur)) / (60 * 60 * 24));
			$duration_hist = floor((strtotime($start_hist) - strtotime($end_hist)) / (60 * 60 * 24));
			
			if ($start_hist != $start_cur || $end_hist != $end_cur) {
				if ($duration_cur == $duration_hist) {
					// New training has the same length as the old training, so we jus need to
					// move the accomodations accordingly.
					self::moveAccomodationsSameDuration($start_cur, $start_hist);
				}
				else {
					self::moveAccomodationsDurationChanged($start_hist, $end_hist, $start_cur, $end_cur);
				}
			}
		}
	}

	protected function moveAccomodationsSameDuration($a_old_start_date, $a_new_start_date) {
		$offset_days = floor((strtotime($a_old_start_date) - strtotime($a_new_start_date)) / (60 * 60 * 24));
		$this->gIldb->manipulate("UPDATE crs_acco"
							 ."   SET night = night + INTERVAL($offset_days) DAY,"
							 // This prevents primary key problems
							 ."       crs_id = ".$this->gIldb->quote(-1 * $this->crs_id, "integer")
							 ." WHERE crs_id = ".$this->gIldb->quote($this->crs_id, "integer")
							 );
		// This reverts the preventing for primary key problems
		$this->gIldb->manipulate("UPDATE crs_acco"
							 ."   SET crs_id = ".$this->gIldb->quote($this->crs_id, "integer")
							 ." WHERE crs_id = ".$this->gIldb->quote(-1 * $this->crs_id, "integer")
							 );
	}
	
	protected function moveAccomodationsDurationChanged($a_old_start_date, $a_old_end_date, $a_new_start_date, $a_new_end_date) {
		$old_nights = array();
		$this->nightsFromTo($a_old_start_date, $a_old_end_date, $old_nights);
		$old_amount_of_nights = count($old_nights);
		
		$new_nights = array();
		$this->nightsFromTo($a_new_start_date, $a_new_end_date, $new_nights);
		$new_amount_of_nights = count($new_nights);
		
		$accos = $this->getAccomodations();
		
		$res = $this->gIldb->query( "SELECT user_id, GROUP_CONCAT(night SEPARATOR \";\") nights"
								."  FROM crs_acco"
								." WHERE crs_id = ".$this->gIldb->quote($this->crs_id, "integer")
								." GROUP BY user_id"
								." ORDER BY night ASC"
								);
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$nights = explode(";", $rec["nights"]);
			$user_id = $rec["user_id"];
			
			$new_accos = array_values($new_nights);
			
			$prearrival = in_array($old_nights[0], $nights);
			$postdeparture = in_array($old_nights[$old_amount_of_nights - 1], $nights);
			$amount_of_nights = count($nights);
			
			// Handle simple cases first
			if ($prearrival && $postdeparture && $amount_of_nights == $old_amount_of_nights) {
				// User had prearrival and postdeparture and all nights
				// Nothing to do here...
			}
			else if ($prearrival && $amount_of_nights + 1 == $old_amount_of_nights) {
				// User had a prearrival but no postdepature and all other nights
				unset($new_accos[$new_amount_of_nights - 1]);
			}
			else if ($postdeparture && $amount_of_nights + 1 == $old_amount_of_nights) {
				// User had a postdeparture but no prearrival and all other nights
				unset($new_accos[0]);
			}
			else if (!$prearrival && !$postdeparture && $amount_of_nights + 2 == $old_amount_of_nights) {
				// User had no postdeparture or prearrival, but all other nights
				unset($new_accos[$new_amount_of_nights - 1]);
				unset($new_accos[0]);
			}
			else {
				if (!$prearrival) {
					unset($new_accos[0]);
				}
				else {
					unset($nights[0]);
				}
				
				if (!$postdeparture) {
					unset($new_accos[$new_amount_of_nights - 1]);
				}
				else {
					unset($nights[$amount_of_nights - 1]);
				}
				
				foreach ($old_nights as $index => $old_night) {
					if (!in_array($old_night, $nights)) {
						unset($new_accos[$index]);
					}
				}
			}
			
			foreach($new_accos as $index => $acco) {
				$new_accos[$index] = new ilDate($acco, IL_CAL_DATE);
			}
			
			$accos->setAccomodationsOfUser($user_id, $new_accos);
		}
	}
	
	// Helpers for moving accomodations
	protected function nightsFromTo($a_start_date, $a_end_date, &$a_nights) {
		require_once("Services/Calendar/classes/class.ilDate.php");
		$start = new ilDate($a_start_date, IL_CAL_DATE);
		// For prearrival
		$start->increment(ilDateTime::DAY, -1);
				
		while ($start->get(IL_CAL_DATE) <= $a_end_date) {
			$a_nights[] = $start->get(IL_CAL_DATE);
			$start->increment(ilDateTime::DAY);
		}
	}

	// assign a new vc to a course
	protected function assignNewVC($a_amount_of_vcs = 1) {
		require_once("Services/VCPool/classes/class.ilVCPool.php");
		$vc_pool = ilVCPool::getInstance();

		$start_datetime = new ilDateTime($this->getStartDate()->get(IL_CAL_DATE)." ".$this->getFormattedStartTime().":00", IL_CAL_DATETIME);
		$end_datetime = new ilDateTime($this->getEndDate()->get(IL_CAL_DATE)." ".$this->getFormattedEndTime().":00", IL_CAL_DATETIME);

		for ($i = 0; $i < $a_amount_of_vcs; $i++) {				
			$to_assign_vc = $vc_pool->getVCAssignment($this->getVirtualClassType(), $this->crs_id, $start_datetime, $end_datetime);

			if($to_assign_vc === null) {
				return false;
			}
			
			$this->setVirtualClassLink($to_assign_vc->getVC()->getUrl());
			$this->setVirtualClassPassword($to_assign_vc->getVC()->getMemberPassword());
			$this->setVirtualClassPasswordTutor($to_assign_vc->getVC()->getTutorPassword());
			$this->setVirtualClassLoginTutor($to_assign_vc->getVC()->getTutorLogin());
		}

		return true;
	}
	
	public function checkVirtualTrainingForPossibleVCAssignment() {
		if (!$this->isStartAndEndDateSet() && $this->getVirtualClassType() === null) {
			ilUtil::sendFailure($this->gLng->txt("gev_vc_no_url_saved_because_no_vc_class_type_and_no_times"));
			return false;
		}
		elseif (!$this->isStartAndEndDateSet() && $this->getVirtualClassType() !== null) {
			ilUtil::sendFailure($this->gLng->txt("gev_vc_no_url_saved_because_no_startenddate_set"));
			return false;
		}
		elseif ($this->isStartAndEndDateSet() && $this->getVirtualClassType() === null) {
			ilUtil::sendFailure($this->gLng->txt("gev_vc_no_url_saved_because_no_vc_class_type"));
			return false;
		}
		return true;
	}

	//handles the assignsystem for VC
	public function adjustVCAssignment() {
		require_once("Services/VCPool/classes/class.ilVCPool.php");
		$vc_pool = ilVCPool::getInstance();
		$vc_types = $vc_pool->getVCTypes();
		
		$assigned_vcs = $vc_pool->getVCAssignmentsByObjId($this->crs_id);
		$has_vc_assigned = !empty($assigned_vcs);
		
		$should_get_vc_assignment = $this->isStartAndEndDateSet() 
								&& in_array($this->getVirtualClassType(), $vc_types);
		
		if ($has_vc_assigned && $should_get_vc_assignment) {
			if ($this->hasStartOrEndDateChangedToVCAssign()) {
				// release current assignments and assign a new vc
				foreach($assigned_vcs as $avc) {
					$avc->release();
				}
				
				if ($this->assignNewVC()) {
					ilUtil::sendInfo($this->gLng->txt("gev_vc_send_invitation_mail_reminder"));
				}
				else {
					$this->cleanupAllVirtualClassAssignmentAMDFields();
					ilUtil::sendFailure($this->gLng->txt("gev_vc_no_free_url"));
				}
			}
			else {
				// everything ok, don't touch it
			}
		}
		elseif ($has_vc_assigned && !$should_get_vc_assignment) {
			// release all assignments and empty amd fields
			foreach($assigned_vcs as $avc) {
				$avc->release();
				if ($this->getVirtualClassLink() == $avc->getVC()->getUrl()) {
					$this->setVirtualClassLink(null);
				}

				if ($this->getVirtualClassPassword() == $avc->getVC()->getMemberPassword()) {
					$this->setVirtualClassPassword(null);
				}
				if ($this->getVirtualClassPasswordTutor() == $avc->getVC()->getTutorPassword()) {
					$this->setVirtualClassPasswordTutor(null);
				}
				if ($this->getVirtualClassLoginTutor() == $avc->getVC()->getTutorLogin()) {
					$this->setVirtualClassLoginTutor(null);
				}
			}
		}
		elseif (!$has_vc_assigned && $should_get_vc_assignment) {
			if ($this->assignNewVC()) {
				ilUtil::sendInfo($this->gLng->txt("gev_vc_send_invitation_mail_reminder"));
			}
			else {
				$this->cleanupAllVirtualClassAssignmentAMDFields();
				ilUtil::sendFailure($this->gLng->txt("gev_vc_no_free_url"));
			}
		}
		else { // $!has_vc_assigned && !$should_get_vc_assignment
			// DON'T TOUCH THIS.
		}
	}

	protected function cleanupAllVirtualClassAssignmentAMDFields() {
		$this->setVirtualClassLink(null);
		$this->setVirtualClassPassword(null);
		$this->setVirtualClassPasswordTutor(null);
		$this->setVirtualClassLoginTutor(null);
	}

	public function deleteVCAssignment() {
		require_once("Services/VCPool/classes/class.ilVCPool.php");
		$vc_pool = ilVCPool::getInstance();
		$assigned_vcs = $vc_pool->getVCAssignmentsByObjId($this->crs_id);

		foreach($assigned_vcs as $avc) {
			$avc->release();

			if ($this->getVirtualClassLink() == $avc->getVC()->getUrl()) {
				$this->setVirtualClassLink(null);
			}

			if ($this->getVirtualClassPassword() == $avc->getVC()->getMemberPassword()) {
				$this->setVirtualClassPassword(null);
			}

			if ($this->getVirtualClassPasswordTutor() == $avc->getVC()->getTutorPassword()) {
				$this->setVirtualClassPasswordTutor(null);
			}

			if ($this->getVirtualClassLoginTutor() == $avc->getVC()->getTutorLogin()) {
				$this->setVirtualClassLoginTutor(null);
			}

		}

	}


	public function getVirtualClassLink() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VC_LINK);
	}
	
	public function getVirtualClassLinkWithHTTP() {
		$link = $this->getVirtualClassLink();

		if($this->startsWith(strtolower($link), "http://") || $this->startsWith(strtolower($link), "https://")) {
			return $link;
		}

		return "http://".strtolower($link);
	}

	private function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
	
	public function setVirtualClassLink($a_value) {
		return $this->amd->setField($this->crs_id, gevSettings::CRS_AMD_VC_LINK, $a_value);
	}

	public function getVirtualClassPassword() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VC_PASSWORD);
	}

	public function setVirtualClassPassword($a_value) {
		return $this->amd->setField($this->crs_id, gevSettings::CRS_AMD_VC_PASSWORD, $a_value);
	}

	public function getVirtualClassPasswordTutor() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VC_PASSWORD_TUTOR);
	}

	public function setVirtualClassPasswordTutor($a_value) {
		return $this->amd->setField($this->crs_id, gevSettings::CRS_AMD_VC_PASSWORD_TUTOR, $a_value);
	}

	public function getVirtualClassLoginTutor() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VC_LOGIN_TUTOR);
	}

	public function setVirtualClassLoginTutor($a_value) {
		return $this->amd->setField($this->crs_id, gevSettings::CRS_AMD_VC_LOGIN_TUTOR, $a_value);
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
	
	public function getTEPOrguId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_TEP_ORGU);
	}
	
	public function setTEPOrguId($a_tep_orgu) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_TEP_ORGU, $a_tep_orgu);
	}
	
	// options for course search
	
	public static function getTypeOptions() {
		global $lng;
		$all = $lng->txt("gev_crs_srch_all");
		$pt = "Präsenztraining";
		$wb = "Webinar";
		$vt = "Virtuelles Training";
		$sk = "Selbstlernkurs";
		return array( $all => $all
					, $pt => $pt
					, $wb => $wb
					, $vt => $vt
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
	

	// Materiallists
	
	public function isMaterialListSend() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$am = new gevCrsAutoMails($this->getId());
		return $am->getAutoMail("materiallist_for_storage")->getLastSend() !== null;
	}
	
	public function getMaterialList() {
		if ($this->material_list === null) {
			require_once "./Services/MaterialList/classes/class.ilMaterialList.php";
			$this->material_list = new ilMaterialList($this->getId());
		}
		return $this->material_list;
	}
	
	public function hasMaterialOnList() {
		return $this->getMateriallist()->hasItems($this->getId());
	}
	
	// derived courses for templates
	
	public function getDerivedCourseIds($future_only = false) {
		if (!$this->isTemplate()) {
			throw new Exception("gevCourseUtils::getDerivedCourseIds: this course is no template and thus has no derived courses.");
		}

		$ref_id_field = $this->amd->getFieldId(gevSettings::CRS_AMD_TEMPLATE_REF_ID);
		$start_date_field = $this->amd->getFieldId(gevSettings::CRS_AMD_START_DATE);
		
		$ref_ids = gevObjectUtils::getAllRefIds($this->crs_id);
		
		$res = $this->gIldb->query("SELECT DISTINCT ai.obj_id FROM adv_md_values_int ai "
								.(!$future_only ? "" 
										: (" JOIN adv_md_values_date ad"
										  ." ON ad.field_id = ".$this->gIldb->quote($start_date_field, "integer")
										  ." AND ad.obj_id = ai.obj_id"))
								." JOIN object_reference oref ON oref.obj_id = ai.obj_id"
								." WHERE ai.field_id = ".$this->gIldb->quote($ref_id_field, "integer")
								."  AND ".$this->gIldb->in("ai.value", $ref_ids, false, "integer")
								.(!$future_only ? "" : " AND ad.value > CURDATE()")
								."  AND oref.deleted IS NULL"
								);
		$obj_ids = array();
		while ($rec = $this->gIldb->fetchAssoc($res)) {
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

		$sql= "UPDATE adv_md_values_text "
							  ."   SET value = ".$this->gIldb->quote($this->getTitle(), "text")
							  ." WHERE ".$this->gIldb->in("obj_id", $obj_ids, false, "integer")
							  ."   AND field_id = ".$this->gIldb->quote($tmplt_title_field, "integer");
		$this->gIldb->manipulate( $sql  );

		foreach($obj_ids as $crs_id) {
			$crs = new ilObjCourse($crs_id, false);
			$crs->update();
		}
	}
	
	// Participants, Trainers and other members
	
	public function getMembership() {
		return $this->getCourse()->getMembersObject();
	}
	
	public function isMember($a_user_id) {
		return $this->getMembership()->isAssigned($a_user_id);
	}
	
	public function getMembersExceptOfAdmins() {
		$ms = $this->getMembership();
		return array_merge($ms->getMembers(), $ms->getTutors());
	}
	
	public function getParticipants() {
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$role = $this->getCourse()->getDefaultMemberRole();
		return gevRoleUtils::getInstance()->getRbacReview()->assignedUsers($role);
	}
	
	public function getTrainers($names = false) {
	$tutors = $this->getMembership()->getTutors();
	if($names) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

		$tutors = gevUserUtils::getFullNames($tutors);
		foreach ($tutors as $userid => &$a_fullname) {
			$fullname = explode(", ", $a_fullname);
			$a_fullname = $fullname[1]." ".$fullname[0];
		}
	}
	return $tutors;
	}
	
	public function hasTrainer($trainer_id) {
		return in_array($trainer_id, $this->getTrainers());
	}
	
	
	public function getAdmins() {
		return $this->getMembership()->getAdmins();
	}

	public function getTrainingCreator() {
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$role_utils = gevRoleUtils::getInstance();
		$local_roles = $role_utils->getLocalRoleIdsAndTitles($this->getCourse()->getId());
		$role_id = null;
		foreach ($local_roles as $key => $value) {
			if($value == self::CREATOR_ROLE_TITLE) {
				$role_id = $key;
			}
		}

		if($role_id === null) {
			return null;
		}

		return $role_utils->getRbacReview()->assignedUsers($role_id);
	}
	
	public function hasAdmin($admin_id) {
		return in_array($trainer_id, $this->getAdmins());
	}
	
	public function getMembers() {
		return array_merge($this->getMembership()->getMembers(), $this->getTrainers(), $this->getAdmins());
	}

	public function getBookedUser() {
		return $this->getParticipants();
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

	public function getMainTrainingCreator() {
		if($this->main_training_creator === null) {
			$training_creator = $this->getTrainingCreatorLogin();
			if ($training_creator !== null) {
				$this->main_training_creator = new ilObjUser(ilObjUser::_lookupId($training_creator));
			}
		}

		return $this->main_training_creator;
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
	
	// Main Trainig Creator info
	
	public function getMainTrainingCreatorFirstname() {
		$tr = $this->getMainTrainingCreator();
		if ($tr !== null) {
			return $tr->getFirstname();
		}
		return "";
	}
	
	public function getMainTrainingCreatorLastname() {
		$tr = $this->getMainTrainingCreator();
		if ($tr !== null) {
			return $tr->getLastname();
		}
		return "";
	}
	
	public function getMainTrainingCreatorName() {
		$tr = $this->getMainTrainingCreator();
		if ($tr !== null) {
			return $this->getMainTrainingCreatorFirstname()." ".$this->getMainTrainingCreatorLastname();
		}
		return "";
	}
	
	public function getMainTrainingCreatorPhone() {
		$tr = $this->getMainTrainingCreator();
		if ($tr !== null) {
			return $tr->getPhoneOffice();
		}
		return "";
	}
	
	public function getMainTrainingCreatorEMail() {
		$tr = $this->getMainTrainingCreator();
		if ($tr !== null) {
			return $tr->getEmail();
		}
		return "";
	}

	public function getInvitationMailPreview() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$am = new gevCrsAutoMails($this->getId());
		return $am->getPreview("invitation");
	}
	
	public function mailCronJobDidRun() {
		$res = $this->gIldb->query("SELECT COUNT(*) cnt FROM gev_crs_dl_mail_cron ".
								"WHERE crs_id = ".$this->gIldb->quote($this->crs_id, "integer"));
		$rec = $this->gIldb->fetchAssoc($res);
		return $rec["cnt"] > 0;
	}
	
	public function isFinalized() {
		return $this->getParticipations()->getProcessState() == ilParticipationStatus::STATE_FINALIZED;
	}

	/**
	* get status started if start datetime is passed.
	* just calculated value
	*
	* @return boolean
	*/
	public function isStarted() {
		$start_datetime = $this->getStartDateTime();
		$now = date("Y-m-d H:m:00");
		
		return $now > $start_datetime;
	}
	
	// Memberlist creation
	
	const MEMBERLIST_TRAINER = 0;
	const MEMBERLIST_HOTEL = 1;
	const MEMBERLIST_PARTICIPANT = 2;
	
	public function deliverMemberList($a_type) {
		$this->buildMemberList(true, null, $a_type);
	}

	public function deliverUVGList() {
		$this->buildUVGList(true, null);
	}

	public function deliverSignatureList($filename = null) {
		$this->buildSignatureList($filename);
	}

	public function deliverCrsScheduleList($filename = null) {
		$this->buildCrsScheduleList($filename);
	}

	public function buildICAL($a_send,$a_filename) {
		$loc = $this->getVenue();
		if ($loc) {
			$loc = $loc->getTitle();
		}
		$street = $this->getVenueStreet();
		$no = $this->getVenueHouseNumber();
		$zip = $this->getVenueZipcode();
		$city = $this->getVenueCity();
		if($loc) {
			if($street) {
				$loc.= ", \n".$street." ".$no;
			}
			if($zip) {
				$loc .= ", \n".$zip;
				if($city) {
					$loc .= " ".$city;
				}
			} else if($city) {
					$loc .= ", \n".$city;
			}

		} else {
			$loc = "";
		}
		
		if ($a_filename === null) {
			if(!$a_send) {
				$a_filename = ilUtil::ilTempnam();
			}
			else {
				$a_filename = "iCalEintrag.ics";
			}
		}

		$organizer = $this->getMainAdmin() ? $this->getMainAdminName().
			($this->getMainAdminEmail() ? '('.$this->getMainAdminEmail().')' : '') : '';

		$start_date_obj = $this->getStartDate();
		$end_date_obj = $this->getEndDate();
		if($start_date_obj === null || $end_date_obj === null) {
			throw new Exception("gevUserUtils::buildICAL:"
								." start- or end-date of course are not set."
								." You have to provide both in order to create an ical event.");
		}

		$start_date =
			$start_date_obj->get(IL_CAL_DATE)." ".$this->getFormattedStartTime().":00";
		$end_date =
			$end_date_obj->get(IL_CAL_DATE)." ".$this->getFormattedEndTime().":00";

		$calendar = new \Eluceo\iCal\Component\Calendar('generali-onlineakademie.de');

		$tz_rule_daytime = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_DAYLIGHT);
		$tz_rule_daytime
			->setTzName('CEST')
			->setDtStart(new \DateTime('1981-03-29 02:00:00', $dtz))
			->setTzOffsetFrom('+0100')
			->setTzOffsetTo('+0200');

		$tz_rule_daytime_rec = new \Eluceo\iCal\Property\Event\RecurrenceRule();
		$tz_rule_daytime_rec
			->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY)
			->setByMonth(3)
			->setByDay('-1SU');

		$tz_rule_daytime->setRecurrenceRule($tz_rule_daytime_rec);

		$tz_rule_standart = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_STANDARD);
		$tz_rule_standart
			->setTzName('CET')
			->setDtStart(new \DateTime('1996-10-27 03:00:00', $dtz))
			->setTzOffsetFrom('+0200')
			->setTzOffsetTo('+0100');

		$tz_rule_standart_rec = new \Eluceo\iCal\Property\Event\RecurrenceRule();
		$tz_rule_standart_rec
			->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY)
			->setByMonth(10)
			->setByDay('-1SU');

		$tz_rule_standart->setRecurrenceRule($tz_rule_standart_rec);

		$tz = new \Eluceo\iCal\Component\Timezone('Europe/Berlin');
		$tz->addComponent($tz_rule_daytime);
		$tz->addComponent($tz_rule_standart);
		$calendar->setTimezone($tz);

		$event = new \Eluceo\iCal\Component\Event();
		$event
			->setDtStart(new \DateTime($start_date))
			->setDtEnd(new \DateTime($end_date))
			->setNoTime(false)
			->setLocation($loc,$loc)
			->setUseTimezone(true)
			->setSummary($this->getTitle())
			->setDescription($this->getSubtitle())
			->setOrganizer(new \Eluceo\iCal\Property\Event\Organizer($organizer));

		$calendar
			->setTimezone($tz)
			->addComponent($event);

		$wstream = fopen($a_filename,"w");
		fwrite($wstream, $calendar->render());

		fclose($wstream);

		if($a_send)	{
			exit();
		}

		return array($a_filename, "calender.ics");
	}
 	
 	public function buildUVGList($a_send, $a_filename) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevDBVUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/User/classes/class.ilObjUser.php");

		if ($a_filename === null) {
			if(!$a_send)
			{
				$a_filename = ilUtil::ilTempnam();
			}
			else
			{
				$a_filename = "uvg_list.xls";
			}
		}

		$this->gLng->loadLanguageModule("common");
		$this->gLng->loadLanguageModule("gev");

		include_once "./Services/Excel/classes/class.ilExcelUtils.php";
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter($a_filename, $a_send);
		$workbook = $adapter->getWorkbook();
		$worksheet = $workbook->addWorksheet();
		$worksheet->setLandscape();

		$columns = array( $this->gLng->txt("gev_bd")
						, "DBV"
						, $this->gLng->txt("gev_company_name")
						, $this->gLng->txt("lastname")
						, $this->gLng->txt("firstname")
						, $this->gLng->txt("street")
						, $this->gLng->txt("zipcode")
						, $this->gLng->txt("city")
						, $this->gLng->txt("phone_office")
						, $this->gLng->txt("phone_mobile")
						, $this->gLng->txt("email")
						, $this->gLng->txt("gev_job_number")
						, $this->gLng->txt("gev_participation_status")
						, $this->gLng->txt("gev_credit_points")
						, $this->gLng->txt("gev_express_login")
						, "Zu welchen Themen hätten Sie gerne nähere Informationen?"
						, "Zu welchen Themen wünschen Sie sich weitere Webinare?"
						);

		$format_wrap = $workbook->addFormat();
		$format_wrap->setTextWrap();
		
		$i = 0;

		foreach ($columns as $column) {
			$worksheet->setColumn($i, $i, min(max(strlen($column),15),30));
			$i++;
		}

		$row = $this->buildListMeta( $workbook
							   , $worksheet
							   , "Trainingsteilnahmen"
							   , $this->gLng->txt("gev_excel_member_row_title")
							   , $columns
							   , $a_type
							   );
		
		$role_utils = gevRoleUtils::getInstance();
		$user_ids = $this->getParticipants();
		$participations = $this->getParticipations();
		$maxPoints = $participations->getMaxCreditPoints();
		$dbv_utils = gevDBVUtils::getInstance();

		if($user_ids) {
			foreach($user_ids as $user_id) {

				$user_utils = gevUserUtils::getInstance($user_id);

				if (!$user_utils->hasRoleIn(array("VP", "ExpressUser","DBV UVG"))) {
					continue;
				}

				$user = new ilObjUser($user_id);
				$user_roles = $user_utils->getGlobalRoles();

				$statusAndPoints = $participations->getStatusAndPoints($user_id);
				$points = 0;
				if($statusAndPoints["status"] == ilParticipationStatus::STATUS_SUCCESSFUL) {
					if(!$points = $statusAndPoints["points"]) {
						$points = $maxPoints;
					} 
				} elseif ($statusAndPoints["status"] == ilParticipationStatus::STATUS_NOT_SET) {
					$points = $this->gLng->txt("gev_in_progress");
				}

				$row++;
				
				$dbvs = $dbv_utils->getDBVsOf($user_id);
				$dbv_names = array_map(function($id) { 
								$names = ilObjUser::_lookupName($id);
								return $names["firstname"]." ".$names["lastname"];
							 }, $dbvs);
				
				$worksheet->write($row, 0, implode(", ", $user_utils->getUVGBDOrCPoolNames()), $format_wrap);
				$worksheet->write($row, 1, implode(", ", $dbv_names), $format_wrap);
				$worksheet->write($row, 2 , $user_utils->getCompanyName(), $format_wrap);
				$worksheet->write($row, 3 , $user_utils->getLastname(), $format_wrap);
				$worksheet->write($row, 4 , $user_utils->getFirstname(), $format_wrap);
				$worksheet->write($row, 5 ,	$user->getStreet(), $format_wrap);
				$worksheet->write($row, 6 ,	$user->getZipcode(), $format_wrap);
				$worksheet->write($row, 7 ,	$user->getCity(), $format_wrap);
				$worksheet->write($row, 8 ,	$user->getPhoneOffice(), $format_wrap);
				$worksheet->write($row, 9 ,	$user->getPhoneMobile(), $format_wrap);
				$worksheet->write($row, 10,	$user->getEmail(), $format_wrap);
				$worksheet->write($row, 11,	$user_utils->getJobNumber(), $format_wrap);
				$worksheet->write($row, 12,	$this->getParticipationStatusLabelOf($user_id), $format_wrap);
				$worksheet->write($row, 13,	$points, $format_wrap);
				$worksheet->write($row, 14, $user_utils->isExpressUser() ? $this->gLng->txt("yes") : $this->gLng->txt("no"));
			}
		}
		$workbook->close();

		if($a_send)
		{
			exit();
		}

		return array($filename, "Teilnehmer.xls");
 	}

 	public function buildSignatureList($a_filename = null) {
		require_once 'Services/GEV/Utils/classes/class.gevCourseSignatureList.php';

		if ($a_filename === null) {
			$a_filename = "signature_list.pdf";
		}

		$list = new gevCourseSignatureList($this);
		$list->Output($a_filename,'D');

 	}

 	public function buildCrsScheduleList($filename = null, $deliver=true) {
 		require_once 'Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreateSchedulePDF.php';

 		if ($a_filename === null) {
			$a_filename = $this->getTitle()."_Ablaufplan.pdf";
		}

		$pdf = new gevDecentralTrainingCreateSchedulePDF($this->getId());
		
		if($deliver) {
			$pdf->deliver($a_filename);
		} else {
			$pdf->build($a_filename);
		}
 	}

	public function buildMemberList($a_send, $a_filename, $a_type) {
		if (!in_array($a_type, array(self::MEMBERLIST_TRAINER, self::MEMBERLIST_HOTEL, self::MEMBERLIST_PARTICIPANT))) {
			throw new Exception ("Unknown type for memberlist: ".$a_type);
		}

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		$this->gLng->loadLanguageModule("common");
		$this->gLng->loadLanguageModule("gev");

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

		$columns = array( $this->gLng->txt("gender")
						, $this->gLng->txt("firstname")
						, $this->gLng->txt("lastname")
						, $this->gLng->txt("gev_org_unit_short")
						);

		$format_wrap = $workbook->addFormat(array("bottom" => 1));
		$format_wrap->setTextWrap();

		$worksheet->setColumn(0, 0, 10);	// gender
		$worksheet->setColumn(1, 1, 12); 	// firstname
		$worksheet->setColumn(2, 2, 12);	// lastname
		$worksheet->setColumn(3, 3, 25);	// org-unit

		if($a_type == self::MEMBERLIST_HOTEL) {
			$columns[] = $this->gLng->txt("gev_hotel_self_payment_pre_arr");
			$columns[] = $this->gLng->txt('status');
			$columns[] = $this->gLng->txt('gev_cnt_overnights');
			$columns[] = $this->gLng->txt('gev_hotel_pre_arrival');

			$worksheet->setColumn(4, 4, 25);	// Selbstzahler
			$worksheet->setColumn(5, 5, 20);	// status
			$worksheet->setColumn(6, 6, 20);	// overnights
			$worksheet->setColumn(7, 7, 20);	// pre_arrival

			$date_format = 'd.m.Y';
			$start_day = $this->getStartDate();
			$start_aux = $this->getStartDate();
			$end_day = $this->getEndDate();

			$post_day = new ilDate($this->getEndDate()->increment(ilDate::DAY,1),IL_CAL_UNIX);
			$count_days = 1;
			$on_column = "I";

			while(ilDate::_before($start_aux,$end_day) && !ilDate::_equals($start_aux,$end_day)) {
				$columns[] = date($date_format,$start_aux->get(IL_CAL_UNIX));
				$worksheet->setColumn(7 + $count_days, 7 + $count_days, 15);
				$start_aux = new ilDate($start_aux->increment(ilDate::DAY,1),IL_CAL_UNIX);
				$count_days++;
				$on_column++;
			}
			$columns[] = $this->gLng->txt('gev_hotel_post_departure');	//post_departure
			$worksheet->setColumn(7 + $count_days, 7 + $count_days, 20);

		}
		else if ($a_type == self::MEMBERLIST_PARTICIPANT) {
			$columns[] = "Funktion";
			
			$worksheet->setColumn(4, 4, 12);
		}
		else {
			$columns[] = $this->gLng->txt("status");
			$columns[] = $this->gLng->txt("birthday");
			$columns[] = $this->gLng->txt("gev_mobile");
			$columns[] = "Vorbedingung erfüllt";
			$columns[] = $this->gLng->txt("gev_signature");
			
			$worksheet->setColumn(4, 4, 8);
			$worksheet->setColumn(5, 5, 10);
			$worksheet->setColumn(6, 6, 14);
			$worksheet->setColumn(7, 7, 12);
			$worksheet->setColumn(8, 8, 12);
		}

		$row = $this->buildListMeta( $workbook
							   , $worksheet
							   , $this->gLng->txt("gev_excel_member_title")." ".
										( (!($a_type == self::MEMBERLIST_HOTEL))
										? $this->gLng->txt("obj_crs")
										: $this->gLng->txt("gev_hotel")
										)
							   , $this->gLng->txt("gev_excel_member_row_title")
							   , $columns
							   , $a_type
							   );

		$user_ids = $this->getParticipants();
		$tutor_ids = $this->getTrainers();

		$user_ids = array_unique(array_merge($user_ids, $tutor_ids));

		if($user_ids) {
			foreach($user_ids as $user_id) {
				$row++;
				$user_utils = gevUserUtils::getInstance($user_id);
				$ou_title = array();

				$employee_ous = $user_utils->getOrgUnitsWhereUserIsEmployee();
				$superior_ous = $user_utils->getOrgUnitsWhereUserIsDirectSuperior();

				foreach ($employee_ous as &$array) {
					$array = $array["obj_id"];
				}
				foreach ($superior_ous as &$array) {
					$array = $array["obj_id"];
				}
				$ou_ids = array_unique(array_merge($employee_ous,$superior_ous));

				foreach($ou_ids as $ou_id) {

					$ou_utils = gevOrgUnitUtils::getInstance($ou_id);
					$ou_above_utils = $ou_utils->getOrgUnitAbove();
					$ou_above_above_utils = $ou_above_utils->getOrgUnitAbove();

					if ($ou_above_above_utils) {
						$ou_title_aux = $ou_above_above_utils->getTitle()." / ".$ou_above_utils->getTitle()." / ".$ou_utils->getTitle();
					}		
					else if ($ou_above_utils) {
						$ou_title_aux = $ou_above_utils->getTitle()." / ".$ou_utils->getTitle();
					}
					else {
						$ou_title_aux = $ou_utils->getTitle();
					}
					$ou_title[] = $ou_title_aux; 
				}
				$ou_title = implode(', ', $ou_title);

				$worksheet->writeString($row, 0, $user_utils->getGender(), $format_wrap);
				$worksheet->writeString($row, 1, $user_utils->getFirstname(), $format_wrap);
				$worksheet->writeString($row, 2, $user_utils->getLastname(), $format_wrap);
				$worksheet->writeString($row, 3, $ou_title, $format_wrap);
				if($a_type == self::MEMBERLIST_HOTEL) {
					$on_det = $user_utils->getOvernightDetailsForCourse($this->getCourse());
					usort($on_det, function ($d_a,$d_b) { //presorting user overnights makes table filling easier/more secure
						if(ilDate::_before($d_a,$d_b)) {
							return -1;
						} elseif(ilDate::_equals($d_a,$d_b)) {
							return 0;
						} elseif(ilDate::_after($d_a,$d_b)){
							return 1;
						}
					});

					$worksheet->write($row, 4, $user_utils->paysPrearrival() ? "Ja" : "Nein", $format_wrap);
					$status = $user_utils->getAllIDHGBAADStatus();
					sort($status, SORT_STRING);
					$worksheet->write($row, 5, implode(", ", $status), $format_wrap);
					$on_fmla =  '=COUNTIF(H'.($row+1).':'.$on_column.($row+1).';"X")';
					$worksheet->write($row, 6, $on_fmla , $format_wrap);
					$day_iterator = new ilDate($this->getStartDate()->increment(ilDate::DAY,-1),IL_CAL_UNIX);
					$count_days = 1;
					$on_day = array_shift($on_det);
					while (ilDate::_before($day_iterator, $post_day)) {
						$has_overnight = "";
						if( $on_day !== null ) {
							if(ilDate::_equals($on_day, $day_iterator)) {
								$has_overnight = "X";
								$on_day = array_shift($on_det);
							}
						}
						$worksheet->write($row, 6 + $count_days, $has_overnight, $format_wrap);
						$day_iterator = new ilDate($day_iterator->increment(ilDateTime::DAY,1),IL_CAL_UNIX);
						$count_days++;
					}
				} else if ($a_type == self::MEMBERLIST_PARTICIPANT) {
					$worksheet->write($row, 4, $user_utils->getFunctionAtCourse($this->crs_id), $format_wrap);
				} else {
					$status = $user_utils->getAllIDHGBAADStatus();
					sort($status, SORT_STRING);
					$worksheet->write($row, 4, implode(", ", $status), $format_wrap);
					$worksheet->write($row, 5, $user_utils->getFormattedBirthday(), $format_wrap);
					$worksheet->write($row, 6, " ".$user_utils->getMobilePhone(), $format_wrap);
					$worksheet->write($row, 7, $user_utils->hasFullfilledPreconditionOf($this->crs_id) ? "Ja" : "Nein", $format_wrap);
					$worksheet->write($row, 8, " ", $format_wrap);
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
	
	public function buildListMeta($workbook, $worksheet, $title, $row_title, array $column_titles, $a_type)
	{
		$num_cols = sizeof($column_titles);

		$format_bold = $workbook->addFormat(array("bold" => 1));
		$format_title = $workbook->addFormat(array("bold" => 1, "size" => 14));
		$format_subtitle = $workbook->addFormat(array("bold" => 1, "bottom" => 6));
		$format_row_header = $workbook->addFormat(array("bold" => 1, "bottom" => 6));
		$format_row_header->setTextWrap();

		$worksheet->writeString(0, 0, $title, $format_title);
		$worksheet->mergeCells(0, 0, 0, $num_cols-1);
		$worksheet->mergeCells(1, 0, 1, $num_cols-1);

		$worksheet->writeString(2, 0, $this->gLng->txt("gev_excel_course_title"), $format_subtitle);
		for($loop = 1; $loop < $num_cols; $loop++)
		{
			$worksheet->writeString(2, $loop, "", $format_subtitle);
		}
		$worksheet->mergeCells(2, 0, 2, $num_cols-1);
		$worksheet->mergeCells(3, 0, 3, $num_cols-1);

		// course info
		$row = 4;
		foreach($this->getListMetaData($a_type) as $caption => $value)
		{
			$worksheet->writeString($row, 0, $caption, $format_bold);

			if(!is_array($value))
			{
				$worksheet->writeString($row, 2, $value);
				$worksheet->mergeCells($row, 0, $row, 1);
				$worksheet->mergeCells($row, 2, $row, $num_cols-1);
			}
			else
			{
				$first = array_shift($value);
				$worksheet->writeString($row, 1, $first);
				$worksheet->mergeCells($row, 0, $row, 1);
				$worksheet->mergeCells($row, 2, $row, $num_cols-1);

				foreach($value as $line)
				{
					if(trim($line))
					{
						$row++;
						$worksheet->write($row, 0, "");
						$worksheet->writeString($row, 1, $line);
						$worksheet->mergeCells($row, 0, $row, 1);
						$worksheet->mergeCells($row, 2, $row, $num_cols-1);
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
			$worksheet->writeString($row, $loop, $column_titles[$loop], $format_row_header);
		}

		return $row;
	}
	
	public function getListMetaData($a_type = null) {
		$start_date = $this->getStartDate();
		$end_date = $this->getEndDate();


		$trainerList = $this->getTrainers();
		foreach($trainerList as &$user_id) {
			$user_utils = gevUserUtils::getInstance($user_id);
			$name = $user_utils->getFirstname()." ".$user_utils->getLastname();
			$email = $user_utils->getEmail();
			$user_id = $name." (".$email.")";
		}
		
		$venue_title = $this->getVenueTitle();
		$venue_title = ($venue_title != "") ? $venue_title : $this->getVenueFreeText();

		$arr = array("Titel" => $this->getTitle()
					, "Untertitel" => $this->getSubtitle()
					, "Nummer der Maßnahme" => $this->getCustomId()
					, "Datum" => ($start_date !== null && $end_date !== null)
								 ? ilDatePresentation::formatPeriod($this->getStartDate(), $this->getEndDate())
								 : ""
					, "Veranstaltungsort" => $venue_title
					, "Bildungspunkte" => $this->getCreditPoints()
					, "Trainer" => 	($trainerList !== null)
					 				? implode(", ", $trainerList)
					 				: " "
					, "Trainingsbetreuer" => $this->getMainAdminName(). " (".$this->getMainAdminContactInfo().")"
					, "Fachlich verantwortlich" => $this->getTrainingOfficerContactInfo()
					);
		
		if ($a_type === self::MEMBERLIST_HOTEL) {
			unset($arr["Bildungspunkte"]);
		}
		
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
			echo $this->encodeForWindows('"'.$user->getFullname().'";"'
				.$this->formatPhoneNumberForExcel($user->getPhoneOffice()).'"'."\n");
		}
		
		exit();
	}
	
	/**
	*	Excel tends to meddle with numbers and to cast them into absurd formats, even if not asked to.
	*	To prevent this, we re move dots and comas and put at least one whitespace into the phone-number
	*	so it hopefully will be processed as text and not changed silently.
	*/
	protected function formatPhoneNumberForExcel($phone_number) {
		$return = preg_replace('#[\,\.]+#', ' ', $phone_number);
		if(0 === preg_match('#(\s|-)#' , $return)) {
			$number_chunks = str_split($return,3);
			$return = "";
			$delim = " ";
			foreach ($number_chunks as $key => $value) {
				$return .= $value.$delim;
				$delim = "";
			}
		} 
		return $return;
	}
	
	// Desk Display creation
	
	public function canBuildDeskDisplays() {
		return count($this->getMembersExceptOfAdmins()) > 0;
	}
	
	public function buildDeskDisplays($a_path = null) {
		require_once("Services/DeskDisplays/classes/class.ilDeskDisplay.php");
		$dd = new ilDeskDisplay($this->gIldb, $this->gLog);
		
		// Generali-Konzept, Kapitel "Tischaufsteller"
		$dd->setLine1Font("Arial", 48, false, false);
		$dd->setLine1Color(120, 120, 150);
		$dd->setLine2Font("Arial", 86, false, false);
		$dd->setLine2Color(0, 0, 0);
		$dd->setSpaceLeft(2);
		$dd->setSpaceBottom1(12.0);
		$dd->setSpaceBottom2(8.5);
		
		$dd->setUsers($this->getMembersExceptOfAdmins());
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

	public function getWaitingListLength() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_MAX_WAITING_LIST_LENGTH);
	}

	public function setWaitingListLength($waiting_list_lenght) {
		$this->amd->setField($this->crs_id, gevSettings::CRS_AMD_MAX_WAITING_LIST_LENGTH, $waiting_list_lenght);
	}

	public function isWaitingListFull() {
		$waiting_list_lenght = $this->getWaitingListLength();
		$waiting_list_count = count($this->getBookings()->getWaitingUsers());
		
		if($waiting_list_lenght === null || $waiting_list_lenght == 0) {
			return false;
		}

		if($waiting_list_count < $waiting_list_lenght) {
			return false;
		}

		return true;
	}
	
	public function canBookCourseForOther($a_user_id, $a_other_id) {
		require_once("Services/GEV/CourseSearch/classes/class.gevCourseSearch.php");
		$crs_srch = gevCourseSearch::getInstance($a_user_id);
		return    $this->getBookingPermissions($a_user_id)->bookCourseForUser($a_other_id)
			   || in_array($a_other_id, $crs_srch->getEmployeeIdsForCourseSearch())
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
		$this->setWaitingListActive(false);
	}
	
	public function cancel() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$mails = new gevCrsAutoMails($this->crs_id);
		
		// Cancel participants
		$this->cleanWaitingList();

		$participants = $this->getParticipants();
		foreach($participants as $participant) {
			$this->getBookings()->cancelWithoutCosts($participant);
		}
		$mails->send("training_cancelled_participant_info", $participants);

		//Send cancel mail to trainer						
		$trainers = $this->getTrainers();
		$mails->send("training_cancelled_trainer_info",$trainers);
		
		// mails will be send by GEVMailingPlugin

		// Send mail C08 to hotel
		$mails->send("training_cancelled");
		
		// Send mail C16 to material storage
		$mails->send("cancellation_mail_for_storage");
		
		// Set training offline
		$crs = $this->getCourse();
		$crs->setOfflineStatus(true);
		// Mark this course as cancelled
		$this->setIsCancelled(true);
		if(!preg_match('/\w+'.$this->gLng->txt("gev_course_is_cancelled_suffix").'$/',$crs->getTitle())) {
			$crs->setTitle($this->getTitle().$this->gLng->txt("gev_course_is_cancelled_suffix"));
		}
		$crs->update();

		// Remove Trainers
		$membership = $this->getCourse()->getMembersObject();
		foreach($trainers as $trainer) {
			$membership->delete($trainer);
		}

		// Delete VC Assignments
		$this->deleteVCAssignment();

		$this->gRbacadmin->revokePermission($this->getRefId());
	}

	public function cancelTrainer(array $trainer_id) {
		$membership = $this->getCourse()->getMembersObject();
		foreach($trainer_id as $trainer) {
			$membership->delete($trainer);
		}
	}
	
	// Participation
	
	public function canModifyParticipationStatus($a_user_id) {
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php");
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusPermissions.php");
		$ps_helper = ilParticipationStatusHelper::getInstance($this->getCourse());
		$ps = ilParticipationStatus::getInstance($this->getCourse());
		$ps_permissions = ilParticipationStatusPermissions::getInstance($this->getCourse(), $a_user_id);
		return     $ps_helper->isStartForParticipationStatusSettingReached()
				&& (    (   $ps->getProcessState() == ilParticipationStatus::STATE_SET 
						 && $ps_permissions->setParticipationStatus())
					 || (   $ps->getProcessState() == ilParticipationStatus::STATE_REVIEW 
					 	 && $ps_permissions->reviewParticipationStatus())
				   );
	}
	
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
		$res = $this->gIldb->query( "SELECT rol_id FROM rbac_ua "
								." WHERE usr_id = ".$this->gIldb->quote($a_user_id)
								."   AND ".$this->gIldb->in("rol_id", array_keys($roles), false, "integer"));
		if ($rec = $this->gIldb->fetchAssoc($res)) {
			return $roles[$rec["rol_id"]];
		}
		return null;
	}

	public function getAllFunctionsOfUser($a_user_id) {
		//this is a check for ROLES, not for function.
		//i.e. member has canceled, but is still member of course...
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$utils = gevRoleUtils::getInstance();
		$roles = $this->getLocalRoles();
		$res = $this->gIldb->query( "SELECT rol_id FROM rbac_ua "
								." WHERE usr_id = ".$this->gIldb->quote($a_user_id)
								."   AND ".$this->gIldb->in("rol_id", array_keys($roles), false, "integer"));
		$return = array();
		if ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $roles[$rec["rol_id"]];
		}
		return $return;
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
	
	public function userFullfilledPrecondition($a_user_id) {
		require_once("Services/AccessControl/classes/class.ilConditionHandler.php");
		$ref_id = gevObjectUtils::getRefId($this->crs_id);
		return ilConditionHandler::_checkAllConditionsOfTarget($ref_id, $this->crs_id, "crs", $a_user_id);
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
		   && $this->isCancelDeadlineExpired()
		   && $bill_utils->getNonFinalizedBillForCourseAndUser($this->crs_id, $a_user_id) !== null
		   ) {
			$action = $this->gLng->txt("gev_costly_cancellation_action");
		}
		else {
			$action = $this->gLng->txt("gev_free_cancellation_action");
		}
		
		$title = new catTitleGUI("gev_cancellation_title", "gev_cancellation_subtitle", "GEV_img/ico-head-trash.png");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->getTitle());
		$this->gCtrl->setParameter($a_gui, "crs_id", $this->crs_id);
		$form->setFormAction($this->gCtrl->getFormAction($a_gui));
		$this->gCtrl->clearParameters($a_gui, "crs_id", $this->crs_id);
		$form->addCommandButton("view", $this->gLng->txt("cancel"));
		$form->addCommandButton("finalizeCancellation", $action);
		
		$officer_contact = $this->getTrainingOfficerContactInfo();

		$vals = array(
			  array( $this->gLng->txt("gev_course_id")
				   , true
				   , $this->getCustomId()
				   )
			, array( $this->gLng->txt("gev_course_type")
				   , true
				   , implode(", ", $this->getType())
				   )
			, array( $this->gLng->txt("appointment")
				   , true
				   , $this->getFormattedAppointment()
				   )
			, array( $this->gLng->txt("gev_provider")
				   , $prv?true:false
				   , $prv?$prv->getTitle():""
				   )
			, array( $this->gLng->txt("gev_venue")
				   , $ven?true:false
				   , $ven?$ven->getTitle():""
				   )
			, array( $this->gLng->txt("gev_instructor")
				   , true
				   , implode(", ",$this->getTrainers(true))
				   )
			, array( $this->gLng->txt("gev_free_cancellation_until")
				   , $status == ilCourseBooking::STATUS_BOOKED
				   , $this->getFormattedCancelDeadline()
				   )
			, array( $this->gLng->txt("gev_free_places")
				   , true
				   , $this->getFreePlaces()
				   )
			, array( $this->gLng->txt("gev_training_contact")
				   , $officer_contact
				   , $officer_contact
				   )
			, array( $this->gLng->txt("gev_overall_prize")
				   , ($bill !== null)
				   , $bill_utils->formatPrize(
				   			$bill !== null?$bill->getAmount():0
				   		)." &euro;"
				   	)
			, array( $this->gLng->txt("gev_credit_points")
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
			$field = new ilNonEditableValueGUI($this->gLng->txt("gev_cancellation_for"), "", true);
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

		$res = $ilDB->query("SELECT DISTINCT edu_program FROM hist_course WHERE edu_program NOT IN ('-empty-', '') AND hist_historic = 0 ORDER BY edu_program ASC");
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

		$res = $ilDB->query("SELECT DISTINCT type FROM hist_course WHERE type NOT IN ('-empty-', '') AND hist_historic = 0 ORDER BY type ASC");
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

		$res = $ilDB->query("SELECT DISTINCT template_title FROM hist_course WHERE template_title NOT IN  ('-empty-', '') AND hist_historic = 0 ORDER BY template_title ASC");
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

		$res = $ilDB->query("SELECT DISTINCT participation_status FROM hist_usercoursestatus WHERE participation_status != '-empty-' AND hist_historic = 0 ORDER BY participation_status ASC");
		self::$hist_participation_status = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			self::$hist_participation_status[] = $rec["participation_status"];
		}
		return self::$hist_participation_status;
	}





	public static function searchCourses($a_search_options, $a_offset,
								$a_limit, $a_order = "title", 
								$a_direction = "desc") {
		
		global $ilDB;
		global $ilUser;
		
		$gev_set = gevSettings::getInstance();
		$db = &$ilDB;

		if ($a_order == "") {
			$a_order = "title";
		}

		if ($a_direction !== "asc" && $a_direction !== "desc") {
			throw new Exception("gevCourseUtils::searchCourses: unknown direction '".$a_direction."'");
		}
		
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
				 "   oref.deleted IS NULL".
				 "   AND is_template.value = ".$db->quote("Nein", "text").

				 $additional_where;

		$res = $db->query($query);
		$crss = array();
		while($val = $db->fetchAssoc($res)) {
			$crss[] = $val["obj_id"];
		}
		$count = count($crss);
	
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
		if ($a_order !== "start_date") {
			$addsql .= ", start_date DESC ";
		}
		$addsql .= " LIMIT ".$db->quote($a_limit, "integer")." OFFSET ".$db->quote($a_offset, "integer");

		$city_amd_id = $gev_set->getAMDFieldId(gevSettings::ORG_AMD_CITY);
		$info = gevAMDUtils::getInstance()->getTable(
				$crss, 
				$crs_amd, 
				array("CONCAT(od_city.title, ', ', city.value) as location"), 
				array(" LEFT JOIN object_data od_city ".
					  "   ON od_city.obj_id = amd2.value "
					 ," LEFT JOIN adv_md_values_text city ".
					  "   ON city.field_id = ".$db->quote($city_amd_id, "integer").
					  "  AND city.obj_id = amd2.value "
					 ),
				$addsql
			);

		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Modules/Course/classes/class.ilObjCourseAccess.php");
		foreach ($info as $key => $value) {
			// TODO: This surely could be tweaked to be faster if there was no need
			// to instantiate the course to get booking information about it.
			$crs_utils = gevCourseUtils::getInstance($value["obj_id"]);
			$crs_ref = gevObjectUtils::getRefId($crs_utils->getCourse()->getId());
			
			$edit_lnk = "ilias.php?cmdClass=ilobjcoursegui&cmd=editInfo&baseClass=ilRepositoryGUI&ref_id=" .$crs_ref;

			$info[$key]["title"] = '<a href="'
									.$edit_lnk
									.'">'
									.$info[$key]["title"]
									.'</a>';

			$trainer = $crs_utils->getMainTrainer();
			if($trainer){
				$info[$key]["trainer"] = $trainer->getFullName();
			} else {
				$info[$key]["trainer"] = '-';
			}

			$mbr_booked_userids = $crs_utils->getParticipants();
			$mbr_waiting_userids = $crs_utils->getWaitingMembers($id);

			$mbr_booked = count($mbr_booked_userids);
			$mbr_waiting = count($mbr_waiting_userids);

			$info[$key]["members"] = $mbr_booked .' (' .$mbr_waiting .')'
									.' / ' .$info[$key]["min_participants"] .'-' .$info[$key]["max_participants"];
			
			$info[$key]["date"] = $info[$key]["start_date"] .'-' .$info[$key]["end_date"];
			
			$info[$key]["status"] = ilObjCourseAccess::_isActivated($value["obj_id"]) ? 'online' : 'offline';
		}

		return array("count" => $count, "info" => $info);
	}

	// setting of permissions
	public function grantPermissionsFor($a_role_name, $a_permissions) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		
		$crs = $this->getCourse();
		$ref_id = gevObjectUtils::getRefId($crs->getId());
		$crs->setRefId($ref_id);

		if ($a_role_name == "tutor" || $a_role_name == "trainer") {
			$role_ids = array($crs->getDefaultTutorRole());
		}
		elseif ($a_role_name == "member") {
			$role_ids = array($crs->getDefaultMemberRole());
		}
		elseif ($a_role_name == "admin") {
			$role_ids = array($crs->getDefaultAdminRole());
		}
		else {
			global $rbacreview;

			// get a map $rol_id => (map with role_info) 
			$roles = $rbacreview->getParentRoleIds($ref_id);

			$role_ids = array();

			foreach ($roles as $id => $info) {
				if ($info["title"] == $a_role_name) {
					$role_ids[] = $id;
				}
			}
		}
		
		foreach ($role_ids as $role_id) {
			$cur_ops = $this->gRbacreview->getRoleOperationsOnObject($role_id, $ref_id);
			$grant_ops = ilRbacReview::_getOperationIdsByName($a_permissions);
			$new_ops = array_unique(array_merge($grant_ops, $cur_ops));
			$this->gRbacadmin->revokePermission($ref_id, $role_id);
			$this->gRbacadmin->grantPermission($role_id, $new_ops, $ref_id);
		}
	}
	
	public function revokePermissionsOf($a_role_name, $a_permissions) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		
		$crs = $this->getCourse();
		$ref_id = gevObjectUtils::getRefId($crs->getId());
		$crs->setRefId($ref_id);

		if ($a_role_name == "tutor" || $a_role_name == "trainer") {
			$role_ids = array($crs->getDefaultTutorRole());
		}
		elseif ($a_role_name == "member") {
			$role_ids = array($crs->getDefaultMemberRole());
		}
		elseif ($a_role_name == "admin") {
			$role_ids = array($crs->getDefaultAdminRole());
		}
		else {
			global $rbacreview;

			// get a map $rol_id => (map with role_info) 
			$roles = $rbacreview->getParentRoleIds($ref_id);

			$role_ids = array();

			foreach ($roles as $id => $info) {
				if ($info["title"] == $a_role_name) {
					$role_ids[] = $id;
				}
			}
		}

		foreach ($role_ids as $role_id) {
			$cur_ops = $this->gRbacreview->getRoleOperationsOnObject($role_id, $ref_id);
			$grant_ops = ilRbacReview::_getOperationIdsByName($a_permissions);
			$new_ops = array_diff($cur_ops, $grant_ops);
			$this->gRbacadmin->revokePermission($ref_id, $role_id);
			$this->gRbacadmin->grantPermission($role_id, $new_ops, $ref_id);
		}
	}

	static public function grantPermissionsForAllCoursesBelow($a_ref_id, $a_role_name, $a_permissions) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");

		$children = self::getAllCoursesBelow(array($a_ref_id));
		foreach($children as $child) {
			$crs_utils = gevCourseUtils::getInstance($child["obj_id"]);
			$crs_utils->grantPermissionsFor($a_role_name, $a_permissions);
		}
	}

	static public function revokePermissionsForAllCoursesBelow($a_ref_id, $a_role_name, $a_permissions) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");

		$children = self::getAllCoursesBelow(array($a_ref_id));
		foreach($children as $child) {
			$crs_utils = gevCourseUtils::getInstance($child["obj_id"]);
			$crs_utils->revokePermissionsOf($a_role_name, $a_permissions);
		}
	}

	static public function getAllCoursesBelow($a_ref_ids) {
		global $ilDB;
		
		$res = $ilDB->query(
			 "SELECT DISTINCT od.obj_id obj_id, c.child ref_id "
			." FROM tree p"
			." RIGHT JOIN tree c ON c.lft > p.lft AND c.rgt < p.rgt AND c.tree = p.tree"
			." LEFT JOIN object_reference oref ON oref.ref_id = c.child"
			." LEFT JOIN object_data od ON od.obj_id = oref.obj_id"
			." WHERE ".$ilDB->in("p.child", $a_ref_ids, false, "integer")
			."   AND od.type = 'crs'"
			);
			
		$ret = array();
		while($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec;
		}
		return $ret;
	}

	static public function timeWithinCourse($timestamp, $ct_minutes,
		ilDateTime $a_start_date, ilDateTime $a_end_date,
		array $a_crs_schedule) {
		$start_time = explode(":",explode("-",$a_crs_schedule[0])[0]);
		$end_time =  explode(":",explode("-",$a_crs_schedule[count($a_crs_schedule)-1])[1]);
		$start_time = $start_time[0]*3600+($start_time[1]-$ct_minutes)*60;
		$end_time = $end_time[0]*3600+($end_time[1]+$ct_minutes)*60;

		$start = $a_start_date->getUnixTime();
		$end = $a_end_date->getUnixTime();

		return $start+$start_time < $timestamp 
			&& $end+$end_time > $timestamp;
	}

	static public function updateGDVTopic($gdv_topic,$a_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$obj_id = gevObjectUtils::getObjId($a_ref_id);
		$amd_utils = gevAMDUtils::getInstance();

		$amd_utils->setField($obj_id,gevSettings::CRS_AMD_GDV_TOPIC, $gdv_topic);
	}

	static public function updateTrainingCategory(array $categories, $a_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$obj_id = gevObjectUtils::getObjId($a_ref_id);
		$amd_utils = gevAMDUtils::getInstance();

		$amd_utils->setField($obj_id,gevSettings::CRS_AMD_TOPIC, $categories);
	}

	static public function updateTargetAndBenefits($targets,$a_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$obj_id = gevObjectUtils::getObjId($a_ref_id);
		$amd_utils = gevAMDUtils::getInstance();

		$amd_utils->setField($obj_id,gevSettings::CRS_AMD_GOALS,$targets);
	}

	static public function updateContent($content, $a_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$obj_id = gevObjectUtils::getObjId($a_ref_id);
		$amd_utils = gevAMDUtils::getInstance();

		$amd_utils->setField($obj_id,gevSettings::CRS_AMD_CONTENTS,$content);
	}

	static public function updateWP($a_wp, $a_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$obj_id = gevObjectUtils::getObjId($a_ref_id);
		$amd_utils = gevAMDUtils::getInstance();

		$amd_utils->setField($obj_id,gevSettings::CRS_AMD_CREDIT_POINTS,$a_wp);
	}

	public function userHasPermissionTo($user_id, $right_name) {
		return $this->gRbacsystem->checkAccessOfUser($user_id, $right_name, $this->getRefId());
	}

	public function userCanCancelCourse($user_id) {
		$now = @date("Y-m-d");
		$start_date = $this->getStartDate();
		if ($this->userHasPermissionTo($user_id, gevSettings::CANCEL_TRAINING) && 
			!$this->getCourse()->getOfflineStatus() && 
			$start_date !== null && 
			($start_date->get(IL_CAL_DATE) > $now || ($start_date->get(IL_CAL_DATE) == $now && !$this->isFinalized()))) 
		{
			return true;
		}

		return false;
	}

	public function getCustomAttachments() {
		$ret = array();

		$sql = "SELECT file_name\n"
				." FROM crs_custom_attachments\n"
				." WHERE obj_id = ".$this->gIldb->quote($this->crs_id, "integer");

		$res = $this->gIldb->query($sql);

		while($row = $this->gIldb->fetchAssoc($res)){
			$ret[] = $row["file_name"];
		}

		return $ret;
	}

	public function deleteCustomAttachment(array $files) {
		assert(is_array($files));

		$datatype_array = array("integer", "text");
		$sql = "DELETE FROM crs_custom_attachments WHERE obj_id = (?) AND file_name = (?)";
		$statement = $this->gIldb->prepare($sql,$datatype_array);
		
		$values = array($this->crs_id);
		
		foreach ($files as $file) {
			$values[1] = $file;
			$this->gIldb->execute($statement,$values);
		}
	}

	public function removeAttachmentsFromMail(array $files) {
		assert(is_array($files));

		require_once("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");
		$current_attachments = new gevCrsMailAttachments($this->crs_id);

		foreach ($files as $filename) {
			if(!$current_attachments->isAutogeneratedFile($filename)) {
				$current_attachments->removeAttachment($filename);
			}
		}
	}

	public function removePreselectedAttachments(array $functions, $files) {
		$invitation_mail_settings = new gevCrsInvitationMailSettings($this->crs_id);

		foreach ($functions as $key => $function) {
			$invitation_mail_settings->removeCustomAttachment($function, $files);
		}
		
		$invitation_mail_settings->save();
	}

	public function saveCustomAttachments(array $files) {
		assert(is_array($files));
		$datatype_array = array("integer", "text");
		$sql = "INSERT INTO crs_custom_attachments (obj_id,file_name) VALUES (?,?)";
		$statement = $this->gIldb->prepare($sql,$datatype_array);
		
		$values = array($this->crs_id);
		
		foreach ($files as $file) {
			$values[1] = $file;
			if(!$this->checkCustomAttachmentExists($file)) {
				$this->gIldb->execute($statement,$values);
			} else {
				throw new Exception("File exists");
			}
		}
	}

	protected function checkCustomAttachmentExists($file) {
		$query = "SELECT count(*) as cnt\n"
				." FROM crs_custom_attachments\n"
				." WHERE obj_id = ".$this->gIldb->quote($this->crs_id,"integer")."\n"
				." AND file_name = ".$this->gIldb->quote($file, "text");



		$res = $this->gIldb->query($query);
		$row = $this->gIldb->fetchAssoc($res);

		if($this->gIldb->numRows($res) > 0) {
			return $row["cnt"] > 0;
		}
		
		return false;
	}

	public function addAttachmentsToMailSingleFolder($files, $folder) {
		foreach ($files as $filename) {
			$this->gLog->write("File: ".$filename." Folder: ".$folder);
			$this->addAttachmentsToMail($filename,$folder."/".$filename);
		}
	}

	public function addAttachmentsToMailSeperateFolder($files) {
		foreach ($files as $file) {
			$this->addAttachmentsToMail($file["name"],$file["tmp_name"]);
		}
	}

	public function addAttachmentsToMail($filename, $folder) {
		$current_attachments = new gevCrsMailAttachments($this->crs_id);

		if(!$current_attachments->isAutogeneratedFile($filename)) {
			$current_attachments->addAttachment($filename, $folder);
		}
	}

	public function addPreselectedAttachments(array $functions, $files) {
		$invitation_mail_settings = new gevCrsInvitationMailSettings($this->crs_id);
		
		foreach ($functions as $key => $function) {
			$invitation_mail_settings->addCustomAttachments($function, $files);
		}
		$invitation_mail_settings->save();
	}

	public function getFunctionsForInvitationMails() {
		$roles = $this->getCustomRoles($this->crs_id);
		$ret = array($this->gLng->txt("crs_member"));
		$ret[] = $this->gLng->txt("crs_tutor");

		foreach($roles as $role) {
			$ret[] = $role["title"];
		}

		return $ret;
	}

	public function getAttachmentLinks($class_name) {
		$invitation_mail_settings = new gevCrsInvitationMailSettings($this->crs_id);

		$ret = array();
		foreach ($invitation_mail_settings->getAttachmentsFor("Mitglied") as $key => $value) {
			if(file_exists($value["path"])) {
				$this->gCtrl->setParameterByClass($class_name, "filename", $value["name"]);
				$this->gCtrl->setParameterByClass($class_name, "crs_id", $this->crs_id);
				$ret[] = '<a href="'.$this->gCtrl->getLinkTargetByClass($class_name, "deliverAttachment").'">'.$value["name"].'</a>';
				$this->gCtrl->clearParametersByClass($class_name);
			} else {
				$ret[] = $value["name"]." (Datei wurde nicht gefunden)";
			}
		}

		return $ret;
	}

	// delivery

	/**
	 * Deliver attachment (ha!)
	 */
	public function deliverAttachment($filename) {
		$mail_attachments = new gevCrsMailAttachments($this->crs_id);

		if ($mail_attachments->isAttachment($filename)) {
			$this->deliverAttachmentFile($filename, $mail_attachments->getPathTo($filename));
		}
	}


	/**
	 * Deliver file with correct mimetype (if that could be determined).
	 *
	 * ATTENTION: Exits after delivery.
	 *
	 * @param string $a_name The name for the delivery of the file.
	 * @param string $a_path The complete path to the file that should be
	 * 						 delivered.
	 */
	protected function deliverAttachmentFile($a_name, $a_path) {
		require_once("Services/Utilities/classes/class.ilFileUtils.php");

		$mimetype = ilFileUtils::_lookupMimeType($a_path);
		ilUtil::deliverFile($a_path, $a_name, $mimetype, false, false, true);
	}

	/**
	* Check if crs is a template and start and/or end date is defined
	*
	*/
	public function warningIfTemplateWithDates() {
		if($this->isStartAndEndDateSet()) {
			ilUtil::sendInfo($this->gLng->txt("gev_crs_tpl_dates_set"));
		}
	}

	/**
	* Gets the Categoryname X Level Above
	*
	* @param int 		$level
	* @return string
	*/
	public function getCategoryNameXLevelAbove($level) {
		$ret = "";
		$obj_ref_id = $this->getRefId();
		for($i = 0; $i < $level; $i++) {
			$obj_ref_id = $this->gTree->getParentId($obj_ref_id);
		}

		return ilObject::_lookupTitle(ilObject::_lookupObjId($obj_ref_id));
	}

	/**
	* gets all crs with amd field Template = "ja"
	*
	* @return array(obj_id => title)
	*/
	public static function getAllTemplates() {
		global $ilDB;
		$template_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$query = "SELECT od.obj_id, od.title\n"
				." FROM object_data od\n"
				." JOIN adv_md_values_text admt ON admt.obj_id = od.obj_id\n"
				."    AND admt.field_id = ".$ilDB->quote($template_field_id,"integer")."\n"
				." WHERE od.type = ".$ilDB->quote("crs", "text")."\n"
				."    AND admt.value = ".$ilDB->quote("Ja", "text")."\n";

		$res = $ilDB->query($query);
		$ret = array();

		while($row = $ilDB->fetchAssoc($res)) {
			$ret[$row["obj_id"]] = $row["title"];
		}

		return $ret;
	}
}