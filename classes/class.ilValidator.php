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
* ILIAS Data Validator & Recovery Tool
*
* @author	Sascha Hofmann <shofmann@databay.de> 
* @version	$Id$
*
* @package	ilias-tools
*/
class ilValidator
{
	/**
	* name of RecoveryFolder
	* @var	string
	*/
	var $recovery_folder_name = "__Recovered Objects";
	
	/**
	* ref_id of RecoveryFolder
	* @var	integer
	*/
	var $recovery_folder_ref_id;

	/**
	* all valid rbac object types
	* @var	arrayr
	*/
	var $rbac_object_types;
	
	
	/**
	* set mode
	* @var	array
	*/
	var $mode = array(
						"analyze"		=> true,
						"clean" 		=> false,
						"recover"		=> false,
						"empty_trash"	=> false
					);
	
	
	/**
	* Constructor
	* 
	* @access	public
	* @param	integer	mode
	* 
	*/
	function ilValidator()
	{
		global $objDefinition, $ilDB;

		$this->rbac_object_types = "'".implode("','",$objDefinition->getAllRBACObjects())."'";
		$this->db =& $ilDB;
	}
	
	/**
	* set mode of ilValidator
	* Usage: setMode("recover",true)	=> enable object recovery
	* 		 setMode("all",true) 		=> enable all features
	* 		For all possible modes see variables declaration
	* @access	public
	* @param	string	mode
	* @param	boolean	value (true=enable/false=disable)
	* @return	boolean	false on error
	*/
	function setMode($a_mode,$a_value)
	{
		if ((!in_array($a_mode,array_keys($this->mode)) and $a_mode != "all") or !is_bool($a_value))
		{
			$this->setError(INVALID_PARAM,"setMode");
			return false;
		}
		
		if ($a_mode == "all")
		{
			foreach ($this->mode as $mode => $value)
			{
				$this->mode[$mode] = $a_value;
			}
		}
		else
		{
			$this->mode[$a_mode] = $a_value;
		}
		
		return true;
	}
	
	/**
	* Gets all object entries with missing reference and/or tree entry.
	* Returns
	*		obj_id		=> actual object entry with missing reference or tree
	*		type		=> symbolic name of object type
	*		ref_id		=> reference entry of object (or NULL if missing)
	* 		child		=> always NULL (only for debugging and verification)
	* 
	* @access	public
	* @return	array/boolean	false if analyze mode disabled
	* @see		this::restoreMissingObjects()
	*/
	function getMissingObjects()
	{

		if ($this->mode["analyze"] !== true)
		{
			return false;
		}

		$q = "SELECT object_data.*, ref_id FROM object_data ".
			 "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id ".
			 "LEFT JOIN tree ON object_reference.ref_id = tree.child ".
			 "WHERE (object_reference.obj_id IS NULL OR tree.child IS NULL) ".
			 "AND object_data.type IN (".$this->rbac_object_types.")";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr_objs[] = array(
								"obj_id"	=> $row->obj_id,
								"type"		=> $row->type,
								"ref_id"	=> $row->ref_id,
								"child"		=> $row->child
								);
		}

		return $arr_objs ? $arr_objs : array();
	}

	/**
	* Gets all reference entries that are linked with invalid object IDs
	* 
	* @access	public
	* @return	array/boolean	reference entries or false if analyze mode disabled
	* @see		this::removeUnboundedReferences()
	*/	
	function getUnboundedReferences()
	{
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}

		$q = "SELECT object_reference.* FROM object_reference ".
			 "LEFT JOIN object_data ON object_data.obj_id = object_reference.obj_id ".
			 "WHERE object_data.obj_id IS NULL ".
			 "OR object_data.type NOT IN (".$this->rbac_object_types.")";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr_objs[] = array(
								"ref_id"	=> $row->ref_id,
								"obj_id"	=> $row->obj_id
								);
		}

		return $arr_objs ? $arr_objs : array();
	}

	/**
	* Gets all tree entries without any link to a valid object
	* 
	* @access	public
	* @return	array/boolean	false if analyze mode disabled
	* @see		this::removeUnboundedChilds()
	*/
	function getUnboundedChilds()
	{
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}

		$q = "SELECT tree.*,object_reference.ref_id FROM tree ".
			 "LEFT JOIN object_reference ON tree.child = object_reference.ref_id ".
			 "WHERE object_reference.ref_id IS NULL";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr_objs[] = array(
								"child"		=> $row->child,
								"ref_id"	=> $row->ref_id
								);
		}

		return $arr_objs ? $arr_objs : array();
	}

	/**
	* Gets all tree entries having no valid parent (=> no valid path to root node)
	* Returns an array with
	*		child		=> actual entry with broken uplink to its parent
	*		parent		=> parent of child that does not exist
	*		grandparent	=> grandparent of child (where path to root node continues)
	* 		deleted		=> containing a negative number (= parent in trash) or NULL (parent does not exists at all)
	* 
	* @access	public
	* @return	array/boolean	array of invalid tree entries or false if analyze mode disabled
	* @see		this::restoreUnboundedChilds()
	* 
	*/
	function getChildsWithInvalidParents()
	{
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}

		$q = "SELECT T2.tree AS deleted,T1.child,T1.parent,T2.parent AS grandparent FROM tree AS T1 ".
			 "LEFT JOIN tree AS T2 ON T2.child=T1.parent ".
			 "WHERE (T2.tree!=1 OR T2.tree IS NULL) AND T1.parent!=0";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr_objs[] = array(
								"child"			=> $row->child,
								"parent"		=> $row->parent,
								"grandparent"	=> $row->grandparent,
								"deleted"		=> $row->deleted
								);
		}

		return $arr_objs ? $arr_objs : array();
	}
	
	/**
	* Removes all reference entries that are linked with invalid object IDs
	* 
	* @access	public
	* @param	array	invalid IDs in object_reference
	* @return	boolean	true if any ID were removed / false on error or clean mode disabled
	* @see		this::getUnboundenReferences()
	*/
	function removeUnboundedReferences($a_unbound_refs)
	{
		if (!is_array($a_unbound_refs) or count($a_unbound_refs) == 0)
		{
			$this->setError(INVALID_PARAM,"removeUnboundedReferences");
			return false;
		}

		if ($this->mode["clean"] !== true)
		{
			return false;
		}

		foreach ($a_unbound_refs as $entry)
		{
			$q = "DELETE FROM object_reference WHERE ref_id='".$entry["ref_id"]."' AND obj_id='".$entry["obj_id"]."'";
			$this->db->query($q);
		}
		
		return true;	
	}

	/**
	* Removes all tree entries without any link to a valid object
	* 
	* @access	public
	* @param	array	invalid IDs in tree
	* @return	boolean	true if any ID were removed / false on error or clean mode disabled
	* @see		this::getUnboundenChilds()
	*/
	function removeUnboundedChilds($a_unbound_childs)
	{
		if (!is_array($a_unbound_childs) or count($a_unbound_childs) == 0)
		{
			$this->setError(INVALID_PARAM,"removeUnboundedChilds");
			return false;
		}

		if ($this->mode["clean"] !== true)
		{
			return false;
		}

		foreach ($a_unbound_childs as $entry)
		{
			$q = "DELETE FROM tree WHERE child='".$entry["child"]."'";
			$this->db->query($q);
		}
		
		return true;	
	}
	
	/**
	* Restores missing reference and/or tree entry for all objects found by this::getMissingObjects()
	* Retored object are placed RecoveryFolder defined in this::recovery_folder_name
	* @access	public
	* @param	object	RecoveryFolder
	* @param	array	object IDs
	* @return	boolean	true if any object were restored / false on error or recover mode disabled
	* @see		this::getMissingObjects()
	*/
	function restoreMissingObjects(&$a_objRecover,$a_objs_no_ref)
	{
		global $tree;

		if (!is_object($a_objRecover) or !is_array($a_objs_no_ref) or count($a_objs_no_ref) == 0)
		{
			$this->setError(INVALID_PARAM,"reestorMissingObjects");
			return false;
		}

		if ($this->mode["recover"] !== true)
		{
			return false;
		}
		
		$restored = false;
		
		// list of excluded objects
		$obj_types_excluded = array("adm","root","ldap","mail","usrf","objf","lngf");

		foreach ($a_objs_no_ref as $missing_obj)
		{
			// restore ref_id in case of missing
			if ($missing_obj["ref_id"] == NULL)
			{
				$missing_obj["ref_id"] = $this->restoreReference($missing_obj["obj_id"]);
			}
			
			// put in tree under recover category if not on exclude list
			if (!in_array($missing_obj["type"],$obj_types_excluded))
			{
				$tree->insertNode($missing_obj["ref_id"],$a_objRecover->getRefId());
				$restored = true;
			}
			
			// TODO: process rolefolders
		}
		
		return $restored;
	}
	
	/**
	* restore a reference for an object
	* Creates a new reference entry in DB table object_reference for $a_obj_id
	* @param	integer	obj_id
	* @access	private
	* @return	integer/boolean	generated ref_id or false on error
	*/
	function restoreReference($a_obj_id)
	{
		if (empty($a_obj_id))
		{
			$this->setError(INVALID_PARAM,"restoreReference");
			return false;
		}
		
		$q = "INSERT INTO object_reference (ref_id,obj_id) VALUES ('0','".$a_obj_id."')";
		$this->db->query($q);
		
		return getLastInsertId();
	}

	/**
	* restore subtrees
	* @param	object	category object for recovered objects
	* @param	array	list of childs with invalid parents
	* @access	public
	* @return	boolean false on error or recover mode disabled
	* @see		this::getChildsWithInvalidParent()
	*/
	function restoreUnboundedChilds(&$a_objRecover,$a_childs_no_parent)
	{
		global $tree,$rbacadmin,$ilias;

		if (!is_object($a_objRecover) or !is_array($a_childs_no_parent) or count($a_childs_no_parent) == 0)
		{
			$this->setError(INVALID_PARAM,"restoreUnboundChilds");
			return false;
		}

		if ($this->mode["recover"] !== true)
		{
			return false;
		}
		
		// list of excluded objects
		$obj_types_excluded = array("adm","root","ldap","mail","usrf","objf","lngf");

		// process move subtree
		foreach ($a_childs_no_parent as $entry)
		{
			// get node data
			$top_node = $tree->getNodeData($entry["child"]);
			
			// don't save rolefolders, remove them
			// TODO process ROLE_FOLDER_ID
			if ($top_node["type"] == "rolf")
			{
				$rolfObj = $ilias->obj_factory->getInstanceByRefId($top_node["child"]);
				$rolfObj->delete();
				unset($top_node);
				unset($rolfObj);
				continue;
			}

			// get subnodes of top nodes
			$subnodes[$entry["child"]] = $tree->getSubtree($top_node);
		
			// delete old tree entries
			$tree->deleteTree($top_node);
		}

		// now move all subtrees to new location
		// TODO: this whole put in place again stuff needs revision. Permission settings get lost.
		foreach ($subnodes as $key => $subnode)
		{
			// first paste top_node ...
			$rbacadmin->revokePermission($key);
			$obj_data =& $ilias->obj_factory->getInstanceByRefId($key);
			$obj_data->putInTree($a_objRecover->getRefId());
			$obj_data->setPermissions($a_objRecover->getRefId());

			// ... remove top_node from list ...
			array_shift($subnode);
			
			// ... insert subtree of top_node if any subnodes exist
			if (count($subnode) > 0)
			{
				foreach ($subnode as $node)
				{
					$rbacadmin->revokePermission($node["child"]);
					$obj_data =& $ilias->obj_factory->getInstanceByRefId($node["child"]);
					$obj_data->putInTree($node["parent"]);
					$obj_data->setPermissions($node["parent"]);
				}
			}
		}
		
		return true;
	}
	
	/**
	* Gets the RecoveryFolder. Looks for an object named this::recovery_folder_name
	* and returns its ref_id. If RecoveryFolder is not found
	* RecoveryFolder will be created by calling private method this::creatRecoveryFolder().
	* 
	* @access	public
	* @return	object	RecoveryFolder (category object)
	*/
	function getRecoveryFolder()
	{
		global $ilias;
		
		if (is_integer($this->recovery_folder_ref_id))
		{
			return $ilias->obj_factory->getInstanceByRefId($this->recovery_folder_ref_id);
		}
		
		$q = "SELECT ref_id FROM object_reference ".
			 "LEFT JOIN object_data ON object_data.obj_id=object_reference.obj_id ".
			 "WHERE object_data.title = '".$this->recovery_folder_name."'";
		$r = $this->db->getRow($q);

		if (!$r->ref_id)
		{
			if ($this->mode["recover"] !== true)
			{
				return false;
			}
			else
			{
				return $this->createRecoveryFolder();
			}
		}
		else
		{
			$this->recovery_folder_ref_id = $r->ref_id;
			return $ilias->obj_factory->getInstanceByRefId($r->ref_id);
		}
	}
	
	/**
	* create the RecoveryFolder. RecoveryFolder is a category object and 
	* will be created under ILIAS root node by default.
	* @access	private
	* @param	integer	ref_id of parent object where RecoveryFolder should be created (optional)
	* @return	object	RecoveryFolder (=> category object)
	* @see		this::getRecoveryFolder()
 	*/
	function createRecoveryFolder($a_parent_id = ROOT_FOLDER_ID)
	{
		include_once "classes/class.ilObjCategory.php";
		$objRecover = new ilObjCategory();
		$objRecover->setTitle(ilUtil::stripSlashes($this->recovery_folder_name));
		$objRecover->setDescription(ilUtil::stripSlashes("Contains restored objects by recovery tool"));
		$objRecover->create();
		$objRecover->createReference();
		$objRecover->putInTree($a_parent_id);
		$objRecover->addTranslation($objRecover->getTitle(),$objRecover->getDescription(),"en",1);
		// don't set any permissions -> recoveryfolder only accessible by admins
		//$objRecover->setPermissions(ROOT_FOLDER_ID);
		
		$this->recovery_folder_ref_id = $objRecover->getRefId();

		return $objRecover;
	}
	
	/**
	* close gaps in lft/rgt values of a tree
	* Wrapper for ilTree::renumber()
	* 
	* @access	public
	* @return	boolean false if clean mode disabled
	* @see		ilTree::renumber()
	*/
	function closeGapsInTree()
	{
		global $tree;
		
		if ($this->mode["clean"] !== true)
		{
			return false;
		}

		$tree->renumber(ROOT_FOLDER_ID);
		return true;
	}

	/**
	* DEPRECATED?
	*/
	function checkMainStructure()
	{
		//$tree = new ilTreeChecker(ROOT_FOLDER_ID);
	}

	
	/**
	* DEPRECATED?
	*/
	function getMissingTreeEntries()
	{
		$q = "SELECT object_reference.* FROM object_reference ".
			 "LEFT JOIN tree ON object_reference.ref_id = tree.child ".
			 "WHERE tree.child IS NULL";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr_objs[] = array(
								"ref_id"	=> $row->ref_id,
								"obj_id"	=> $row->obj_id
								);
		}

		return $arr_objs ? $arr_objs : array();
	}
	
	/**
	* stores information about an error if an error occurred 
	* @access	private
	* @param	integer	error code
	* @param	string	name of method that raised the error
	* @param	integer	error level
	*/
	function setError($a_err_code,$a_method_name,$a_err_level = FATAL)
	{
		$this->error["code"]	= $a_error_code;
		$this->error["class"]	= get_class($this);
		$this->error["method"]	= $a_method_name;
		$this->error["level"]	= $a_err_level;
		$this->err_flag			= true;
	}
	
	/**
	* Outputs error (formatted and translated)
	* Resets error flag to false
	* TODO: Move this method to GUI class
	* @access	public
	* @return	string/boolean	HTML output or false if no error is stored
	*/
	function getErrorMessage()
	{
		if ($this->err_flag !== true)
		{
			return false;
		}
		
		echo "<br/>".$this->error["level"].": ".$this->error["code"]." in ".$this->error["class"]."::".$this->level["method"]."() !";
		$this->err_flag = false;
		
		return true;
	}
} // END class.ilValidator
?>