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
		if($this->objective_id)
		{
			$this->__read();
		}
	}
	
	/**
	 * Get container of object 
	 *
	 * @access public
	 * @static
	 *
	 * @param int objective id
	 */
	public static function _lookupContainerIdByObjectiveId($a_objective_id)
	{
		global $ilDB;
		
		$query = "SELECT crs_id FROM crs_objectives ".
			"WHERE objective_id = ".$ilDB->quote($a_objective_id ,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->crs_id;
		}
		return false;
	}
	
	/**
	 * get count objectives
	 *
	 * @access public
	 * @param int obj_id
	 * @return
	 * @static
	 */
	public static function _getCountObjectives($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM crs_objectives ".
			"WHERE crs_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	/**
	 * clone objectives
	 *
	 * @access public
	 * @param int target id
	 * @param int copy id
	 * 
	 */
	public function ilClone($a_target_id,$a_copy_id)
	{
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Start cloning learning objectives...');
		
	 	$query = "SELECT * FROM crs_objectives ".
	 		"WHERE crs_id  = ".$this->db->quote($this->course_obj->getId() ,'integer').' '.
	 		"ORDER BY position ";
	 	$res = $this->db->query($query);
	 	if(!$res->numRows())
	 	{
			$ilLog->write(__METHOD__.': ... no objectives found.');
	 		return true;
	 	}
	 	
	 	if(!is_object($new_course = ilObjectFactory::getInstanceByRefId($a_target_id,false)))
	 	{
			$ilLog->write(__METHOD__.': Cannot init new course object.');
	 		return true;
	 	}
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
			$new_objective = new ilCourseObjective($new_course);
			$new_objective->setTitle($row->title);
			$new_objective->setDescription($row->description);
			$objective_id = $new_objective->add();
			$ilLog->write(__METHOD__.': Added new objective nr: '.$objective_id);
			
			// Clone crs_objective_tst entries
			include_once('Modules/Course/classes/class.ilCourseObjectiveQuestion.php');
			$objective_qst = new ilCourseObjectiveQuestion($row->objective_id);
			$objective_qst->cloneDependencies($objective_id,$a_copy_id);

			$ilLog->write(__METHOD__.': Finished objective question dependencies: '.$objective_id);
			
			// Clone crs_objective_lm entries (assigned course materials)
			include_once('Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
			$objective_material = new ilCourseObjectiveMaterials($row->objective_id);
			$objective_material->cloneDependencies($objective_id,$a_copy_id);
	 	}
		$ilLog->write(__METHOD__.': Finished cloning objectives.');
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
		global $ilDB;
		
		$next_id = $ilDB->nextId('crs_objectives');
		$query = "INSERT INTO crs_objectives (crs_id,objective_id,title,description,position,created) ".
			"VALUES( ".
			$ilDB->quote($this->course_obj->getId() ,'integer').", ".
			$ilDB->quote($next_id,'integer').", ".
			$ilDB->quote($this->getTitle() ,'text').", ".
			$ilDB->quote($this->getDescription() ,'text').", ".
			$ilDB->quote($this->__getLastPosition() + 1 ,'integer').", ".
			$ilDB->quote(time() ,'integer')." ".
			")";
		$res = $ilDB->manipulate($query);
		
		return $this->objective_id = $next_id;
	}

	function update()
	{
		global $ilDB;
		
		$query = "UPDATE crs_objectives ".
			"SET title = ".$ilDB->quote($this->getTitle() ,'text').", ".
			"description = ".$ilDB->quote($this->getDescription() ,'text')." ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId() ,'integer')." ".
			"AND crs_id = ".$ilDB->quote($this->course_obj->getId() ,'integer')."";
		$res = $ilDB->manipulate($query);
		
		return true;
	}
	
	/**
	 * write position
	 *
	 * @access public
	 * @param int new position
	 * @return
	 */
	public function writePosition($a_position)
	{
		global $ilDB;
		
		$query = "UPDATE crs_objectives ".
			"SET position = ".$this->db->quote((string) $a_position ,'integer')." ".
			"WHERE objective_id = ".$this->db->quote($this->getObjectiveId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
	}
	
	/**
	 * validate
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function validate()
	{
		return (bool) strlen($this->getTitle());
	}
	
	function delete()
	{
		global $ilDB;
		
		include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';

		$tmp_obj_qst =& new ilCourseObjectiveQuestion($this->getObjectiveId());
		$tmp_obj_qst->deleteAll();

		include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';

		$tmp_obj_lm =& new ilCourseObjectiveMaterials($this->getObjectiveId());
		$tmp_obj_lm->deleteAll();


		$query = "DELETE FROM crs_objectives ".
			"WHERE crs_id = ".$ilDB->quote($this->course_obj->getId() ,'integer')." ".
			"AND objective_id = ".$ilDB->quote($this->getObjectiveId() ,'integer')." ";
		$res = $ilDB->manipulate($query);

		return true;
	}

	function moveUp()
	{
		global $ilDB;
		
		if(!$this->getObjectiveId())
		{
			return false;
		}
		// Stop if position is first
		if($this->__getPosition() == 1)
		{
			return false;
		}

		$query = "UPDATE crs_objectives ".
			"SET position = position + 1 ".
			"WHERE position = ".$ilDB->quote($this->__getPosition() - 1 ,'integer')." ".
			"AND crs_id = ".$ilDB->quote($this->course_obj->getId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
		
		$query = "UPDATE crs_objectives ".
			"SET position = position - 1 ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId() ,'integer')." ".
			"AND crs_id = ".$ilDB->quote($this->course_obj->getId() ,'integer')." ";
		$res = $ilDB->manipulate($query);

		$this->__read();

		return true;
	}

	function moveDown()
	{
		global $ilDB;
		
		if(!$this->getObjectiveId())
		{
			return false;
		}
		// Stop if position is last
		if($this->__getPosition() == $this->__getLastPosition())
		{
			return false;
		}
		
		$query = "UPDATE crs_objectives ".
			"SET position = position - 1 ".
			"WHERE position = ".$ilDB->quote($this->__getPosition() + 1 ,'integer')." ".
			"AND crs_id = ".$ilDB->quote($this->course_obj->getId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
		
		$query = "UPDATE crs_objectives ".
			"SET position = position + 1 ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId() ,'integer')." ".
			"AND crs_id = ".$ilDB->quote($this->course_obj->getId() ,'integer')." ";
		$res = $ilDB->manipulate($query);

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
		global $ilDB;
		
		if($this->getObjectiveId())
		{
			$query = "SELECT * FROM crs_objectives ".
				"WHERE crs_id = ".$ilDB->quote($this->course_obj->getId() ,'integer')." ".
				"AND objective_id = ".$ilDB->quote($this->getObjectiveId() ,'integer')." ";


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
			case ilContainer::SORT_MANUAL:
				return 'ORDER BY position';

			case ilContainer::SORT_TITLE:
				return 'ORDER BY title';

			case ilContainer::SORT_ACTIVATION:
				return 'ORDER BY create';
		}
		return false;
	}

	function __updateTop()
	{
		global $ilDB;
		
		$query = "UPDATE crs_objectives ".
			"SET position = position - 1 ".
			"WHERE position > ".$ilDB->quote($this->__getPosition() ,'integer')." ".
			"AND crs_id = ".$ilDB->quote($this->course_obj->getId() ,'integer')." ";
		$res = $ilDB->manipulate($query);

		return true;
	}

	function __getLastPosition()
	{
		global $ilDB;
		
		$query = "SELECT MAX(position) pos FROM crs_objectives ".
			"WHERE crs_id = ".$ilDB->quote($this->course_obj->getId() ,'integer')." ";

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
			"WHERE crs_id = ".$ilDB->quote($course_id ,'integer')." ".
			"ORDER BY position";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = $row->objective_id;
		}

		return $ids ? $ids : array();
	}

	function _deleteAll($course_id)
	{
		global $ilDB;

		$ids = ilCourseObjective::_getObjectiveIds($course_id);
		
		if(!count($ids))
		{
			return true;
		}

		$in = $ilDB->in('objective_id',$ids,false,'integer');


		$query = "DELETE FROM crs_objective_lm WHERE  ".$in;
		$res = $ilDB->manipulate($query);

		$query = "DELETE FROM crs_objective_tst WHERE ".$in;
		$res = $ilDB->manipulate($query);
		
		$query = "DELETE FROM crs_objective_qst WHERE ".$in;
		$res = $ilDB->manipulate($query);
		
		$query = "DELETE FROM crs_objectives WHERE crs_id = ".$ilDB->quote($course_id ,'integer');
		$res = $ilDB->manipulate($query);

		return true;
	}
}
?>