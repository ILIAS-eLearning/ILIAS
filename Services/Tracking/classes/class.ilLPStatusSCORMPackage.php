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
* @author Stefan Meyer <alex.killing@gmx.de>
*
* @version $Id$
*
*/

include_once './Services/Tracking/classes/class.ilLPStatus.php';

class ilLPStatusSCORMPackage extends ilLPStatus
{

	function ilLPStatusSCORMPackage($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getInProgress($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$users = $status_info['in_progress'];
		return array_unique($users);
	}

	function _getCompleted($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$users = $status_info['completed'];
		return array_unique($users);
	}

	function _getFailed($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$users = $status_info['failed'];
		return array_unique($users);
	}

	
	function _getStatusInfo($a_obj_id)
	{
		include_once './Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php';
		$status_info['subtype'] = "scorm2004";
		$info = ilSCORM2004Tracking::_getProgressInfo($a_obj_id);

		$status_info['completed'] = $info['completed'];
		$status_info['failed'] = $info['failed'];
		$status_info['in_progress'] = $info['in_progress'];

		return $status_info;
	}
}	
?>