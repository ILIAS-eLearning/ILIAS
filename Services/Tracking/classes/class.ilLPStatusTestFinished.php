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

class ilLPStatusTestFinished extends ilLPStatus
{

	function ilLPStatusTestFinished($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getInProgress($a_obj_id)
	{
		global $ilDB;

		include_once './Modules/Test/classes/class.ilObjTestAccess.php';

		$query = "SELECT DISTINCT(user_fi) FROM tst_active ".
			"WHERE tries = 0 ".
			"AND test_fi = '".ilObjTestAccess::_getTestIDFromObjectID($a_obj_id)."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->user_fi;
		}
		return $user_ids ? $user_ids : array();
	}


	function _getCompleted($a_obj_id)
	{
		global $ilDB;

		include_once './Modules/Test/classes/class.ilObjTestAccess.php';

		$query = "SELECT DISTINCT(user_fi) FROM tst_active ".
			"WHERE tries > 0 ".
			"AND test_fi = '".ilObjTestAccess::_getTestIDFromObjectID($a_obj_id)."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->user_fi;
		}
		return $user_ids ? $user_ids : array();
	}
}	
?>