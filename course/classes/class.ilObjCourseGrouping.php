<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Class ilObj<module_name>
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "./classes/class.ilObject.php";

class ilObjCourseGrouping
{
	var $db;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjCourseGrouping($a_id = 0)
	{
		global $ilDB;

		$this->setType('crsg');
		$this->db =& $ilDB;

		$this->setId($a_id);

		if($a_id)
		{
			$this->read();
		}
	}
	function setId($a_id)
	{
		$this->id = $a_id;
	}
	function getId()
	{
		return $this->id;
	}
	function setCourseRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	function getCourseRefId()
	{
		return $this->ref_id;
	}
	function setCourseObjId($a_obj_id)
	{
		$this->obj_id = $a_obj_id;
	}
	function getCourseObjId()
	{
		return $this->obj_id;
	}
	function setType($a_type)
	{
		$this->type = $a_type;
	}
	function getType()
	{
		return $this->type;
	}

	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getTitle()
	{
		return $this->title;
	}
	function setDescription($a_desc)
	{
		$this->description = $a_desc;
	}
	function getDescription()
	{
		return $this->description;
	}
	function setUniqueField($a_uni)
	{
		$this->unique_field = $a_uni;
	}
	function getUniqueField()
	{
		return $this->unique_field;
	}

	function getCountAssignedCourses()
	{
		return count($this->getAssignedCourses());
	}

	function getAssignedCourses()
	{
		include_once './classes/class.ilConditionHandler.php';

		return ilConditionHandler::_getConditionsOfTrigger($this->getType(),$this->getId());
	}


	function delete()
	{
		include_once './classes/class.ilConditionHandler.php';

		if($this->getId() and $this->getType() === 'crsg')
		{
			$query = "DELETE FROM object_data WHERE obj_id = '".$this->getId()."'";
			$this->db->query($query);

			$query = "DELETE FROM crs_groupings ".
				"WHERE crs_grp_id = '".$this->getId()."'";
			$this->db->query($query);

			// Delete conditions
			$condh =& new ilConditionHandler();
			$condh->deleteByObjId($this->getId());

			return true;
		}
		return false;
	}

	function create($a_course_ref_id,$a_course_id)
	{
		global $ilUser;

		// INSERT IN object_data
		$query = "INSERT INTO object_data ".
			"(type,title,description,owner,create_date,last_update,import_id) ".
			"VALUES ".
			"('".$this->type."',".$this->db->quote($this->getTitle()).",'".ilUtil::prepareDBString($this->getDescription())."',".
			"'".$ilUser->getId()."',now(),now(),'')";
			
		$this->db->query($query);

		// READ this id
		$query = "SELECT LAST_INSERT_ID() as last";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setId($row->last);
		}


		// INSERT in crs_groupings
		$query = "INSERT INTO crs_groupings ".
			"SET crs_ref_id = '".$a_course_ref_id."', ".
			"crs_id = '".$a_course_id."',".
			"crs_grp_id = '".$this->getId()."', ".
			"unique_field = '".$this->getUniqueField()."'";

		$this->db->query($query);

		return $this->getId();
	}

	function update()
	{
		if($this->getId() and $this->getType() === 'crsg')
		{
			// UPDATe object_data
			$query = "UPDATE object_data ".
				"SET title = '".ilUtil::prepareDBString($this->getTitle())."', ".
				"description = '".ilUtil::prepareDBString($this->getDescription())."' ".
				"WHERE obj_id = '".$this->getId()."' ".
				"AND type = '".$this->getType()."'";

			$this->db->query($query);

			// UPDATE crs_groupings
			$query = "UPDATE crs_groupings ".
				"SET unique_field = '".$this->getUniqueField()."' ".
				"WHERE crs_grp_id = '".$this->getId()."'";

			$this->db->query($query);

			// UPDATE conditions
			$query = "UPDATE conditions ".
				"SET value = '".$this->getUniqueField()."' ".
				"WHERE trigger_obj_id = '".$this->getId()."' ".
				"AND trigger_type = 'crsg'";
			$this->db->query($query);

			return true;
		}
		return false;
	}

	function isAssigned($a_course_id)
	{
		foreach($this->getAssignedCourses() as $condition_data)
		{
			if($a_course_id == $condition_data['target_obj_id'])
			{
				return true;
			}
		}
		return false;
	}

	function read()
	{
		$query = "SELECT * FROM object_data ".
			"WHERE obj_id = '".$this->getId()."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setTitle($row->title);
			$this->setDescription($row->description);
		}

		$query = "SELECT * FROM crs_groupings ".
			"WHERE crs_grp_id = '".$this->getId()."'";
		$res = $this->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setUniqueField($row->unique_field);
			$this->setCourseRefId($row->crs_ref_id);
			$this->setCourseObjId($row->crs_id);
		}

		return true;
	}

	function _getAllGroupings($a_crs_ref_id,$a_check_write = true)
	{
		global $ilObjDataCache,$ilUser;

		if($a_check_write)
		{
			$courses = ilUtil::_getObjectsByOperations('crs','write',$ilUser->getId(),1000);
		}
		else
		{
			$courses = ilUtil::_getObjectsByOperations('crs','visible',$ilUser->getId(),1000);
		}

		$groupings = array();
		foreach($courses as $crs_id)
		{
			if($a_crs_ref_id != $crs_id)
			{
				$groupings = array_merge($groupings,ilObjCourseGrouping::_getGroupings($ilObjDataCache->lookupObjId($crs_id)));
			}
		}
		return $groupings ? $groupings : array();
	}

	function assign($a_crs_ref_id,$a_course_id)
	{
		// Add the parent course of grouping
		$this->__addCondition($this->getCourseRefId(),$this->getCourseObjId());
		$this->__addCondition($a_crs_ref_id,$a_course_id);

		return true;
	}

	function deassign($a_crs_ref_id,$a_course_id)
	{
		include_once './classes/class.ilConditionHandler.php';


		$condh =& new ilConditionHandler();

		// DELETE also original course if its the last
		if($this->getCountAssignedCourses() == 2)
		{
			$condh->deleteByObjId($this->getId());

			return true;
		}
		
		foreach(ilConditionHandler::_getConditionsOfTrigger('crsg',$this->getId()) as $cond_data)
		{

			if($cond_data['target_ref_id'] == $a_crs_ref_id and
			   $cond_data['target_obj_id'] == $a_course_id)
			{
				$condh->deleteCondition($cond_data['id']);
			}
		}

		return true;
			
	}
	// PRIVATE
	function __addCondition($a_target_ref_id,$a_target_obj_id)
	{
		include_once './classes/class.ilConditionHandler.php';

		$tmp_condh =& new ilConditionHandler();
		$tmp_condh->enableAutomaticValidation(false);

		$tmp_condh->setTargetRefId($a_target_ref_id);
		$tmp_condh->setTargetObjId($a_target_obj_id);
		$tmp_condh->setTargetType('crs');
		$tmp_condh->setTriggerRefId(0);
		$tmp_condh->setTriggerObjId($this->getId());
		$tmp_condh->setTriggerType('crsg');
		$tmp_condh->setOperator('not_member');
		$tmp_condh->setValue($this->getUniqueField());

		if(!$tmp_condh->checkExists())
		{
			$tmp_condh->storeCondition();

			return true;
		}
		return false;
	}
	
	// STATIC
	function _deleteAll($a_course_id)
	{
		global $ilDB;

		// DELETE CONDITIONS
		foreach($groupings = ilObjCourseGrouping::_getGroupings($a_course_id) as $grouping_id)
		{
			include_once './classes/class.ilConditionHandler.php';

			$condh =& new ilConditionHandler();
			$condh->deleteByObjId($grouping_id);
		}

		$query = "DELETE FROM crs_groupings ".
			"WHERE crs_id = '".$a_course_id."'";

		$ilDB->query($query);

		return true;
	}

	function _getGroupings($a_course_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_groupings ".
			"WHERE crs_id = '".$a_course_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$groupings[] = $row->crs_grp_id;
		}
		return $groupings ? $groupings : array();
	}

	function _isInGrouping($a_crs_id)
	{
		include_once './classes/class.ilConditionHandler.php';

		foreach(ilConditionHandler::_getConditionsOfTarget($a_crs_id,'crs') as $cond)
		{
			if($cond['operator'] == 'not_member')
			{
				return true;
			}
		}
		return false;
	}

	function _checkCondition($trigger_obj_id,$operator,$value)
	{
		// in the moment i alway return true, there are some problems with presenting the condition if it fails,
		// only course register class check manually if this condition is fullfilled
		return true;
	}


	/**
	* Get all ids of courses that are grouped with another course
	* @access	static
	* @param	integer	 object_id of one course
	* @param	array integer ids of courses or empty array if course is not in grouping
	*/
	function _getGroupingCourseIds($a_course_id)
	{
		include_once './classes/class.ilConditionHandler.php';

		// get all grouping ids the course is assigned to
		foreach(ilConditionHandler::_getConditionsOfTarget($a_course_id,'crs') as $condition)
		{
			if($condition['trigger_type'] == 'crsg')
			{
				foreach(ilConditionHandler::_getConditionsOfTrigger('crsg',$condition['trigger_obj_id']) as $target_condition)
				{
					$course_ids[] = array('id'			=> $target_condition['target_obj_id'],
										  'unique'		=> $target_condition['value']);
				}
			}
		}
		return $course_ids ? $course_ids : array();
	}	


} // END class.ilObjCourseGrouping
?>
