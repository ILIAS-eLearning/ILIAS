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

class ilCourseStart
{
	var $db;

	var $ref_id;
	var $id;
	var $start_objs = array();

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilCourseStart($a_course_ref_id,$a_course_obj_id)
	{
		global $ilDB;

		$this->db =& $ilDB;

		$this->ref_id = $a_course_ref_id;
		$this->id = $a_course_obj_id;

		$this->__read();
	}
	function setId($a_id)
	{
		$this->id = $a_id;
	}
	function getId()
	{
		return $this->id;
	}
	function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	function getRefId()
	{
		return $this->ref_id;
	}
	function getStartObjects()
	{
		return $this->start_objs ? $this->start_objs : array();
	}

	function delete($a_crs_start_id)
	{
		$query = "DELETE FROM crs_start ".
			"WHERE crs_start_id = '".$a_crs_start_id."' ".
			"AND crs_id = '".$this->getId()."'";

		$this->db->query($query);

		return true;
	}

	function exists($a_item_ref_id)
	{
		$query = "SELECT * FROM crs_start ".
			"WHERE crs_id = '".$this->getId()."' ".
			"AND item_ref_id = '".$a_item_ref_id."'";

		$res = $this->db->query($query);

		return $res->numRows() ? true : false;
	}

	function add($a_item_ref_id)
	{
		if($a_item_ref_id)
		{
			$query = "INSERT INTO crs_start ".
				"SET crs_id = '".$this->getId()."', ".
				"item_ref_id = '".$a_item_ref_id."'";

			$this->db->query($query);

			return true;
		}
		return false;
	}

	function __deleteAll()
	{
		$query = "DELETE FROM crs_start ".
			"WHERE crs_id = '".$this->getId()."'";


		$this->db->query($query);

		return true;
	}

	function getPossibleStarters(&$item_obj)
	{
		foreach($item_obj->getItems() as $node)
		{
			switch($node['type'])
			{
				case 'lm':
				case 'sahs':
				case 'tst':
					$poss_items[] = $node['ref_id'];
					break;
			}
		}
		return $poss_items ? $poss_items : array();
	}

	function isFullfilled($user_id)
	{
		$fullfilled = true;


		include_once './course/classes/class.ilCourseLMHistory.php';

		$lm_continue =& new ilCourseLMHistory($this->getRefId(),$user_id);
		$continue_data = $lm_continue->getLMHistory();
		
		foreach($this->getStartObjects() as $item)
		{
			$tmp_obj = ilObjectFactory::getInstanceByRefId($item['item_ref_id']);

			if($tmp_obj->getType() == 'tst')
			{
				include_once './assessment/classes/class.ilObjTestAccess.php';

				if(!ilObjTestAccess::_checkCondition($tmp_obj->getId(),'finished',''))
				{
					$fullfilled = false;
					continue;
				}
			}
			elseif($tmp_obj->getType() == 'sahs')
			{
				include_once 'Services/Tracking/classes/class.ilLPStatusSCORM.php';

				$completed = ilLPStatusSCORM::_getCompleted($tmp_obj->getId());

				if(!in_array($user_id,$completed))
				{
					$fullfilled = false;
					continue;
				}
			}
			else
			{
				if(!isset($continue_data[$tmp_obj->getRefId()]))
				{
					$fullfilled = false;
					continue;
				}
			}
		}
		return $fullfilled;
	}


	// PRIVATE
	function __read()
	{
		global $tree;

		$this->start_objs = array();

		$query = "SELECT * FROM crs_start ".
			"WHERE crs_id = '".$this->getId()."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($tree->isInTree($row->item_ref_id))
			{
				$this->start_objs[$row->crs_start_id]['item_ref_id'] = $row->item_ref_id;
			}
			else
			{
				$this->delete($row->item_ref_id);
			}
		}
		return true;
	}

		


} // END class.ilObjCourseGrouping
?>
