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

class ilLPStatusManual extends ilLPStatus
{

	function ilLPStatusManual($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getNotAttempted($a_obj_id)
	{
		return array();

		global $ilObjDataCache;

		switch($ilObjDataCache->lookupType($a_obj_id))
		{
			case 'crs':

				include_once 'course/classes/class.ilCourseMembers.php';
				
				$members = ilCourseMembers::_getMembers($a_obj_id);
			
				// diff in progress and completed (use stored result in LPStatusWrapper)
				$users = array_diff($members,$inp = ilLPStatusWrapper::_getInProgress($a_obj_id));
				$users = array_diff($users,$com = ilLPStatusWrapper::_getCompleted($a_obj_id));

				return $users;

			default:
				return array();
		}
	}

	function _getCountNotAttempted($a_obj_id)
	{
		return count(ilLPStatusWrapper::_getNotAttempted($a_obj_id));
	}

	
	function _getCountInProgress($a_obj_id)
	{
		return count(ilLPStatusManual::_getInProgress($a_obj_id));
	}
	function _getInProgress($a_obj_id)
	{
		global $ilObjDataCache;

		switch($ilObjDataCache->lookupType($a_obj_id))
		{
			case 'lm':
				return ilLPStatusManual::__getLMInProgress($a_obj_id);

			case 'crs':
				return ilLPStatusManual::__getCourseInProgress($a_obj_id);

			default:
				echo "ilLPStatusManual: unknown type ".$ilObjDataCache->lookupType($a_obj_id);
				
		}
		return array();
	}

	function _getCompleted($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT DISTINCT(usr_id) as user_id FROM ut_lp_marks ".
			"WHERE obj_id = '".$a_obj_id."' ".
			"AND completed = '1'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->user_id;
		}
		return $usr_ids ? $usr_ids : array();
	}

	function _getCountCompleted($a_obj_id)
	{
		return count(ilLPStatusManual::_getCompleted($a_obj_id));
	}


	// Private
	function __getLMInProgress($a_obj_id)
	{
		global $ilDB;

		$completed = ilLPStatusManual::_getCompleted($a_obj_id);

		$query = "SELECT DISTINCT(user_id) FROM ut_learning_progress ".
			"WHERE obj_id = '".$a_obj_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!in_array($row->user_id,$completed))
			{
				$user_ids[] = $row->user_id;
			}
		}
		return $user_ids ? $user_ids : array();
	}

	function __getCourseInProgress($a_obj_id)
	{
		global $ilDB;

		$completed = ilLPStatusManual::_getCompleted($a_obj_id);

		include_once 'course/classes/class.ilCourseMembers.php';

		foreach(ilCourseMembers::_getMembers($a_obj_id) as $usr_id)
		{
			if(!in_array($usr_id,$completed))
			{
				$user_ids[] = $usr_id;
			}
		}
		return $user_ids ? $user_ids : array();
	}

}	
?>