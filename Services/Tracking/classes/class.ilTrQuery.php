<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tracking query class. Put any complex queries into this class. Keep 
 * tracking class small.
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilTrQuery
{
	function getObjectsStatusForUser($a_user_id, array $obj_refs)
	{
		global $ilDB;

		if(sizeof($obj_refs))
		{
			$obj_ids = array_keys($obj_refs);		
			self::refreshObjectsStatus($obj_ids, array($a_user_id));
			
			include_once "Services/Object/classes/class.ilObjectLP.php";
			include_once "Services/Tracking/classes/class.ilLPStatus.php";
		
			// prepare object view modes
			include_once 'Modules/Course/classes/class.ilObjCourse.php';
			$view_modes = array();
			$query = "SELECT obj_id, view_mode FROM crs_settings".
				" WHERE ".$ilDB->in("obj_id", $obj_ids , false, "integer");
			$set = $ilDB->query($query);
			while($rec = $ilDB->fetchAssoc($set))
			{
				$view_modes[(int)$rec["obj_id"]] = (int)$rec["view_mode"];
			}

			$sessions = self::getSessionData($a_user_id, $obj_ids);

			$query = "SELECT object_data.obj_id, title, CASE WHEN status IS NULL THEN ".ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM." ELSE status END AS status,".
				" status_changed, percentage, read_count+childs_read_count AS read_count, spent_seconds+childs_spent_seconds AS spent_seconds,".
				" u_mode, type, visits, mark, u_comment".
				" FROM object_data".
				" LEFT JOIN ut_lp_settings ON (ut_lp_settings.obj_id = object_data.obj_id)".
				" LEFT JOIN read_event ON (read_event.obj_id = object_data.obj_id AND read_event.usr_id = ".$ilDB->quote($a_user_id, "integer").")".
				" LEFT JOIN ut_lp_marks ON (ut_lp_marks.obj_id = object_data.obj_id AND ut_lp_marks.usr_id = ".$ilDB->quote($a_user_id, "integer").")".
				// " WHERE (u_mode IS NULL OR u_mode <> ".$ilDB->quote(ilLPObjSettings::LP_MODE_DEACTIVATED, "integer").")".
				" WHERE ".$ilDB->in("object_data.obj_id", $obj_ids, false, "integer").
				" ORDER BY title";
			$set = $ilDB->query($query);
			$result = array();
			while($rec = $ilDB->fetchAssoc($set))
			{
				$rec["comment"] = $rec["u_comment"];
				unset($rec["u_comment"]);
				
				$rec["ref_ids"] = $obj_refs[(int)$rec["obj_id"]];
				$rec["status"] = (int)$rec["status"];
				$rec["percentage"] = (int)$rec["percentage"];
				$rec["read_count"] = (int)$rec["read_count"];
				$rec["spent_seconds"] = (int)$rec["spent_seconds"];
				$rec["u_mode"] = (int)$rec["u_mode"];

				if($rec["type"] == "sess")
				{
					$session = $sessions[$rec["obj_id"]];
					$rec["title"] = $session["title"];
					// $rec["status"] = (int)$session["status"];
				}

				// lp mode might not match object/course view mode
				if($rec["type"] == "crs" && $view_modes[$rec["obj_id"]] == IL_CRS_VIEW_OBJECTIVE)
				{
					$rec["u_mode"] = ilLPObjSettings::LP_MODE_OBJECTIVES;
				}
				else if(!$rec["u_mode"])
				{
					$olp = ilObjectLP::getInstance($rec["obj_id"]);
					$rec["u_mode"] = $olp->getCurrentMode();
				}

				// can be default mode
				if(/*$rec["u_mode"] != ilLPObjSettings::LP_MODE_DEACTIVATE*/ true)
				{
					$result[] = $rec;
				}
			}
			return $result;
		}
	}

	function getObjectivesStatusForUser($a_user_id, array $a_objective_ids)
	{
		global $ilDB;
						
		include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";								
		$lo_lp_status = ilLOUserResults::getObjectiveStatusForLP($a_user_id, $a_objective_ids);
		
		$query =  "SELECT crs_id, crs_objectives.objective_id AS obj_id, title,".$ilDB->quote("lobj", "text")." AS type".
			" FROM crs_objectives".			
			" WHERE ".$ilDB->in("crs_objectives.objective_id", $a_objective_ids, false, "integer").
			" AND active = ".$ilDB->quote(1, "integer").
			" ORDER BY position";
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{			
			if(array_key_exists($rec["obj_id"], $lo_lp_status))
			{
				$rec["status"] = $lo_lp_status[$rec["obj_id"]];
			}		
			else
			{
				$rec["status"] = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
			}
			$result[] = $rec;
		}
		
		return $result;
	}
	
	function getSCOsStatusForUser($a_user_id, $a_parent_obj_id, array $a_sco_ids)
	{
		self::refreshObjectsStatus(array($a_parent_obj_id), array($a_user_id));	
		
		// import score from tracking data
		$scores_raw = $scores = array();
		include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
		$subtype = ilObjSAHSLearningModule::_lookupSubType($a_parent_obj_id);		
		switch($subtype)
		{
			case 'hacp':
			case 'aicc':			
			case 'scorm':
				include_once './Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php';
				$module = new ilObjSCORMLearningModule($a_parent_obj_id, false);
				$scores_raw = $module->getTrackingDataAgg($a_user_id);
				break;
				
			case 'scorm2004':
				include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';
				$module = new ilObjSCORM2004LearningModule($a_parent_obj_id, false);
				$scores_raw = $module->getTrackingDataAgg($a_user_id);
				break;
		}
		if($scores_raw)
		{
			foreach($scores_raw as $item)
			{
				$scores[$item["sco_id"]] = $item["score"];
			}
			unset($module);
			unset($scores_raw);
		}		
		
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_parent_obj_id);
		
		$items = array();
		foreach($a_sco_ids as $sco_id)
		{				
			// #9719 - can have in_progress AND failed/completed
			if(in_array($a_user_id, $status_info["failed"][$sco_id]))
			{
				$status = ilLPStatus::LP_STATUS_FAILED;
			}
			elseif(in_array($a_user_id, $status_info["completed"][$sco_id]))
			{
				$status = ilLPStatus::LP_STATUS_COMPLETED;
			}
			elseif(in_array($a_user_id, $status_info["in_progress"][$sco_id]))
			{
				$status = ilLPStatus::LP_STATUS_IN_PROGRESS;
			}
			else
			{
				$status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED;
			}
			
			$items[$sco_id] = array(
				"title" => $status_info["scos_title"][$sco_id],
				"status" => $status,
				"type" => "sahs",
				"score" => (int)$scores[$sco_id]
				);						
		}
		
		return $items;
	}
	
	function getSubItemsStatusForUser($a_user_id, $a_parent_obj_id, array $a_item_ids)
	{
		self::refreshObjectsStatus(array($a_parent_obj_id), array($a_user_id));	
		
		switch(ilObject::_lookupType($a_parent_obj_id))
		{
			case "lm":				
				include_once './Services/Object/classes/class.ilObjectLP.php';
				$olp = ilObjectLP::getInstance($a_parent_obj_id);
				$collection = $olp->getCollectionInstance();
				if($collection)
				{					
					$ref_ids = ilObject::_getAllReferences($a_parent_obj_id);
					$ref_id = end($ref_ids);		
					$item_data = $collection->getPossibleItems($ref_id);
				}
				break;
			
			default:
				return array();
		}
	
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_parent_obj_id);
		
		$items = array();
		foreach($a_item_ids as $item_id)
		{			
			if(!isset($item_data[$item_id]))
			{
				continue;
			}
			
			if(in_array($a_user_id, $status_info["completed"][$item_id]))
			{
				$status = ilLPStatus::LP_STATUS_COMPLETED;
			}
			elseif(in_array($a_user_id, $status_info["in_progress"][$item_id]))
			{
				$status = ilLPStatus::LP_STATUS_IN_PROGRESS;
			}
			else
			{
				$status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED;
			}
			
			$items[$item_id] = array(
				"title" => $item_data[$item_id]["title"],
				"status" => $status,
				"type" => "st"
				);						
		}
		
		return $items;
	}

	/**
	 * Get all user-based tracking data for object
	 *
	 * @param	int		$a_ref_id
	 * @param	string	$a_order_field
	 * @param	string	$a_order_dir
	 * @param	int		$a_offset
	 * @param	int		$a_limit
	 * @param	array	$a_filters
	 * @param	array	$a_additional_fields
	 * @param	int  	$check_agreement (obj id of parent course)
	 * @param	arry	$privacy_fields
	 * @return	array	cnt, set
	 */
	static function getUserDataForObject($a_ref_id, $a_order_field = "", $a_order_dir = "",
		$a_offset = 0, $a_limit = 9999, array $a_filters = NULL, array $a_additional_fields = NULL,
		$check_agreement = false, $privacy_fields = NULL)
	{
		global $ilDB;

		$fields = array("usr_data.usr_id", "login", "active");
		$udf = self::buildColumns($fields, $a_additional_fields);
		
	    $where = array();
		$where[] = "usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");

		// users
		$left = "";
		$a_users = self::getParticipantsForObject($a_ref_id);

		$obj_id = ilObject::_lookupObjectId($a_ref_id);
		self::refreshObjectsStatus(array($obj_id), $a_users);

		if (is_array($a_users))
		{
			$left = "LEFT";
			$where[] = $ilDB->in("usr_data.usr_id", $a_users, false, "integer");
		}

		$query = " FROM usr_data ".$left." JOIN read_event ON (read_event.usr_id = usr_data.usr_id".
			" AND read_event.obj_id = ".$ilDB->quote($obj_id, "integer").")".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id ".
			" AND ut_lp_marks.obj_id = ".$ilDB->quote($obj_id, "integer").")".
			" LEFT JOIN usr_pref ON (usr_pref.usr_id = usr_data.usr_id AND keyword = ".$ilDB->quote("language", "text").")".
			self::buildFilters($where, $a_filters);

		$queries = array(array("fields"=>$fields, "query"=>$query));
	
		// #9598 - if language is not in fields alias is missing
		if($a_order_field == "language")
		{
			$a_order_field = "usr_pref.value";
		}
		
		// udf data is added later on, not in this query
		$udf_order = null;
		if(!$a_order_field)
		{
			$a_order_field = "login";
		}
		else if(substr($a_order_field, 0, 4) == "udf_")
		{
			$udf_order = $a_order_field;
			$a_order_field = null;
		}

		$result = self::executeQueries($queries, $a_order_field, $a_order_dir, $a_offset, $a_limit);
		
		self::getUDFAndHandlePrivacy($result, $udf, $check_agreement, $privacy_fields, $a_filters);
		
		// as we cannot do this in the query, sort by custom field here
		// this will not work with pagination!
		if($udf_order)
		{
			include_once "Services/Utilities/classes/class.ilStr.php";
			$result["set"] = ilUtil::stableSortArray($result["set"],
				$udf_order, $a_order_dir);
		}
		
		return $result;
	}
	
	/**
	 * Handle privacy and add udf data to (user) result data
	 * 
	 * @param array $a_result
	 * @param array $a_udf
	 * @param int $a_check_agreement
	 * @param array $a_privacy_fields
	 * @param array $a_filters
	 */
	protected static function getUDFAndHandlePrivacy(array &$a_result, array $a_udf = null, 
		$a_check_agreement = null, array $a_privacy_fields = null, array $a_filters = null)
	{
		global $ilDB;
		
		if(!$a_result["cnt"])
		{
			return;
		}
		
		if(sizeof($a_udf))
		{
			$query = "SELECT usr_id, field_id, value FROM udf_text WHERE ".$ilDB->in("field_id", $a_udf, false, "integer");
			$set = $ilDB->query($query);
			$udf = array();
			while($row = $ilDB->fetchAssoc($set))
			{
				$udf[$row["usr_id"]]["udf_".$row["field_id"]] = $row["value"];
			}
		}

		// (course/group) user agreement
		if($a_check_agreement)
		{
			// admins/tutors (write-access) will never have agreement ?!
			include_once "Services/Membership/classes/class.ilMemberAgreement.php";
			$agreements = ilMemberAgreement::lookupAcceptedAgreements($a_check_agreement);

			// public information for users
			$query = "SELECT usr_id FROM usr_pref WHERE keyword = ".$ilDB->quote("public_profile", "text").
				" AND value = ".$ilDB->quote("y", "text")." OR value = ".$ilDB->quote("g", "text");
			$set = $ilDB->query($query);
			$all_public = array();
			while($row = $ilDB->fetchAssoc($set))
			{
				$all_public[] = $row["usr_id"];
			}
			$query = "SELECT usr_id,keyword FROM usr_pref WHERE ".$ilDB->like("keyword", "text", "public_%", false).
				" AND value = ".$ilDB->quote("y", "text")." AND ".$ilDB->in("usr_id", $all_public, "", "integer");
			$set = $ilDB->query($query);
			$public = array();
			while($row = $ilDB->fetchAssoc($set))
			{
				$public[$row["usr_id"]][] = substr($row["keyword"], 7);
			}
			unset($all_public);
		}

		foreach($a_result["set"] as $idx => $row)
		{
			// add udf data
			if(isset($udf[$row["usr_id"]]))
			{
				$a_result["set"][$idx] = $row = array_merge($row, $udf[$row["usr_id"]]);
			}

			// remove all private data - if active agreement and agreement not given by user
			if(sizeof($a_privacy_fields) && $a_check_agreement && !in_array($row["usr_id"], $agreements))
			{
				foreach($a_privacy_fields as $field)
				{
					// check against public profile
					if(isset($row[$field]) && (!isset($public[$row["usr_id"]]) ||
						!in_array($field, $public[$row["usr_id"]])))
					{
						// remove complete entry - offending field was filtered
						if(isset($a_filters[$field]))
						{
							// we cannot remove row because of pagination!
							foreach(array_keys($row) as $col_id)
							{
								$a_result["set"][$idx][$col_id] = null;
							}
							$a_result["set"][$idx]["privacy_conflict"] = true;
							// unset($a_result["set"][$idx]);
							break;
						}
						// remove offending field
						else
						{
							$a_result["set"][$idx][$field] = false;
						}
					}
				}
			}
		}
		
		// $a_result["cnt"] = sizeof($a_result["set"]);		
	}

	/**
	 * Get all object-based tracking data for user and parent object
	 *
	 * @param	int		$a_user_id
	 * @param	int		$a_parent_obj_id
	 * @param	int		$a_parent_ref_id
	 * @param	string	$a_order_field
	 * @param	string	$a_order_dir
	 * @param	int		$a_offset
	 * @param	int		$a_limit
	 * @param	array	$a_filters
	 * @param	array	$a_additional_fields
	 * @param	bool	$use_collection
	 * @return	array	cnt, set
	 */
	static function getObjectsDataForUser($a_user_id, $a_parent_obj_id, $a_parent_ref_id, $a_order_field = "", $a_order_dir = "", $a_offset = 0, $a_limit = 9999,
		array $a_filters = NULL, array $a_additional_fields = NULL, $use_collection = true)
	{
		global $ilDB;
		
		$fields = array("object_data.obj_id", "title", "type");
		self::buildColumns($fields, $a_additional_fields);

		$objects = self::getObjectIds($a_parent_obj_id, $a_parent_ref_id, $use_collection, true, array($a_user_id));

		$query = " FROM object_data LEFT JOIN read_event ON (object_data.obj_id = read_event.obj_id AND".
			" read_event.usr_id = ".$ilDB->quote($a_user_id, "integer").")".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = ".$ilDB->quote($a_user_id, "integer")." AND".
			" ut_lp_marks.obj_id = object_data.obj_id)".
			" WHERE ".$ilDB->in("object_data.obj_id", $objects["object_ids"], false, "integer").
			self::buildFilters(array(), $a_filters);

		$queries = array();
		$queries[] = array("fields"=>$fields, "query"=>$query);

		// objectives data 
		if($objects["objectives_parent_id"])
		{
			$objective_fields = array("crs_objectives.objective_id AS obj_id", "title",
				$ilDB->quote("lobj", "text")." as type");
			
			include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";	
				
			if (is_array($a_additional_fields))
			{
              foreach($a_additional_fields as $field)
			  {
				if($field != "status")
				{
					$objective_fields[] = "NULL AS ".$field;
				}
				else
				{
		            include_once("Services/Tracking/classes/class.ilLPStatus.php");
					$objective_fields[] = "CASE WHEN status = ".$ilDB->quote(ilLOUserResults::STATUS_COMPLETED, "integer").
						" THEN ".ilLPStatus::LP_STATUS_COMPLETED_NUM.
						" WHEN status = ".$ilDB->quote(ilLOUserResults::STATUS_FAILED, "integer").
						" THEN ".ilLPStatus::LP_STATUS_FAILED_NUM.
						" ELSE NULL END AS status";
				}
			  }
			}

			$where = array();
			$where[] = "crs_objectives.crs_id = ".$ilDB->quote($objects["objectives_parent_id"], "integer");
			$where[] = "crs_objectives.active = ".$ilDB->quote(1, "integer");
		
			$objectives_query = " FROM crs_objectives".
				" LEFT JOIN loc_user_results ON (crs_objectives.objective_id = loc_user_results.objective_id".
				" AND loc_user_results.user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND loc_user_results.type = ".$ilDB->quote(ilLOUserResults::TYPE_QUALIFIED, "integer").")".			
				self::buildFilters($where, $a_filters);
			
			$queries[] = array("fields"=>$objective_fields, "query"=>$objectives_query, "count"=>"crs_objectives.objective_id");
		}
		
		if(!in_array($a_order_field, $fields))
		{
			$a_order_field = "title";
		}

		$result = self::executeQueries($queries, $a_order_field, $a_order_dir, $a_offset, $a_limit);
		if($result["cnt"])
		{
			// session data
			$sessions = self::getSessionData($a_user_id, $objects["object_ids"]);

			foreach($result["set"] as $idx => $item)
			{
				if($item["type"] == "sess")
				{
					$session = $sessions[$item["obj_id"]];
					$result["set"][$idx]["title"] = $session["title"];
					$result["set"][$idx]["sort_title"] = $session["e_start"];
					// $result["set"][$idx]["status"] = (int)$session["status"];
				}

				$result["set"][$idx]["ref_id"] = $objects["ref_ids"][$item["obj_id"]];
			}

			// scos data (:TODO: will not be part of offset/limit)
			if($objects["scorm"])
			{
				include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
				$subtype = ilObjSAHSLearningModule::_lookupSubType($a_parent_obj_id);
				if($subtype == "scorm2004")
				{
					include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
					$sobj = new ilObjSCORM2004LearningModule($a_parent_ref_id, true);
					$scos_tracking = $sobj->getTrackingDataAgg($a_user_id, true);
				}
				else
				{
					include_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");
					$sobj = new ilObjSCORMLearningModule($a_parent_ref_id, true);
					$scos_tracking = array();
					foreach($sobj->getTrackingDataAgg($a_user_id) as $item)
					{
						// format: hhhh:mm:ss ?!
						if($item["time"])
						{
							$time = explode(":", $item["time"]);
							$item["time"] = $time[0]*60*60+$time[1]*60+$time[2];
						}
						$scos_tracking[$item["sco_id"]] = array("session_time"=>$item["time"]);
					}
				}
			
				foreach($objects["scorm"]["scos"] as $sco)
				{
					$row = array("title" => $objects["scorm"]["scos_title"][$sco],
						"type" => "sco");

					$status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
					if(in_array($a_user_id, $objects["scorm"]["completed"][$sco]))
					{
						$status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
					}
					else if(in_array($a_user_id, $objects["scorm"]["failed"][$sco]))
					{
						$status = ilLPStatus::LP_STATUS_FAILED_NUM;
					}
					else if(in_array($a_user_id, $objects["scorm"]["in_progress"][$sco]))
					{
						$status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
					}
					$row["status"] = $status;

					// add available tracking data
					if(isset($scos_tracking[$sco]))
					{
					   if(isset($scos_tracking[$sco]["last_access"]))
					   {
						   $date = new ilDateTime($scos_tracking[$sco]["last_access"], IL_CAL_DATETIME);
						   $row["last_access"] = $date->get(IL_CAL_UNIX);
					   }
					   $row["spent_seconds"] = $scos_tracking[$sco]["session_time"];
					}

					$result["set"][] = $row;
					$result["cnt"]++;
				}
			}
		}
		return $result;
	}

	/**
	 * Get session data for given objects and user
	 *
	 * @param	int		$a_user_id
	 * @param	array	$obj_ids
	 * @return	array
	 */
	protected static function getSessionData($a_user_id, array $obj_ids)
	{
		global $ilDB;

		$query = "SELECT obj_id, title, e_start, e_end, CASE WHEN participated = 1 THEN 2 WHEN registered = 1 THEN 1 ELSE NULL END AS status,".
			" mark, e_comment".
			" FROM event".
			" JOIN event_appointment ON (event.obj_id = event_appointment.event_id)".
			" LEFT JOIN event_participants ON (event_participants.event_id = event.obj_id AND usr_id = ".$ilDB->quote($a_user_id, "integer").")".
			" WHERE ".$ilDB->in("obj_id", $obj_ids , false, "integer");
		$set = $ilDB->query($query);
		$sessions = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$rec["comment"] = $rec["e_comment"];
			unset($rec["e_comment"]);
			
			$date = ilDatePresentation::formatPeriod(
				new ilDateTime($rec["e_start"], IL_CAL_DATETIME),
				new ilDateTime($rec["e_end"], IL_CAL_DATETIME));

			if($rec["title"])
			{
				$rec["title"] = $date.': '.$rec["title"];
			}
			else
			{
				$rec["title"] = $date;
			}
			$sessions[$rec["obj_id"]] = $rec;
		}
		return $sessions;
	}

	/**
	 * Get all aggregated tracking data for parent object
	 *
	 * :TODO: sorting, offset, limit, objectives, collection/all
	 *
	 * @param	int		$a_parent_obj_id
	 * @param	int		$a_parent_ref_id
	 * @param	string	$a_order_field
	 * @param	string	$a_order_dir
	 * @param	int		$a_offset
	 * @param	int		$a_limit
	 * @param	array	$a_filter
	 * @param	array	$a_additional_fields
	 * @param	array	$a_preselected_obj_ids
	 * @return	array	cnt, set
	 */
	static function getObjectsSummaryForObject($a_parent_obj_id, $a_parent_ref_id, $a_order_field = "", $a_order_dir = "", $a_offset = 0, $a_limit = 9999,
		array $a_filters = NULL, array $a_additional_fields = NULL, $a_preselected_obj_ids = NULL)
	{
		global $ilDB;
		
		$fields = array();
		self::buildColumns($fields, $a_additional_fields, true);

		$objects = array();
		if($a_preselected_obj_ids === NULL)
		{
			$objects = self::getObjectIds($a_parent_obj_id, $a_parent_ref_id, false, false);
		}
		else
		{
			foreach($a_preselected_obj_ids as $obj_id => $ref_ids)
			{							
				$objects["object_ids"][] = $obj_id;
				$objects["ref_ids"][$obj_id] = array_pop($ref_ids);
			}						
		}

		$result = array();
		if($objects)
		{
			// object data
			$set = $ilDB->query("SELECT obj_id,title,type FROM object_data".
				" WHERE ".$ilDB->in("obj_id", $objects["object_ids"], false, "integer"));
			while($rec = $ilDB->fetchAssoc($set))
			{
				$object_data[$rec["obj_id"]] = $rec;
				if($a_preselected_obj_ids)
				{
					$object_data[$rec["obj_id"]]["ref_ids"] = $a_preselected_obj_ids[$rec["obj_id"]];
				}
				else
				{
					$object_data[$rec["obj_id"]]["ref_ids"] = array($objects["ref_ids"][$rec["obj_id"]]);
				}
			}

			foreach($objects["ref_ids"] as $object_id => $ref_id)
			{
				$object_result = self::getSummaryDataForObject($ref_id, $fields, $a_filters);
				if(sizeof($object_result))
				{
					if($object_data[$object_id])
					{
						$result[] = array_merge($object_data[$object_id], $object_result);
					}
				}
			}

			// :TODO: objectives
			if($objects["objectives_parent_id"])
			{

			}
		}
		
		return array("cnt"=>sizeof($result), "set"=>$result);
	}

	/**
	 * Get all aggregated tracking data for object
	 *
	 * @param	int		$a_ref_id
	 * @param	array	$fields
	 * @param	array	$a_filters
	 * @return	array
	 */
	protected static function getSummaryDataForObject($a_ref_id, array $fields, array $a_filters = NULL)
	{
		global $ilDB;

		$where = array();
		$where[] = "usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");

		// users
		$a_users = self::getParticipantsForObject($a_ref_id);
		$left = "";
		if (is_array($a_users)) // #14840
		{
			$left = "LEFT";
			$where[] = $ilDB->in("usr_data.usr_id", $a_users, false, "integer");
		}

		$obj_id = ilObject::_lookupObjectId($a_ref_id);
		self::refreshObjectsStatus(array($obj_id), $a_users);
		
		$query = " FROM usr_data ".$left." JOIN read_event ON (read_event.usr_id = usr_data.usr_id".
			" AND obj_id = ".$ilDB->quote($obj_id, "integer").")".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id ".
			" AND ut_lp_marks.obj_id = ".$ilDB->quote($obj_id, "integer").")".
			" LEFT JOIN usr_pref ON (usr_pref.usr_id = usr_data.usr_id AND keyword = ".$ilDB->quote("language", "text").")".
			self::buildFilters($where, $a_filters, true);

		$fields[] = 'COUNT(usr_data.usr_id) AS user_count';

		$queries = array();
		$queries[] = array("fields"=>$fields, "query"=>$query, "count"=>"*");

		$result = self::executeQueries($queries);
		$result = $result["set"][0];
		$users_no = $result["user_count"];

		$valid = true;
		if(!$users_no)
		{
			$valid = false;
		}
		else if(isset($a_filters["user_total"]))
		{
			if($a_filters["user_total"]["from"] && $users_no < $a_filters["user_total"]["from"])
			{
				$valid = false;
			}
			else if($a_filters["user_total"]["to"] && $users_no > $a_filters["user_total"]["to"])
			{
				$valid = false;
			}
		}

		if($valid)
		{
			$result["country"] = self::getSummaryPercentages("country", $query);
			$result["sel_country"] = self::getSummaryPercentages("sel_country", $query);
			$result["city"] = self::getSummaryPercentages("city", $query);
			$result["gender"] = self::getSummaryPercentages("gender", $query);
			$result["language"] = self::getSummaryPercentages("usr_pref.value", $query, "language");
			$result["status"] = self::getSummaryPercentages("status", $query);
			$result["mark"] = self::getSummaryPercentages("mark", $query);
		}
		else
		{
			$result = array();
		}

		if($result)
		{
			$result["user_total"] = $users_no;
		}

		return $result;
	}

	/**
	 * Get aggregated data for field
	 *
	 * @param	string	$field
	 * @param	string	$base_query
	 * @param	string	$alias
	 * @return	array
	 */
	protected static function getSummaryPercentages($field, $base_query, $alias = NULL)
	{
		global $ilDB;

		if(!$alias)
		{
		  $field_alias = $field;
		}
		else
		{
		  $field_alias = $alias;
		  $alias = " AS ".$alias;
		}

		// move having BEHIND group by
		$having = "";
		if(preg_match("/".preg_quote(" [[--HAVING")."(.+)".preg_quote("HAVING--]]")."/", $base_query, $hits))
		{
			$having = " HAVING ".$hits[1];
			$base_query = str_replace($hits[0], "", $base_query);
		}

		$query = "SELECT COUNT(*) AS counter, ".$field.$alias." ".$base_query. " GROUP BY ".$field.$having." ORDER BY counter DESC";
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[$rec[$field_alias]] = (int)$rec["counter"];
		}
		return $result;
	}

	/**
	 * Get participant ids for given object
	 *
	 * @param	int		$a_ref_id
	 * @return	array
	 */
	public static function getParticipantsForObject($a_ref_id)
	{
		global $tree;
		
		$obj_id = ilObject::_lookupObjectId($a_ref_id);		
		$obj_type = ilObject::_lookupType($obj_id);

		// try to get participants from (parent) course/group
		switch($obj_type)
		{
			case "crs":
				include_once "Modules/Course/classes/class.ilCourseParticipants.php";
				$member_obj = ilCourseParticipants::_getInstanceByObjId($obj_id);
				return $member_obj->getMembers();

			case "grp":
				include_once "Modules/Group/classes/class.ilGroupParticipants.php";
				$member_obj = ilGroupParticipants::_getInstanceByObjId($obj_id);
				return $member_obj->getMembers();
			
			default:				
				// walk path to find course or group object and use members of that object
				$path = $tree->getPathId($a_ref_id);
				array_pop($path);
				foreach(array_reverse($path) as $path_ref_id)
				{
					$type = ilObject::_lookupType($path_ref_id, true);
					if($type == "crs" || $type == "grp")
					{
						return self::getParticipantsForObject($path_ref_id);
					}
				}
				break;
		}
		
		$a_users = null;
		
		// no participants possible: use tracking/object data where possible
		switch($obj_type)
		{
			case "sahs":
				include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
				$subtype = ilObjSAHSLearningModule::_lookupSubType($obj_id);
				if ($subtype == "scorm2004")
				{										
					// based on cmi_node/cp_node, used for scorm tracking data views
					include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
					$mod = new ilObjSCORM2004LearningModule($obj_id, false);
					$all = $mod->getTrackedUsers("");					
					if($all)
					{
						$a_users = array();
						foreach($all as $item)
						{
							$a_users[] = $item["user_id"];
						}
					}
				}
				else
				{
					include_once("./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php");
					$a_users = ilObjSCORMTracking::_getTrackedUsers($obj_id);
				}
				break;

			case "exc":
				include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
				include_once("./Modules/Exercise/classes/class.ilObjExercise.php");
				$exc = new ilObjExercise($obj_id, false);
				$members = new ilExerciseMembers($exc);
				$a_users = $members->getMembers();
				break;

			case "tst":
				include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
				$class = ilLPStatusFactory::_getClassById($obj_id, ilLPObjSettings::LP_MODE_TEST_FINISHED);
				$a_users = $class::getParticipants($obj_id);
				break;
			
			default:
				// no sensible data: return null
				break;
		}
		
		return $a_users;
	}

	/**
	 * Build sql from filter definition
	 *
	 * @param	array	$where
	 * @param	array	$a_filters
	 * @param	bool	$a_aggregate
	 * @return	string
	 */
	static protected function buildFilters(array $where, array $a_filters = NULL, $a_aggregate = false)
    {
		global $ilDB;

		$having = array();

		if(sizeof($a_filters))
		{
			foreach($a_filters as $id => $value)
			{
				switch($id)
				{
					case "login":
					case "firstname":
					case "lastname":
					case "institution":
					case "department":
					case "street":
					case "email":
					case "matriculation":
					case "country":
					case "city":
					case "title":
						$where[] =  $ilDB->like("usr_data.".$id, "text", "%".$value."%");
						break;
					
					case "gender":
					case "zipcode":
					case "sel_country":
						$where[] = "usr_data.".$id." = ".$ilDB->quote($value ,"text");
						break;

					case "u_comment":
						$where[] = $ilDB->like("ut_lp_marks.".$id, "text", "%".$value."%");
						break;

					case "status":
						if($value == ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM)
						{
							// #10645 - not_attempted is default
							$where[] = "(ut_lp_marks.status = ".$ilDB->quote(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM ,"text").
								" OR ut_lp_marks.status IS NULL)";
							break;
						}
						// fallthrough
						
					case "mark":
						$where[] = "ut_lp_marks.".$id." = ".$ilDB->quote($value ,"text");
						break;

					case "percentage":
						if(!$a_aggregate)
						{
							if($value["from"])
							{
								$where[] =  "ut_lp_marks.".$id." >= ".$ilDB->quote($value["from"] ,"integer");
							}
							if($value["to"])
							{
								$where[] = "(ut_lp_marks.".$id." <= ".$ilDB->quote($value["to"] ,"integer").
									" OR ut_lp_marks.".$id." IS NULL)";
							}
						}
						else
						{
							if($value["from"])
							{
								$having[] = "ROUND(AVG(ut_lp_marks.".$id.")) >= ".$ilDB->quote($value["from"] ,"integer");
							}
							if($value["to"])
							{
								$having[] = "ROUND(AVG(ut_lp_marks.".$id.")) <= ".$ilDB->quote($value["to"] ,"integer");
							}
						}
					    break;

				    case "language":
						$where[] = "usr_pref.value = ".$ilDB->quote($value ,"text");
						break;

					// timestamp
					case "last_access":
						if($value["from"])
						{
							$value["from"] = substr($value["from"], 0, -2)."00";					
							$value["from"] = new ilDateTime($value["from"], IL_CAL_DATETIME);
							$value["from"] = $value["from"]->get(IL_CAL_UNIX);
						}
						if($value["to"])
						{
							if(strlen($value["to"]) == 19)
							{
								$value["to"] = substr($value["to"], 0, -2)."59"; // #14858					
							}
							$value["to"] = new ilDateTime($value["to"], IL_CAL_DATETIME);
							$value["to"] = $value["to"]->get(IL_CAL_UNIX);
						}
						// fallthrough

					case 'status_changed':
						// fallthrough
						
					case "registration":
						if($id == "registration")
						{
							$id = "create_date";
						}
						// fallthrough
						
				    case "create_date":
					case "first_access":
					case "birthday":
						if($value["from"])
						{
							$where[] = $id." >= ".$ilDB->quote($value["from"] ,"date");
						}
						if($value["to"])
						{							
							if(strlen($value["to"]) == 19)
							{
								$value["to"] = substr($value["to"], 0, -2)."59"; // #14858								
							}
							$where[] = $id." <= ".$ilDB->quote($value["to"] ,"date");
						}
					    break;

					case "read_count":
						if(!$a_aggregate)
						{
							if($value["from"])
							{
								$where[] =  "(read_event.".$id."+read_event.childs_".$id.") >= ".$ilDB->quote($value["from"] ,"integer");
							}
							if($value["to"])
							{
								$where[] = "((read_event.".$id."+read_event.childs_".$id.") <= ".$ilDB->quote($value["to"] ,"integer").
									" OR (read_event.".$id."+read_event.childs_".$id.") IS NULL)";
							}
						}
						else
						{
							if($value["from"])
							{
								$having[] =  "SUM(read_event.".$id."+read_event.childs_".$id.") >= ".$ilDB->quote($value["from"] ,"integer");
							}
							if($value["to"])
							{
								$having[] = "SUM(read_event.".$id."+read_event.childs_".$id.") <= ".$ilDB->quote($value["to"] ,"integer");
							}
						}
						break;

				    case "spent_seconds":						
						if(!$a_aggregate)
						{							
							if($value["from"])
							{
								$where[] =  "(read_event.".$id."+read_event.childs_".$id.") >= ".$ilDB->quote($value["from"] ,"integer");
							}
							if($value["to"])
							{
								$where[] = "((read_event.".$id."+read_event.childs_".$id.") <= ".$ilDB->quote($value["to"] ,"integer").
									" OR (read_event.".$id."+read_event.childs_".$id.") IS NULL)";
							}
						}
						else
						{
							if($value["from"])
							{
								$having[] =  "ROUND(AVG(read_event.".$id."+read_event.childs_".$id.")) >= ".$ilDB->quote($value["from"] ,"integer");
							}
							if($value["to"])
							{
								$having[] = "ROUND(AVG(read_event.".$id."+read_event.childs_".$id.")) <= ".$ilDB->quote($value["to"] ,"integer");
							}
						}
					    break;

					default:
						// var_dump("unknown: ".$id);
						break;
				}
			}
		}

		$sql = "";
		if(sizeof($where))
		{
			$sql .= " WHERE ".implode(" AND ", $where);
		}
		if(sizeof($having))
		{
			// ugly "having" hack because of summary view
			$sql .= " [[--HAVING ".implode(" AND ", $having)."HAVING--]]";
		}

		return $sql;
	}

	/**
	 * Build sql from field definition
	 *
	 * @param	array	&$a_fields
	 * @param	array	$a_additional_fields
	 * @param	bool	$a_aggregate
	 * @return array
	 */
	static protected function buildColumns(array &$a_fields, array $a_additional_fields = NULL, $a_aggregate = false)
	{
		if(sizeof($a_additional_fields))
		{
			$udf = NULL;
			foreach($a_additional_fields as $field)
			{
				if(substr($field, 0, 4) != "udf_")
				{
					$function = NULL;
					if($a_aggregate)
					{
						$pos = strrpos($field, "_");
						if($pos === false)
						{
							continue;
						}
						$function = strtoupper(substr($field, $pos+1));
						$field =  substr($field, 0, $pos);
						if(!in_array($function, array("MIN", "MAX", "SUM", "AVG", "COUNT")))
						{
							continue;
						}
					}

					switch($field)
					{
						case "language":
							if($function)
							{
								$a_fields[] = $function."(value) ".$field."_".strtolower($function);
							}
							else
							{
								$a_fields[] = "value ".$field;
							}
							break;
						
						case "read_count":
						case "spent_seconds":
							if(!$function)
							{
								$a_fields[] = "(".$field."+childs_".$field.") ".$field;
							}
							else
							{
								if($function == "AVG")
								{
									$a_fields[] = "ROUND(AVG(".$field."+childs_".$field."), 2) ".$field."_".strtolower($function);
								}
								else
								{
									$a_fields[] = $function."(".$field."+childs_".$field.") ".$field."_".strtolower($function);
								}
							}
							break;

						case "read_count_spent_seconds":							
							if($function == "AVG")
							{
								$a_fields[] = "ROUND(AVG((spent_seconds+childs_spent_seconds)/(read_count+childs_read_count)), 2) ".$field."_".strtolower($function);
							}
							break;

						default:
							if($function)
							{
								if($function == "AVG")
								{
									$a_fields[] = "ROUND(AVG(".$field."), 2) ".$field."_".strtolower($function);
								}
								else
								{
									$a_fields[] = $function."(".$field.") ".$field."_".strtolower($function);
								}
							}
							else
							{
								$a_fields[] = $field;
							}
							break;
					}
				}
				else
				{
					$udf[] = substr($field, 4);
				}
			}
			
			// clean-up
			$a_fields = array_unique($a_fields);
			if(is_array($udf))
			{
				$udf = array_unique($udf);
			}
			
			return $udf;
		}
	}

    /**
	 * Get (sub)objects for given object, also handles learning objectives (course only)
	 *
	 * @param	int		$a_parent_obj_id
	 * @param	int		$a_parent_ref_id
	 * @param	int		$use_collection
	 * @param	bool	$a_refresh_status
	 * @param	array	$a_user_ids
	 * @return	array	object_ids, objectives_parent_id
	 */
	static public function getObjectIds($a_parent_obj_id, $a_parent_ref_id = false,  $use_collection = true, $a_refresh_status = true, $a_user_ids = null)
	{	
		include_once "Services/Object/classes/class.ilObjectLP.php";
		
		$object_ids = array($a_parent_obj_id);
		$ref_ids = array($a_parent_obj_id => $a_parent_ref_id);
		$objectives_parent_id = $scorm = $subitems = false;		
		
		$olp = ilObjectLP::getInstance($a_parent_obj_id);
		$mode = $olp->getCurrentMode();
		switch($mode)
		{
			// what about LP_MODE_SCORM_PACKAGE ?
			case ilLPObjSettings::LP_MODE_SCORM:
				include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
				$status_scorm = ilLPStatusFactory::_getInstance($a_parent_obj_id, ilLPObjSettings::LP_MODE_SCORM);
				$scorm = $status_scorm->_getStatusInfo($a_parent_obj_id);
				break;
			
			case ilLPObjSettings::LP_MODE_OBJECTIVES:				
				if(ilObject::_lookupType($a_parent_obj_id) == "crs")
				{
					$objectives_parent_id = $a_parent_obj_id;
				}
				break;
				
			case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
				include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
				$status_coll_man = ilLPStatusFactory::_getInstance($a_parent_obj_id, ilLPObjSettings::LP_MODE_COLLECTION_MANUAL);
				$subitems = $status_coll_man->_getStatusInfo($a_parent_obj_id);
				break;
				
			case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
				include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
				$status_coll_tlt = ilLPStatusFactory::_getInstance($a_parent_obj_id, ilLPObjSettings::LP_MODE_COLLECTION_TLT);
				$subitems = $status_coll_tlt->_getStatusInfo($a_parent_obj_id);
				break;
				
			default:
				// lp collection
				if($use_collection)
				{				
					$collection = $olp->getCollectionInstance();
					if($collection)
					{
						foreach($collection->getItems() as $child_ref_id)
						{
							$child_id = ilObject::_lookupObjId($child_ref_id);
							$object_ids[] = $child_id;
							$ref_ids[$child_id] = $child_ref_id;
						}
					}
				}
				// all objects in branch
				else
				{
				   self::getSubTree($a_parent_ref_id, $object_ids, $ref_ids);
				   $object_ids = array_unique($object_ids);
				}
									
				foreach($object_ids as $idx => $object_id)
				{
					if(!$object_id)
					{						
						unset($object_ids[$idx]);
					}
				}								
				break;
		}
		
		if($a_refresh_status)
		{
			self::refreshObjectsStatus($object_ids, $a_user_ids);
		}
		
		return array("object_ids" => $object_ids,
			"ref_ids" => $ref_ids,
			"objectives_parent_id" => $objectives_parent_id,
			"scorm" => $scorm,
			"subitems" => $subitems);
	}

	/**
	 * Get complete branch of tree (recursively)
	 *
	 * @param int $a_parent_ref_id
	 * @param array $a_object_ids
	 * @param array $a_ref_ids
	 */
	static protected function getSubTree($a_parent_ref_id, array &$a_object_ids, array &$a_ref_ids)
	{
		global $tree;

		$children = $tree->getChilds($a_parent_ref_id);
		if($children)
		{
			foreach($children as $child)
			{
				if($child["type"] == "adm" || $child["type"] == "rolf")
				{
					continue;
				}

				// as there can be deactivated items in the collection
				// we should allow them here too
				
				$olp = ilObjectLP::getInstance($child["obj_id"]);			
				$cmode = $olp->getCurrentMode();
				
				/* see ilPluginLP
				if($cmode == ilLPObjSettings::LP_MODE_PLUGIN)
				{
					// #11368
					include_once "Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php";	
					if(ilRepositoryObjectPluginSlot::isTypePluginWithLP($child["type"], false))
					{
						$a_object_ids[] = $child["obj_id"];
						$a_ref_ids[$child["obj_id"]] = $child["ref_id"];
					}	
				} 
				*/
				
				if(/* $cmode != ilLPObjSettings::LP_MODE_DEACTIVATED && */ $cmode != ilLPObjSettings::LP_MODE_UNDEFINED)
				{
					$a_object_ids[] = $child["obj_id"];
					$a_ref_ids[$child["obj_id"]] = $child["ref_id"];
				}

				self::getSubTree($child["ref_id"], $a_object_ids, $a_ref_ids);
			}
	   }
	}

	/**
	 * Execute given queries, including count query
	 *
	 * @param	array	$queries	fields, query, count
	 * @param	string	$a_order_field
	 * @param	string	$a_order_dir
	 * @param	int		$a_offset
	 * @param	int		$a_limit
	 * @return	array	cnt, set
	 */
	static function executeQueries(array $queries,  $a_order_field = "", $a_order_dir = "", $a_offset = 0, $a_limit = 9999)
	{
		global $ilDB;

		$cnt = 0;
		$subqueries = array();
		foreach($queries as $item)
		{
			// ugly "having" hack because of summary view
			$item = str_replace("[[--HAVING", "HAVING", $item);
			$item = str_replace("HAVING--]]", "", $item);

			if(!isset($item["count"]))
			{
				$count_field = $item["fields"];
				$count_field = array_shift($count_field);
			}
			else
			{
				$count_field = $item["count"];
			}
			$count_query = "SELECT COUNT(".$count_field.") AS cnt".$item["query"];
			$set = $ilDB->query($count_query);
			if ($rec = $ilDB->fetchAssoc($set))
			{
				$cnt += $rec["cnt"];
			}

			$subqueries[] = "SELECT ".implode(",", $item["fields"]).$item["query"];
		}

		// set query
		$result = array();
		if($cnt > 0)
		{
			if(sizeof($subqueries) > 1)
			{
				$base = array_shift($subqueries);
				$query  = $base." UNION (".implode(") UNION (", $subqueries).")";
			}
			else
			{
				$query = $subqueries[0];
			}

			if ($a_order_dir != "asc" && $a_order_dir != "desc")
			{
				$a_order_dir = "asc";
			}
			if($a_order_field)
			{
				$query.= " ORDER BY ".$a_order_field." ".strtoupper($a_order_dir);
			}

			$offset = (int) $a_offset;
			$limit = (int) $a_limit;
			$ilDB->setLimit($limit, $offset);

			$set = $ilDB->query($query);
			while($rec = $ilDB->fetchAssoc($set))
			{
				$result[] = $rec;
			}
		}

		return array("cnt" => $cnt, "set" => $result);
	}

    /**
	 * Get status matrix for users on objects
	 *
	 * @param	int		$a_parent_ref_id
	 * @param	array	$a_obj_ids
	 * @param	string	$a_user_filter
	 * @param	array	$a_additional_fields
	 * @param	array	$a_privacy_fields
	 * @param	int		$a_check_agreement
	 * @return	array	cnt, set
	 */
	static function getUserObjectMatrix($a_parent_ref_id, $a_obj_ids, $a_user_filter = NULL,
		array $a_additional_fields = null, array $a_privacy_fields = null, $a_check_agreement = null)
	{
		global $ilDB;

		$result = array("cnt"=>0, "set"=>NULL);
	    if(sizeof($a_obj_ids))
		{			
			$where = array();
			$where[] = "usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");
			if($a_user_filter)
			{
				$where[] = $ilDB->like("usr_data.login", "text", "%".$a_user_filter."%");
			}

			// users
			$left = "";
			$a_users = self::getParticipantsForObject($a_parent_ref_id);
			if (is_array($a_users))
			{
				$left = "LEFT";
				$where[] = $ilDB->in("usr_data.usr_id", $a_users, false, "integer");
			}
			
			$parent_obj_id = ilObject::_lookupObjectId($a_parent_ref_id);	
			self::refreshObjectsStatus($a_obj_ids, $a_users);
			
			$fields = array("usr_data.usr_id", "login", "active");
			$udf = self::buildColumns($fields, $a_additional_fields);
				
			include_once("./Services/Tracking/classes/class.ilLPStatus.php");
																							
			$raw = array();
			foreach($a_obj_ids as $obj_id)
			{				
				// one request for each object
				$query = " FROM usr_data ".$left." JOIN read_event ON (read_event.usr_id = usr_data.usr_id".
					" AND read_event.obj_id = ".$ilDB->quote($obj_id, "integer").")".
					" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id ".
					" AND ut_lp_marks.obj_id = ".$ilDB->quote($obj_id, "integer").")".
					" LEFT JOIN usr_pref ON (usr_pref.usr_id = usr_data.usr_id AND keyword = ".$ilDB->quote("language", "text").")".
					self::buildFilters($where);

				$raw = self::executeQueries(array(array("fields"=>$fields, "query"=>$query)), "login");
				if($raw["cnt"])
				{
					// convert to final structure
					foreach($raw["set"] as $row)
					{
						$result["set"][$row["usr_id"]]["login"] = $row["login"];
						$result["set"][$row["usr_id"]]["usr_id"] = $row["usr_id"];
						
						// #14953
						$result["set"][$row["usr_id"]]["obj_".$obj_id] = $row["status"];
						$result["set"][$row["usr_id"]]["obj_".$obj_id."_perc"] = $row["percentage"];
						
						if($obj_id == $parent_obj_id)
						{
							$result["set"][$row["usr_id"]]["status_changed"] = $row["status_changed"];
							$result["set"][$row["usr_id"]]["last_access"] = $row["last_access"];
							$result["set"][$row["usr_id"]]["spent_seconds"] = $row["spent_seconds"];
							$result["set"][$row["usr_id"]]["read_count"] = $row["read_count"];
						}
						
						foreach($fields as $field)
						{
							// #14957 - value [as] language
							if(stristr($field, "language"))
							{
								$field = "language";
							}
							
							if(isset($row[$field]))
							{
								// #14955
								if($obj_id == $parent_obj_id || 
									!in_array($field, array("mark", "u_comment")))
								{
									$result["set"][$row["usr_id"]][$field] = $row[$field];							
								}
							}
						}						
					}
				}
			}
			
			$result["cnt"] = sizeof($result["set"]);	
			$result["users"] = $a_users;	
			
			self::getUDFAndHandlePrivacy($result, $udf, $a_check_agreement, $a_privacy_fields, $a_additional_fields);																									
		}
		return $result;
	}

	static public function getUserObjectiveMatrix($a_parent_obj_id, $a_users)
	{
		global $ilDB;
		
		if($a_parent_obj_id && $a_users)
		{						
			$res = array();
								
		    include_once "Services/Tracking/classes/class.ilLPStatus.php";									
			include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";	
			include_once "Modules/Course/classes/class.ilCourseObjective.php";
			$objective_ids = ilCourseObjective::_getObjectiveIds($a_parent_obj_id,true);
			
			// there may be missing entries for any user / objective combination
			foreach($objective_ids as $objective_id)
			{
				foreach($a_users as $user_id)
				{
					$res[$user_id][$objective_id] = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
				}
			}
			
			$query = "SELECT * FROM loc_user_results".
				" WHERE ".$ilDB->in("objective_id", $objective_ids, "", "integer").
				" AND ".$ilDB->in("user_id", $a_users, "", "integer").
				" AND type = ".$ilDB->quote(ilLOUserResults::TYPE_QUALIFIED, "integer");
			$set = $ilDB->query($query);
			while($row = $ilDB->fetchAssoc($set))
			{
				$objective_id = $row["objective_id"];
				$user_id = $row["user_id"];
				
				// see ilLOUserResults::getObjectiveStatusForLP()
				if($row["status"] == ilLOUserResults::STATUS_COMPLETED)
				{
					$res[$user_id][$objective_id] = ilLPStatus::LP_STATUS_COMPLETED_NUM;
				}
				else
				{
					$res[$user_id][$objective_id] = ilLPStatus::LP_STATUS_FAILED_NUM;
				}
			}
			
			return $res;						
		}
	}

	static public function getObjectAccessStatistics(array $a_ref_ids, $a_year, $a_month = null)
	{
		global $ilDB;

		$obj_ids = array_keys($a_ref_ids);

		if($a_month)
		{
			$column = "dd";
		}
		else
		{
			$column = "mm";
		}

		$res = array();
		$sql = "SELECT obj_id,".$column.",SUM(read_count) read_count,SUM(childs_read_count) childs_read_count,".
			"SUM(spent_seconds) spent_seconds,SUM(childs_spent_seconds) childs_spent_seconds".
			" FROM obj_stat".
			" WHERE ".$ilDB->in("obj_id", $obj_ids, "", "integer").
			" AND yyyy = ".$ilDB->quote($a_year, "integer");
		if($a_month)
		{
			$sql .= " AND mm = ".$ilDB->quote($a_month, "integer");
		}
		$sql .= " GROUP BY obj_id,".$column;
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$row["read_count"] += $row["childs_read_count"];
			$row["spent_seconds"] += $row["childs_spent_seconds"];
			$res[$row["obj_id"]][$row[$column]]["read_count"] += $row["read_count"];
			$res[$row["obj_id"]][$row[$column]]["spent_seconds"] += $row["spent_seconds"];
		}
		
		
		// add user data
		
		$sql = "SELECT obj_id,".$column.",SUM(counter) counter".
			" FROM obj_user_stat".
			" WHERE ".$ilDB->in("obj_id", $obj_ids, "", "integer").
			" AND yyyy = ".$ilDB->quote($a_year, "integer");
		if($a_month)
		{
			$sql .= " AND mm = ".$ilDB->quote($a_month, "integer");
		}
		$sql .= " GROUP BY obj_id,".$column;
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{							
			$res[$row["obj_id"]][$row[$column]]["users"] += $row["counter"];
		}
		
		return $res;
	}

	function getObjectTypeStatistics()
	{
		global $ilDB, $objDefinition;
		
		// re-use add new item selection (folder is not that important)
		$types = array_keys($objDefinition->getCreatableSubObjects("root", ilObjectDefinition::MODE_REPOSITORY));
		
		include_once "Services/Tree/classes/class.ilTree.php";
		$tree = new ilTree(1);
		$sql = "SELECT ".$tree->table_obj_data.".obj_id,".$tree->table_obj_data.".type,".
			$tree->table_tree.".".$tree->tree_pk.",".$tree->table_obj_reference.".ref_id".
			" FROM ".$tree->table_tree.
			" ".$tree->buildJoin().
			" WHERE ".$ilDB->in($tree->table_obj_data.".type", $types, "", "text");
		$set = $ilDB->query($sql);
		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["type"]]["type"] = $row["type"];
			$res[$row["type"]]["references"]++;
			$res[$row["type"]]["objects"][] = $row["obj_id"];
			if($row[$tree->tree_pk] < 0)
			{
				$res[$row["type"]]["deleted"]++;
			}
		}

		foreach($res as $type => $values)
		{
			$res[$type]["objects"] = sizeof(array_unique($values["objects"]));
		}
		
		return $res;
	}

	static public function getObjectDailyStatistics(array $a_ref_ids, $a_year, $a_month = null)
	{
		global $ilDB;

		$obj_ids = array_keys($a_ref_ids);

		$res = array();
		$sql = "SELECT obj_id,hh,SUM(read_count) read_count,SUM(childs_read_count) childs_read_count,".
			"SUM(spent_seconds) spent_seconds,SUM(childs_spent_seconds) childs_spent_seconds".
			" FROM obj_stat".
			" WHERE ".$ilDB->in("obj_id", $obj_ids, "", "integer").
			" AND yyyy = ".$ilDB->quote($a_year, "integer");
		if($a_month)
		{
			$sql .= " AND mm = ".$ilDB->quote($a_month, "integer");
		}
		$sql .= " GROUP BY obj_id,hh";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$row["read_count"] += $row["childs_read_count"];
			$row["spent_seconds"] += $row["childs_spent_seconds"];
			$res[$row["obj_id"]][(int)$row["hh"]]["read_count"] += $row["read_count"];
			$res[$row["obj_id"]][(int)$row["hh"]]["spent_seconds"] += $row["spent_seconds"];
		}
		return $res;
	}

	static public function getObjectStatisticsMonthlySummary()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT COUNT(*) AS COUNTER,yyyy,mm".
			" FROM obj_stat".
			" GROUP BY yyyy, mm".
			" ORDER BY yyyy DESC, mm DESC");
		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = array("month"=>$row["yyyy"]."-".$row["mm"],
				"count"=>$row["counter"]);
		}
		return $res;
	}

	static public function deleteObjectStatistics(array $a_months)
	{
		global $ilDB;
		
		// no combined column, have to concat
		$date_compare = $ilDB->in($ilDB->concat(array(array("yyyy", ""), 
						array($ilDB->quote("-", "text"), ""),
						array("mm", ""))), $a_months, "", "text");
		$sql = "DELETE FROM obj_stat".
			" WHERE ".$date_compare;	
		$ilDB->manipulate($sql);
		
		// fulldate == YYYYMMDD
		$tables = array("obj_lp_stat", "obj_type_stat", "obj_user_stat");				
		foreach($a_months as $month)
		{
			$year = substr($month, 0, 4);
			$month = substr($month, 5);
			$from = $year.str_pad($month, 2, "0", STR_PAD_LEFT)."01";
			$to = $year.str_pad($month, 2, "0", STR_PAD_LEFT)."31";

			foreach($tables as $table)
			{
				$sql = "DELETE FROM ".$table.
					" WHERE fulldate >= ".$ilDB->quote($from, "integer").
					" AND fulldate <= ".$ilDB->quote($to, "integer");
				$ilDB->manipulate($sql);
			}
		}		
	}
	
	static public function searchObjects($a_type, $a_title = null, $a_root = null, $a_hidden = null, $a_preset_obj_ids = null)
	{
		global $ilDB, $tree;
		
		if($a_type == "lres")
		{
			$a_type = array('lm','sahs','htlm','dbk');
		}
		
		$sql = "SELECT r.ref_id,r.obj_id".
			" FROM object_data o".
			" JOIN object_reference r ON (o.obj_id = r.obj_id)".
			" JOIN tree t ON (t.child = r.ref_id)".
			" WHERE t.tree = ".$ilDB->quote(1, "integer");
		
		if(!is_array($a_type))
		{
			$sql .= " AND o.type = ".$ilDB->quote($a_type, "text");		
		}
		else
		{
			$sql .= " AND ".$ilDB->in("o.type", $a_type, "", "text");
		}
		
		if($a_title)
		{
			$sql .= " AND (".$ilDB->like("o.title", "text", "%".$a_title."%").
				" OR ".$ilDB->like("o.description", "text", "%".$a_title."%").")";
		}
		
		if(is_array($a_hidden))
		{
			$sql .= " AND ".$ilDB->in("o.obj_id", $a_hidden, true, "integer");
		}
		
		if(is_array($a_preset_obj_ids))
		{
			$sql .= " AND ".$ilDB->in("o.obj_id", $a_preset_obj_ids, false, "integer");
		}
		
		$set = $ilDB->query($sql);
		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			if($a_root && $a_root != ROOT_FOLDER_ID)
			{
				foreach(ilObject::_getAllReferences($row['obj_id']) as $ref_id)
				{
					if($tree->isGrandChild($a_root, $ref_id))
					{
						$res[$row["obj_id"]][] = $row["ref_id"];	
						continue;
					}
				}
			}
			else
			{
				$res[$row["obj_id"]][] = $row["ref_id"];	
			}	
		}
		return $res;
	}
	
	/**
	 * check whether status (for all relevant users) exists
	 * 
	 * @param array $a_obj_ids
	 * @param array $a_users
	 */
	protected static function refreshObjectsStatus(array $a_obj_ids, $a_users = null)
	{		
		include_once("./Services/Tracking/classes/class.ilLPStatus.php");		
		foreach($a_obj_ids as $obj_id)
		{
			ilLPStatus::checkStatusForObject($obj_id, $a_users);
		}		
	}		
	
	/**
	 * Get last update info for object statistics
	 * 
	 * @return array
	 */
	public static function getObjectStatisticsLogInfo()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT COUNT(*) counter, MIN(tstamp) tstamp".
			" FROM obj_stat_log");
		return $ilDB->fetchAssoc($set);
	}
	
	static public function getObjectLPStatistics(array $a_obj_ids, $a_year, $a_month = null, $a_group_by_day = false)
	{
		global $ilDB;
		
		if($a_group_by_day)
		{
			$column = "dd";
		}
		else
		{
			$column = "mm,yyyy";
		}
		
		$res = array();
		$sql = "SELECT obj_id,".$column.",".
			"MIN(mem_cnt) mem_cnt_min,AVG(mem_cnt) mem_cnt_avg, MAX(mem_cnt) mem_cnt_max,".
			"MIN(in_progress) in_progress_min,AVG(in_progress) in_progress_avg,MAX(in_progress) in_progress_max,".
			"MIN(completed) completed_min,AVG(completed) completed_avg,MAX(completed) completed_max,".
			"MIN(failed) failed_min,AVG(failed) failed_avg,MAX(failed) failed_max,".
			"MIN(not_attempted) not_attempted_min,AVG(not_attempted) not_attempted_avg,MAX(not_attempted) not_attempted_max".
			" FROM obj_lp_stat".
			" WHERE ".$ilDB->in("obj_id", $a_obj_ids, "", "integer").
			" AND yyyy = ".$ilDB->quote($a_year, "integer");
		if($a_month)
		{
			$sql .= " AND mm = ".$ilDB->quote($a_month, "integer");
		}
		$sql .= " GROUP BY obj_id,".$column;
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		
		return $res;
	}
	
	function getObjectTypeStatisticsPerMonth($a_aggregation, $a_year = null)
	{
		global $ilDB;
		
		if(!$a_year)
		{
			$a_year = date("Y");
		}
		
		$agg = strtoupper($a_aggregation);
		
		$res = array();		
		$sql = "SELECT type,yyyy,mm,".$agg."(cnt_objects) cnt_objects,".$agg."(cnt_references) cnt_references,".
			"".$agg."(cnt_deleted) cnt_deleted FROM obj_type_stat".
			" WHERE yyyy = ".$ilDB->quote($a_year, "integer").
			" GROUP BY type,yyyy,mm";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{			
			$row["mm"] = str_pad($row["mm"], 2, "0", STR_PAD_LEFT);
			$res[$row["type"]][$row["yyyy"]."-".$row["mm"]] = array(
				"objects" => (int)$row["cnt_objects"],
				"references" => (int)$row["cnt_references"],
				"deleted" => (int)$row["cnt_deleted"]
			);
		}
		
		return $res;
	}
}

?>