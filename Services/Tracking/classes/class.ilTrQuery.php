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
	/**
	 * Get all user-based tracking data for object
	 *
	 * @param	int		$a_obj_id
	 * @param	string	$a_order_field
	 * @param	string	$a_order_dir
	 * @param	int		$a_offset
	 * @param	int		$a_limit
	 * @param	array	$a_filters
	 * @param	array	$a_additional_fields
	 * @return	array	cnt, set
	 */
	static function getUserDataForObject($a_obj_id, $a_order_field = "", $a_order_dir = "", 
		$a_offset = 0, $a_limit = 9999, array $a_filters = NULL, array $a_additional_fields = NULL)
	{
		global $ilDB;

		include_once("./Services/Tracking/classes/class.ilLPStatus.php");
		ilLPStatus::checkStatusForObject($a_obj_id);

		$fields = array("usr_data.usr_id", "login");
		self::buildColumns($fields, $a_additional_fields);

	    $where = array();
		$where[] = "usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");

		// users
		$left = "";
		$a_users = self::getParticipantsForObject($a_obj_id);
		if (is_array($a_users))
		{
			$left = "LEFT";
			$where[] = $ilDB->in("usr_data.usr_id", $a_users, false, "integer");
		}

		$query = " FROM usr_data ".$left." JOIN read_event ON (read_event.usr_id = usr_data.usr_id".
			" AND read_event.obj_id = ".$ilDB->quote($a_obj_id, "integer").")".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id ".
			" AND ut_lp_marks.obj_id = ".$ilDB->quote($a_obj_id, "integer").")".
			self::buildFilters($where, $a_filter);

		$queries = array(array("fields"=>$fields, "query"=>$query));

		if(!in_array($a_order_field, $fields))
		{
			$a_order_field = "login";
		}

		return self::executeQueries($queries, $a_order_field, $a_order_dir, $a_offset, $a_limit);
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
	 * @param	array	$a_filter
	 * @param	array	$a_additional_fields
	 * @param	bool	$use_collection
	 * @return	array	cnt, set
	 */
	static function getObjectsDataForUser($a_user_id, $a_parent_obj_id, $a_parent_ref_id, $a_order_field = "", $a_order_dir = "", $a_offset = 0, $a_limit = 9999,
		array $a_filter = NULL, array $a_additional_fields = NULL, $use_collection = true)
	{
		global $ilDB;
		
		$fields = array("object_data.obj_id", "title", "type");
		self::buildColumns($fields, $a_additional_fields);

		$objects = self::getObjectIds($a_parent_obj_id, $a_parent_ref_id, $use_collection);
		
		$query = " FROM object_data LEFT JOIN read_event ON (object_data.obj_id = read_event.obj_id AND".
			" read_event.usr_id = ".$ilDB->quote($a_user_id, "integer").")".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = ".$ilDB->quote($a_user_id, "integer")." AND".
			" ut_lp_marks.obj_id = object_data.obj_id)".
			" WHERE ".$ilDB->in("object_data.obj_id", $objects["object_ids"], false, "integer").
			self::buildFilters(array(), $a_filters);

		$queries = array();
		$queries[] = array("fields"=>$fields, "query"=>$query);

		if($objects["objectives_parent_id"])
		{
			$objective_fields = array("crs_objectives.objective_id AS obj_id", "title",
				$ilDB->quote("lobj", "text")." as type");
			
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
					$objective_fields[] = "(CASE WHEN status THEN ".LP_STATUS_COMPLETED_NUM." ELSE NULL END) AS status";
				}
			  }
			}

			$where = array();
			$where[] = "crs_objectives.crs_id = ".$ilDB->quote($objects["objectives_parent_id"], "integer");

			$objectives_query = " FROM crs_objectives".
				" LEFT JOIN crs_objective_status ON (crs_objectives.objective_id = crs_objective_status.objective_id".
				" AND crs_objective_status.user_id = ".$ilDB->quote($a_user_id, "integer").")".
				self::buildFilters($where, $a_filters);

			$queries[] = array("fields"=>$objective_fields, "query"=>$objectives_query, "count"=>"crs_objectives.objective_id");
		}
		
		if(!in_array($a_order_field, $fields))
		{
			$a_order_field = "title";
		}

		return self::executeQueries($queries, $a_order_field, $a_order_dir, $a_offset, $a_limit);
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
	 * @param	bool	$use_collection
	 * @return	array	cnt, set
	 */
	static function getObjectsSummaryForObject($a_parent_obj_id, $a_parent_ref_id, $a_order_field = "", $a_order_dir = "", $a_offset = 0, $a_limit = 9999,
		array $a_filters = NULL, array $a_additional_fields = NULL, $use_collection = true)
	{
		global $ilDB;
		
		$fields = array();
		self::buildColumns($fields, $a_additional_fields, true);
		
		$objects = self::getObjectIds($a_parent_obj_id, $a_parent_ref_id, false);

		// object data
		$set = $ilDB->query("SELECT obj_id,title,type FROM object_data WHERE ".$ilDB->in("obj_id", $objects["object_ids"], false, "integer"));
		while($rec = $ilDB->fetchAssoc($set))
		{
			$object_data[$rec["obj_id"]] = $rec;
		}
	
		$result = array();
		foreach($objects["object_ids"] as $object_id)
		{
			$object_result = self::getSummaryDataForObject($object_id, $fields, $a_filters);
			$result[] = array_merge($object_data[$object_id], $object_result);
		}

		// :TODO: objectives
		if($objects["objectives_parent_id"])
		{
			
		}

		return array("cnt"=>sizeof($result), "set"=>$result);
	}

	/**
	 * Get all aggregated tracking data for object
	 *
	 * @param	int		$a_obj_id
	 * @param	array	$fields
	 * @param	array	$a_filters
	 * @return	array
	 */
	protected static function getSummaryDataForObject($a_obj_id, array $fields, array $a_filters = NULL)
	{
		global $ilDB;

		$where = array();
		$where[] = "usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");

		// users
		$a_users = self::getParticipantsForObject($a_obj_id);
		$left = "";
		if (is_array($a_users) && sizeof($a_users))
		{
			$left = "LEFT";
			$where[] = $ilDB->in("usr_data.usr_id", $a_users, false, "integer");
		}

		$query = " FROM usr_data ".$left." JOIN read_event ON (read_event.usr_id = usr_data.usr_id".
			" AND obj_id = ".$ilDB->quote($a_obj_id, "integer").")".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id ".
			" AND ut_lp_marks.obj_id = ".$ilDB->quote($a_obj_id, "integer").")".
			" LEFT JOIN usr_pref ON (usr_pref.usr_id = usr_data.usr_id AND keyword = ".$ilDB->quote("language", "text").")".
			self::buildFilters($where, $a_filters);

		$queries = array();
		$queries[] = array("fields"=>$fields, "query"=>$query." GROUP BY read_event.obj_id", "count"=>"*");

		$result = self::executeQueries($queries);
		$users_no = $result["cnt"];
		$result = $result["set"][0];
		if($users_no && (!isset($a_filters["user_total"]) || ($users_no >= $a_filters["user_total"]["from"] && $users_no <= $a_filters["user_total"]["to"])))
		{
			$result["country"] = self::getSummaryPercentages("country", $query);
			$result["city"] = self::getSummaryPercentages("city", $query);
			$result["gender"] = self::getSummaryPercentages("gender", $query);
			$result["language"] = self::getSummaryPercentages("usr_pref.value", $query, "language");
			$result["status"] = self::getSummaryPercentages("status", $query);
			$result["mark"] = self::getSummaryPercentages("mark", $query);
		}

		$result["user_total"] = $users_no;

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

		$query = "SELECT COUNT(*) AS counter, ".$field.$alias." ".$base_query. " GROUP BY ".$field." ORDER BY counter DESC";
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[$rec[$field_alias]] = (int)$rec["counter"];
		}
		return $result;
	}

	/*
	static function getUserStatusForAllObjects($a_order_field = "", $a_order_dir = "", $a_offset = 0, $a_limit = 9999,
		array $a_filter = NULL)
	{
		// status only
		// rows: objects + objectives(?)
		// aggr. user

		// a_parent_obj_id: ROOT
		// a_ref_id: ROOT
		// fields: title, status
		// group_by: obj_id, status
	}

	static function getObjectStatusForUser($a_order_field = "", $a_order_dir = "", $a_offset = 0, $a_limit = 9999,
		array $a_filter = NULL)
	{
		// 1 user
		// status only
		// rows: objects

		// a_parent_obj_id: ROOT
		// a_ref_id: ROOT
		// fields: title, status
		// group_by: -
	}

	static function getStatusOverviewForObject($a_parent_obj_id, $a_order_field = "", $a_order_dir = "", $a_offset = 0, $a_limit = 9999,
		array $a_filter = NULL)
	{
		// status only
		// rows: objects + objectives
		// aggr user

		// a_parent_obj_id: ok
		// a_ref_id: ok
		// fields: title, status
		// group by: obj_id, status
	}
	 */

	/**
	 * Get participant ids for given object
	 *
	 * @param	int		$a_obj_id
	 * @return	array
	 */
	public static function getParticipantsForObject($a_obj_id)
	{
		$a_users = NULL;

		// @todo: move this to a parent or type related class later
		switch(ilObject::_lookupType($a_obj_id))
		{
			case "crs":
				$member_obj = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
				$a_users = $member_obj->getParticipants();
				break;

			case "sahs":
				$subtype = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);
				switch ($subtype)
				{
					case 'scorm2004':
						include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php");
						$a_users = ilSCORM2004Tracking::_getTrackedUsers($a_obj_id);
						break;

					default:
						include_once("./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php");
						$a_users = ilObjSCORMTracking::_getTrackedUsers($a_obj_id);
						break;
				}
				break;

			case "exc":
				include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
				include_once("./Modules/Exercise/classes/class.ilObjExercise.php");
				$exc = new ilObjExercise($a_obj_id, false);
				$members = new ilExerciseMembers($exc);
				$a_users = $members->getMembers();
				break;

			case "tst":
				include_once("./Services/Tracking/classes/class.ilLPStatusTestFinished.php");
				$a_users = ilLPStatusTestFinished::getParticipants($a_obj_id);
				break;
		}
		
		return $a_users;
	}

	/**
	 * Build sql from filter definition
	 *
	 * @param	array	$where
	 * @param	array	$a_filters
	 * @return	string
	 */
	static protected function buildFilters(array $where, array $a_filters = NULL)
    {
		global $ilDB;

		if(sizeof($a_filters))
		{
			foreach($a_filters as $id => $value)
			{
				switch($id)
				{
					case "country":
					case "gender":
					case "city":
						$where[] = "usr_data.".$id." = ".$ilDB->quote($value ,"text");
						break;

				    case "language":
						$where[] = "usr_pref.value = ".$ilDB->quote($value ,"text");
						break;

					// timestamp
					case "last_access":
						if($value["from"])
						{
							$value["from"] = new ilDateTime($value["from"], IL_CAL_DATETIME);
							$value["from"] = $value["from"]->get(IL_CAL_UNIX);
						}
						if($value["to"])
						{
							$value["to"] = new ilDateTime($value["to"], IL_CAL_DATETIME);
							$value["to"] = $value["to"]->get(IL_CAL_UNIX);
						}
						// fallthrough

					case "create_date":
					case "first_access":
						if($value["from"])
						{
							$where[] = $id." >= ".$ilDB->quote($value["from"] ,"date");
						}
						if($value["to"])
						{
							$where[] = $id." <= ".$ilDB->quote($value["to"] ,"date");
						}
					    break;

					default:
						break;
				}
			}
		}

		if(sizeof($where))
		{
			return " WHERE ".implode(" AND ", $where);
		}
	}

	/**
	 * Build sql from field definition
	 *
	 * @param	array	&$a_fields
	 * @param	array	$a_additional_fields
	 * @param	bool	$aggregate
	 */
	static protected function buildColumns(array &$a_fields, array $a_additional_fields = NULL, $aggregate = false)
	{
		if(sizeof($a_additional_fields))
		{
			foreach($a_additional_fields as $field)
			{
				$function = NULL;
				if($aggregate)
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
					case "read_count":
					case "spent_seconds":
						if(!$function)
						{
							$a_fields[] = "(".$field."+childs_".$field.") AS ".$field;
						}
						else
						{
							if($function == "AVG")
							{
								$a_fields[] = "ROUND(AVG(".$field."+childs_".$field."), 2) AS ".$field."_".strtolower($function);
							}
							else
							{
								$a_fields[] = $function."(".$field."+childs_".$field.") AS ".$field."_".strtolower($function);
							}
						}
						break;

					default:
						if($function)
						{
							if($function == "AVG")
							{
								$a_fields[] = "ROUND(AVG(".$field."), 2) AS ".$field."_".strtolower($function);
							}
							else
							{
								$a_fields[] = $function."(".$field.") AS ".$field."_".strtolower($function);
							}
						}
						else
						{
							$a_fields[] = $field;
						}
						break;
				}
			}
		}
	}

    /**
	 * Get (sub)objects for given object, also handles learning objectives (course only)
	 *
	 * @param	int		$a_parent_obj_id
	 * @param	int		$a_parent_ref_id
	 * @param	int		$use_collection
	 * @return	array	object_ids, objectives_parent_id
	 */
	static protected function getObjectIds($a_parent_obj_id, $a_parent_ref_id = false,  $use_collection = true)
	{
		global $tree;

		include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
		
		$object_ids = array($a_parent_obj_id);
		$objectives_parent_id = false;

		// lp collection
		if($use_collection)
		{
			if(ilLPObjSettings::_lookupMode($a_parent_obj_id) != LP_MODE_OBJECTIVES)
			{
				include_once 'Services/Tracking/classes/class.ilLPCollectionCache.php';
				foreach(ilLPCollectionCache::_getItems($a_parent_obj_id) as $child_ref_id)
				{
					$child_id = ilObject::_lookupObjId($child_ref_id);
					$object_ids[] = $child_id;
				}
			}
			// add objectives?
			else if(ilObject::_lookupType($a_parent_obj_id) == "crs")
			{
				$objectives_parent_id = $a_parent_obj_id;
			}
		}
		// all objects in branch
		else
		{
		   $children = $tree->getChilds($a_parent_ref_id);
		   if($children)
		   {
				foreach($children as $child)
				{
					$cmode = ilLPObjSettings::_lookupMode($child["obj_id"]);
					if($cmode != LP_MODE_DEACTIVATED && $cmode != LP_MODE_UNDEFINED)
					{
						$object_ids[] = $child["obj_id"];
					}
				}
		   }
		}

		include_once("./Services/Tracking/classes/class.ilLPStatus.php");
		foreach($object_ids as $object_id)
		{
			ilLPStatus::checkStatusForObject($object_id);
		}

		return array("object_ids"=>$object_ids, "objectives_parent_id"=>$objectives_parent_id);
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
}

?>