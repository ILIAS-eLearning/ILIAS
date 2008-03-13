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

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesContainer 
*/
class ilContainerSorting
{
	protected $obj_id;
	protected $db;
	
	protected $manual_sort_enabled = false;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int obj_id
	 * 
	 */
	public function __construct($a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$this->db = $ilDB;
	 	$this->obj_id = $a_obj_id;
	 	
	 	$this->read();
	}
	
	/**
	 * Sort tree data
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function sortTreeData($a_tree_data)
	{
		$nodes_by_type = array();
		foreach($a_tree_data as $node)
		{
			$type = $node['type'];
			$nodes_by_type[$type][] = $node;
		}
		$sorted = $this->sortTreeDataByType($nodes_by_type);
		foreach($sorted as $type => $nodes)
		{
			foreach($nodes as $node)
			{
				$sorted_nodes[] = $node;
			}
		}

		return $sorted_nodes ? $sorted_nodes : $a_tree_data;
	}
	
	
	/**
	 * Sort
	 *
	 * @access public
	 * @param array of objects by type 
	 * 
	 */
	public function sortTreeDataByType($a_tree_data)
	{
		if(!$this->manual_sort_enabled)
		{
			return $a_tree_data;
		}
		if(!count($a_tree_data))
		{
			return $a_tree_data;
		}
		foreach($a_tree_data as $type => $data)
		{
			$new_key = 0;
			if(!is_array($this->sorting[$type]))
			{
				$sorted[$type] = $data;
				continue;
			}
			
			$tmp_indexes = array();
			foreach($data as $key => $obj)
			{
				$tmp_indexes[$obj['child']] = $key;
			}
			// First sort all items that have entries in sorting table			
			foreach($this->sorting[$type] as $ref_id => $pos)
			{
				if(is_array($data[$tmp_indexes[$ref_id]]))
				{
					$sorted[$type][$new_key++] = $data[$tmp_indexes[$ref_id]];
				}
			}
			// No append all items that are not in sorting table
			foreach($tmp_indexes as $ref_id => $key)
			{
				if(!isset($this->sorting[$type][$ref_id]))
				{
					$sorted[$type][$new_key++] = $data[$key];
				}
			}
		}
		return $sorted ? $sorted : array(); 
	}
	
	/**
	 * is manual sorting enabled
	 *
	 * @access public
	 * @return bool
	 */
	public function isManualSortingEnabled()
	{
		return (bool) $this->manual_sort_enabled;
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
	 	if(!is_array($a_type_positions))
	 	{
	 		return false;
	 	}
	 	foreach($a_type_positions as $type => $positions)
	 	{
	 		if(!is_array($positions))
	 		{
	 			continue;
	 		}
	 		asort($positions,SORT_NUMERIC);
	 		$this->saveByType($type,$positions);
	 	}
	}
	
	/**
	 * Save positions by type
	 *
	 * @access public
	 * @param string type e.g lres,crs
	 * @param array items
	 * 
	 */
	public function saveByType($a_type,$a_items)
	{
	 	$query = "REPLACE INTO container_sorting SET ".
	 		"obj_id = ".$this->db->quote($this->obj_id).", ".
	 		"type = ".$this->db->quote($a_type).", ".
	 		"items = ".$this->db->quote(serialize($a_items))." ";
	 	$res = $this->db->query($query);
	}
	
	
	/**
	 * Read
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	if(!$this->obj_id)
	 	{
	 		return true;
	 	}
	 	
	 	include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
	 	$this->manual_sort_enabled = ilContainerSortingSettings::_isManualSortingEnabled($this->obj_id);
	 	
	 	$query = "SELECT * FROM container_sorting ".
	 		"WHERE obj_id = ".$this->db->quote($this->obj_id)." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->sorting[$row->type] = unserialize($row->items);
	 	}
		return true;	
	}
}


?>