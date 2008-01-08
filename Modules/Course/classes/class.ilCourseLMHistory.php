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
* class ilCourseLMHistory
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
*/

class ilCourseLMHistory
{
	var $db;

	var $course_id;
	var $user_id;

	function ilCourseLMHistory($crs_id,$user_id)
	{
		global $ilDB;

		$this->db =& $ilDB;

		$this->course_id = $crs_id;
		$this->user_id = $user_id;
	}

	function getUserId()
	{
		return $this->user_id;
	}
	function getCourseRefId()
	{
		return $this->course_id;
	}

	function _updateLastAccess($a_user_id,$a_lm_ref_id,$a_page_id)
	{
		global $tree,$ilDB;

		if(!$crs_ref_id = $tree->checkForParentType($a_lm_ref_id,'crs'))
		{
			return true;
		}

		// Delete old entries
		$query = "DELETE FROM crs_lm_history ".
			"WHERE lm_ref_id = ".$ilDB->quote($a_lm_ref_id)." ".
			"AND usr_id = ".$ilDB->quote($a_user_id)."";

		$ilDB->query($query);

		// Add new entry
		$query = "INSERT INTO crs_lm_history ".
			"SET usr_id = ".$ilDB->quote($a_user_id).", ".
			"crs_ref_id = ".$ilDB->quote($crs_ref_id).", ".
			"lm_ref_id = ".$ilDB->quote($a_lm_ref_id).", ".
			"lm_page_id = ".$ilDB->quote($a_page_id).", ".
			"last_access = ".$ilDB->quote(time())."";

		$ilDB->query($query);

		return true;
	}

	function getLastLM()
	{
		global $ilDB;
		
		$query = "SELECT * FROM crs_lm_history ".
			"WHERE usr_id = ".$ilDB->quote($this->getUserId())." ".
			"AND crs_ref_id = ".$ilDB->quote($this->getCourseRefId())." ".
			"ORDER BY last_access ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			return $row->lm_ref_id;
		}
		return false;
	}

	function getLMHistory()
	{
		global $ilDB;
		
		$query = "SELECT * FROM crs_lm_history ".
			"WHERE usr_id = ".$ilDB->quote($this->getUserId())." ".
			"AND crs_ref_id = ".$ilDB->quote($this->getCourseRefId())."";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$lm[$row->lm_ref_id]['lm_ref_id'] = $row->lm_ref_id;
			$lm[$row->lm_ref_id]['lm_page_id'] = $row->lm_page_id;
			$lm[$row->lm_ref_id]['last_access'] = $row->last_access;
		}
		return $lm ? $lm : array();
	}

	function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_lm_history WHERE usr_id = ".$ilDB->quote($a_usr_id)." ";
		$ilDB->query($query);

		return true;
	}
			
}
?>