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

class gevCourseUtils {
	static $instances = array();
	
	protected function __construct($a_crs_id) {
		global $ilDB;
		
		$this->db = &$ilDB;
		
		$this->crs_id = $a_crs_id;
		$this->crs_obj = null;
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
	
	public function getCourse() {
		require_once("Modules/Course/classes/class.ilObjCourse.php");
		
		if ($this->crs_obj === null) {
			$this->crs_obj = new ilObjCourse($this->crs_id, false);
		}
		
		return $this->crs_obj;
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
	
	public function getFormattedStartDate() {
		ilDatePresentation::setUseRelativeDates(false);
		$val = ilDatePresentation::formatDate($this->crs_utils->getStartDate());
		ilDatePresentation::setUseRelativeDates(true);
		return $val;
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
	
	public function getCancelDeadlineDate() {
		return self::mkDeadlineDate($this->getStartDate(), $this->getCancelDeadline());
	}
	
	public function getBookingDeadline() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_BOOKING_DEADLINE);
	}
	
	public function getBookingDeadlineDate() {
		return self::mkDeadlineDate($this->getStartDate(), $this->getBookingDeadline());
	}
	
	public function getCancelWaitingList() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_CANCEL_WAITING);
	}
	
	public function getCancelWaitingListDate() {
		return self::mkDeadlineDate($this->getStartDate(), $this->getCancelWaitingList());
	}
	
	public function getProviderId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_PROVIDER);
	}
	
	public function getVenueId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_VENUE);
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
	
	public function getAccomodationId() {
		return $this->amd->getField($this->crs_id, gevSettings::CRS_AMD_ACCOMODATION);
	}
	
	public function getAccomodation() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$id = $this->getAccomodationId();
		if ($id === null) {
			return null;
		}
		return gevOrgUnitUtils::getInstance($id);	
	}
	
	public function getTrainer() {
		// TODO: implement
		return "TBD";
	}
	
	public function getTrainingAdviser() {
		// TODO: implement
		return "TBD";
	}
	
	public function getDerivedCourseIds() {
		if (!$this->isTemplate()) {
			throw new Exception("gevCourseUtils::getDerivedCourseIds: this course is no template and thus has no derived courses.");
		}
		
	
	}
	
	
	// Memberlist creation
	
	public function deliverMemberList($a_hotel_list) {
		$this->buildMemberList(true, null, $a_hotel_list);
	}
	
	public function buildMemberList($a_send, $a_filename, $a_hotel_list) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		
		global $lng;

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
		
		if($a_hotel_list)
		{
			$columns[] = $lng->txt("gev_crs_book_overnight_details"); // #3764

			$worksheet->setColumn(4, 4, 50); // #4481
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
										( !$a_hotel_list 
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
				$worksheet->write($row, 3, $user_utils->getFirstname(), $format_wrap);
				
				if($a_hotel_list)
				{
					// vfstep3.1
					$worksheet->write($row, 4, $user_utils->getOvernightDetailsForCourse($this->crs_id), $format_wrap);

					//$txt[] = $lng->txt("vofue_crs_book_overnight_details").": ".$user_data["ov"];
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
		ilDatePresentation::setUseRelativeDates(false);
		
		$arr = array("Titel" => $this->getTitle()
					, "Untertitel" => $this->getSubtitle()
					, "Nummer der Maßnahme" => $this->getCustomId()
					, "Datum" => ilDatePresentation::formatPeriod($this->getStartDate(), $this->getEndDate())
					, "Veranstaltungsort" => $this->getVenueTitle()
					, "Trainer" => $this->getTrainer()
					, "Trainingsbetreuer" => $this->getTrainingAdviser()
					, "Bildungspunkte" => $this->getCreditPoints()
					);
		
		ilDatePresentation::setUseRelativeDates(true);
		return $arr;
	}

}

?>