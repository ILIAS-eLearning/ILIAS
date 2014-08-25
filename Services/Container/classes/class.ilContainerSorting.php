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
include_once('Services/Container/classes/class.ilContainerSortingSettings.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesContainer 
*/
class ilContainerSorting
{
	protected static $instances = array();

	protected $obj_id;
	protected $db;
	
	protected $sorting_settings = null;
	const ORDER_DEFAULT = 999999;

	/**
	 * Constructor
	 *
	 * @access private
	 * @param int obj_id
	 * 
	 */
	private function __construct($a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$this->db = $ilDB;
	 	$this->obj_id = $a_obj_id;
	 	
	 	$this->read();
	}
	
	/**
	 * Get sorting settings
	 * @return ilContainerSortingSettings
	 */
	public function getSortingSettings()
	{
		return $this->sorting_settings;
	}
	
	/**
	 * get instance by obj_id
	 *
	 * @access public
	 * @param int obj_id
	 * @return object ilContainerSorting
	 * @static
	 */
	public static function _getInstance($a_obj_id)
	{
		if(isset(self::$instances[$a_obj_id]))
		{
			return self::$instances[$a_obj_id];
		}
		return self::$instances[$a_obj_id] = new ilContainerSorting($a_obj_id);
	}
	
	/**
	 * Get positions of subitems
	 * @param int $a_obj_id
	 * @return 
	 */
	public static function lookupPositions($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM container_sorting WHERE ".
			"obj_id = ".$ilDB->quote($a_obj_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$sorted[$row->child_id] = $row->position;
		}
		return $sorted ? $sorted : array();
	} 
	
	/**
	 * clone sorting 
	 *
	 * @return
	 * @static
	 */
	public function cloneSorting($a_target_id,$a_copy_id)
	{
		global $ilDB;
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Cloning container sorting.');
		
		$target_obj_id = ilObject::_lookupObjId($a_target_id);
		
		include_once('./Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		$mappings = ilCopyWizardOptions::_getInstance($a_copy_id)->getMappings(); 
		
		$query = "SELECT * FROM container_sorting ".
			"WHERE obj_id = ".$ilDB->quote($this->obj_id, 'integer');

		$res = $ilDB->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
	 		if(!isset($mappings[$row->child_id]) or !$mappings[$row->child_id])
	 		{
				#$ilLog->write(__METHOD__.': No mapping found for:'.$row->child_id);
	 			continue;
	 		}
			
			if($row->parent_id and (!isset($mappings[$row->parent_id]) or !$mappings[$row->parent_id]))
			{
				continue;
			}

			$query = "DELETE FROM container_sorting ".
				"WHERE obj_id = ".$ilDB->quote($target_obj_id,'integer')." ".
				"AND child_id = ".$ilDB->quote($mappings[$row->child_id],'integer')." ".
				"AND parent_type = ".$ilDB->quote($row->parent_type,'text').' '.
				"AND parent_id = ".$ilDB->quote((int) $mappings[$row->parent_id],'integer');
			$ilDB->manipulate($query);
	 		
	 		// Add new value
	 		$query = "INSERT INTO container_sorting (obj_id,child_id,position,parent_type,parent_id) ".
	 			"VALUES( ".
				$ilDB->quote($target_obj_id ,'integer').", ".
	 			$ilDB->quote($mappings[$row->child_id] ,'integer').", ".
	 			$ilDB->quote($row->position,'integer').", ".
				$ilDB->quote($row->parent_type,'text').", ".
				$ilDB->quote((int) $mappings[$row->parent_id],'integer').
	 			")";
			$ilDB->manipulate($query);
		}
		return true;		
	}
	
	
	
	/**
	 * sort subitems
	 *
	 * @access public
	 * @param array item data
	 * @return array sorted item data
	 */
	public function sortItems($a_items)
	{
		$sorted = array();
		if($this->getSortingSettings()->getSortMode() != ilContainer::SORT_MANUAL)
		{
			switch($this->getSortingSettings()->getSortMode())
			{
				case ilContainer::SORT_TITLE:
					foreach((array) $a_items as $type => $data)
					{
						// this line used until #4389 has been fixed (3.10.6)
						// reanimated with 4.4.0
						$sorted[$type] = ilUtil::sortArray(
								(array) $data,
								'title',
								($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
								FALSE
						);

						// the next line tried to use db sorting and has replaced sortArray due to bug #4389
						// but leads to bug #12165. PHP should be able to do a proper sorting, if the locale
						// is set correctly, so we witch back to sortArray (with 4.4.0) and see what
						// feedback we get
						// (next line has been used from 3.10.6 to 4.3.x)
//						$sorted[$type] = $data;
					}
					return $sorted ? $sorted : array();
					
				case ilContainer::SORT_ACTIVATION:
					foreach((array) $a_items as $type => $data)
					{
						$sorted[$type] = ilUtil::sortArray(
								(array) $data,
								'start',
								($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
								TRUE
						);
						
					}
					return $sorted ? $sorted : array();
					
					
				case ilContainer::SORT_CREATION:
					foreach((array) $a_items as $type => $data)
					{
						$sorted[$type] = ilUtil::sortArray(
								(array) $data,
								'create_date',
								($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
								TRUE
						);
					}
					return $sorted ? $sorted : array();
			}
			return $a_items;
		}
		if(!count($a_items))
		{
			return $a_items;
		}
		foreach((array) $a_items as $type => $data)
		{
			// Add position
			$items = array();
			foreach((array) $data as $key => $item)
			{
				$items[$key] = $item;
				if(is_array($this->sorting['all']) and isset($this->sorting['all'][$item['child']]))
				{
					$items[$key]['position'] = $this->sorting['all'][$item['child']];
				}
				else
				{
					$items[$key]['position'] = self::ORDER_DEFAULT;
				}
			}

			$items = $this->sortOrderDefault($items);

			switch($type)
			{
				case '_all':
					$sorted[$type] = ilUtil::sortArray((array) $items,'position','asc',true);
					break;
				
				case '_non_sess':
					$sorted[$type] = ilUtil::sortArray((array) $items,'position','asc',true);
					break;
				
				default:
					$sorted[$type] = ilUtil::sortArray((array) $items,'position','asc',true);
					break;
			}
		}
		return $sorted ? $sorted : array();
	}
	
	/**
	 * sort subitems (items of sessions or learning objectives)
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function sortSubItems($a_parent_type,$a_parent_id,$a_items)
	{
		switch($this->getSortingSettings()->getSortMode())
		{
			case ilContainer::SORT_MANUAL:
				$items = array();
				foreach($a_items as $key => $item)
				{
					$items[$key] = $item;
					$items[$key]['position'] = isset($this->sorting[$a_parent_type][$a_parent_id][$item['child']]) ? 
													$this->sorting[$a_parent_type][$a_parent_id][$item['child']] : self::ORDER_DEFAULT;
				}

				$items = $this->sortOrderDefault($items);
				return ilUtil::sortArray((array) $items,'position','asc',TRUE);
				

			case ilContainer::SORT_ACTIVATION:
				return ilUtil::sortArray(
					(array) $a_items,
					'start',
					($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
					TRUE
				);

			case ilContainer::SORT_CREATION:
				return ilUtil::sortArray(
					(array) $a_items,
					'create_date',
					($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
					TRUE
				);

			default:
			case ilContainer::SORT_TITLE:
				return ilUtil::sortArray(
					(array) $a_items,
					'title',
					($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
					FALSE
				);
		}

	}
	
	/**
	 * Save post
	 *
	 * @access public
	 * @param array of positions e.g array(crs => array(1,2,3),'lres' => array(3,5,6))
	 * 
	 */
	public function savePost($a_type_positions)
	{
		global $ilLog; 

	 	if(!is_array($a_type_positions))
	 	{
	 		return false;
	 	}
	 	foreach($a_type_positions as $key => $position)
	 	{
			if($key == "blocks")
			{
				$this->saveBlockPositions($position);
			}
			else if(!is_array($position))
	 		{
	 			$items[$key] = $position * 100;
	 		}
			else
			{
				$ilLog->write(__METHOD__.': Deprecated call');
				foreach($position as $parent_id => $sub_items)
				{
					$this->saveSubItems($key,$parent_id,$sub_items ? $sub_items : array());
				}
			}
	 	}
	 	$this->saveItems($items ? $items : array());
	}
	
	
	/**
	 * save items
	 *
	 * @access protected
	 * @param string parent_type only used for sessions and objectives in the moment. Otherwise empty
	 * @param int parent id
	 * @param array array of items
	 * @return
	 */
	protected function saveItems($a_items)
	{
		global $ilDB;
		
		foreach($a_items as $child_id => $position)
		{
			$ilDB->replace(
				'container_sorting',
				array(
					'obj_id'	=> array('integer',$this->obj_id),
					'child_id'	=> array('integer',$child_id),
					'parent_id'	=> array('integer',0)
				),
				array(
					'parent_type' => array('text',''),
					'position'	  => array('integer',$position)
				)
			);
		}
		return true;
	}
	
	/**
	 * Save subitem ordering (sessions, learning objectives)
	 * @param string $a_parent_type
	 * @param integer $a_parent_id
	 * @param array $a_items
	 * @return 
	 */
	protected function saveSubItems($a_parent_type,$a_parent_id,$a_items)
	{
		global $ilDB;

		foreach($a_items as $child_id => $position)
		{
			$ilDB->replace(
				'container_sorting',
				array(
					'obj_id'	=> array('integer',$this->obj_id),
					'child_id'	=> array('integer',$child_id),
					'parent_id'	=> array('integer',$a_parent_id)
				),
				array(
					'parent_type' => array('text',$a_parent_type),
					'position'	  => array('integer',$position)
				)
			);
		}
		return true;
		
	}
		
	/**
	 * Save block custom positions (for current object id)
	 *
	 * @param array $a_values
	 */
	protected function saveBlockPositions(array $a_values)
	{
		global $ilDB;
		
		asort($a_values);
	
		$ilDB->replace(
			'container_sorting_bl',
			array(
				'obj_id'	=> array('integer',$this->obj_id)
			),
			array(
				'block_ids' => array('text', implode(";", array_keys($a_values)))
			)
		);
	}
	
	/**
	 * Read block custom positions (for current object id)
	 * 
	 * @return array
	 */
	public function getBlockPositions()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT block_ids".
			" FROM container_sorting_bl".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		if($row["block_ids"])
		{
			return explode(";", $row["block_ids"]);
		}
	}
	
	
	/**
	 * Read
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	global $tree;
		
		if(!$this->obj_id)
	 	{
	 		$this->sorting_settings = new ilContainerSortingSettings();
			return true;
	 	}
		
		$this->sorting_settings = ilContainerSortingSettings::getInstanceByObjId($this->obj_id);
		if($this->getSortingSettings()->getSortMode() == ilContainer::SORT_INHERIT)
		{
			// lookup settings of parent course
			$ref_ids = ilObject::_getAllReferences($this->obj_id);
			$ref_id = end($ref_ids);
			$crs_ref_id = $tree->checkForParentType($ref_id,'crs');
			$crs_obj_id = ilObject::_lookupObjId($crs_ref_id);
			
			$crs_settings = ilContainerSortingSettings::getInstanceByObjId($crs_obj_id);
			$this->sorting_settings = clone $crs_settings;
			
		}
	 	$query = "SELECT * FROM container_sorting ".
	 		"WHERE obj_id = ".$this->db->quote($this->obj_id ,'integer')." ORDER BY position";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		if($row->parent_id)
	 		{
		 		$this->sorting[$row->parent_type][$row->parent_id][$row->child_id] = $row->position;
	 		}
	 		else
	 		{
	 			$this->sorting['all'][$row->child_id] = $row->position;
	 		}
	 	}
		return true;
	}

	/**
	 * Position and order sort order for new object without position in manual sorting type
	 *
	 * @param $items
	 * @return array
	 */
	private function sortOrderDefault($items)
	{
		$no_position = array();

		foreach($items as $key => $item)
		{
			if($item["position"] == self::ORDER_DEFAULT)
			{
				$no_position[]= array("key" => $key, "title" => $item["title"], "create_date" => $item["create_date"],
					"start" => $item["start"]);
			}

		}

		if(!count($no_position))
		{
			return $items;
		}

		switch($this->getSortingSettings()->getSortNewItemsOrder())
		{
			case ilContainer::SORT_NEW_ITEMS_ORDER_TITLE:
				$no_position = ilUtil::sortArray( (array) $no_position,
					'title',
					($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
					TRUE);
				break;
			case ilContainer::SORT_NEW_ITEMS_ORDER_CREATION:
				$no_position = ilUtil::sortArray((array) $no_position,
					'create_date',
					($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
					TRUE);
				break;
			case ilContainer::SORT_NEW_ITEMS_ORDER_ACTIVATION:
				$no_position = ilUtil::sortArray((array) $no_position,
					'start',
					($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
					TRUE);

		}
		$count = $this->getSortingSettings()->getSortNewItemsPosition()
			== ilContainer::SORT_NEW_ITEMS_POSITION_TOP ? 0 : 900000;

		foreach($no_position as $values)
		{
			$items[$values["key"]]["position"] = $count;
			$count++;
		}
		return $items;
	}
}


?>