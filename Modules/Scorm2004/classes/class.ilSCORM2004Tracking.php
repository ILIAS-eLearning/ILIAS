<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilSCORM2004Tracking
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @ingroup ModulesScormAicc
*/
class ilSCORM2004Tracking
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjSCORM2004Tracking()
	{
	}

	function _getInProgress($scorm_item_id,$a_obj_id)
	{
		
die("Not Implemented: ilSCORM2004Tracking_getInProgress");
/*
		global $ilDB;

		if(is_array($scorm_item_id))
		{
			$where = "WHERE sco_id IN(";
			$where .= implode(",",ilUtil::quoteArray($scorm_item_id));
			$where .= ") ";
			$where .= ("AND obj_id = ".$ilDB->quote($a_obj_id)." ");
			   
		}
		else
		{
			$where = "WHERE sco_id = ".$ilDB->quote($scorm_item_id)." ";
			$where .= ("AND obj_id = ".$ilDB->quote($a_obj_id)." ");
		}
				

		$query = "SELECT user_id,sco_id FROM scorm_tracking ".
			$where.
			"GROUP BY user_id, sco_id";
		

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$in_progress[$row->sco_id][] = $row->user_id;
		}
		return is_array($in_progress) ? $in_progress : array();
*/
	}

	function _getCompleted($scorm_item_id,$a_obj_id)
	{
		global $ilDB;
		
die("Not Implemented: ilSCORM2004Tracking_getCompleted");
/*
		if(is_array($scorm_item_id))
		{
			$where = "WHERE sco_id IN(".implode(",",ilUtil::quoteArray($scorm_item_id)).") ";
		}
		else
		{
			$where = "sco_id = ".$ilDB->quote($scorm_item_id)." ";
		}

		$query = "SELECT DISTINCT(user_id) FROM scorm_tracking ".
			$where.
			"AND obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND lvalue = 'cmi.core.lesson_status' ".
			"AND ( rvalue = 'completed' ".
			"OR rvalue = 'passed')";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
*/
	}

	function _getFailed($scorm_item_id,$a_obj_id)
	{
		global $ilDB;
		
die("Not Implemented: ilSCORM2004Tracking_getFailed");
/*
		if(is_array($scorm_item_id))
		{
			$where = "WHERE sco_id IN('".implode("','",$scorm_item_id)."') ";
		}
		else
		{
			$where = "sco_id = '".$scorm_item_id."' ";
		}

		$query = "SELECT DISTINCT(user_id) FROM scorm_tracking ".
			$where.
			"AND obj_id = '".$a_obj_id."' ".
			"AND lvalue = 'cmi.core.lesson_status' ".
			"AND rvalue = 'failed'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
*/
	}

	function _getCountCompletedPerUser($a_scorm_item_ids,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT cmi_node.user_id as user_id, COUNT(user_id) as completed FROM cp_node, cmi_node ".
			"WHERE cp_node.cp_node_id IN(".implode(",",ilUtil::quoteArray($a_scorm_item_ids)).
			") AND cp_node.cp_node_id = cmi_node.cp_node_id".
			" AND cp_node.slm_id = ".$ilDB->quote($a_obj_id).
			" AND completion_status = 'completed' ".
			" GROUP BY cmi_node.user_id";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$users[$row->user_id] = $row->completed;
		}

		return $users ? $users : array();
	}

	
	function _getProgressInfo($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM cmi_gobjective ".
			" WHERE objective_id = ".$ilDB->quote("-course_overall_status-").
			" AND scope_id = ".$ilDB->quote($a_obj_id);

		$res = $ilDB->query($query);

		$info['completed'] = array();
		$info['failed'] = array();
		$info['in_progress'] = array();

		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (self::_isCompleted($row["status"], $row["status"]))
			{
				$info['completed'][] = $row["user_id"];
			}
			if (self::_isInProgress($row["status"], $row["status"]))
			{
				$info['in_progress'][] = $row["user_id"];
			}
			if (self::_isFailed($row["status"], $row["status"]))
			{
				$info['failed'][] = $row["user_id"];
			}
		}

		return $info;
	}
	
	function _getItemProgressInfo($a_scorm_item_ids, $a_obj_id)
	{
		global $ilDB;

		$query = "SELECT cp_node.cp_node_id as id, cmi_node.user_id as user_id, ".
			" cmi_node.completion_status as completion, cmi_node.success_status as success ".
			" FROM cp_node, cmi_node ".
			"WHERE cp_node.cp_node_id IN(".implode(",",ilUtil::quoteArray($a_scorm_item_ids)).
			") AND cp_node.cp_node_id = cmi_node.cp_node_id".
			" AND cp_node.slm_id = ".$ilDB->quote($a_obj_id);

		$res = $ilDB->query($query);

		$info['completed'] = array();
		$info['failed'] = array();
		$info['in_progress'] = array();

		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// if any data available, set in progress.
			$info['in_progress'][$row["id"]][] = $row["user_id"];
			if ($row["completion"] == "completed" || $row["success"] == "passed")
			{
				$info['completed'][$row["id"]][] = $row["user_id"];
			}
			if ($row["success"] == "failed")
			{
				$info['failed'][$row["id"]][] = $row["user_id"];
			}
		}

		return $info;
	}

	/**
	* 
	*/
	static function _isCompleted($a_status, $a_satisfied)
	{
		if ($a_status == "completed")
		{
			return true;
		}
		
		return false;
	}
			
	/**
	* 
	*/
	static function _isInProgress($a_status, $a_satisfied)
	{
		if ($a_status != "completed")
		{
			return true;
		}
		
		return false;
	}

	/**
	* 
	*/
	static function _isFailed($a_status, $a_satisfied)
	{
		if ($a_status == "completed" && $a_satisfied == "notSatisfied")
		{
			return true;
		}
		
		return false;
	}

} // END class.ilSCORM2004Tracking
?>
