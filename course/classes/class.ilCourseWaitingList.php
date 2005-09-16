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
* class ilobjcourse
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/

class ilCourseWaitingList
{
	var $db = null;
	var $course_id = 0;
	


	function ilCourseWaitingList($a_course_id)
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->course_id = $a_course_id;

		$this->__read();
	}

	function getCourseId()
	{
		return $this->course_id;
	}

	function addToList($a_usr_id)
	{
		if($this->isOnList($a_usr_id))
		{
			return false;
		}
		$query = "INSERT INTO crs_waiting_list ".
			"SET obj_id = '".$this->getCourseId()."', ".
			"usr_id = '".$a_usr_id."', ".
			"sub_time = '".time()."'";

		$this->db->query($query);
		$this->__read();

		return false;
	}

	function updateSubscriptionTime($a_usr_id,$a_subtime)
	{
		$query = "UPDATE crs_waiting_list ".
			"SET sub_time = '".ilUtil::prepareDBString($a_subtime)."' ".
			"WHERE usr_id = '".ilUtil::prepareDBString($a_usr_id)."' ".
			"AND obj_id = '".$this->getCourseId()."'";

		$this->db->query($query);

		return true;
	}

	function removeFromList($a_usr_id)
	{
		$query = "DELETE FROM crs_waiting_list ".
			" WHERE obj_id = '".$this->getCourseId()."' ".
			" AND usr_id = '".$a_usr_id."'";

		$this->db->query($query);
		$this->__read();

		return true;
	}

	function isOnList($a_usr_id)
	{	
		return isset($this->users[$a_usr_id]) ? true : false;
	}

	function getCountUsers()
	{
		return count($this->users);
	}

	function getPosition($a_usr_id)
	{
		return isset($this->users[$a_usr_id]) ? $this->users[$a_usr_id]['position'] : -1;
	}

	function getAllUsers()
	{
		return $this->users ? $this->users : array();
	}
	
	function getUser($a_usr_id)
	{
		return isset($this->users[$a_usr_id]) ? $this->users[$a_usr_id] : false;
	}


	// PRIVATE
	function __read()
	{
		$this->users = array();

		$query = "SELECT * FROM crs_waiting_list ".
			"WHERE obj_id = '".$this->getCourseId()."' ORDER BY sub_time";

		$res = $this->db->query($query);
		$counter = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			++$counter;
			$this->users[$row->usr_id]['position']	= $counter;
			$this->users[$row->usr_id]['time']		= $row->sub_time;
			$this->users[$row->usr_id]['usr_id']	= $row->usr_id;

		}
		return true;
	}

	// Static
	function _deleteAll($a_course_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_waiting_list WHERE obj_id = '".$a_course_id."'";
		$ilDB->query($query);

		return true;
	}
	function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_waiting_list WHERE usr_id = '".$a_usr_id."'";
		$ilDB->query($query);

		return true;
	}
}
?>