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
* class ilTimingAccepted
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
*/


class ilTimingAccepted
{
	var $ilErr;
	var $ilDB;
	var $lng;

	function ilTimingAccepted($crs_id,$a_usr_id)
	{
		global $ilErr,$ilDB,$lng,$tree;

		$this->ilErr =& $ilErr;
		$this->db  =& $ilDB;
		$this->lng =& $lng;

		$this->crs_id = $crs_id;
		$this->user_id = $a_usr_id;

		$this->__read();
	}
	
	function getUserId()
	{
		return $this->user_id;
	}
	function getCourseId()
	{
		return $this->crs_id;
	}
	function accept($a_status)
	{
		$this->accepted = $a_status;
	}
	function isAccepted()
	{
		return $this->accepted ? true : false;
	}
	function setRemark($a_remark)
	{
		$this->remark = $a_remark;
	}
	function getRemark()
	{
		return $this->remark;
	}
	function setVisible($a_visible)
	{
		$this->visible = $a_visible;
	}
	function isVisible()
	{
		return $this->visible ? true : false;
	}

	function update()
	{
		ilTimingAccepted::_delete($this->getCourseId(),$this->getUserId());
		$this->create();
		return true;
	}

	function create()
	{
		global $ilDB;
		
		$query = "INSERT INTO crs_timings_usr_accept ".
			"SET crs_id = ".$ilDB->quote($this->getCourseId()).", ".
			"usr_id = ".$ilDB->quote($this->getUserId()).", ".
			"visible = ".$ilDB->quote($this->isVisible()).", ".
			"accept = ".$ilDB->quote($this->isAccepted()).", ".
			"remark = ".$ilDB->quote($this->getRemark())." ";
		$this->db->query($query);
	}

	function delete()
	{
		return ilTimingAccepted::_delete($this->getCourseId(),$this->getUserId());
	}

	function _delete($a_crs_id,$a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_timings_usr_accept ".
			"WHERE crs_id = ".$ilDB->quote($a_crs_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
		$ilDB->query($query);
	}

	function _deleteByCourse($a_crs_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_timings_usr_accept ".
			"WHERE crs_id = ".$ilDB->quote($a_crs_id)." ";
		$ilDB->query($query);
	}

	function _deleteByUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_timings_usr_accept ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)."";
		$ilDB->query($query);
	}

	function __read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM crs_timings_usr_accept ".
			"WHERE crs_id = ".$ilDB->quote($this->getCourseId())." ".
			"AND usr_id = ".$this->getUserId()."";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setVisible($row->visible);
			$this->setRemark($row->remark);
			$this->accept($row->accept);
		}
		return true;
	}		
}
?>