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

		$fields = array("usr_data.usr_id", "login");
	
		if (is_array($a_additional_fields))
		{
			$fields = array_merge($fields, $a_additional_fields);
		}
		
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
	

}
