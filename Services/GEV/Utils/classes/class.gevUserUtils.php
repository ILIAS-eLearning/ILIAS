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
require_once("Services/GEV/Utils/classes/class.gevDBVUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
require_once("Services/GEV/Utils/classes/class.gevGeneralUtils.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");


class gevUserUtils {
	static protected $instances = array();

	// Für diese Rollen wird bei der Selbstbuchung der Hinweis "Vorabendanreise 
	// mit Führungskraft klären" angezeigt.
	static $roles_with_prearrival_note = array(
		  "UA"
		, "HA 84"
		, "BA 84"
		, "Org PV 59"
		, "PV 59"
		, "ID MA"
		, "OD/FD/BD ID"
		, "OD/FD ID"
		, "BD ID"
		, "Agt-ID"
		, "VA 59"
		, "VA HGB 84"
		, "NFK"
		, "FDA"
		, "Azubi"
		, "DBV UVG"
		, "DBV EVG"
	);

	protected function __construct($a_user_id) {
		global $ilDB;
		global $ilAccess;
		global $lng;
		
		$this->user_id = $a_user_id;
		$this->gLng = $lng;
		$this->courseBookings = ilUserCourseBookings::getInstance($a_user_id);
		$this->gev_set = gevSettings::getInstance();
		$this->udf_utils = gevUDFUtils::getInstance();
		$this->db = &$ilDB;
		$this->access = &$ilAccess;
		$this->user_obj = null;
		$this->org_id = null;
		$this->direct_superior_ous = null;
		$this->direct_superior_ou_names = null;
		$this->superior_ous = null;
		$this->superior_ou_names = null;
		$this->edu_bio_ou_names = null;
		$this->edu_bio_ou_ref_ids_empl = null;
		$this->edu_bio_ou_ref_ids_all = null;
		$this->edu_bio_ou_ref_ids = null;
		$this->edu_bio_usr_ids = null;
		$this->employees_active = null;
		$this->employees_all = null;
		$this->employees_for_course_search = null;
		$this->employee_ids_for_course_search = null;
		$this->employees_for_booking_cancellations = null;
		$this->employee_ids_for_booking_cancellations = null;
		$this->employee_ids_for_booking_view = null;
		$this->employee_ous = null;
		$this->employees_ou_names = null;
		$this->od = false;
		
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
	
	public function getId() {
		return $this->user_id;
	}
	
	static public function getInstance($a_user_id) {
		if($a_user_id === null) {
			throw new Exception("gevUserUtils::getInstance: ".
								"No User-ID given.");
		}

		if(!self::userIdExists($a_user_id)) {
			throw new Exception("gevUserUtils::getInstance: ".
									"User with ID '".$a_user_id."' does not exist.");
		}

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

	static public function userIdExists($a_user_id) {
		global $ilDB;

		$sql = "SELECT usr_id FROM usr_data WHERE usr_id = ".$ilDB->quote($a_user_id, "integer");
		$res = $ilDB->query($sql);

		if($ilDB->numRows($res) == 0) {
			return false;
		}

		return true;
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

	CONST TEILGENOMMEN = 3;
	CONST FEHLT_ENTSCHULDIGT = 2;
	CONST FEHLT_OHNE_ABSAGE = 1;
	CONST NICHT_GESETZT = 0;
	CONST SONSTIGES = -1;

	public function coursesBefore($date) {
		$query = "SELECT row_id, crs_id, usr_id, participation_status\n"
				." ,CASE\n"
				."    WHEN participation_status = 'teilgenommen' THEN ".self::TEILGENOMMEN."\n"
				."    WHEN participation_status = 'fehlt entschuldigt' THEN ".self::FEHLT_ENTSCHULDIGT."\n"
				."    WHEN participation_status = 'fehlt ohne Absage' THEN ".self::FEHLT_OHNE_ABSAGE."\n"
				."    WHEN participation_status = 'nicht gesetzt' THEN ".self::NICHT_GESETZT."\n"
				."    ELSE ".self::SONSTIGES."\n"
				." END as participation_status_level\n"
				." FROM hist_usercoursestatus\n"
				." WHERE usr_id = ".$this->db->quote($this->user_id,"integer")."\n"
				." AND hist_historic = 0\n"
				." AND end_date < ".$this->db->quote($date,"text")."\n"
				." AND booking_status = ".$this->db->quote("gebucht","text")."\n";

		$ret = array();
		$res = $this->db->query($query);
		while($row = $this->db->fetchAssoc($res)) {
			array_push($ret,$row);
		}

		return $ret;
	}

	public function coursesAfter($date) {
		$query = "SELECT row_id, crs_id, usr_id, participation_status\n"
				." ,CASE\n"
				."    WHEN participation_status = 'teilgenommen' THEN ".self::TEILGENOMMEN."\n"
				."    WHEN participation_status = 'fehlt entschuldigt' THEN ".self::FEHLT_ENTSCHULDIGT."\n"
				."    WHEN participation_status = 'fehlt ohne Absage' THEN ".self::FEHLT_OHNE_ABSAGE."\n"
				."    WHEN participation_status = 'nicht gesetzt' THEN ".self::NICHT_GESETZT."\n"
				."    ELSE ".self::SONSTIGES."\n"
				." END as participation_status_level\n"
				." FROM hist_usercoursestatus\n"
				." WHERE usr_id = ".$this->db->quote($this->user_id,"integer")."\n"
				." AND hist_historic = 0\n"
				." AND begin_date > ".$this->db->quote($date,"text")."\n"
				." AND booking_status = ".$this->db->quote("gebucht","text")."\n";

		$ret = array();
		$res = $this->db->query($query);
		while($row = $this->db->fetchAssoc($res)) {
			array_push($ret,$row);
		}

		return $ret;
	}

	public function getEduBioLink() {
		return self::getEduBioLinkFor($this->user_id);
	}
	
	static public function getEduBioLinkFor($a_target_user_id) {
		global $ilCtrl;
		$ilCtrl->setParameterByClass("gevEduBiographyGUI", "target_user_id", $a_target_user_id);
		$link = $ilCtrl->getLinkTargetByClass("gevEduBiographyGUI", "view");
		$ilCtrl->clearParametersByClass("gevEduBiographyGUI");
		return $link;
	}

	public function filter_for_online_courses($ar){
		/*
		check, if course exists and is online;
		*/
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Modules/Course/classes/class.ilObjCourseAccess.php");		
		$ret = array();
		foreach ($ar as $crsid) {
			if(gevObjectUtils::checkObjExistence($crsid)){
				if(ilObjCourseAccess::_isActivated($crsid)) {
					$ret[] = $crsid;
				}
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
				 , gevSettings::CRS_AMD_ABSOLUTE_CANCEL_DEADLINE => "absolute_cancel_date"
				 , gevSettings::CRS_AMD_SCHEDULED_FOR		=> "scheduled_for"
				 , gevSettings::CRS_AMD_SCHEDULE			=> "crs_amd_schedule"
				 //, gevSettings::CRS_AMD_ => "title"
				 //, gevSettings::CRS_AMD_START_DATE => "status"
				 , gevSettings::CRS_AMD_TYPE 				=> "type"
				 , gevSettings::CRS_AMD_VENUE 				=> "location"
				 , gevSettings::CRS_AMD_VENUE_FREE_TEXT 	=> "location_free_text"
				 , gevSettings::CRS_AMD_CREDIT_POINTS 		=> "points"
				 , gevSettings::CRS_AMD_FEE					=> "fee"
				 , gevSettings::CRS_AMD_TARGET_GROUP_DESC	=> "target_group"
				 , gevSettings::CRS_AMD_TARGET_GROUP		=> "target_group_list"
				 , gevSettings::CRS_AMD_GOALS 				=> "goals"
				 , gevSettings::CRS_AMD_CONTENTS 			=> "content"
			);
		
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");
		$booked = array_diff($this->filter_for_online_courses($this->getBookedCourses()),
			$this->getCoursesWithStatusIn(array( ilParticipationStatus::STATUS_SUCCESSFUL
												,ilParticipationStatus::STATUS_ABSENT_EXCUSED
												,ilParticipationStatus::STATUS_ABSENT_NOT_EXCUSED)
											)
							);

		$booked_amd = gevAMDUtils::getInstance()->getTable($booked, $crs_amd);
		foreach ($booked_amd as $key => $value) {
			$booked_amd[$key]["status"] = ilCourseBooking::STATUS_BOOKED;
			$booked_amd[$key]["cancel_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																			 , $value["cancel_date"]
																			 );
			$booked_amd[$key]["absolute_cancel_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																					  , $value["absolute_cancel_date"]
																					  );
			// TODO: Push this to SQL-Statement.
			$orgu_utils = gevOrgUnitUtils::getInstance($value["location"]);
			$crs_utils = gevCourseUtils::getInstance($value["obj_id"]);
			$booked_amd[$key]["overnights"] = $this->getFormattedOvernightDetailsForCourse($crs_utils->getCourse());
			$booked_amd[$key]["location"] = $orgu_utils->getLongTitle();
			$booked_amd[$key]["fee"] = floatval($booked_amd[$key]["fee"]);
			$list = "";

			if(is_array($booked_amd[$key]["target_group_list"])) {
				foreach ($booked_amd[$key]["target_group_list"] as $val) {
					$list .= "<li>".$val."</li>";
				}
			}
			
			if($lis != "") {
				$booked_amd[$key]["target_group"] = "<ul>".$list."</ul>".$booked_amd[$key]["target_group"];
			}
		}


		$waiting = $this->getWaitingCourses();
		$waiting = $this->filter_for_online_courses($waiting);

		$waiting_amd = gevAMDUtils::getInstance()->getTable($waiting, $crs_amd);
		foreach ($waiting_amd as $key => $value) {
			$waiting_amd[$key]["status"] = ilCourseBooking::STATUS_WAITING;
			$waiting_amd[$key]["cancel_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																			  , $value["cancel_date"]
																			  );
			$waiting_amd[$key]["absolute_cancel_date"] = gevCourseUtils::mkDeadlineDate( $value["start_date"]
																					   , $value["absolute_cancel_date"]
																					   );
			
			$orgu_utils = gevOrgUnitUtils::getInstance($value["location"]);
			$crs_utils = gevCourseUtils::getInstance($value["obj_id"]);
			$waiting_amd[$key]["overnights"] = $this->getFormattedOvernightDetailsForCourse($crs_utils->getCourse());
			$waiting_amd[$key]["location"] = $orgu_utils->getLongTitle();
			$waiting_amd[$key]["fee"] = floatval($waiting_amd[$key]["fee"]);
			$list = "";
			foreach ($waiting_amd[$key]["target_group_list"] as $val) {
				$list .= "<li>".$val."</li>";
			}
			$waiting_amd[$key]["target_group"] = "<ul>".$list."</ul>".$waiting_amd[$key]["target_group"];
		}
		
		return array_merge($booked_amd, $waiting_amd);
	}
	
	protected function getCourseIdsWhereUserIs($roles, $search_opts = null) {
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$like_role = array();
		foreach ($roles as $role) {
			$like_role[] = "od.title LIKE ".$this->db->quote($role);
		}
		$like_role = implode(" OR ", $like_role);
		
		$tmplt_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$join_n_wheres = $this->createJoinNWheresFromSearchOpts($search_opts);

		$res = $this->db->query(
			 "SELECT oref.obj_id, oref.ref_id "
			."  FROM object_reference oref"
			."  JOIN object_data od ON od.type = 'role' AND ( ".$like_role ." )"
			."  JOIN rbac_fa fa ON fa.rol_id = od.obj_id"
			."  JOIN tree tr ON tr.child = fa.parent"
			."  JOIN rbac_ua ua ON ua.rol_id = od.obj_id"
			."  JOIN object_data od2 ON od2.obj_id = oref.obj_id"
			." LEFT JOIN adv_md_values_text is_template "
			."    ON oref.obj_id = is_template.obj_id "
			."   AND is_template.field_id = ".$this->db->quote($tmplt_field_id, "integer")
			.$join_n_wheres["joins"]
			." WHERE oref.ref_id = tr.parent"
			."   AND ua.usr_id = ".$this->db->quote($this->user_id, "integer")
			."   AND od2.type = 'crs'"
			."   AND oref.deleted IS NULL"
			."   AND is_template.value = 'Nein'"
			.$join_n_wheres["wheres"]
			);
		
		$crs_ids = array();
		while($rec = $this->db->fetchAssoc($res)) {
			//we need only one ref-id here
			$crs_ids[$rec['obj_id']] = $rec['ref_id'];
		}

		return $crs_ids;
	}

	protected function createJoinNWheresFromSearchOpts($search_opts) {
		if(!$search_opts) {
			return array("joins"=>"", "wheres"=>"");
		}

		$additional_join = "";
		$additional_where = "";
		if (array_key_exists("period", $search_opts)) {
			$start_date_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
			$end_date_field_id = $this->gev_set->getAMDFieldId(gevSettings::CRS_AMD_END_DATE);
			
			// this is knowledge from the course amd plugin!
			$additional_join .=
				" LEFT JOIN adv_md_values_date end_date\n".
				"   ON oref.obj_id = end_date.obj_id\n".
				"   AND end_date.field_id = ".$this->db->quote($end_date_field_id, "integer")."\n"
				;
			$additional_join .=
				" LEFT JOIN adv_md_values_date start_date\n".
				"   ON oref.obj_id = start_date.obj_id\n".
				"   AND start_date.field_id = ".$this->db->quote($start_date_field_id, "integer")."\n"
				;

			$additional_where .=
				" AND ( start_date.value <= ".$this->db->quote(date("Y-m-d", $search_opts["period"]["end"]))." \n".
				"       AND end_date.value >= ".$this->db->quote(date("Y-m-d", $search_opts["period"]["start"]))." ) \n";
		}

		return array("joins"=>$additional_join, "wheres"=>$additional_where);
	}

	public function getMyAppointmentsCourseInformation($a_order_field = null, $a_order_direction = null) {
			// used by gevMyTrainingsApTable, i.e.
		
			if ((!$a_order_field && $a_order_direction) || ($a_order_field && !$a_order_direction)) {
				throw new Exception("gevUserUtils::getMyAppointmentsCourseInformation: ".
									"You need to set bost: order_field and order_direction.");
			}
			
			if ($a_order_direction) {
				$a_order_direction = strtoupper($a_order_direction);
				if (!in_array($a_order_direction, array("ASC", "DESC"))) {
					throw new Exception("gevUserUtils::getMyAppointmentsCourseInformation: ".
										"order_direction must be ASC or DESC.");
				}
			}
			
			//require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
			require_once("Services/TEP/classes/class.ilTEPCourseEntries.php");
			require_once "Modules/Course/classes/class.ilObjCourse.php";
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");
			require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php");
			require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusPermissions.php");
			
			$crss = $this->getCourseIdsWhereUserIs(gevSettings::$TUTOR_ROLES);
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
			);
			
			if ($a_order_field) {
				$order_sql = " ORDER BY ".$this->db->quoteIdentifier($a_order_field)." ".$a_order_direction;
			}
			else {
				$order_sql = "";
			}
			
			$crss_amd = gevAMDUtils::getInstance()->getTable($crss_ids, $crs_amd, array("pstatus.state pstate"),
				// Join over participation status table to remove courses, where state is already
				// finalized
				array(" LEFT JOIN crs_pstatus_crs pstatus ON pstatus.crs_id = od.obj_id "),
				" AND ( pstatus.state != ".$this->db->quote(ilParticipationStatus::STATE_FINALIZED, "integer").
			    "       OR pstatus.state IS NULL) ".$order_sql
				);

			$ret = array();

			foreach ($crss_amd as $id => $entry) {
				$entry['crs_ref_id'] = $crss[$id];

				$crs_utils = gevCourseUtils::getInstance($id);
				$orgu_utils = gevOrgUnitUtils::getInstance($entry["location"]);
				$ps_helper = ilParticipationStatusHelper::getInstance($crs_utils->getCourse());
				$ps_permission = ilParticipationStatusPermissions::getInstance($crs_utils->getCourse(), $this->user_id);

				$entry["location"] = $orgu_utils->getLongTitle();

				if($entry['start_date'] && $entry['end_date']) {
					$crs_obj = new ilObjCourse($crss[$id]);
					$tep_crsentries = ilTEPCourseEntries::getInstance($crs_obj);
					$tep_opdays_inst = $tep_crsentries->getOperationsDaysInstance();
					$tep_opdays = $tep_opdays_inst->getDaysForUser($this->user_id);
				} else {
					$tep_opdays =array();
				}
				
				$entry['mbr_booked_userids'] = $crs_utils->getParticipants();
				$entry['mbr_booked'] = count($entry['mbr_booked_userids']);
				$entry['mbr_waiting_userids'] = $crs_utils->getWaitingMembers($id);
				$entry['mbr_waiting'] = count($entry['mbr_waiting_userids']);
				$entry['apdays'] = $tep_opdays;
				//$entry['category'] = '-';
				
				$entry['may_finalize'] = $crs_utils->canModifyParticipationStatus($this->user_id);

				$ret[$id] = $entry;
			}

			//sort?
			return $ret;
	}

	public function getEmployeeIdsForBookingView() {
		if ($this->employee_ids_for_booking_view) {
			return $this->employee_ids_for_booking_view;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

		// we need the employees in those ous
		$_d_ous = $this->getOrgUnitsWhereUserCanViewEmployeeBookings();
		// we need the employees in those ous and everyone in the ous
		// below those.
		$_r_ous = $this->getOrgUnitsWhereUserCanViewEmployeeBookingsRecursive();
		
		$e_ous = array_merge($_d_ous, $_r_ous);
		$a_ous = array();
		foreach(gevOrgUnitUtils::getAllChildren($_r_ous) as $val) {
			$a_ous[] = $val["ref_id"];
		}
		
		$e_ids = array_unique(array_merge( gevOrgUnitUtils::getEmployeesIn($e_ous)
										 , gevOrgUnitUtils::getAllPeopleIn($a_ous)
										 )
							 );
		$this->employee_ids_for_booking_view = $e_ids;
		
		return $e_ids;
	}
	
	public function getEmployeeIdsForBookingCancellations() {
		if ($this->employee_ids_for_booking_cancellations) {
			return $this->employee_ids_for_booking_cancellations;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

		// we need the employees in those ous
		$_d_ous = $this->getOrgUnitsWhereUserCanCancelEmployeeBookings();
		// we need the employees in those ous and everyone in the ous
		// below those.
		$_r_ous = $this->getOrgUnitsWhereUserCanCancelEmployeeBookingsRecursive();
		
		$e_ous = array_merge($_d_ous, $_r_ous);
		$a_ous = array();
		foreach(gevOrgUnitUtils::getAllChildren($_r_ous) as $val) {
			$a_ous[] = $val["ref_id"];
		}
		
		$e_ids = array_unique(array_merge( gevOrgUnitUtils::getEmployeesIn($e_ous)
										 , gevOrgUnitUtils::getAllPeopleIn($a_ous)
										 )
							 );
		$this->employee_ids_for_booking_cancellations = $e_ids;
		
		return $e_ids;
	}
	
	public function getEmployeesForBookingCancellations() {
		if ($this->employees_for_booking_cancellations) {
			return $this->employees_for_booking_cancellations;
		}

		$e_ids = $this->getEmployeeIdsForBookingCancellations();
		
		$res = $this->db->query( "SELECT usr_id, firstname, lastname"
								." FROM usr_data "
								." WHERE ".$this->db->in("usr_id", $e_ids, false, "integer")
								);
		
		$this->employees_for_booking_cancellations = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$this->employees_for_booking_cancellations[] = $rec;
		}
		
		return $this->employees_for_booking_cancellations;
	}

	public function isProfileComplete() {
		$wbd = gevWBD::getInstance($this->getId());
		if (!$wbd->forceWBDUserProfileFields()) {
			return true;
		}
		require_once("Services/GEV/Desktop/classes/class.gevUserProfileGUI.php");
		$email = $this->getEmail();
		$mobile = $this->getMobilePhone();
		$bday = $this->getUser()->getBirthday();
		$street = $this->getUser()->getStreet();
		$city = $this->getUser()->getCity();
		$zipcode = $this->getUser()->getZipcode();
		
		return $email && $mobile && preg_match(gevUserProfileGUI::$telno_regexp, $mobile)
				&& $mobile && $bday && $city && $zipcode;
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
	
	static public function getFullNames($a_user_ids) {
		global $ilDB;
		
		$query = "SELECT usr_id, CONCAT(lastname, ', ', firstname) as fullname"
				."  FROM usr_data"
				." WHERE ".$ilDB->in("usr_id", $a_user_ids, false, "integer");
		$res = $ilDB->query($query);
		
		$ret = array();
		while($rec = $ilDB->fetchAssoc($res)) {
			$ret[$rec["usr_id"]] = $rec["fullname"];
		}
		return $ret;
	}
	
	public function getEMail() {
		return $this->getUser()->getEmail();
	}
	
	public function setEMail($email) {
		return $this->getUser()->setEmail($email);
	}
	
	public function getOrgUnitId() {
		if ($this->orgu_id === null) {
			$query = "SELECT oref.obj_id FROM object_data od "
					." JOIN rbac_ua ua ON od.obj_id = ua.rol_id "
					." JOIN object_reference oref ON oref.ref_id = SUBSTR(od.title, 18) "
					." WHERE od.type = 'role' " 
					." AND ua.usr_id = ".$this->db->quote($this->user_id, "integer")
					." AND od.title LIKE 'il_orgu_employee_%' "
					." AND oref.deleted IS NULL"
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
						." AND oref.deleted IS NULL"
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
	
	public function getODTitle() {
		$od = $this->getOD();
		if ($od === null) {
			return "";
		}
		return $od["title"];
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
	
	public function getADPNumberGEV() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_ADP_GEV_NUMBER);
	}

	public function setADPNumberGEV($a_adp) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_ADP_GEV_NUMBER, $a_adp);
	}	

	public function getADPNumberVFS() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_ADP_VFS_NUMBER);
	}
	
	public function setADPNumberVFS($a_adp) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_ADP_VFS_NUMBER, $a_adp);
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

	public function getAgentKeyVFS() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_AGENT_KEY_VFS);
	}
	
	public function setAgentKeyVFS($a_key) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_AGENT_KEY_VFS, $a_key);
	}

	public function getAgentPositionVFS() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_AGENT_POSITION_VFS);
	}
	
	public function setAgentPositionVFS($a_key) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_AGENT_POSITION_VFS, $a_key);
	}

	
	/*
	public function getCompanyTitle() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_COMPANY_TITLE);
	}
	
	public function setCompanyTitle($a_title) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_COMPANY_TITLE, $a_title);
	}
	*/
	
	public function getCompanyName() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_COMPANY_NAME, $a_name);
	}

	public function setCompanyName($a_name) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_COMPANY_NAME, $a_name);
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
	

	public function getMobilePhone() {
		return $this->getUser()->getPhoneMobile();
	}
	public function setMobilePhone($a_phone) {
		return $this->getUser()->setPhoneMobile(a_phone);
	}

	/*
	public function getPrivatePhone() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_PHONE);
	}
	public function setPrivatePhone($a_phone) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_PHONE, $a_phone);
	}
	
	public function getPrivateState() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_STATE);
	}
	
	public function setPrivateState($a_state) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_STATE, $a_state);
	}
	
	
	public function getPrivateFax() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PRIV_FAX);
	}
	
	public function setPrivateFax($a_fax) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PRIV_FAX, $a_fax);
	}
	*/
	
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

	public function isExitDatePassed() {
		$now = date("Y-m-d");
		$exit_date = $this->getExitDate();

		if(!$exit_date) {
			return false;
		}

		if($now > $exit_date) {
			return true;
		}

		return false;
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


	public function getFormattedContactInfo() {
		$name = $this->getFullName();
		$phone = $this->getUser()->getPhoneOffice();
		$email = $this->getEmail();
		
		if (!$phone && !$email) {
			return $name;
		}
		
		if ($phone) {
			if ($email) {
				return $name." ($phone, $email)";
			}
			return $name." ($phone)";
		}
		return $name." ($email)";
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
	
	public function paysPrearrival() {
		return !$this->hasRoleIn(gevSettings::$NO_PREARRIVAL_PAYMENT_ROLES);
	}

	public function isVFS() {
		return $this->hasRoleIn(array('VFS'));
	}
	
	public function isExpressUser() {
		return $this->hasRoleIn(array("ExpressUser"));
	}
	
	public function getIDHGBAADStatus() {
		$roles = $this->getGlobalRoles();
		foreach ($roles as $role) {
			$title = ilObject::_lookupTitle($role);
			$status = gevSettings::$IDHGBAAD_STATUS_MAPPING[$title];
			if ($status !== null) {
				return $status;
			}
		}
		return "";
	}

	public function getAllIDHGBAADStatus() {
		$roles = $this->getGlobalRoles();
		$return = array();
		foreach ($roles as $role) {
			$title = ilObject::_lookupTitle($role);
			$status = gevSettings::$IDHGBAAD_STATUS_MAPPING[$title];
			if ($status !== null) {
				$return[] = $status;
			}
		}
		return $return;
	}

	public function isNA() {
		return $this->hasRoleIn(array("NA"));
	}
	
	public function getNAAdviserUtils() {
		if (!$this->isNA()) {
			throw new Exception("User ".$this->user_id." is no NA.");
		}
		
		require_once("Services/GEV/Utils/classes/class.gevNAUtils.php");
		$adviser_id = gevNAUtils::getInstance()->getAdviserOf($this->user_id);
		if ($adviser_id === null) {
			return null;
		}
		
		return gevUserUtils::getInstance($adviser_id);
	}

	public function isUVGDBV() {
		return $this->hasRoleIn(array("DBV UVG"));
		// TODO: implement this correctly
		//return true;
	}

	public function getOD() {
		if ($this->od !== false) {
			return $this->od;
		}
		
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		
		if (!$this->isNA()) {
			$ous = $tree->getOrgUnitOfUser($this->user_id);
		}
		else {
			require_once("Services/GEV/Utils/classes/class.gevNAUtils.php");
			$ous = $tree->getOrgUnitOfUser(gevNAUtils::getInstance()->getAdviserOf($this->user_id));
		}
		foreach($ous as $ou_ref) {
			while ($ou_ref !== null) {
				$ou_id = ilObject::_lookupObjectId($ou_ref);
				$title = ilObject::_lookupTitle($ou_id);
				if (preg_match("/(Organisationsdirektion|OD).*/", $title)) {
					$this->od = array( "obj_id" => $ou_id
									 , "title" => $title
									 );
					return $this->od;
				}
				$ou_ref = $tree->getParent($ou_ref);
			}
		}
		
		$this->od = null;
		return $this->od;
	}

	// Soll für den Benutzer  bei der Selbstbuchung der Hinweis "Vorabendanreise 
	// mit Führungskraft klären" angezeigt werden?
	public function showPrearrivalNoteInBooking() {
		return $this->hasRoleIn(gevUserUtils::$roles_with_prearrival_note);
	}
	
	public function isAdmin() {
		// root
		if ($this->user_id == 6) {
			return true;
		}
		
		return $this->hasRoleIn(gevSettings::$ADMIN_ROLES);
	}

	public function isSystemAdmin() {
		return $this->hasRoleIn(gevSettings::$SYSTEM_ADMIN_ROLES);
	}
	
	public function getGlobalRoles() {
		return gevRoleUtils::getInstance()->getGlobalRolesOf($this->user_id);
	}
	
	public function hasRoleIn($a_roles) {
		$roles = $this->getGlobalRoles();

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

	public function getAllFunctionsAtCourse($a_crs_id) {
		return gevCourseUtils::getInstance($a_crs_id)->getAllFunctionsOfUser($this->user_id);
	}
	
	public function hasFullfilledPreconditionOf($a_crs_id) {
		return gevCourseUtils::getInstance($a_crs_id)->userFullfilledPrecondition($this->user_id);
	}
	
	public function getOvernightDetailsForCourse(ilObjCourse $a_crs) {
		require_once("Services/Accomodations/classes/class.ilAccomodations.php");
		return ilAccomodations::getInstance($a_crs)
							  ->getAccomodationsOfUser($this->user_id);
	}
	
	public function getFormattedOvernightDetailsForCourse(ilObjCourse $a_crs) {
		return gevGeneralUtils::foldConsecutiveOvernights($this->getOvernightDetailsForCourse($a_crs));
	}
	
	public function getOvernightAmountForCourse(ilObjCourse $a_crs) {
		return count($this->getOvernightDetailsForCourse($a_crs));
	}
	
	public function getUserWhoBookedAtCourse($a_crs_id) {
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		if (!array_key_exists($a_crs_id, $this->users_who_booked_at_course)) {
			$bk_info = ilCourseBooking::getUserData($a_crs_id, $this->user_id);
			$this->users_who_booked_at_course[$a_crs_id] 
				= self::userIdExists($bk_info["status_changed_by"]) ? new ilObjUser($bk_info["status_changed_by"]) : null;
		}
		return $this->users_who_booked_at_course[$a_crs_id];
	}
	
	public function getFirstnameOfUserWhoBookedAtCourse($a_crs_id) {
		$return = $this->getUserWhoBookedAtCourse($a_crs_id) 
			? $this->getUserWhoBookedAtCourse($a_crs_id)->getFirstname()
			: '';
		return $return;
	}
	
	public function getLastnameOfUserWhoBookedAtCourse($a_crs_id) {
		$return = $this->getUserWhoBookedAtCourse($a_crs_id) 
			? $this->getUserWhoBookedAtCourse($a_crs_id)->getLastname()
			: $this->gLng->txt("gev_deleted_user");
		return $return;
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
	
	/**
	*	Get all courses where the participation status is set for user.
	*/
	public function getCoursesWithStatusIn (array $stati) {
		$query = 	"SELECT crs_id FROM crs_pstatus_usr WHERE "
					."	".$this->db->in('status', $stati, false, 'integer')
					."	AND user_id = ".$this->db->quote($this->user_id, "integer");
		$res = $this->db->query($query);
		$return = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$return[] = $rec["crs_id"];
		}
		return $return;
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
	
	public function isSuperior() {
		return count($this->getOrgUnitsWhereUserIsDirectSuperior()) > 0;
	}
	
	public function isSuperiorOf($a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		return in_array($this->user_id, gevOrgUnitUtils::getSuperiorsOfUser($a_user_id));
	}
	
	static public function removeInactiveUsers($a_usr_ids) {
		global $ilDB;
		$res = $ilDB->query("SELECT usr_id "

						   ."  FROM usr_data"
						   ." WHERE ".$ilDB->in("usr_id", $a_usr_ids, false, "integer")
						   ."   AND active = 1"
						   );
		$ret = array();
		while($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec["usr_id"];
		}
		return $ret;
	}
	
	public function getDirectSuperiors() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();

		// This starts with all the org units the user is member in.
		// During the loop we might fill this array with more org units
		// if we could not find any superiors for the user in them.
		$orgus = array_values($tree->getOrgUnitOfUser($this->user_id));

		if (count($orgus) == 0) {
			return array();
		}

		$the_superiors = array();

		$i = -1;
		$initial_amount = count($orgus);
		// We need to check this on every loop as the amount of orgus might change
		// during looping.
		while ($i < count($orgus)) {
			$i++;
			$ref_id = $orgus[$i];

			// Reached the top of the tree.
			if (!$ref_id || $ref_id == ROOT_FOLDER_ID) {
				continue;
			}

			$superiors = $tree->getSuperiors($ref_id);
			$user_is_superior = in_array($this->user_id, $superiors);
			$in_initial_orgus = $i < $initial_amount;

			// I always need to go one org unit up if we are in the original
			// orgu and the user is superior there.
			if ( $in_initial_orgus && $user_is_superior) {
				$orgus[] = $tree->getParent($ref_id);
			}

			// Skip the orgu if there are no superiors there.
			if ( count($superiors) == 0
			|| (   $in_initial_orgus
				// This is only about the org units the user actually is a member of
				&& $user_is_superior
				// If a user is an employee and a superior in one orgunit, he
				// actually seem to be his own superior.
				&& !in_array($this->user_id, $tree->getEmployees($ref_id)))
			) {
				$orgus[] = $tree->getParent($ref_id);
				continue;
			}

			$the_superiors[] = $superiors;
		}

		$the_superiors = call_user_func_array("array_merge", $the_superiors);

		return gevUserUtils::removeInactiveUsers(array_unique($the_superiors));
	}
	
	public function isEmployeeOf($a_user_id) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		// propably faster then checking the employees of this->user
		return in_array($this->user_id, gevUserUtils::getInstance($a_user_id)->getEmployees());
	}
	
	// returns array containing entries with obj_id and ref_id
	public function getOrgUnitsWhereUserIsDirectSuperior() {
		if ($this->direct_superior_ous !== null) {
			return $this->direct_superior_ous;
		}
		
		$like_role = array();
		foreach (gevSettings::$SUPERIOR_ROLES as $role) {
			$like_role[] = "od.title LIKE ".$this->db->quote($role);
		}
		$like_role = implode(" OR ", $like_role);
		
		$res = $this->db->query(
			 "SELECT oref.obj_id, oref.ref_id "
			."  FROM object_reference oref"
			."  JOIN object_data od ON od.type = 'role' AND ( ".$like_role ." )"
			."  JOIN rbac_fa fa ON fa.rol_id = od.obj_id"
			."  JOIN tree tr ON tr.child = fa.parent"
			."  JOIN rbac_ua ua ON ua.rol_id = od.obj_id"
			."  JOIN object_data od2 ON od2.obj_id = oref.obj_id"
			." WHERE oref.ref_id = tr.parent"
			."   AND oref.deleted IS NULL"
			."   AND ua.usr_id = ".$this->db->quote($this->user_id, "integer")
			."   AND od2.type = 'orgu'"
			);
		$this->direct_superior_ous = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$this->direct_superior_ous[] = array( "obj_id" => $rec["obj_id"]
												, "ref_id" => $rec["ref_id"]
												);
		}
		return $this->direct_superior_ous;
	}
	
	public function getOrgUnitsWhereUserIsSuperior() {
		if ($this->superior_ous !== null) {
			return $this->superior_ous;
		}

		$_ds_ous = $this->getOrgUnitsWhereUserIsDirectSuperior();
		$where = array(" 0 = 1 ");
		$ds_ous = array();
		
		foreach ($_ds_ous as $ou) {
			$where[] = " tr.child = ".$this->db->quote($ou["ref_id"], "integer");
			$ds_ous[] = $ou["ref_id"];
		}
		
		$lr_res = $this->db->query("SELECT lft, rgt FROM tree WHERE ".$this->db->in("child", $ds_ous, false, "integer"));
		
		while ($lr_rec = $this->db->fetchAssoc($lr_res)) {
			$where[] = "(tr.lft > ".$this->db->quote($lr_rec["lft"])." AND tr.rgt < ".$this->db->quote($lr_rec["rgt"]).")";
		}
		$where = implode(" OR ", $where);
		
		$res = $this->db->query(
			 "SELECT DISTINCT oref.ref_id, oref.obj_id "
			."  FROM object_reference oref"
			."  JOIN object_data od ON od.obj_id = oref.obj_id"
			."  JOIN tree tr ON ( ".$where." )"
			." WHERE od.type = 'orgu'"
			."   AND oref.ref_id = tr.child"
			."   AND oref.deleted IS NULL"
			);
		
		$this->superior_ous = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			$this->superior_ous[] = array( "ref_id" => $rec["ref_id"]
										 , "obj_id" => $rec["obj_id"]
										 );
		}
		
		return $this->superior_ous;
	}
	
	public function getOrgUnitNamesWhereUserIsSuperior() {
		if ($this->superior_ou_names !== null) {
			return $this->superior_ou_names;
		}
		
		$ids = $this->getOrgUnitsWhereUserIsSuperior();
		foreach($ids as $key => $value) {
			$ids[$key] = $ids[$key]["obj_id"];
		}
		
		$res = $this->db->query( "SELECT title FROM object_data"
								." WHERE ".$this->db->in("obj_id", $ids, false, "integer")
								." ORDER BY title ASC"
								);
		$this->superior_ou_names = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			$this->superior_ou_names[] = $rec["title"];
		}
		
		return $this->superior_ou_names;
	}

	public function getOrgUnitNamesWhereUserIsDirectSuperior() {
		if ($this->direct_superior_ou_names !== null) {
			return $this->direct_superior_ou_names;
		}
		
		$ids = $this->getOrgUnitsWhereUserIsDirectSuperior();
		foreach($ids as $key => $value) {
			$ids[$key] = $ids[$key]["obj_id"];
		}
		
		$res = $this->db->query( "SELECT title FROM object_data"
								." WHERE ".$this->db->in("obj_id", $ids, false, "integer")
								." ORDER BY title ASC"
								);
		$this->direct_superior_ou_names = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			$this->direct_superior_ou_names[] = $rec["title"];
		}
		
		return $this->direct_superior_ou_names;
	}

	public function getOrgUnitsWhereUserIsEmployee() {
		if ($this->employee_ous !== null) {
			return $this->employee_ous;
		}
		
		$like_role = array();
		foreach (gevSettings::$EMPLOYEE_ROLES as $role) {
			$like_role[] = "od.title LIKE ".$this->db->quote($role);
		}
		$like_role = implode(" OR ", $like_role);

		$res = $this->db->query(
			 "SELECT oref.obj_id, oref.ref_id "
			."  FROM object_reference oref"
			."  JOIN object_data od ON od.type = 'role' AND ( ".$like_role ." )"
			."  JOIN rbac_fa fa ON fa.rol_id = od.obj_id"
			."  JOIN tree tr ON tr.child = fa.parent"
			."  JOIN rbac_ua ua ON ua.rol_id = od.obj_id"
			."  JOIN object_data od2 ON od2.obj_id = oref.obj_id"
			." WHERE oref.ref_id = tr.parent"
			."   AND oref.deleted IS NULL"
			."   AND ua.usr_id = ".$this->db->quote($this->user_id, "integer")
			."   AND od2.type = 'orgu'"
			);
		$this->employee_ous = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$this->employee_ous[] = array( "obj_id" => $rec["obj_id"]
												, "ref_id" => $rec["ref_id"]
												);
		}
		return $this->employee_ous;
	}

	public function getOrgUnitNamesWhereUserIsEmployee() {
		if ($this->employee_ou_names !== null) {
			return $this->employee_ou_names;
		}
		
		$ids = $this->getOrgUnitsWhereUserIsEmployee();
		foreach($ids as $key => $value) {
			$ids[$key] = $ids[$key]["obj_id"];
		}
		
		$res = $this->db->query( "SELECT title FROM object_data"
								." WHERE ".$this->db->in("obj_id", $ids, false, "integer")
								." ORDER BY title ASC"
								);
		$this->employee_ou_names = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			$this->employee_ou_names[] = $rec["title"];
		}
		
		return $this->employee_ou_names;
	}

	public function getAllOrgUnitTitlesUserIsMember() {
		$superior_orgus = $this->getOrgUnitNamesWhereUserIsDirectSuperior();
		$employee_orgus = $this->getOrgUnitNamesWhereUserIsEmployee();

		return array_merge($superior_orgus, $employee_orgus);
	}
	
	public function getOrgUnitsWhereUserCanBookEmployees() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		return $tree->getOrgusWhereUserHasPermissionForOperation("book_employees");
	}
	
	public function getOrgUnitsWhereUserCanBookEmployeesRecursive() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		return $tree->getOrgusWhereUserHasPermissionForOperation("book_employees_rcrsv");
	}
	
	public function getOrgUnitsWhereUserCanViewEmployeeBookings($user_id = null) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		return $tree->getOrgusWhereUserHasPermissionForOperation("view_employee_bookings", $user_id);
	}
	
	public function getOrgUnitsWhereUserCanViewEmployeeBookingsRecursive() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		return $tree->getOrgusWhereUserHasPermissionForOperation("view_employee_bookings_rcrsv");
	}
	
	public function getOrgUnitsWhereUserCanCancelEmployeeBookings() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		return $tree->getOrgusWhereUserHasPermissionForOperation("cancel_employee_bookings");
	}
	
	public function getOrgUnitsWhereUserCanCancelEmployeeBookingsRecursive() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		return $tree->getOrgusWhereUserHasPermissionForOperation("cancel_employee_bookings_rcrsv");
	}
	
	public function getOrgUnitsWhereUserIsTutor() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		return $tree->getOrgusWhereUserHasPermissionForOperation("tep_is_tutor", $this->user_id);
	}

	public function canViewEmployeeBookings() {
		return count($this->getOrgUnitsWhereUserCanViewEmployeeBookings()) > 0
			|| count($this->getOrgUnitsWhereUserCanViewEmployeeBookingsRecursive()) > 0;
	}
	
	public function canCancelEmployeeBookings() {
		return count($this->getOrgUnitsWhereUserCanCancelEmployeeBookings()) > 0
			|| count($this->getOrgUnitsWhereUserCanCancelEmployeeBookingsRecursive()) > 0;
	}

	public function getOrgUnitsWhereUserCanViewEduBios() {
		if ($this->edu_bio_ou_ref_ids) {
			return $this->edu_bio_ou_ref_ids;
		}
		
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		$d = $tree->getOrgusWhereUserHasPermissionForOperation("view_learning_progress");
		$r = $tree->getOrgusWhereUserHasPermissionForOperation("view_learning_progress_rec");
		$rs = array_map(function($v) { return $v["ref_id"]; }, gevOrgUnitUtils::getAllChildren($r));
		$ous = array_unique(array_merge($d, $r, $rs));
		
		$this->edu_bio_ou_ref_ids_all = $rs;
		$this->edu_bio_ou_ref_ids_empl = array_unique(array_merge($d, $r));
		
		$this->edu_bio_ou_ref_ids = $ous;
		return $ous;
	}

	public function getOrgUnitNamesWhereUserCanViewEduBios($with_ids = false) {
		if ($this->edu_bio_ou_names !== null) {
			return $this->edu_bio_ou_names;
		}
		
		$ids = $this->getOrgUnitsWhereUserCanViewEduBios();
		$res = $this->db->query( "SELECT od.obj_id, title FROM object_data od "
								."  JOIN object_reference oref ON od.obj_id = oref.obj_id"
								." WHERE ".$this->db->in("oref.ref_id", $ids, false, "integer")
								);
		$this->edu_bio_ou_names = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			if ($with_ids) {
				$this->edu_bio_ou_names[(int)$rec["obj_id"]] = $rec["title"];
			}
			else {
				$this->edu_bio_ou_names[] = $rec["title"];
			}
		}
		
		return $this->edu_bio_ou_names;
	}
	
	public function getEmployeesWhereUserCanViewEduBios() {
		if ($this->edu_bio_usr_ids 	!== null) {
			return $this->edu_bio_usr_ids;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$this->getOrgUnitsWhereUserCanViewEduBios();
		$e = gevOrgUnitUtils::getEmployeesIn($this->edu_bio_ou_ref_ids_empl);
		$a = gevOrgUnitUtils::getAllPeopleIn($this->edu_bio_ou_ref_ids_all);
		
		$this->edu_bio_usr_ids = array_unique(array_merge($e, $a));
		
		return $this->edu_bio_usr_ids;
	}
	
	public function getEmployees($include_inactive = false) {
		if ($this->employees_active !== null && !$include_inactive) {
			return $this->employees_active;
		}
		if ($this->employees_all !== null && $include_inactive) {
			return $this->employees_all;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

		$_ds_ous = $this->getOrgUnitsWhereUserIsDirectSuperior();
		$_s_ous = $this->getOrgUnitsWhereUserIsSuperior();
	
		// ref_ids of ous where user is direct superior
		$ds_ous = array();
		foreach($_ds_ous as $ou) {
			$ds_ous[] = $ou["ref_id"];
		}
		// ref_ids of ous where user is superior
		$s_ous = array();
		foreach($_s_ous as $ou) {
			$s_ous[] = $ou["ref_id"];
		}
		
		// ref_ids of ous where user is superior but not direct superior
		$nds_ous = array_diff($s_ous, $ds_ous);
		
		$de = gevOrgUnitUtils::getEmployeesIn($ds_ous);
		$re = gevOrgUnitUtils::getAllPeopleIn($nds_ous);
		
		if (!$include_inactive) {
			$this->employees_active = gevUserUtils::removeInactiveUsers(array_unique(array_merge($de, $re)));
			return $this->employees_active;
		}
		else {
			$this->employees_all = array_unique(array_merge($de, $re));
			return $this->employees_all;
		}
		
	}
	
	public function getVenuesWhereUserIsMember() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$ou_tree = ilObjOrgUnitTree::_getInstance();
		$ous = $ou_tree->getOrgUnitOfUser($this->user_id, 0, true);
		$ret = array();
		foreach ($ous as $ou_id) {
			$utils = gevOrgUnitUtils::getInstance($ou_id);
			if (!$utils->isVenue()) {
				continue;
			}
			$ret[] = $ou_id;
		}
		return $ret;
	}
	
	// billing info
	
	public function getLastBillingDataMaybe() {
		$res = $this->db->query( "SELECT bill_recipient_name, bill_recipient_street, bill_recipient_zip"
								."     , bill_recipient_hnr, bill_recipient_city, bill_recipient_email, bill_cost_center "
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
	
	static $hist_position_keys = null;

	static function getPositionKeysFromHisto() {
		if (self::$hist_position_keys !== null) {
			return self::$hist_position_keys;
		}

		global $ilDB;

		$res = $ilDB->query("SELECT DISTINCT position_key FROM hist_user WHERE position_key != '-empty-'");
		self::$hist_position_keys = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			self::$hist_position_keys[] = $rec["position_key"];
		}
		return self::$hist_position_keys;
	}

	public function getPaisyNr() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_PAISY_NUMBER);
	}
	public function setPaisyNr($a_nr) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_PAISY_NUMBER, $a_nr);
	}
	
	public function getFinancialAccountVFS() {
		return $this->udf_utils->getField($this->user_id, gevSettings::USR_UDF_FINANCIAL_ACCOUNT);
	}
	public function setFinancialAccountVFS($a_nr) {
		$this->udf_utils->setField($this->user_id, gevSettings::USR_UDF_FINANCIAL_ACCOUNT, $a_nr);
	}
	
	static public function userIsInactive($a_user_id) {
		global $ilDB;
		$res = $ilDB->query("SELECT active FROM usr_data"
						   ." WHERE usr_id = ".$ilDB->quote($a_user_id, "integer"));
		
		if ($rec = $ilDB->fetchAssoc($res)) {
			return $rec["active"] != 1;
		}
		
		return false;
	}

	static public function setUserActiveState($user_id, $active) {
		require_once("Services/User/classes/class.ilObjUser.php");
		$user = new ilObjUser($user_id);
		$user->setActive($active);
		$user->update();
	}

	public function getUVGBDOrCPoolNames() {
		$names = array();
		$dbv_utils = gevDBVUtils::getInstance();
		foreach ($dbv_utils->getUVGOrgUnitsOf($this->getId()) as $obj_id) {
			$uvg_top_level_orgu_obj_id = $dbv_utils->getUVGTopLevelOrguIdFor($obj_id);
			$names[] = ilObject::_lookupTitle($uvg_top_level_orgu_obj_id);
		}
		return $names;
	}

	/*
	* Gets the user data for report SuperiorWeeklyAction
	*
	* @return array
	*/
	public function getUserDataForSuperiorWeeklyReport($a_start_ts, $a_end_ts) {
		$booking_status = array("gebucht" => "gebucht"
						,"kostenfrei_storniert" => "kostenfrei storniert"
						,"kostenpflichtig_storniert" => "kostenpflichtig storniert"
//						,"auf_warteliste" => "auf Warteliste"
						,"fehlt_ohne_absage" => "fehlt ohne Absage"
						);

		$actions = array(); 
 		$actions["gebucht"] = array();
		$actions["kostenfrei_storniert"] = array();
		$actions["kostenpflichtig_storniert"] = array();
//		$actions["auf_Warteliste"] = array();
		$actions["teilgenommen"] = array();
		$actions["fehlt_ohne_Absage"] = array();

		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
		$org_units = $this->getOrgUnitsWhereUserIsDirectSuperior();
		$has_view_empl_perm_ref_ids = $this->getOrgUnitsWhereUserCanViewEmployeeBookings($this->user_id);
		$ref_ids = array();
		$ref_id_child_orgunit = array();
		
		foreach ($org_units as $org_unit) {
			// Only take the org units where the user is superior and also has the permission
			// to view bookings of employees.
			if (!in_array($org_unit["ref_id"], $has_view_empl_perm_ref_ids)) {
				continue;
			}

			$ref_ids[] = $org_unit["ref_id"];
			$org_util = gevOrgUnitUtils::getInstance($org_unit["obj_id"]);
			foreach($org_util->getOrgUnitsOneTreeLevelBelow() as $org_unit_child) {
				$ref_id_child_orgunit[] = $org_unit_child["ref_id"];
			}
		}

		$empl = array();
		$sup = array();

		if(!empty($ref_ids)) {
			$empl = gevOrgUnitUtils::getEmployeesIn($ref_ids);
		}

		if(!empty($ref_id_child_orgunit)) {
			$sup = gevOrgUnitUtils::getSuperiorsIn($ref_id_child_orgunit);
		}

		if(!empty($empl) || !empty($sup)) {
			$to_search = array_merge($empl,$sup);

			$sql_emp = "SELECT DISTINCT" 
					." histc.crs_id, histc.begin_date, histc.end_date, histucs.overnights, histucs.booking_status,"
					." histucs.participation_status, histu.firstname, histu.lastname, histc.title, histc.type, histc.edu_program, "
					." IF(crsa_start.night IS NULL, false, true) AS prearrival,"
					." IF(crsa_end.night IS NULL, false, true) AS postdeparture"
					." FROM hist_usercoursestatus histucs"
					." JOIN hist_user histu ON histu.user_id = histucs.usr_id AND histu.hist_historic = 0"
					." JOIN hist_course histc ON histc.crs_id = histucs.crs_id AND histc.hist_historic = 0"
					." LEFT JOIN crs_acco crsa_start ON crsa_start.user_id = histu.user_id AND crsa_start.crs_id = histc.crs_id AND crsa_start.night = DATE_SUB(histucs.begin_date, INTERVAL 1 DAY)"
					." LEFT JOIN crs_acco crsa_end ON crsa_start.user_id = histu.user_id AND crsa_start.crs_id = histc.crs_id AND crsa_end.night = histucs.end_date"
					." WHERE histucs.created_ts BETWEEN ".$this->db->quote($a_start_ts, "integer")." AND ".$this->db->quote($a_end_ts, "integer").""
					." AND ".$this->db->in("histucs.booking_status", $booking_status, false, "text").""
					." AND histucs.hist_historic = 0"
					." AND ".$this->db->in("histu.user_id", $to_search, false, "integer").""
					." AND histucs.creator_user_id != ".$this->db->quote(gevWBD::WBD_IMPORT_CREATOR_ID, "integer").""
					." ORDER BY histucs.booking_status, histu.lastname, histu.firstname, histucs.created_ts";

			$res_emp = $this->db->query($sql_emp);

			while($row_emp = $this->db->fetchAssoc($res_emp)) {
				switch($row_emp["booking_status"]) {
					case "gebucht":
						if($row_emp["participation_status"] == "teilgenommen") {
							$actions["teilgenommen"][] = $row_emp;
							break;
						}

						if($row_emp["participation_status"] == "fehlt ohne Absage") {
							$actions["fehlt_ohne_Absage"][] = $row_emp;
							break;
						}

						$actions["gebucht"][] = $row_emp;
						break;
					case "kostenfrei storniert":
						$actions["kostenfrei_storniert"][] = $row_emp;
						break;
					case "kostenpflichtig storniert":
						$actions["kostenpflichtig_storniert"][] = $row_emp;
						break;
/*					case "auf Warteliste":
						$actions["auf_Warteliste"][] = $row_emp;
						break;*/
					default:
						break;
				}
			}
 		}

	 	return $actions;
	}

	public function seeBiproAgent() {
		$roles = array("Administrator"
					   ,"Admin-Voll"
					   ,"Admin-eingeschraenkt"
					   ,"Admin-Ansicht"
					   ,"OD/BD"
					   ,"OD"
					   ,"BD"
					   ,"FD"
					   ,"UA"
					   ,"HA 84"
					   ,"BA 84"
					   ,"Org PV 59"
					   ,"PV 59"
					   ,"AVL"
					   ,"ID FK"
					   ,"ID MA"
					   ,"OD/FD/BD ID"
					   ,"OD/FD ID"
					   ,"BD ID"
					   ,"Agt-ID"
					   ,"NFK"
					   ,"FDA"
					   ,"int. Trainer"
					   ,"OD-Betreuer"
					   ,"DBV UVG"
					   ,"DBV EVG"
					   ,"DBV-Fin-UVG"
					   ,"RTL"
					);

		return $this->hasRoleIn($roles);
	}

	public function seeBiproSuperior() {
		$roles = array("Administrator"
					   ,"Admin-Voll"
					   ,"Admin-eingeschraenkt"
					   ,"Admin-Ansicht"
					   ,"OD/BD"
					   ,"OD"
					   ,"BD"
					   ,"FD"
					   ,"UA"
					   ,"AVL"
					   ,"ID FK"
					   ,"NFK"
					   ,"FDA"
					   ,"int. Trainer"
					   ,"OD-Betreuer"
					   ,"RTL"
					);
		
		return $this->hasRoleIn($roles);
	}

	public function notEditBuildingBlocks() {
		$roles = array("Admin-Ansicht");

		return $this->hasRoleIn($roles);
	}

	public function isTrainingManagerOnAnyCourse() {
		$query = "SELECT count(*) as cnt\n"
				." FROM `rbac_ua` rua\n"
				." JOIN object_data od ON rua.rol_id = od.obj_id\n"
				." WHERE rua.usr_id = ".$this->db->quote($this->user_id,"integer")."\n"
				."       AND (od.title LIKE ".$this->db->quote("il_crs_admin_%", "text")."\n"
				."            OR title = ".$this->db->quote("Pool Trainingsersteller","text").")";

		$res = $this->db->query($query);
		$row = $this->db->fetchAssoc($res);

		if($row["cnt"] > 0) {
			return true;
		}

		return false;
	}

	public function courseToday($date) {
		$crs_ids = $this->getCourseIdsWhereUserIs(array("il_crs_member_%"), array("period"=>array("start"=>$date, "end"=>$date)));
		return count($crs_ids) > 0;
	}

	static function getBuildingBlockPoolsUserHasPermissionsTo($user_id, array $permissions) {
		global $ilDB;

		$opsids = ilRbacReview::_getOperationIdsByName($permissions);

		$query = "SELECT rep_obj_bbpool.obj_id, rbac_pa.ops_id\n"
		." FROM rep_obj_bbpool\n"
		." JOIN rbac_operations ON ".$ilDB->in("rbac_operations.operation", $permissions, false, "text")."\n"
		." JOIN rbac_ua ON rbac_ua.usr_id = ".$ilDB->quote($user_id, "integer")."\n"
		." JOIN rbac_pa ON rbac_pa.rol_id = rbac_ua.rol_id\n"
		."      AND rbac_pa.ops_id LIKE CONCAT('%', rbac_operations.ops_id, '%')\n"
		." JOIN object_reference ON object_reference.ref_id = rbac_pa.ref_id\n"
		." WHERE rep_obj_bbpool.obj_id = object_reference.obj_id\n"
		."    AND rep_obj_bbpool.is_online = 1\n";

		$res = $ilDB->query($query);
		$bb_pools = array();
		while($row = $ilDB->fetchAssoc($res)){
			$perm_check = unserialize($row['ops_id']);

			if(in_array($opsids[0], $perm_check) && in_array($opsids[1], $perm_check)) {
				$bb_pools[] = $row["obj_id"];
			}
		}
		$bb_pools = array_unique($bb_pools);

		return $bb_pools;
	}

	static function getBuildingBlockPoolsTitleUserHasPermissionsTo($user_id, array $permissions) {
		global $ilDB;

		$opsids = ilRbacReview::_getOperationIdsByName($permissions);

		$is_system_admin = gevUserUtils::getInstance($user_id)->isSystemAdmin();
		
		if($is_system_admin) {
			$query = "SELECT rep_obj_bbpool.obj_id, object_data.title\n"
					." FROM rep_obj_bbpool\n"
					." JOIN object_data ON object_data.obj_id = rep_obj_bbpool.obj_id\n"
					." ORDER BY object_data.title\n";
		} else {
			$query = "SELECT rep_obj_bbpool.obj_id, object_data.title, rbac_pa.ops_id\n"
					." FROM rep_obj_bbpool\n"
					." JOIN rbac_operations ON ".$ilDB->in("rbac_operations.operation", $permissions, false, "text")."\n"
					." JOIN rbac_ua ON rbac_ua.usr_id = ".$ilDB->quote($user_id, "integer")."\n"
					." JOIN rbac_pa ON rbac_pa.rol_id = rbac_ua.rol_id\n"
					."      AND rbac_pa.ops_id LIKE CONCAT('%', rbac_operations.ops_id, '%')\n"
					." JOIN object_reference ON object_reference.ref_id = rbac_pa.ref_id\n"
					." JOIN object_data ON object_data.obj_id = rep_obj_bbpool.obj_id\n"
					." WHERE rep_obj_bbpool.obj_id = object_reference.obj_id\n"
					."    AND rep_obj_bbpool.is_online = 1\n"
					." ORDER BY object_data.title\n";
		}

		$res = $ilDB->query($query);
		$bb_pools = array();
		while($row = $ilDB->fetchAssoc($res)){
			$perm_check = unserialize($row['ops_id']);

			if(!$is_system_admin && !in_array($opsids[0], $perm_check) && !in_array($opsids[1], $perm_check)) {
				continue;
			}

			$bb_pools[$row["obj_id"]] = $row["title"];
		}
		$bb_pools = array_unique($bb_pools);

		return $bb_pools;
	}

	public function getRoleHistory() {
		$query = "SELECT hiur.rol_title, ud.firstname, ud.lastname, hiur.action, hiur.created_ts"
				." FROM hist_userrole hiur"
				." JOIN usr_data ud ON ud.usr_id = hiur.creator_user_id"
				." WHERE hiur.usr_id = ".$this->db->quote($this->user_id, "integer")
				." ORDER BY hiur.created_ts";

		$ret = array();
		$res = $this->db->query($query);
		while($row = $this->db->fetchAssoc($res)) {
			$ret[] = $row;
		}

		return $ret;
	}
}