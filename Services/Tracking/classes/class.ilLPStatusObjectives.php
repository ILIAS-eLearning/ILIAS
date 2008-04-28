<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

include_once 'Services/Tracking/classes/class.ilLPStatus.php';

class ilLPStatusObjectives extends ilLPStatus
{

	function ilLPStatusObjectives($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getNotAttempted($a_obj_id)
	{
		global $ilObjDataCache;

		global $ilBench;
		$ilBench->start('LearningProgress','9171_LPStatusObjectives_notAttempted');

		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		$members_obj = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
		$members = $members_obj->getParticipants();
			
		// diff in progress and completed (use stored result in LPStatusWrapper)
		$users = array_diff($members,$inp = ilLPStatusWrapper::_getInProgress($a_obj_id));
		$users = array_diff($users,$com = ilLPStatusWrapper::_getCompleted($a_obj_id));

		$ilBench->stop('LearningProgress','9171_LPStatusObjectives_notAttempted');
		return $users ? $users : array();
	}

	function _getInProgress($a_obj_id)
	{
		global $ilDB;

		$completed = ilLPStatusWrapper::_getCompleted($a_obj_id);
		
		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		$members_obj = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
		$members = $members_obj->getParticipants();

		$query = "SELECT DISTINCT(usr_id) FROM read_event ".
			"WHERE obj_id = '".$a_obj_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!in_array($row->usr_id,$completed) and in_array($row->usr_id,$members))
			{
				$user_ids[] = $row->usr_id;
			}
		}
		return $user_ids ? $user_ids : array();
	}

	function _getCompleted($a_obj_id)
	{
		global $ilDB;

		global $ilBench;
		$ilBench->start('LearningProgress','9173_LPStatusObjectives_completed');

		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		foreach($status_info['objective_result'] as $user_id => $completed)
		{
			if(count($completed) == $status_info['num_objectives'])
			{
				$usr_ids[] = $user_id;
			}
		}
		$ilBench->stop('LearningProgress','9173_LPStatusObjectives_completed');
		return $usr_ids ? $usr_ids : array();
	}


	function _getStatusInfo($a_obj_id)
	{
		include_once 'Modules/Course/classes/class.ilCourseObjective.php';

		global $ilDB;

		$status_info['objective_result'] = array();
		$status_info['objectives'] = ilCourseObjective::_getObjectiveIds($a_obj_id);
		$status_info['num_objectives'] = count($status_info['objectives']);

		if(!$status_info['num_objectives'])
		{
			return $status_info;
		}
		else
		{
			$in = "objective_id IN('".implode("','",$status_info['objectives'])."') ";
		}

		$query = "SELECT * FROM crs_objective_status ".
			"WHERE ".$in;

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$status_info['completed'][$row->objective_id][] = $row->user_id;
			$status_info['objective_result'][$row->user_id][$row->objective_id] = $row->objective_id;
		}

		// Read title/description
		$query = "SELECT * FROM crs_objectives ".
			"WHERE ".$in;
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$status_info['objective_title'][$row->objective_id] = $row->title;
			$status_info['objective_description'][$row->objective_id] = $row->description;
		}
		return $status_info;
	}
}
		

?>