<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once './Services/Container/classes/class.ilContainer.php';

/** 
* @defgroup ServicesContainer Services/Container 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesContainer 
*/
class ilContainerSortingSettings
{
	protected $obj_id;
	protected $sort_mode;
	
	protected $db;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$this->obj_id = $a_obj_id;
	 	$this->db = $ilDB;
	 	
	 	$this->read();
	}
	
	/**
	 * lookup sort mode
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _lookupSortMode($a_obj_id)
	{
		global $tree;
		global $ilDB;
		
		$ref_ids = ilObject::_getAllReferences($a_obj_id);
		$ref_id = current($ref_ids);
		
		if($course_ref_id = $tree->checkForParentType($ref_id,'crs'))
		{
			$a_obj_id = ilObject::_lookupObjId($course_ref_id);
		}
				
		
		$query = "SELECT * FROM container_sorting_set ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->sort_mode;
		}
		return ilContainer::SORT_TITLE;
	}
	
	/**
	 * is manual sorting enabled
	 *
	 * @access public
	 * @param int obj_id
	 * 
	 */
	public function _isManualSortingEnabled($a_obj_id)
	{
	 	return self::_lookupSortMode($a_obj_id) == ilContainer::SORT_MANUAL;
	}
	
	/**
	 * Clone settings
	 *
	 * @access public
	 * @static
	 *
	 * @param int orig obj_id
	 * @þaram int new obj_id
	 */
	public static function _cloneSettings($a_old_id,$a_new_id)
	{
		global $ilLog;
		global $ilDB;
		
		$query = "SELECT sort_mode FROM container_sorting_set ".
			"WHERE obj_id = ".$ilDB->quote($a_old_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow())
		{
			$query = "DELETE FROM container_sorting_set ".
				"WHERE obj_id = ".$ilDB->quote($a_new_id)." ";
			$ilDB->manipulate($query);

			$query = "INSERT INTO container_sorting_set  (obj_id,sort_mode) ".
				"VALUES( ".
				$ilDB->quote($a_new_id ,'integer').", ".
				$ilDB->quote($row[0] ,'integer')." ".
				")";
			$ilDB->manipulate($query);
		}
		return true;
	}
	
	/**
	 * get sort mode
	 *
	 * @access public
	 * 
	 */
	public function getSortMode()
	{
	 	return $this->sort_mode ? $this->sort_mode : 0;
	}
	
	/**
	 * set sort mode
	 *
	 * @access public
	 * @param int MODE_TITLE | MODE_MANUAL | MODE_ACTIVATION
	 * 
	 */
	public function setSortMode($a_mode)
	{
	 	$this->sort_mode = (int) $a_mode;
	}
	
	/**
	 * Update
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
		global $ilDB;
		
		$query = "DELETE FROM container_sorting_set ".
			"WHERE obj_id = ".$ilDB->quote($this->obj_id,'integer');
		$res = $ilDB->manipulate($query);
		
		$this->save();
	}

	/**
	 * save settings
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
		global $ilDB;

		$query = "INSERT INTO container_sorting_set (obj_id,sort_mode) ".
			"VALUES ( ".
			$this->db->quote($this->obj_id ,'integer').", ".
			$this->db->quote($this->sort_mode ,'integer')." ".
			")";
		$res = $ilDB->manipulate($query);
	}
	
	/**
	 * read settings
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function read()
	{
	 	if(!$this->obj_id)
	 	{
	 		return true;
	 	}
	 	
	 	$query = "SELECT * FROM container_sorting_set ".
	 		"WHERE obj_id = ".$this->db->quote($this->obj_id ,'integer')." ";
	 		
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->sort_mode = $row->sort_mode;
	 	}
	}
	
}


?>