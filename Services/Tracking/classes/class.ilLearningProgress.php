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
* Class ilLearningProgress
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
*/

class ilLearningProgress
{
	var $db = null;

	function ilLearningProgress()
	{
		global $ilDB;
		
		$this->db = $ilDB;
	}

	// Static
	function _tracProgress($a_user_id,$a_obj_id, $a_obj_type = '')
	{
		global $ilDB,$ilObjDataCache;

		if(!strlen($a_obj_type))
		{
			$a_obj_type = $ilObjDataCache->lookupType($a_obj_id);
		}

		if($progress = ilLearningProgress::_getProgress($a_user_id,$a_obj_id))
		{
			ilLearningProgress::_updateProgress($progress);
			return true;
		}
		$query = "INSERT INTO ut_learning_progress ".
			"SET user_id = '".$a_user_id."', ".
			"obj_type = '".$a_obj_type."', ".
			"obj_id = '".$a_obj_id."', ".
			"spent_time = '0',".
			"access_time = '".time()."', ".
			"visits = '1'";

		$ilDB->query($query);
		return true;
	}

	function _getProgress($a_user_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM ut_learning_progress ".
			"WHERE user_id = '".$a_user_id."' ".
			"AND obj_id = '".$a_obj_id."'";
		$res = $ilDB->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$progress['lp_id'] = $row->lp_id;
			$progress['obj_id'] = $row->obj_id;
			$progress['obj_type'] = $row->obj_type;
			$progress['user_id'] = $row->user_id;
			$progress['spent_time'] = $row->spent_time;
			$progress['access_time'] = $row->access_time;
			$progress['visits'] = $row->visits;
		}
		return $progress ? $progress : array();
	}

	function _updateProgress($data)
	{
		global $ilDB;

		$spent = $data['spent_time'];
		
		include_once('Services/Tracking/classes/class.ilObjUserTracking.php');
		if((time() - $data['access_time']) <= ilObjUserTracking::_getValidTimeSpan())
		{
			$spent = $data['spent_time'] + time() - $data['access_time'];
		}
		$visits = $data['visits'] + 1;
		
		$query = "UPDATE ut_learning_progress ".
			"SET visits = visits + 1, ".
			"spent_time = '".$spent."', ".
			"access_time = '".time()."' ". 
			"WHERE lp_id = '".$data['lp_id']."'";

		$ilDB->query($query);
	}
}
?>