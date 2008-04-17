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

		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		$member_obj = ilCourseParticipants::_getInstanceByObjId($status_info['crs_id']);
		$members = $member_obj->getParticipants();
		
		// diff in progress and completed (use stored result in LPStatusWrapper)
		$users = array_diff((array) $members,$inp = ilLPStatusWrapper::_getInProgress($a_obj_id));
		$users = array_diff((array) $users,$com = ilLPStatusWrapper::_getCompleted($a_obj_id));
		return $users;
	}

	function _getInProgress($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

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
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		return $status_info['participated_users'] ? $status_info['participated_users'] : array();
	}

	function _getStatusInfo($a_obj_id)
	{
		global $tree;

		include_once './Modules/Session/classes/class.ilEventParticipants.php';
		include_once('./Modules/Session/classes/class.ilObjSession.php');
		include_once('./Modules/Session/classes/class.ilSessionAppointment.php');

		$references	= ilObject::_getAllReferences($a_obj_id);	
		$ref_id = end($references);
		
		$course_ref_id = $tree->checkForParentType($ref_id,'crs');
		$course_obj_id = ilObject::_lookupObjId($course_ref_id);
		
		$status_info['crs_id'] = $course_obj_id;
		$status_info['registration'] = ilObjSession::_lookupRegistrationEnabled($a_obj_id);
		$status_info['title'] = ilObject::_lookupTitle($a_obj_id);
		$status_info['description'] = ilObject::_lookupDescription($a_obj_id);
		
		// TODO: needs static method
		#$appointment =& $event->getFirstAppointment();
		$time_info = ilSessionAppointment::_lookupAppointment($a_obj_id);
		$status_info['starting_time'] = $time_info['start'];
		$status_info['ending_time'] = $time_info['end'];
		$status_info['fullday'] = $time_info['fullday'];

		$status_info['registered_users'] = ilEventParticipants::_getRegistered($a_obj_id);
		$status_info['participated_users'] = ilEventParticipants::_getParticipated($a_obj_id);
		#var_dump("<pre>",$a_obj_id,$time_info,$status_info,"</pre>");
		return $status_info;
	}

}	
?>