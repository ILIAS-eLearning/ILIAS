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
* @author Stefan Meyer <meyer@leifos.com>
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

		include_once './Services/Tracking/classes/class.ilChangeEvent.php';
		$all = ilChangeEvent::lookupUsersInProgress($a_obj_id);
		foreach($all as $user_id)
		{
			if(!in_array($user_id,$completed) and in_array($user_id,$members))
			{
				$user_ids[] = $user_id;
			}
		}
		return $user_ids ? $user_ids : array();
	}

	function _getCompleted($a_obj_id)
	{
		global $ilDB;

		global $ilBench;

		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		foreach($status_info['objective_result'] as $user_id => $completed)
		{
			if(count($completed) == $status_info['num_objectives'])
			{
				$usr_ids[] = $user_id;
			}
		}
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
			$in = $ilDB->in('objective_id',$status_info['objectives'], false,'integer');
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
	
	/**
	 * Determine status
	 *
	 * @param	integer		object id
	 * @param	integer		user id
	 * @param	object		object (optional depends on object type)
	 * @return	integer		status
	 */
	function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
	{
		global $ilObjDataCache, $ilDB;
	
		// the status completed depends on:
		// $status_info['num_objectives'] (ilLPStatusWrapper::_getStatusInfo($a_obj_id);)
		// - ilCourseObjective::_getObjectiveIds($a_obj_id);
		// - table crs_objectives manipulated in
		// - ilCourseObjective
		
		// $status_info['objective_result']  (ilLPStatusWrapper::_getStatusInfo($a_obj_id);)
		// table crs_objective_status (must not contain a dataset)
		// ilCourseObjectiveResult -> added ilLPStatusWrapper::_updateStatus()
	
		$status = LP_STATUS_NOT_ATTEMPTED_NUM;
		switch ($ilObjDataCache->lookupType($a_obj_id))
		{
			case "crs":
				if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id))
				{
					$status = LP_STATUS_IN_PROGRESS_NUM;

					include_once 'Modules/Course/classes/class.ilCourseObjective.php';
					$objectives = ilCourseObjective::_getObjectiveIds($a_obj_id);
					if ($objectives)
					{
						$set = $ilDB->query("SELECT count(objective_id) cnt FROM crs_objective_status ".
							"WHERE ".$ilDB->in('objective_id',$objectives, false,'integer').
							" AND user_id = ".$ilDB->quote($a_user_id, "integer"));
						if ($rec = $ilDB->fetchAssoc($set))
						{
							if ($rec["cnt"] == count($objectives))
							{
								$status = LP_STATUS_COMPLETED_NUM;
							}
						}
					}
				}
				break;			
		}
		return $status;
	}

}
		

?>