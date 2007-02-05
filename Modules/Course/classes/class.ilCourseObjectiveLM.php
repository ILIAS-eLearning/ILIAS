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
* class ilcourseobjectivelm
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
*/

class ilCourseObjectiveLM
{
	var $db = null;

	var $objective_id = null;
	var $lms;

	function ilCourseObjectiveLM($a_objective_id)
	{
		global $ilDB;

		$this->db =& $ilDB;
	
		$this->objective_id = $a_objective_id;

		$this->__read();
	}

	function getLMs()
	{
		return $this->lms ? $this->lms : array();
	}

	function getChapters()
	{
		foreach($this->lms as $lm_data)
		{
			if($lm_data['type'] == 'st')
			{
				$chapters[] = $lm_data;
			}
		}
		return $chapters ? $chapters : array();
	}
	
	function getLM($lm_id)
	{
		return $this->lms[$lm_id] ? $this->lms[$lm_id] : array();
	}

	function getObjectiveId()
	{
		return $this->objective_id;
	}

	function setLMRefId($a_ref_id)
	{
		$this->lm_ref_id = $a_ref_id;
	}
	function getLMRefId()
	{
		return $this->lm_ref_id ? $this->lm_ref_id : 0;
	}
	function setLMObjId($a_obj_id)
	{
		$this->lm_obj_id = $a_obj_id;
	}
	function getLMObjId()
	{
		return $this->lm_obj_id ? $this->lm_obj_id : 0;
	}
	function setType($a_type)
	{
		$this->type = $a_type;
	}
	function getType()
	{
		return $this->type;
	}

	function checkExists()
	{
		global $ilDB;
		
		if($this->getLMObjId())
		{
			$query = "SELECT * FROM crs_objective_lm ".
				"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ".
				"AND ref_id = ".$ilDB->quote($this->getLMRefId())." ".
				"AND obj_id = ".$ilDB->quote($this->getLMObjId())." ";
		}
		else
		{
			$query = "SELECT * FROM crs_objective_lm ".
				"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ".
				"AND ref_id = ".$ilDB->quote($this->getLMRefId())." ";
		}

		$res = $this->db->query($query);

		return $res->numRows() ? true : false;
	}

	function add()
	{
		global $ilDB;
		
		$query = "INSERT INTO crs_objective_lm ".
			"SET objective_id = ".$ilDB->quote($this->getObjectiveId()).", ".
			"ref_id = ".$ilDB->quote($this->getLMRefId()).", ".
			"obj_id = ".$ilDB->quote($this->getLMObjId()).", ".
			"type = ".$ilDB->quote($this->getType())."";

		$this->db->query($query);

		return true;
	}
	function delete($lm_id)
	{
		global $ilDB;
		
		if(!$lm_id)
		{
			return false;
		}

		$query = "DELETE FROM crs_objective_lm ".
			"WHERE lm_ass_id = ".$ilDB->quote($lm_id)." ";

		$this->db->query($query);

		return true;
	}

	function deleteAll()
	{
		global $ilDB;
		
		$query = "DELETE FROM crs_objective_lm ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ";

		$this->db->query($query);

		return true;
	}

	// PRIVATE
	function __read()
	{
		global $tree,$ilDB;

		$this->lms = array();
		$query = "SELECT * FROM crs_objective_lm ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!$tree->isInTree($row->ref_id))
			{
				$this->delete($row->lm_ass_id);
				continue;
			}
			$lm['ref_id'] = $row->ref_id;
			$lm['obj_id'] = $row->obj_id;
			$lm['type'] = $row->type;
			$lm['lm_ass_id'] = $row->lm_ass_id;

			$this->lms[$row->lm_ass_id] = $lm;
		}
		return true;
	}
}
?>