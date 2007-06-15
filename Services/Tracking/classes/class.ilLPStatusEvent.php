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

class ilLPStatusEvent extends ilLPStatus
{

	function ilLPStatusEvent($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getNotAttempted($a_obj_id)
	{
		global $ilObjDataCache;

		$status_info = ilLPStatusWrapper::_getStatusInfoByType($a_obj_id,'event');

		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		$member_obj = ilCourseParticipants::_getInstanceByObjId($status_info['crs_id']);
		$members = $member_obj->getParticipants();
		
		// diff in progress and completed (use stored result in LPStatusWrapper)
		$users = array_diff((array) $members,$inp = ilLPStatusWrapper::_getInProgressByType($a_obj_id,'event'));
		$users = array_diff((array) $users,$com = ilLPStatusWrapper::_getCompletedByType($a_obj_id,'event'));
		return $users;
	}

	function _getInProgress($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfoByType($a_obj_id,'event');

		// If registration is disabled in_progress is not available
		if(!$status_info['registration'])
		{
			return array();
		}
		// If event has occured in_progress is impossible
		if($status_info['starting_time'] < time())
		{
			return array();
		}

		// Otherwise all users who registered will get the status in progress
		return $status_info['registered_users'] ? $status_info['registered_users'] : array();
	}

	function _getCompleted($a_obj_id)
	{

		$status_info = ilLPStatusWrapper::_getStatusInfoByType($a_obj_id,'event');
		return $status_info['participated_users'] ? $status_info['participated_users'] : array();
	}

	function _getStatusInfo($a_obj_id)
	{
		include_once 'Modules/Course/classes/Event/class.ilEvent.php';
		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';

		$event = new ilEvent($a_obj_id);
		
		$status_info['crs_id'] = $event->getObjId();
		$status_info['registration'] = $event->enabledRegistration();
		$status_info['title'] = $event->getTitle();
		$status_info['description'] = $event->getDescription();

		$appointment =& $event->getFirstAppointment(); 
		$status_info['starting_time'] = $appointment->getStartingTime();
		$status_info['ending_time'] = $appointment->getEndingTime();

		$status_info['registered_users'] = ilEventParticipants::_getRegistered($a_obj_id);
		$status_info['participated_users'] = ilEventParticipants::_getParticipated($a_obj_id);

		return $status_info;
	}

}	
?>