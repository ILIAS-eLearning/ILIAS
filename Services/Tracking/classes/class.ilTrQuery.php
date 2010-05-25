<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tracking query class. Put any complex queries into this class. Keep 
 * tracking class small.
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilTrQuery
{
	/**
	* Get data for user administration list.
	*/
	static function getDataForObject($a_obj_id, $a_order_field, $a_order_dir, $a_offset, $a_limit,
		$a_filter = array(), $a_additional_fields = "")
	{
		global $ilDB, $rbacreview;

		include_once("./Services/Tracking/classes/class.ilLPStatus.php");
		ilLPStatus::checkStatusForObject($a_obj_id);

		$fields = array("usr_data.usr_id", "login");
	
		if (is_array($a_additional_fields))
		{
			foreach($a_additional_fields as $field)
			{
				switch($field)
				{
					// add values from children
					case "spent_seconds":
					case "read_count":
						$fields[] = "(".$field."+childs_".$field.") AS ".$field;
						break;

					default:
						$fields[] = $field;
						break;
				}
			}
		}

		$a_users = self::getParticipantsForObject($a_obj_id);
		if (is_array($a_users))
		{
			$left = "LEFT";
			$join_and = "AND ".$ilDB->in("usr_data.usr_id", $a_users, false, "integer");
		}
		
		// count query
		$count_query = "SELECT count(usr_data.usr_id) cnt".
			" FROM usr_data $left JOIN read_event ON (read_event.usr_id = usr_data.usr_id".
			" AND read_event.obj_id = ".$ilDB->quote($a_obj_id, "integer").")".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id ".
			" AND ut_lp_marks.obj_id = ".$ilDB->quote($a_obj_id, "integer").")";
			
		// basic query
		$query = "SELECT ".implode($fields, ",").
			" FROM usr_data $left JOIN read_event ON (read_event.usr_id = usr_data.usr_id".
			" AND obj_id = ".$ilDB->quote($a_obj_id, "integer").")".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id ".
			" AND ut_lp_marks.obj_id = ".$ilDB->quote($a_obj_id, "integer").")";

		// filter
		$query.= " WHERE usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer")." ".$join_and;
		$count_query.= " WHERE usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer").
			" ".$join_and;
		$where = " AND";
//var_dump($query);	
		if ($a_filter["string"] != "")		// email, name, login
		{
			$add = $where." (".$ilDB->like("usr_data.login", "text", $a_filter["string"]."%")." ".
				"OR ".$ilDB->like("usr_data.firstname", "text", $a_filter["string"]."%")." ".
				"OR ".$ilDB->like("usr_data.lastname", "text", $a_filter["string"]."%")." ".
				"OR ".$ilDB->like("usr_data.email", "text", "%".$a_filter["string"]."%").") ";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}

		// order by
		if (!in_array($a_order_field, $fields))
		{
			$a_order_field = "login";
		}
		if ($a_order_dir != "asc" && $a_order_dir != "desc")
		{
			$a_order_dir = "asc";
		}
		$query.= " ORDER BY ".$a_order_field." ".strtoupper($a_order_dir);
		
		// count query
		$set = $ilDB->query($count_query);
		$cnt = 0;
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$cnt = $rec["cnt"];
		}
		
		$offset = (int) $a_offset;
		$limit = (int) $a_limit;
		$ilDB->setLimit($limit, $offset);
//var_dump($query);
		// set query
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec;
		}
		return array("cnt" => $cnt, "set" => $result);
	}

	public static function getParticipantsForObject($a_obj_id, array $filter = NULL)
	{
		$a_users = NULL;

		// @todo: move this to a parent or type related class later
		if (ilObject::_lookupType($a_obj_id) == "crs")
		{
			$member_obj = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
			$a_users = $member_obj->getParticipants();
		}
		if (ilObject::_lookupType($a_obj_id) == "exc")
		{
			include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
			include_once("./Modules/Exercise/classes/class.ilObjExercise.php");
			$exc = new ilObjExercise($a_obj_id, false);
			$members = new ilExerciseMembers($exc);
			$a_users = $members->getMembers();
		}
		if (ilObject::_lookupType($a_obj_id) == "sahs")
		{
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
		}
		if (ilObject::_lookupType($a_obj_id) == "tst")
		{
			include_once("./Services/Tracking/classes/class.ilLPStatusTestFinished.php");
			$a_users = ilLPStatusTestFinished::getParticipants($a_obj_id);
		}

		return $a_users;
	}

	public static function getSummaryDataForObject($a_obj_id, array $filter = NULL)
	{
		global $ilDB;
		
		$a_users = self::getParticipantsForObject($a_obj_id);
		$left = $join_and = "";
		if (is_array($a_users) && sizeof($a_users))
		{
			$left = "LEFT";
			$join_and = "AND ".$ilDB->in("usr_data.usr_id", $a_users, false, "integer");
		}

		$base_query = " FROM usr_data ".$left." JOIN read_event ON (read_event.usr_id = usr_data.usr_id".
			" AND obj_id = ".$ilDB->quote($a_obj_id, "integer").")".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id ".
			" AND ut_lp_marks.obj_id = ".$ilDB->quote($a_obj_id, "integer").")".
			" LEFT JOIN usr_pref ON (usr_pref.usr_id = usr_data.usr_id AND keyword = ".$ilDB->quote("language", "text").")".
			" WHERE usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer")." ".$join_and;

		// additional filters
		if(sizeof($filter))
		{
			$where_and = array();
			$date_map = array(
				"registration"=>"usr_data.create_date",
				"activity_earliest"=>"first_access",
				"activity_latest"=>"last_access");
			foreach($filter as $id => $value)
			{
				switch($id)
				{
					case "country":
					case "gender":
					case "city":
						$where_and[] = "usr_data.".$id." = ".$ilDB->quote($value ,"text");
						break;

				    case "language":
						$where_and[] = "usr_pref.value = ".$ilDB->quote($value ,"text");
						break;

					// timestamp
					case "activity_latest":
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

					case "registration":
					case "activity_earliest":
						if($value["from"])
						{
							$where_and[] = $date_map[$id]." >= ".$ilDB->quote($value["from"] ,"date");
						}
						if($value["to"])
						{
							$where_and[] = $date_map[$id]." <= ".$ilDB->quote($value["to"] ,"date");
						}
					    break;

					default:
						break;
				}
			}
			if(sizeof($where_and))
			{
				$base_query .= " AND ".join(" AND ", $where_and);
			}
		}

		$result = array();
	 
		// count query

		$query = "SELECT COUNT(*) AS counter".$base_query;
		$set = $ilDB->query($query);
		$users_no = $ilDB->fetchAssoc($set);
		$users_no = $users_no["counter"];
		if($users_no && (!isset($filter["user_total"]) || ($users_no >= $filter["user_total"]["from"] && $users_no <= $filter["user_total"]["to"])))
		{
			// --- user related

			// percentages
			$result["countries"] = self::getSummaryPercentages("country", $base_query);
			$result["cities"] = self::getSummaryPercentages("city", $base_query);
			$result["gender"] = self::getSummaryPercentages("gender", $base_query);
			$result["languages"] = self::getSummaryPercentages("usr_pref.value", $base_query, "language");

			// registration dates
			$query = "SELECT MIN(create_date) AS first_registration, MAX(create_date) AS last_registration ".$base_query;
			$set = $ilDB->query($query);
			$dates = $ilDB->fetchAssoc($set);
			$result["first_registration"] = $dates["first_registration"]; // datetime
			$result["last_registration"] = $dates["last_registration"]; // datetime


			// --- tracking / read-event related

			$query = "SELECT SUM(read_count+childs_read_count) AS sum_accesss, AVG(read_count+childs_read_count) AS avg_access, MIN(first_access) AS first_access, MAX(last_access) AS last_access, AVG(spent_seconds) AS learn_time, AVG(percentage) AS completion ".$base_query;
			$set = $ilDB->query($query);
			$access = $ilDB->fetchAssoc($set);
			$result["sum_access"] = (int)$access["sum_accesss"];
			$result["avg_access"] = (int)$access["avg_access"];
			$result["first_access"] = $access["first_access"]; // datetime
			$result["last_access"] = $access["last_access"]; // timestamp
			$result["avg_learn_time"] = (int)$access["learn_time"];
			$result["avg_completion"] = (int)$access["completion"];

			$result["status"] = self::getSummaryPercentages("status", $base_query);
			$result["mark"] = self::getSummaryPercentages("mark", $base_query);
		}

		return array("cnt"=>$users_no, "set"=>$result);
	}

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

	/**
	 *
	 * 
	 *
	 */
	static function getObjectHierarchy($a_parent_id, array &$result)
	{
		include_once 'Services/Tracking/classes/class.ilLPCollectionCache.php';
		foreach(ilLPCollectionCache::_getItems($a_parent_id) as $child_ref_id)
		{
			$child_id = ilObject::_lookupObjId($child_ref_id);
			$result[] = $child_id;
			self::getObjectHierarchy($child_id, $result);
		}
	}

	/**
	* Get data for user administration list.
	*/
	static function getDataForUser($a_user_id, array $a_object_ids, $a_order_field, $a_order_dir, $a_offset, $a_limit,
		$a_filter = array(), $a_additional_fields = "")
	{
		global $ilDB, $rbacreview;

		/*
		include_once("./Services/Tracking/classes/class.ilLPStatus.php");
		ilLPStatus::checkStatusForObject($a_obj_id);
		*/

		$fields = $group_by = array("read_event.obj_id");

		if (is_array($a_additional_fields))
		{
			foreach($a_additional_fields as $field)
			{
				switch($field)
				{
					case "first_access":
						$fields[] = "min(".$field.") AS ".$field;
						break;

				    case "last_access":
						$fields[] = "max(".$field.") AS ".$field;
						break;

					case "read_count":
					case "spent_seconds":
						$fields[] = "sum(".$field."+childs_".$field.") AS ".$field;
						break;
					
					default:
						$group_by[] = $field;
						$fields[] = $field;
						break;
				}
			}
		}

		// count query
		$count_query = "SELECT count(read_event.obj_id) cnt".
			" FROM read_event".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = read_event.usr_id AND".
			" ut_lp_marks.obj_id = read_event.obj_id)";

		// basic query
		$query = "SELECT ".implode($fields, ",").
			" FROM read_event".
			" LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = read_event.usr_id AND".
			" ut_lp_marks.obj_id = read_event.obj_id)";

		// filter
		$query.= " WHERE read_event.usr_id = ".$ilDB->quote($a_user_id, "integer")." AND ".$ilDB->in("read_event.obj_id", $a_object_ids, false, "integer");
		$count_query.= " WHERE read_event.usr_id = ".$ilDB->quote($a_user_id, "integer")." AND ".$ilDB->in("read_event.obj_id", $a_object_ids, false, "integer");

		/*
		$where = " AND";
//var_dump($query);
		if ($a_filter["string"] != "")		// email, name, login
		{
			$add = $where." (".$ilDB->like("usr_data.login", "text", $a_filter["string"]."%")." ".
				"OR ".$ilDB->like("usr_data.firstname", "text", $a_filter["string"]."%")." ".
				"OR ".$ilDB->like("usr_data.lastname", "text", $a_filter["string"]."%")." ".
				"OR ".$ilDB->like("usr_data.email", "text", "%".$a_filter["string"]."%").") ";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		 */

		$query .= " GROUP BY ".implode(", ", $group_by);
		$count_query .= " GROUP BY ".implode(", ", $group_by);

		// order by
		if (!in_array($a_order_field, $fields))
		{
			$a_order_field = "read_event.obj_id";
		}
		if ($a_order_dir != "asc" && $a_order_dir != "desc")
		{
			$a_order_dir = "asc";
		}
		$query.= " ORDER BY ".$a_order_field." ".strtoupper($a_order_dir);

		// count query
		$set = $ilDB->query($count_query);
		$cnt = 0;
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$cnt = $rec["cnt"];
		}

		$offset = (int) $a_offset;
		$limit = (int) $a_limit;
		$ilDB->setLimit($limit, $offset);

		// set query
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec;
		}

		return array("cnt" => $cnt, "set" => $result);
	}
}
