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
* Class ilLPObjSettings
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

class ilLPCollections
{
	var $db = null;

	var $obj_id = null;
	var $items = array();

	function ilLPCollections($a_obj_id)
	{
		global $ilObjDataCache,$ilDB;

		$this->db =& $ilDB;

		$this->obj_id = $a_obj_id;

		$this->__read();
	}

	function getObjId()
	{
		return (int) $this->obj_id;
	}

	function getItems()
	{
		return $this->items;
	}

	function isAssigned($a_obj_id)
	{
		return (bool) in_array($a_obj_id,$this->items);
	}

	function add($item_id)
	{
		if($this->isAssigned($item_id))
		{
			return false;
		}

		$query = "INSERT INTO ut_lp_collections ".
			"SET obj_id = '".$this->obj_id."', ".
			"item_id = '".(int) $item_id."'";
		$this->db->query($query);
		
		$this->__read();

		return true;
	}

	function delete($item_id)
	{
		$query = "DELETE FROM ut_lp_collections ".
			"WHERE item_id = '".$item_id."' ".
			"AND obj_id = '".$this->obj_id."'";
		$this->db->query($query);

		$this->__read();

		return true;
	}


	// Static
	function _getPossibleItems($a_target_id)
	{
		global $tree;

		foreach($tree->getChilds($a_target_id) as $node)
		{
			switch($node['type'])
			{
				case 'sahs':
				case 'lm':
				case 'tst':
					$all_possible["$node[ref_id]"] = $node['obj_id'];
					break;
			}
		}
		return $all_possible ? $all_possible : array();
	}

	function _getCountPossibleItems($a_target_id)
	{
		return count(ilLPCollections::_getPossibleItems($a_target_id));
	}

	function _getCountPossibleSAHSItems($a_target_id)
	{
		return count(ilLPCollections::_getPossibleSAHSItems($a_target_id));
	}


	/**
	* get all tracking items of scorm or aicc object
	*/

	function _getPossibleSAHSItems($target_id)
	{
		include_once './content/classes/class.ilObjSAHSLearningModule.php';

		switch(ilObjSAHSLearningModule::_lookupSubType($target_id))
		{
			case 'aicc':
				include_once './content/classes/class.ilObjAICCLearningModule.php';

				foreach(ilObjAICCLearningModule::_getTrackingItems($target_id) as $item)
				{
					$items[$item->getId()]['title'] = $item->getTitle();
				}
				return $items ? $items : array();

			case 'scorm':
				include_once './content/classes/class.ilObjSCORMLearningModule.php';
				include_once './content/classes/SCORM/class.ilSCORMItem.php';

				foreach(ilObjSCORMLearningModule::_getTrackingItems($target_id) as $item)
				{
					$items[$item->getId()]['title'] = $item->getTitle();
				}
				return $items ? $items : array();
		}
		return array();
	}


	function _deleteAll($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM ut_lp_collections ".
			"WHERE obj_id = '".$a_obj_id."'";
		$ilDB->query($query);

		return true;
	}

	function _getItems($a_obj_id)
	{
		global $ilObjDataCache;
		global $ilDB;

		if($ilObjDataCache->lookupType($a_obj_id) == 'crs')
		{
			$course_ref_ids = ilObject::_getAllReferences($a_obj_id);
			$course_ref_id = end($course_ref_ids);
		}

		$query = "SELECT * FROM ut_lp_collections WHERE obj_id = '".(int) $a_obj_id."'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($ilObjDataCache->lookupType($a_obj_id) == 'crs')
			{
				if(!in_array($row->item_id,ilLPCollections::_getPossibleItems($course_ref_id)))
				{
					$query = "DELETE FROM ut_lp_collections ".
						"WHERE obj_id = '".$a_obj_id."' ".
						"AND item_id = '".$row->item_id."'";
					$ilDB->query($query);
					continue;
				}
			}
			$items[] = $row->item_id;
		}
		return $items ? $items : array();
	}

	// Private
	function __read()
	{
		global $ilObjDataCache;

		if($ilObjDataCache->lookupType($this->getObjId()) == 'crs')
		{
			$course_ref_ids = ilObject::_getAllReferences($this->getObjId());
			$course_ref_id = end($course_ref_ids);
		}

		$query = "SELECT * FROM ut_lp_collections WHERE obj_id = '".$this->db->quote($this->obj_id)."'";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($ilObjDataCache->lookupType($this->getObjId()) == 'crs')
			{
				if(!in_array($row->item_id,ilLPCollections::_getPossibleItems($course_ref_id)))
				{
					$query = "DELETE FROM ut_lp_collections ".
						"WHERE obj_id = '".$this->getObjId()."' ".
						"AND item_id = '".$row->item_id."'";
					$this->db->query($query);
					continue;
				}
			}
			$this->items[] = $row->item_id;
		}
		
	}
}
?>