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
* class ilcourseobjective
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/

class ilCourseObjective
{
	var $db = null;

	var $course_obj = null;
	var $objective_id = null;
	
	function ilCourseObjective(&$course_obj,$a_objective_id = 0)
	{
		global $ilDB;

		$this->db =& $ilDB;
		$this->course_obj =& $course_obj;

		$this->objective_id = $a_objective_id;
		$this->__read();
	}

	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getTitle()
	{
		return $this->title;
	}
	function setDescription($a_description)
	{
		$this->description = $a_description;
	}
	function getDescription()
	{
		return $this->description;
	}
	function setObjectiveId($a_objective_id)
	{
		$this->objective_id = $a_objective_id;
	}
	function getObjectiveId()
	{
		return $this->objective_id;
	}

	function add()
	{
		$query = "INSERT INTO crs_objectives ".
			"SET crs_id = '".$this->course_obj->getId()."', ".
			"title = '".ilUtil::prepareDBString($this->getTitle())."', ".
			"description = '".ilUtil::prepareDBString($this->getDescription())."', ".
			"position = '".($this->__getLastPosition() + 1)."', ".
			"created = '".time()."'";

		$this->db->query($query);

		return true;
	}

	function update()
	{
		$query = "UPDATE crs_objectives ".
			"SET title = '".ilUtil::prepareDBString($this->getTitle())."', ".
			"description = '".ilUtil::prepareDBString($this->getDescription())."' ".
			"WHERE objective_id = '".$this->getObjectiveId()."' ".
			"AND crs_id = '".$this->course_obj->getId()."'";
		
		$this->db->query($query);
		
		return true;
	}
	
	function delete()
	{
		// TODOO delete question and lm assignment

		$query = "DELETE FROM crs_objectives ".
			"WHERE crs_id = '".$this->course_obj->getId()."' ".
			"AND objective_id = '".$this->getObjectiveId()."'";

		$this->db->query($query);

		$this->__updateTop($this->objectives['objective_id']['position']);
		
		return true;
	}

	function moveUp($a_objective_id)
	{
		$pos = $this->__getPosition();
		
		
		$query = "UPDATE crs_objectives ".
			"SET position = position + 1 ".
			"WHERE position = '".($pos - 1)."' ".
			"AND crs_id = '".$this->course_obj->getId()."'";

		$this->db->query($query);

		$query = "UPDATE crs_objectives ".
			"SET position = position - 1 ".
			"WHERE objective_id = '".$a_objective_id."' ".
			"AND crs_id = '".$this->course_obj->getId()."'";

		$this->db->query($query);

		$this->__read();

		return true;
	}

	function moveDown($a_objective_id)
	{
		$pos = $this->__getPosition();
		
		$query = "UPDATE crs_objectives ".
			"SET position = '".$pos."'".
			"WHERE position = '".($pos + 1)."' ".
			"AND crs_id = '".$this->course_obj->getId()."'";

		$this->db->query($query);
		
		$query = "UPDATE crs_objectives ".
			"SET position = '".($pos + 1)."' ".
			"WHERE objective_id = '".$a_objective_id."' ".
			"AND crs_id = '".$this->course_obj->getId()."'";

		$this->db->query($query);

		$this->__read();

		return true;
	}

	// PRIVATE
	function __setPosition($a_position)
	{
		$this->position = $a_position;
	}
	function __getPosition()
	{
		return $this->position;
	}
	function __setCreated($a_created)
	{
		$this->created = $a_created;
	}
	function __getCreated()
	{
		return $this->created;
	}


	function __read()
	{

		if($this->getObjectiveId())
		{
			$query = "SELECT * FROM crs_objectives ".
				"WHERE crs_id = '".$this->course_obj->getId()."' ".
				"AND objective_id = '".$this->getObjectiveId()."'";
				

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setObjectiveId($row->objective_id);
				$this->setTitle($row->title);
				$this->setDescription($row->description);
				$this->__setPosition($row->position);
				$this->__setCreated($row->created);
			}
			return true;
		}
		return false;
	}

	function __getOrderColumn()
	{
		switch($this->course_obj->getOrderType())
		{
			case $this->course_obj->SORT_MANUAL:
				return 'ORDER BY position';

			case $this->course_obj->SORT_TITLE:
				return 'ORDER BY title';

			case $this->course_obj->SORT_ACTIVATION:
				return 'ORDER BY create';
		}
		return false;
	}

	function __updateTop($a_position)
	{
		$query = "UPDATE crs_objectives ".
			"SET position = position - 1 ".
			"WHERE position > '".$a_position."'";

		$this->db->query($query);

		return true;
	}

	function __getLastPosition()
	{
		$query = "SELECT MAX(position) AS pos FROM crs_objectives ".
			"WHERE crs_id = '".$this->course_obj->getId()."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->pos;
		}
		return 0;
	}

	// STATIC
	function _getObjectiveIds($course_id)
	{
		global $ilDB;

		$query = "SELECT objective_id FROM crs_objectives ".
			"WHERE crs_id = '".$course_id."' ".
			"ORDER BY position";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = $row->objective_id;
		}

		return $ids ? $ids : array();
	}
}
?>