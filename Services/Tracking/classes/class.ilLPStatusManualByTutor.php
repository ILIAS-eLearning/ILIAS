<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesTracking 
*/

include_once 'Services/Tracking/classes/class.ilLPStatus.php';

class ilLPStatusManualByTutor extends ilLPStatus
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int object id
	 * 
	 */
	function __construct($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db = $ilDB;
	}
	
	/**
	 * get not attempted
	 *
	 * @access public
	 * @param int object id
	 * @return array int Array of user ids
	 * 
	 */
	public function _getNotAttempted($a_obj_id)
	{
		global $ilObjDataCache;

		global $ilBench;
		$ilBench->start('LearningProgress','9161_LPStatusManual_notAttempted');

		switch($ilObjDataCache->lookupType($a_obj_id))
		{
			case 'crs':

				include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
				$members_obj = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
				$members = $members_obj->getParticipants();
			
				// diff in progress and completed (use stored result in LPStatusWrapper)
				$users = array_diff($members,$inp = ilLPStatusWrapper::_getInProgress($a_obj_id));
				$users = array_diff($users,$com = ilLPStatusWrapper::_getCompleted($a_obj_id));

				$ilBench->stop('LearningProgress','9161_LPStatusManual_notAttempted');
				return $users;

			case 'grp':
				
				include_once './Modules/Group/classes/class.ilObjGroup.php';

				$members = ilObjGroup::_getMembers($a_obj_id);
				// diff in progress and completed (use stored result in LPStatusWrapper)
				$users = array_diff($members,$inp = ilLPStatusWrapper::_getInProgress($a_obj_id));
				$users = array_diff($users,$com = ilLPStatusWrapper::_getCompleted($a_obj_id));

				$ilBench->stop('LearningProgress','9161_LPStatusManual_notAttempted');
				return $users;

			default:
				$ilBench->stop('LearningProgress','9161_LPStatusManual_notAttempted');
				return array();
		}
	}
	
	/**
	 * get in progress
	 *
	 * @access public
	 * @param int object id
	 * @return array int Array of user ids
	 * 
	 */
	public function _getInProgress($a_obj_id)
	{
		global $ilObjDataCache;

		global $ilBench;
		$ilBench->start('LearningProgress','9162_LPStatusManualByTutor_inProgress');


		switch($ilObjDataCache->lookupType($a_obj_id))
		{
			case 'crs':
				$ilBench->stop('LearningProgress','9162_LPStatusManualByTutor_inProgress');
				return self::__getCourseInProgress($a_obj_id);

			case 'grp':
				$ilBench->stop('LearningProgress','9162_LPStatusManualByTutor_inProgress');
				return self::__getGroupInProgress($a_obj_id);

			default:
				$ilBench->stop('LearningProgress','9162_LPStatusManualByTutor_inProgress');
				break;				
		}
		return array();
	 	
	}
	
	
	function __getCourseInProgress($a_obj_id)
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

	function __getGroupInProgress($a_obj_id)
	{
		global $ilDB;

		$completed = ilLPStatusWrapper::_getCompleted($a_obj_id);
		
		include_once './Modules/Group/classes/class.ilObjGroup.php';
		$members = ilObjGroup::_getMembers($a_obj_id);

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
		$ilBench->start('LearningProgress','9163_LPStatusManualByTutor_completed');

		$query = "SELECT DISTINCT(usr_id) as user_id FROM ut_lp_marks ".
			"WHERE obj_id = '".$a_obj_id."' ".
			"AND completed = '1'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->user_id;
		}
		$ilBench->stop('LearningProgress','9163_LPStatusManualByTutor_completed');
		return $usr_ids ? $usr_ids : array();
	}
}
?>