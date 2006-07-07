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
* class ilTimingPlaned
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/


class ilTimingPlaned
{
	var $ilErr;
	var $ilDB;
	var $lng;

	function ilTimingPlaned($item_id,$a_usr_id)
	{
		global $ilErr,$ilDB,$lng,$tree;

		$this->ilErr =& $ilErr;
		$this->db  =& $ilDB;
		$this->lng =& $lng;

		$this->item_id = $item_id;
		$this->user_id = $a_usr_id;

		$this->__read();
	}
	
	function getUserId()
	{
		return $this->user_id;
	}
	function getItemId()
	{
		return $this->item_id;
	}

	function getPlanedStartingTime()
	{
		return $this->start;
	}
	function setPlanedStartingTime($a_time)
	{
		$this->start = $a_time;
	}
	function getPlanedEndingTime()
	{
		return $this->end;
	}
	function setPlanedEndingTime($a_end)
	{
		$this->end = $a_end;
	}

	function validate()
	{
		include_once 'course/classes/class.ilCourseItems.php';
		$item_data = ilCourseItems::_getItem($this->getItemId());

		#var_dump("<pre>",date('Y-m-d H:i:s',$this->getPlanedStartingTime()),"<pre>");
		#var_dump("<pre>",date('Y-m-d H:i:s',$this->getPlanedEndingTime()),"<pre>");
		#var_dump("<pre>",date('Y-m-d H:i:s',$item_data['earliest_start']),"<pre>");
		#var_dump("<pre>",date('Y-m-d H:i:s',$item_data['latest_end']),"<pre>");
		#var_dump("<pre>",$this->getPlanedStartingTime() < $item_data['earliest_start'],"<pre>");
		#var_dump("<pre>",$this->getPlanedStartingTime() > $item_data['latest_end'],"<pre>");
		#var_dump("<pre>",$this->getPlanedEndingTime() < $item_data['earliest_start'],"<pre>");
		#var_dump("<pre>",$this->getPlanedEndingTime() > $item_data['earliest_end'],"<pre>");


		if($this->getPlanedStartingTime() < $item_data['earliest_start'] or
		   $this->getPlanedStartingTime() > $item_data['latest_end'] or
		   $this->getPlanedEndingTime() < $item_data['earliest_start'] or
		   $this->getPlanedEndingTime() > $item_data['latest_end'])
		{
			return false;
		}
		return $this->getPlanedStartingTime() < $this->getPlanedEndingTime();
	}

	function update()
	{
		ilTimingPlaned::_delete($this->getItemId(),$this->getUserId());
		$this->create();
		return true;
	}

	function create()
	{
		$query = "INSERT INTO crs_timings_planed ".
			"SET item_id = '".$this->getItemId()."', ".
			"usr_id = '".$this->getUserId()."', ".
			"planed_start = '".(int) $this->getPlanedStartingTime()."', ".
			"planed_end = '".(int) $this->getPlanedEndingTime()."'";
		$this->db->query($query);
	}

	function delete()
	{
		return ilTimingPlaned::_delete($this->getItemId(),$this->getUserId());
	}

	function _delete($a_crs_id,$a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_timings_planed ".
			"WHERE item_id = '".$a_item_id."' ".
			"AND usr_id = '".$a_usr_id."'";
		$ilDB->query($query);
	}

	function _deleteByItem($a_item_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_timings_planed ".
			"WHERE item_id = '".$a_item_id."'";
		$ilDB->query($query);
	}

	function _deleteByUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_timings_planed ".
			"WHERE usr_id = '".$a_usr_id."'";
		$ilDB->query($query);
	}

	function __read()
	{
		$query = "SELECT * FROM crs_timings_planed ".
			"WHERE item_id = '".$this->getItemId()."' ".
			"AND usr_id = '".$this->getUserId()."'";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setPlanedStartingTime($row->planed_start);
			$this->setPlanedEndingTime($row->planed_end);
		}
		return true;
	}		
}
?>