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

//require_once ("./classes/ilTreeChecker.php");
//require_once ("./classes/ilPermissionChecker.php");
/**
* ILIAS Data Validator & Recovery
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @package ilias-setup
*/
class ilValidator
{
	/**
	* name of RecoveryFolder
	*/
	var $recovery_folder_name = "__Recovered Objects";
	
	/**
	* set mode to recover/analyze only
	*/
	var $recover;

	function ilValidator($a_recover = false)
	{
		global $objDefinition;

		$this->rbac_obj_types = "'".implode("','",$objDefinition->getAllRBACObjects())."'";
		$this->recover = $a_recover;
	}
	
	function checkMainStructure()
	{
		//$tree = new ilTreeChecker(ROOT_FOLDER_ID);
	}
	
	function getMissingObjects()
	{
		global $ilDB,$ilErr;

		$q = "SELECT object_data.*, ref_id FROM object_data ".
			 "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id ".
			 "LEFT JOIN tree ON object_reference.ref_id = tree.child ".
			 "WHERE (object_reference.obj_id IS NULL OR tree.child IS NULL) ".
			 "AND object_data.type IN (".$this->rbac_obj_types.")";
		$r = $ilDB->query($q);
		
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
	
	function getMissingTreeEntries()
	{
		global $ilDB,$ilErr,$objDefinition,$ilias;
		
		$q = "SELECT object_reference.* FROM object_reference ".
			 "LEFT JOIN tree ON object_reference.ref_id = tree.child ".
			 "WHERE tree.child IS NULL";
		$r = $ilDB->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr_objs[] = array(
								"ref_id"	=> $row->ref_id,
								"obj_id"	=> $row->obj_id
								);
		}

		return $arr_objs ? $arr_objs : array();
	}
	
	function getUnboundedReferences()
	{
		global $ilDB,$ilErr;
		
		$q = "SELECT object_reference.* FROM object_reference ".
			 "LEFT JOIN object_data ON object_data.obj_id = object_reference.obj_id ".
			 "WHERE object_data.obj_id IS NULL ".
			 "OR object_data.type NOT IN (".$this->rbac_obj_types.")";
		$r = $ilDB->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr_objs[] = array(
								"ref_id"	=> $row->ref_id,
								"obj_id"	=> $row->obj_id
								);
		}

		return $arr_objs ? $arr_objs : array();
	}

	function getUnboundedChilds()
	{
		global $ilDB,$ilErr;
		
		$q = "SELECT tree.*,object_reference.ref_id FROM tree ".
			 "LEFT JOIN object_reference ON tree.child = object_reference.ref_id ".
			 "WHERE object_reference.ref_id IS NULL";
		$r = $ilDB->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr_objs[] = array(
								"child"		=> $row->child,
								"ref_id"	=> $row->ref_id
								);
		}

		return $arr_objs ? $arr_objs : array();
	}

	function getChildsWithInvalidParents()
	{
		global $ilDB,$ilErr;
		// hier gehts weiter
		$q = "SELECT T2.tree AS deleted,T1.child,T1.parent,T2.parent AS grandparent FROM tree AS T1 ".
			 "LEFT JOIN tree AS T2 ON T2.child=T1.parent ".
			 "WHERE (T2.tree!=1 OR T2.tree IS NULL) AND T1.parent!=0";
		$r = $ilDB->query($q);
		
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
	
	function removeUnboundedReferences($a_unbound_refs)
	{
		global $ilDB;

		if (!$this->recover or !is_array($a_unbound_refs) or count($a_unbound_refs) == 0)
		{
			return false;
		}

		foreach ($a_unbound_refs as $entry)
		{
			$q = "DELETE FROM object_reference WHERE ref_id='".$entry["ref_id"]."' AND obj_id='".$entry["obj_id"]."'";
			$ilDB->query($q);
		}
		
		return true;	
	}

	function removeUnboundedChilds($a_unbound_childs)
	{
		global $ilDB;

		if (!$this->recover or !is_array($a_unbound_childs) or count($a_unbound_childs) == 0)
		{
			return false;
		}

		foreach ($a_unbound_childs as $entry)
		{
			$q = "DELETE FROM tree WHERE child='".$entry["child"]."'";
			$ilDB->query($q);
		}
		
		return true;	
	}
	
	function restoreMissingObjects(&$a_objRecover,$a_objs_no_ref)
	{
		global $tree;

		if (!$this->recover or !is_object($a_objRecover) or !is_array($a_objs_no_ref) or count($a_objs_no_ref) == 0)
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
	* @param	integer	obj_id
	* @access	private
	*/
	function restoreReference($a_obj_id)
	{
		global $ilDB;
		
		$q = "INSERT INTO object_reference (ref_id,obj_id) VALUES ('0','".$a_obj_id."')";
		$ilDB->query($q);
		
		return getLastInsertId();
	}

	/**
	* restore subtrees
	* @param	object	category object for recovered objects
	* @param	array	list of childs with invalid parents
	* @access	public
	* @return	boolean
	*/
	function restoreUnboundedChilds(&$a_objRecover,$a_childs_no_parent)
	{
		global $tree,$rbacadmin,$ilias;

		if (!$this->recover or !is_object($a_objRecover) or !is_array($a_childs_no_parent) or count($a_childs_no_parent) == 0)
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
	
	function getRecoveryFolder()
	{
		global $ilDB, $ilias;
		
		$q = "SELECT ref_id FROM object_reference ".
			 "LEFT JOIN object_data ON object_data.obj_id=object_reference.obj_id ".
			 "WHERE object_data.title = '".$this->recovery_folder_name."'";
		$r = $ilDB->getRow($q);

		if (!$r->ref_id)
		{
			return $this->createRecoveryFolder();
		}
		else
		{
			return $ilias->obj_factory->getInstanceByRefId($r->ref_id);
		}
	}
	
	function createRecoveryFolder()
	{
		include_once "classes/class.ilObjCategory.php";
		$objRecover = new ilObjCategory();
		$objRecover->setTitle(ilUtil::stripSlashes($this->recovery_folder_name));
		$objRecover->setDescription(ilUtil::stripSlashes("Contains restored objects by recovery tool"));
		$objRecover->create();
		$objRecover->createReference();
		$objRecover->putInTree(ROOT_FOLDER_ID);
		$objRecover->addTranslation($objRecover->getTitle(),$objRecover->getDescription(),"en",1);
		// don't set any permissions -> recoveryfolder only accessible by admins
		//$objRecover->setPermissions(ROOT_FOLDER_ID);

		return $objRecover;
	}
	
	function closeGapsInTree()
	{
		global $tree;
		
		if (!$this->recover)
		{
			return false;
		}

		$tree->renumber(ROOT_FOLDER_ID);
		return true;
	}
}
?>