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

define("IL_LAST_NODE", -2);
define("IL_FIRST_NODE", -1);

/**
* Tree class
* data representation in hierachical trees using the Nested Set Model by Joe Celco
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-core
*/

class ilGroupTree extends ilTree
{
	/**
	* ilias object
	* @var		object	ilias
	* @access	private
	*/
	var $ilias;

	/**
	* points to root node (may be a subtree)
	* @var		integer
	* @access	public
	*/
	var $root_id;

	/**
	* to use different trees in one db-table
	* @var		integer
	* @access	public
	*/
	var $tree_id;

	/**
	* table name of tree table
	* @var		string
	* @access	private
	*/
	var $table_tree;

	/**
	* table name of object_data table
	* @var		string
	* @access	private
	*/
	var $table_obj_data;

	/**
	* table name of object_reference table
	* @var		string
	* @access	private
	*/
	var $table_obj_reference;

	/**
	* column name containing primary key in reference table
	* @var		string
	* @access	private
	*/
	var $ref_pk;

	/**
	* column name containing primary key in object table
	* @var		string
	* @access	private
	*/
	var $obj_pk;

	/**
	* column name containing tree id in tree table
	* @var		string
	* @access	private
	*/
	var $tree_pk;

	/**
	* Constructor
	* @access	public
	* @param	integer	$a_tree_id		tree_id
	* @param	integer	$a_root_id		root_id (optional)
	*/
	function ilGroupTree($a_tree_id, $a_root_id = 0)
	{
		global $ilias;

		// set ilias
		$this->ilias =& $ilias;

		if (!isset($a_tree_id) or (func_num_args() == 0) )
		{
			$this->ilias->raiseError(get_class($this)."::Constructor(): No tree_id given!",$this->ilias->error_obj->WARNING);
		}

		if (func_num_args() > 2)
		{
			$this->ilias->raiseError(get_class($this)."::Constructor(): Wrong parameter count!",$this->ilias->error_obj->WARNING);
		}

		//init variables
		if (empty($a_root_id))
		{
			$a_root_id = ROOT_FOLDER_ID;
		}

		$this->tree_id		  = $a_tree_id;
		$this->root_id		  = $a_root_id;
		$this->table_tree     = 'grp_tree';
		$this->table_obj_data = 'object_data';
		$this->table_obj_reference = 'object_reference';
		$this->ref_pk = 'ref_id';
		$this->obj_pk = 'obj_id';
		$this->tree_pk = 'tree';
	}

	/**
	* save subtree: copy a subtree (defined by node_id) to a new tree
	* with $this->tree_id -node_id. This is neccessary for undelete functionality
	* @param	integer	node_id
	* @return	integer
	* @access	public
	*/
	function saveSubTree($a_node_id)
	{	
		if (!isset($a_node_id))
		{
			$this->ilias->raiseError(get_class($this)."::saveSubTree(): No node_id given!",$this->ilias->error_obj->WARNING);
		}

		// GET LEFT AND RIGHT VALUE
		$q = "SELECT * FROM ".$this->table_tree." ".
			 "WHERE ".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND child = '".$a_node_id."' ";
		
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{	
			$lft = $row->lft;
			$rgt = $row->rgt;
		}
		
		// GET ALL SUBNODES
		$q = "SELECT * FROM ".$this->table_tree." ".
			 "WHERE ".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND lft >= '".$lft."' ".
			 "AND rgt <= '".$rgt."'";
		
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$subnodes[$row["child"]] = $this->fetchNodeData($row);
		}
		
		// SAVE SUBTREE
		foreach($subnodes as $node)
		{	
			$q = "INSERT INTO ".$this->table_tree." ".
				 "VALUES ('".-$a_node_id."','".$node["child"]."','".$node["parent"]."','".
				 $node["lft"]."','".$node["rgt"]."','".$node["depth"]."','".
				 $node["perm"]."','".$node["ref_id"]."')";
			$r = $this->ilias->db->query($q);
		}

		return true;
	}

	
} // END class.grouptree
?>
