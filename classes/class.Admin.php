<?php
/**
 * Class Admin
 * Objectmanagement functions
 * 
 * @author Stefan Meyer <smeyer@databay.de>
 * @author SAscha Hofmann <shofmann@databay.de> 
 * @version $Id$
 * 
 * @package ilias-core
 */
class Admin 
{
	/**
	* ilias object
	* @var	object	ilias
	* @access	private
	*/
	var $ilias;
	
	/**
	* language object
	* @var	object	language
	* @access	private
	*/
	var $lng;

	/**
	* Constructor
	* @access	public
	*/
	function Admin()
	{
		global $ilias, $lng;
		
		$this->ilias = &$ilias;
		$this->lng   = &$lng;
	}
	
	/**
	* cut an object out from tree an copy information to clipboard
	* @access	public
	* @param	array	array of ref_ids to delete
	* @param	string	delete command
	* @param	integer	obj_id	// maybe deprecated
	* // TODO: a_obj_id is saved in $clipboard. We don't need the parent. We may get it by tree->getParent
	*/
	function cutObject($a_post_data,$a_post_cmd,$a_obj_id)
	{
		global $clipboard,$tree,$rbacsystem,$rbacadmin;
		
		if (!isset($a_post_data))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		// CHECK ACCESS
		foreach ($a_post_data as $ref_id)
		{
			if(!$rbacsystem->checkAccess('delete',$ref_id))
			{
				$no_cut[] = $ref_id;
			}
		}
		// NO ACCESS
		if (count($no_cut))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_cut")." ".
									 implode(',',$no_cut),$this->ilias->error_obj->MESSAGE);
		}

		// SAVE SUBTREE AND DELETE SUBTREE FROM TREE
		foreach($a_post_data as $ref_id)
		{
			// DELETE OLD PERMISSION ENTRIES
/*			$subnodes = $tree->getSubtree($tree->getNodeData($ref_id));

			foreach($subnodes as $subnode)
			{
				$rbacadmin->revokePermission($subnode["ref_id"]);
			}

			// TODO: is clipboard not enough???
			$tree->saveSubTree($ref_id);
			$tree->deleteTree($tree->getNodeData($ref_id));
*/
			$clipboard[$ref_id]["parent"] = $a_obj_id;
			$clipboard[$ref_id]["cmd"] = $a_post_cmd;
		}

		$_SESSION["clipboard"] = $clipboard;
	}
	
	/**
	* create an new reference of an object in tree
	* it's like a hard link of unix
	* @access	public
	* @param	array	array of ref_ids to link
	* @param	string	command ???
	* @param	integer	obj_id	// maybe deprecated
	*/	
	function linkObject($a_post_data,$a_post_cmd,$a_obj_id)
	{
		global $clipboard,$tree,$rbacsystem,$rbacadmin,$objDefinition;
		
		if (!isset($a_post_data))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		// CHECK ACCESS
		foreach ($a_post_data as $ref_id)
		{
			if (!$rbacsystem->checkAccess('delete',$ref_id))
			{
				$no_cut[] = $ref_id;
			}

			$object = getObjectByReference($ref_id);
			$actions = $objDefinition->getActions($object["type"]);

			if ($actions["link"]["exec"] == 'false')
			{
				$no_link[] = $object["type"];
			}
		}

		// NO ACCESS
		if (count($no_cut))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_link")." ".
									 implode(',',$no_cut),$this->ilias->error_obj->MESSAGE);
		}

		if (count($no_link))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_possible_link")." ".
									 implode(',',$no_link),$this->ilias->error_obj->MESSAGE);
		}

		// SAVE OBJECT
		foreach ($a_post_data as $ref_id)
		{
			//$tree->saveNode($ref_id);
			// TODO: Should the linked object save in db temporary?
			$clipboard[$ref_id]["parent"] = $a_obj_id;
			$clipboard[$ref_id]["cmd"] = $a_post_cmd;
		}

		$_SESSION["clipboard"] = $clipboard;
	} // END COPY

	/**
	* create an new reference of an object in tree
	* it's like a hard link of unix
	* @access	public
	* @param	array	array of ref_ids to link
	* @param	string	command ???
	* @param	integer	obj_id	// maybe deprecated
	*/	
	function copyObject($a_post_data,$a_post_cmd,$a_obj_id)
	{
		global $tree, $rbacsystem, $rbacadmin, $objDefinition;
		
		// AT LEAST ONE OBJECT HAS TO BE CHOSEN. 
		if (!isset($a_post_data))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// TODO: WE NEED ONLY THE ID IN THIS PLACE. MAYBE BY A FUNCTION getNodeIdsOfSubTree??
		// FOR ALL SELECTED OBJECTS
		foreach ($a_post_data as $ref_id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $tree->getNodeData($ref_id);
			$subtree_nodes = $tree->getSubTree($node_data);
			
			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach ($subtree_nodes as $node)
			{
				if (!$rbacsystem->checkAccess('read',$node["ref_id"]))
				{
					$no_copy[] = $node["ref_id"];
					$perform_copy = false;
				}
			}
		}

		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'read'
		if (count($no_copy))
		{
			$no_copy = implode(',',$no_copy);
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_copy")." ".
									 $no_copy,$this->ilias->error_obj->MESSAGE);
		}

		// COPY THEM
		// SAVE SUBTREE
		// TODO: clipboard is enough
		foreach ($a_post_data as $ref_id)
		{
			//$tree->saveSubTree($ref_id);
			$clipboard[$ref_id]["parent"] = $a_obj_id;
			$clipboard[$ref_id]["cmd"] = $a_post_cmd;
		}

		$_SESSION["clipboard"] = $clipboard;
	}

	// TODO: DO WE NEED parent_id HERE???
	function pasteObject($a_ref_id,$a_parent_id)
	{
		global $rbacsystem,$tree,$objDefinition,$lng;

		// CHECK SOME THINGS
		// TODO: clipboard array contains command multiple times. But there is only one command per clipboard action!!
		foreach($_SESSION["clipboard"] as $id => $object)
		{

			// IF CMD WAS 'copy' CALL PRIVATE CLONE METHOD
			if ($object["cmd"] == $lng->txt('copy'))
			{
				$this->cloneObject($a_ref_id);
				return true;
			}

			// TODO: both function below fetch almost the same data!!!
			$obj_data = getObjectByReference($id);
			$data = $tree->getNodeData($id);

			// CHECK ACCESS
			if (!$rbacsystem->checkAccess('create',$a_ref_id,$obj_data["type"]))
			{
				$no_paste[] = $id;
			}

			// CHECK IF REFERENCE ALREADY EXISTS
			if ($data["ref_id"])
			{
				$exists[] = $id;
			}

			// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
			// TODO: FUNCTION IST NOT LONGER NEEDED IN THIS WAY. WE ONLY NEED TO CHECK IF
			// THE COMBINATION child/parent ALREADY EXISTS

			//if ($tree->isGrandChild(1,0))
			if ($tree->isGrandChild($id,$a_ref_id))
			{
				$is_child[] = $id;
			}

			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			$object = getObjectByReference($a_ref_id);
			
			if (!in_array($obj_data["type"],array_keys($objDefinition->getSubObjects($object["type"]))))
			{
				$not_allowed_subobject[] = $obj_data["type"];
			}
		}

		if (count($exists))
		{
			$this->ilias->raiseError($this->lng->txt("msg_obj_exists"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($is_child))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_in_itself")." ".implode(',',$is_child),
									 $this->ilias->error_obj->MESSAGE);
		}

		if (count($not_allowed_subobject))
		{
			$this->ilias->raiseError($this->lng->txt("msg_may_not_contain")." ".implode(',',$not_allowed_subobject),
									 $this->ilias->error_obj->MESSAGE);
		}

		if (count($no_paste))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_paste")." ". 
									 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
		}

		// TODO: WE DONT NEED THIS
//		foreach ($_SESSION["clipboard"] as $id => $object)
//		{
//			$this->insertSavedNodes($id,$object["parent"],$a_obj_id,$a_parent_id,-(int) $id);
//		}

		$this->clearObject();
	}

	/**
	* clone Object subtree
	* @access	private
	*/	
	function cloneObject($a_ref_id,$a_parent_id)
	{
		global $objDefinition,$tree,$rbacsystem;

		foreach ($_SESSION["clipboard"] as $id => $object)
		{
			// CHECK SOME THNGS
			$obj_data = getObjectByReference($id);
			$data = $tree->getNodeData($id);

			// CHECK ACCESS
			if (!$rbacsystem->checkAccess('create',$a_ref_id,$obj_data["type"]))
			{
				$no_paste[] = $id;
			}

			// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
			if ($tree->isGrandChild($id,$a_ref_id))
			{
				$is_child[] = $id;
			}

			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			$object = getObjectByReference($a_ref_id);

			if (!in_array($obj_data["type"],array_keys($objDefinition->getSubObjects($object["type"]))))
			{
				$not_allowed_subobject[] = $obj_data["type"];
			}
		}

		if (count($no_paste))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create")." ".
									 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
		}

		if (count($is_child))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_in_itself")." ".implode(',',$is_child),
									 $this->ilias->error_obj->MESSAGE);
		}

		if (count($not_allowed_subobject))
		{
			$this->ilias->raiseError($this->lng->txt("msg_may_not_contain")." ".implode(',',$not_allowed_subobject),
									 $this->ilias->error_obj->MESSAGE);
		}
		// NOW CLONE ALL OBJECTS
		// THERFORE THE CLONE METHOD OF ALL OBJECTS IS CALLED
//		foreach ($_SESSION["clipboard"] as $id => $object)
//		{
//			$this->cloneSavedNodes($id,$object["parent"],$a_obj_id,$a_parent_id,-(int) $id);
//		}
//		$this->clearObject();
	}
	
	/**
	* remove clipboard from session
	* @access	private
	*/	
	function clearObject()
	{
//		foreach($_SESSION["clipboard"] as $id => $object)
//		{
//			$saved_tree = new Tree($id,0,-(int)$id);
//			$saved_tree->deleteTree($saved_tree->getNodeData($id,$object["parent"]));
//		}

		session_unregister("clipboard");
	}

	function cloneSavedNodes($a_source_id,$a_source_parent,$a_dest_id,$a_dest_parent,$a_tree_id)
	{
		global $tree;
		
		$object = getObject($a_source_id);
		$new_object_id = $this->callCloneMethod($a_source_id,$a_source_parent,$a_dest_id,$a_dest_parent,$object["type"]);

		$saved_tree = new Tree($a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);

		foreach ($childs as $child)
		{
			$this->cloneSavedNodes($child["child"],$child["parent"],$new_object_id,$a_dest_id,$a_tree_id);
		}
	}

	/**
	* recursive method to insert all saved nodes of the clipboard
	* @access	private
	*/	
	function insertSavedNodes($a_source_id,$a_source_parent,$a_dest_id,$a_dest_parent,$a_tree_id)
	{
		global $tree,$rbacadmin,$rbacreview;
		
		$tree->insertNode($a_source_id,$a_dest_id,$a_dest_parent);

		// SET PERMISSIONS
		$parentRoles = $rbacadmin->getParentRoleIds($a_dest_id,$a_dest_parent);
		$obj = getObject($a_source_id);

		foreach ($parentRoles as $parRol)
		{
			$ops = $rbacreview->getOperations($parRol["obj_id"], $obj["type"], $parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"],$ops,$a_source_id);
		}

		$saved_tree = new Tree($a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);

		foreach ($childs as $child)
		{
			$this->insertSavedNodes($child["child"],$child["parent"],$a_source_id,$a_dest_id,$a_tree_id);
		}
	}

	/**
	* delete objects from ILIAS
	* However objects are only removed from tree!! That means that the objects
	* itself stay in the database but are not linked in any context within the system.
	* Trash Bin Feature: Objects can be refreshed in trash
	* @access	public
	*/
	function deleteObject($a_post_data,$a_obj_id,$a_parent_id)
	{
		global $tree, $rbacsystem, $rbacadmin, $objDefinition;

		
		// AT LEAST ONE OBJECT HAS TO BE CHOSEN. 
		if (!isset($a_post_data))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// FOR ALL SELECTED OBJECTS
		foreach ($a_post_data as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $tree->getNodeData($id);
			$subtree_nodes = $tree->getSubTree($node_data);
			
			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach ($subtree_nodes as $node)
			{
				if (!$rbacsystem->checkAccess('delete',$node["obj_id"]))
				{
					$not_deletable[] = $node["obj_id"];
					$perform_delete = false;
				}
			}
		}

		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO DELETE
		if (count($not_deletable))
		{
			$not_deletable = implode(',',$not_deletable);
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ". 
									 $not_deletable,$this->ilias->error_obj->MESSAGE);
		}

		// DELETE THEM
		if (!$all_node_data[0]["type"])
		{
			// OBJECTS ARE NO 'TREE OBJECTS'
			if ($rbacsystem->checkAccess('delete',$a_obj_id))
			{
				foreach($a_post_data as $id)
				{
					$obj = getObject($id);
					$this->callDeleteMethod($id,$a_obj_id,$obj["type"]);
				}
			}
			else
			{
				$this->ilias->raiseError($this->lng->txt("no_perm_delete"),$this->ilias->error_obj->MESSAGE);
			}
		}
		else
		{
			// SAVE SUBTREE AND DELETE SUBTREE FROM TREE
			foreach ($a_post_data as $id)
			{
				// DELETE OLD PERMISSION ENTRIES
				$subnodes = $tree->getSubtree($tree->getNodeData($id));
	
				foreach ($subnodes as $subnode)
				{
					$rbacadmin->revokePermission($subnode["obj_id"]);
				}

				$tree->saveSubTree($id);
				$tree->deleteTree($tree->getNodeData($id));
			}
		}

		$this->ilias->error_obj->sendInfo($this->lng->txt("info_deleted"));
	}

	/**
	* remove objects from trash bin and all entries therefore every object needs a specific deleteObject() method 
	* @param	array	array of id to remove
	* @param	integer	obj_id
	* @param	integer	parent_id
	* @access	public
	*/
	function removeObject($a_trash_data,$a_obj_id,$a_parent_id)
	{
		global $rbacsystem,$tree;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN. 
		if (!isset($a_trash_data))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($a_trash_data as $id)
		{
			$obj_data = getObject($id);

			if (!$rbacsystem->checkAccess('delete',$a_obj_id))
			{
				$no_delete[] = $id;
			}
		}

		if (count($no_delete))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ". 
									 implode(',',$no_delete),$this->ilias->error_obj->MESSAGE);
		}

		// DELETE THEM
		foreach ($a_trash_data as $id)
		{

			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$saved_tree = new Tree(-(int)$id);
			$node_data = $saved_tree->getNodeData($id);
			$subtree_nodes = $saved_tree->getSubTree($node_data);

			// FIRST DELETE AL ENTRIES IN TREE
			$tree->deleteTree($node_data);

			foreach ($subtree_nodes as $node)
			{
				$this->callDeleteMethod($node["obj_id"],$node["parent"],$node["type"]);
			}
		}
	}

	/**
	* undelete objects from trash bin
	* @param	array	array of id to undelete
	* @param	integer	obj_id
	* @param	integer	parent_id
	* @access	public
	*/
	function undeleteObject($a_trash_id,$a_obj_id,$a_parent_id)
	{
		global $rbacsystem;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN. 
		if (!isset($a_trash_id))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($a_trash_id as $id)
		{
			$obj_data = getObject($id);

			if (!$rbacsystem->checkAccess('create',$a_obj_id,$obj_data["type"]))
			{
				$no_create[] = $id;
			}
		}

		if(count($no_create))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_paste")." ". 
									 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
		}

		foreach($a_trash_id as $id)
		{
			// INSERT AND SET PERMISSIONS
			$this->insertSavedNodes($id,$a_obj_id,$a_obj_id,$a_parent_id,-(int) $id);
			// DELETE SAVED TREE
			$saved_tree = new Tree(-(int)$id);
			$saved_tree->deleteTree($saved_tree->getNodeData($id));
		}
	}
		
	/**
	* Call delete method of a specific object type
	* @access	private
	*/	
	function callDeleteMethod($a_obj_id, $a_parent, $a_type)
	{
		global $objDefinition;

		$class_name = $objDefinition->getClassName($a_type);
		$class_constr = $class_name."Object";
		require_once("./classes/class.".$class_name."Object.php");

		$obj = new $class_constr();

		return $obj->deleteObject($a_obj_id,$a_parent);
	}

	/**
	* Call clone method of a specific object type
	* @access	private
	*/	
	function callCloneMethod($a_obj_id, $a_parent,$a_dest_id,$a_dest_parent, $a_type)
	{
		global $objDefinition;

		$class_name = $objDefinition->getClassName($a_type);
		$class_constr = $class_name."Object";
		require_once("./classes/class.".$class_name."Object.php");

		$obj = new $class_constr();

		return $obj->cloneObject($a_obj_id,$a_parent,$a_dest_id,$a_dest_parent);
	}
} // END class.Admin
