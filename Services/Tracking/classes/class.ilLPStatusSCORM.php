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

class ilLPStatusSCORM extends ilLPStatus
{

	function ilLPStatusSCORM($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getCountNotAttempted($a_obj_id)
	{
		return 999;
	}
	
	function _getCountInProgress($a_obj_id)
	{
		return count(ilLPStatusSCORM::_getInProgress($a_obj_id));
	}

	function _getInProgress($a_obj_id)
	{
		include_once './Services/Tracking/classes/class.ilLPCollections.php';
		include_once './content/classes/SCORM/class.ilObjSCORMTracking.php';

		return  array_diff(ilObjSCORMTracking::_getInProgress(ilLPCollections::_getItems($a_obj_id)),
						   ilLPStatusSCORM::_getCompleted($a_obj_id));
	}

	function _getCountCompleted($a_obj_id)
	{
		return count(ilLPStatusSCORM::_getCompleted($a_obj_id));
	}

	function _getCompleted($a_obj_id)
	{
		global $ilDB;

		include_once './Services/Tracking/classes/class.ilLPCollections.php';
		include_once './content/classes/SCORM/class.ilObjSCORMTracking.php';

		$counter = 0;
		foreach(ilLPCollections::_getItems($a_obj_id) as $sco_id)
		{
			$tmp_users = ilObjSCORMTracking::_getCompleted($sco_id);
			if(!$counter++)
			{
				$users = $tmp_users;
			}
			else
			{
				$users = array_intersect($users,$tmp_users);
			}
		}
		return (array) $users;

	}		

}	
?>