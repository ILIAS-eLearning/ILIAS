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
	* Constructor
	* @access	public
	* @param	integer	$a_tree_id		tree_id
	*/
	function ilGroupTree($a_tree_id)
	{
		$this->ilTree($a_tree_id,$a_tree_id);
		$this->setTableNames("grp_tree","object_data","object_reference");
	}

	/**
	* save subtree: copy a subtree (defined by node_id) to a new tree
	* with $this->tree_id -node_id. This is neccessary for undelete functionality
	* TODO: If entire group is deleted entries of object in group that are lying in trash (-> negative tree ID) are not removed!
	* @param	integer	node_id
	* @return	boolean
	* @access	public
	*/
	function saveSubTree($a_node_id)
	{	
		if (!isset($a_node_id))
		{
			$this->ilErr->raiseError(get_class($this)."::saveSubTree(): No node_id given!",$this->ilias->error_obj->WARNING);
		}

		// GET LEFT AND RIGHT VALUE
		$q = "SELECT * FROM ".$this->table_tree." ".
			 "WHERE ".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND child = '".$a_node_id."' ";
		
		$r = $this->ilDB->db->query($q);

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
		
		$r = $this->ilDB->db->query($q);

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
				 $node["perm"]."','".$node["obj_id"]."')";
			$r = $this->ilDB->db->query($q);
		}

		return true;
	}
} // END class.ilGroupTree
?>
