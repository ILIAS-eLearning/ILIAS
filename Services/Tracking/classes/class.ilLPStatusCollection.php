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

include_once './Services/Tracking/classes/class.ilLPStatus.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';

class ilLPStatusCollection extends ilLPStatus
{

	function ilLPStatusCollection($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getNotAttempted($a_obj_id)
	{
		include_once 'course/classes/class.ilCourseMembers.php';

		$members = ilCourseMembers::_getMembers($a_obj_id);

		// diff in progress and completed (use stored result in LPStatusWrapper)
		$users = array_diff((array) $members,$inp = ilLPStatusWrapper::_getInProgress($a_obj_id));
		$users = array_diff((array) $users,$com = ilLPStatusWrapper::_getCompleted($a_obj_id));

		return $users;
	}

	function _getCountNotAttempted($a_obj_id)
	{
		return count(ilLPStatusWrapper::_getNotAttempted($a_obj_id));
	}

	function _getCountInProgress($a_obj_id)
	{
		return count(ilLPStatusCollection::_getInProgress($a_obj_id));
	}

	function _getInProgress($a_obj_id)
	{
		include_once './Services/Tracking/classes/class.ilLPCollections.php';
		include_once 'course/classes/class.ilCourseMembers.php';

		$in_progress = 0;
		foreach(ilLPCollections::_getItems($a_obj_id) as $item_id)
		{
			// merge arrays of users with status 'in progress'
			$users = array_unique(array_merge((array) $users,ilLPStatusWrapper::_getInProgress($item_id)));
			$users = array_unique(array_merge((array) $users,ilLPStatusWrapper::_getCompleted($item_id)));
		}

		// Exclude all users with status completed.
		$users = array_diff((array) $users,ilLPStatusCollection::_getCompleted($a_obj_id));

		// Exclude all non members
		$users = array_intersect(ilCourseMembers::_getMembers($a_obj_id),(array) $users);

		return $users;
	}

	function _getCountCompleted($a_obj_id)
	{
		return count(ilLPStatusCollection::_getCompleted($a_obj_id));
	}

	function _getCompleted($a_obj_id)
	{
		include_once './Services/Tracking/classes/class.ilLPCollections.php';
		include_once 'course/classes/class.ilCourseMembers.php';

		$counter = 0;
		foreach(ilLPCollections::_getItems($a_obj_id) as $item_id)
		{
			$tmp_users = ilLPStatusWrapper::_getCompleted($item_id);
			if(!$counter++)
			{
				$users = $tmp_users;
			}
			else
			{
				$users = array_intersect($users,$tmp_users);
			}

		}
		// Exclude all non members
		$users = array_intersect(ilCourseMembers::_getMembers($a_obj_id),(array) $users);

		return (array) $users;
	}		

}	
?>