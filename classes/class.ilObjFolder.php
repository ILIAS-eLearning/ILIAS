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
* Class ilObjFolder
*
* @author Wolfgang Merkens <wmerkens@databay.de> 
* @version $Id$
*
* @extends Object
* @package ilias-core
*/

require_once "class.ilFolder.php";
require_once "class.ilObject.php";

class ilObjFolder extends ilObject
{/**
	* ilias object
	* @var object ilias
	* @access private
	*/
	var $ilias;

	/**
	* lng object
	* @var		object language
	* @access	private
	*/
	var $lng;

	/**
	* tree object
	* @var object tree
	* @access private
	*/
	var $gtree;

	/**
	* group_id
	* @var int group_id
	* @access private
	*/
	var $group_id;



	/**
	* table name of table mail object data
	* @var string
	* @access private
	*/
	var $table_obj_data;

	/**
	* table name of tree table
	* @var string
	* @access private
	*/
	var $table_tree;

	/**
	* Forum object
	* @var		object Forum
	* @access	private
	*/
	var $Folder;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjFolder($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "fold";
		$this->ilObject($a_id,false);
		
		// TODO: needs to rewrite scripts that are using Forum outside this class
		
		
	}
	
	//todo make it more flexible; it should be useful also for categories  
	function putInGrpTree($local_table,$parent_id)
	{  
		require_once("classes/class.ilTree.php");
		
		global $ilias,$lng;
		
		
		//echo "type".$parent_type."type";
		$this->ilias = &$ilias;
		$this->lng = &$lng;
		//$this->group_id = $a_group_id;
		
		$this->table_obj_data = 'object_data';
		$this->tree_table = $tree_table;
		//$this->table_obj_reference = 'object_reference';
		
		$this->gtree = new ilTree($tree_id);
		$this->gtree->setTableNames($this->table_tree,$this->table_obj_data);
		
		
		$local_tree->insertNode($this->getId(), $parent_id);
		
	}
	
	function setRefId($tree_id,$obj_id,$parent)
	{
		$query = "UPDATE grp_tree SET ref_id = -1 WHERE tree=".$tree_id." AND child=".$obj_id." AND parent=".$parent;   
		$this->ilias->db->query($query);
	}
	
	
	
	
} // END class.FolderObject
?>
