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
* class ilCourseObjectiveMaterials
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id:class.ilCourseObjectiveMaterials.php 13383 2007-03-02 10:54:46 +0000 (Fr, 02 Mrz 2007) smeyer $
* 
*/

class ilCourseObjectiveMaterials
{
	var $db = null;

	var $objective_id = null;
	var $lms;

	public function __construct($a_objective_id)
	{
		global $ilDB;

		$this->db =& $ilDB;
	
		$this->objective_id = $a_objective_id;

		$this->__read();
	}
	

	/**
	 * Get an array of course material ids that can be assigned to learning objectives
	 * No tst, fold and grp.
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj id of course
	 * @return array data of course materials
	 */
	public static function _getAssignableMaterials($a_container_id)
	{
		global $tree,$ilDB;
		
		$all_materials = $tree->getSubTree($tree->getNodeData($a_container_id),true);
		$all_materials = ilUtil::sortArray($all_materials,'title','asc');
		
		// Filter
		foreach($all_materials as $material)
		{
			switch($material['type'])
			{
				case 'tst':
				case 'fold':
				case 'grp':
				case 'rolf':
				case 'crs':
					continue;
				
				default:
					$assignable[] = $material;
					break;
			}
		}
		return $assignable ? $assignable : array();
	}
	
	/**
	 * Get all assigned materials
	 *
	 * @access public
	 * @static
	 *
	 * @param in 
	 */
	public static function _getAllAssignedMaterials($a_container_id)
	{
		global $ilDB;
		
		$query = "SELECT DISTINCT(com.ref_id) as ref_id FROM crs_objectives as co ".
			"JOIN crs_objective_lm as com ON co.objective_id = com.objective_id ".
			"JOIN object_reference as obr ON com.ref_id = obr.ref_id ".
			"JOIN object_data as obd ON obr.obj_id = obd.obj_id ".
			"WHERE co.crs_id = ".$ilDB->quote($a_container_id)." ".
			"ORDER BY obd.title ";
			
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ref_ids[] = $row->ref_id;
		}
		return $ref_ids ? $ref_ids : array();
	}

	public function getMaterials()
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
	
	/**
	 * Check if material is assigned 
	 *
	 * @access public
	 *
	 * @param int ref id
	 * @return bool
	 */
	public function isAssigned($a_ref_id)
	{
		$query = "SELECT * FROM crs_objective_lm ".
			"WHERE ref_id = ".$this->db->quote($a_ref_id)." ".
			"AND objective_id = ".$this->db->quote($this->getObjectiveId())." ".
			"AND type != 'st'";
		$res = $this->db->query($query);
		return $res->numRows() ? true : false;
	}

	/**
	 * Check if chapter is assigned 
	 *
	 * @access public
	 *
	 * @param int ref id
	 * @return bool
	 */
	public function isChapterAssigned($a_ref_id,$a_obj_id)
	{
		$query = "SELECT * FROM crs_objective_lm ".
			"WHERE ref_id = ".$this->db->quote($a_ref_id)." ".
			"AND obj_id = ".$this->db->quote($a_obj_id)." ".
			"AND objective_id = ".$this->db->quote($this->getObjectiveId())." ".
			"AND type = 'st'";
		$res = $this->db->query($query);
		return $res->numRows() ? true : false;
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
		$query = "SELECT lm_ass_id,lm.ref_id,lm.obj_id,lm.type FROM crs_objective_lm as lm ".
			"JOIN object_reference as obr ON lm.ref_id = obr.ref_id ".
			"JOIN object_data as obd ON obr.obj_id = obd.obj_id ".
			"LEFT JOIN lm_data as lmd ON lmd.obj_id = lm.obj_id ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ".
			"ORDER BY obd.title,lmd.title";
			
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