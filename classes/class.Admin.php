<?php
/**
 * Class Admin
 * Objectmanagement functions
 * 
 * @author Stefan Meyer <smeyer@databay.de>
 * @author SAscha Hofamnn <shofmann@databay.de> 
 * @version $Id$
 * 
 * @package ilias-core
 */
class Admin 
{
	/**
	 * ilias object
	 * @var object ilias
	 * @access private
	 */
	var $ilias;
	
	/**
	 * language object
	 * @var object language
	 * @access private
	 */
	var $lng;


	/**
	 * Constructor
	 * @access public
	 */
	function Admin()
	{
		global $ilias, $lng;
		
		$this->ilias = &$ilias;
		$this->lng   = &$lng;
	}
	
	/**
	 * cut an object out from tree an copy information to clipboard
	 * @access public
	 */
	function cutObject()
	{
		global $clipboard,$tree,$rbacsystem,$rbacadmin;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		// CHECK ACCESS
		foreach($_POST["id"] as $obj_id)
		{
			if(!$rbacsystem->checkAccess('delete',$obj_id,$_GET["obj_id"]))
			{
				$no_cut[] = $obj_id;
			}
		}
		// NO ACCESS
		if(count($no_cut))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_cut")." ".
									 implode(',',$no_cut),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE SUBTREE AND DELETE SUBTREE FROM TREE
		foreach($_POST["id"] as $id)
		{
			// DELETE OLD PERMISSION ENTRIES
			$subnodes = $tree->getSubtree($tree->getNodeData($id,$_GET["obj_id"]));
			foreach($subnodes as $subnode)
			{
				$rbacadmin->revokePermission($subnode["obj_id"],$subnode["parent"]);
			}
			$tree->saveSubtree($id,$_GET["obj_id"],1);
			$tree->deleteTree($tree->getNodeData($id,$_GET["obj_id"]));
			$clipboard[$id]["parent"] = $_GET["obj_id"];
			$clipboard[$id]["cmd"] = $_POST["cmd"];
		}
		$_SESSION["clipboard"] = $clipboard;
	}
	
	/**
	 * create an new reference of an object in tree
	 * it's like a hard link of unix
	 * @access public
	 */	
	function linkObject()
	{
		global $clipboard,$tree,$rbacsystem,$rbacadmin,$objDefinition;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		// CHECK ACCESS
		foreach($_POST["id"] as $obj_id)
		{
			if(!$rbacsystem->checkAccess('delete',$obj_id,$_GET["obj_id"]))
			{
				$no_cut[] = $obj_id;
			}
			$object = getObject($obj_id);
			$actions = $objDefinition->getActions($object["type"]);
			if($actions["link"]["exec"] == 'false')
			{
				$no_link[] = $object["type"];
			}
		}
		// NO ACCESS
		if(count($no_cut))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_link")." ".
									 implode(',',$no_cut),$this->ilias->error_obj->MESSAGE);
		}
		if(count($no_link))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_possible_link")." ".
									 implode(',',$no_link),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE SUBTREE
		foreach($_POST["id"] as $id)
		{
			$tree->saveSubtree($id,$_GET["obj_id"],1);
			$clipboard[$id]["parent"] = $_GET["obj_id"];
			$clipboard[$id]["cmd"] = $_POST["cmd"];
		}
		$_SESSION["clipboard"] = $clipboard;
	} // END COPY
	
	function copyObject()
	{
		global $tree, $rbacsystem, $rbacadmin, $objDefinition;
		
		// AT LEAST ONE OBJECT HAS TO BE CHOSEN. 
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// FOR ALL SELECTED OBJECTS
		foreach($_POST["id"] as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $tree->getNodeData($id,$_GET["obj_id"]);
			$subtree_nodes = $tree->getSubTree($node_data);
			
			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach($subtree_nodes as $node)
			{
				if(!$rbacsystem->checkAccess('read',$node["obj_id"],$node["parent"]))
				{
					$no_copy[] = $node["obj_id"];
					$perform_copy = false;
				}
			}
		}
		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'read'
		if(count($no_copy))
		{
			$no_copy = implode(',',$no_copy);
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_copy")." ".
									 $no_copy,$this->ilias->error_obj->MESSAGE);
		}

		// COPY TRHEM
		// SAVE SUBTREE
		foreach($_POST["id"] as $id)
		{
			$tree->saveSubtree($id,$_GET["obj_id"],1);
			$clipboard[$id]["parent"] = $_GET["obj_id"];
			$clipboard[$id]["cmd"] = $_POST["cmd"];
		}
		$_SESSION["clipboard"] = $clipboard;
	}
		

	function pasteObject()
	{
		global $rbacsystem,$tree,$objDefinition,$lng;

		// CHECK SOME THINGS
		foreach($_SESSION["clipboard"] as $id => $object)
		{
			// IF CMD WAS 'copy' CALL PRIVATE CLONE METHOD
			if($object["cmd"] == $lng->txt('copy'))
			{
				$this->cloneObject();
				return true;
			}

			$obj_data = getObject($id);
			$data = $tree->getNodeData($id,$_GET["obj_id"]);
			// CHECK ACCESS
			if(!$rbacsystem->checkAccess('create',$_GET["obj_id"],$_GET["parent"],$obj_data["type"]))
			{
				$no_paste[] = $id;
			}
			// CHECK IF REFERENCE ALREADY EXISTS
			if($data["obj_id"])
			{
				$exists[] = $id;
			}
			// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
			if($tree->isGrandChild($id,$object["parent"],$_GET["obj_id"],$_GET["parent"]))
			{
				$is_child[] = $id;
			}
			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			$object = getObject($_GET["obj_id"]);
			if(!in_array($obj_data["type"],array_keys($objDefinition->getSubObjects($object["type"]))))
			{
				$not_allowed_subobject[] = $obj_data["type"];
			}
		}
		if(count($exists))
		{
			$this->ilias->raiseError($this->lng->txt("msg_obj_exists"),$this->ilias->error_obj->MESSAGE);
		}
		if(count($is_child))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_in_itself")." ".implode(',',$is_child),
									 $this->ilias->error_obj->MESSAGE);
		}
		if(count($not_allowed_subobject))
		{
			$this->ilias->raiseError($this->lng->txt("msg_may_not_contain")." ".implode(',',$not_allowed_subobject),
									 $this->ilias->error_obj->MESSAGE);
		}
		if(count($no_paste))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_paste")." ". 
									 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
		}
		foreach($_SESSION["clipboard"] as $id => $object)
		{
			$this->insertSavedNodes($id,$object["parent"],$_GET["obj_id"],$_GET["parent"],-(int) $id);
		}
		$this->clearObject();
	}

	/**
	 * clone Object subtree
	 * @access private
	 */	
	function cloneObject()
	{
		global $objDefinition,$tree,$rbacsystem;

		foreach($_SESSION["clipboard"] as $id => $object)
		{
			// CHECK SOME THNGS
			$obj_data = getObject($id);
			$data = $tree->getNodeData($id,$_GET["obj_id"]);

			// CHECK ACCESS
			if(!$rbacsystem->checkAccess('create',$_GET["obj_id"],$_GET["parent"],$obj_data["type"]))
			{
				$no_paste[] = $id;
			}

			// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
			if($tree->isGrandChild($id,$object["parent"],$_GET["obj_id"],$_GET["parent"]))
			{
				$is_child[] = $id;
			}

			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			$object = getObject($_GET["obj_id"]);
			if(!in_array($obj_data["type"],array_keys($objDefinition->getSubObjects($object["type"]))))
			{
				$not_allowed_subobject[] = $obj_data["type"];
			}
		}
		if(count($no_paste))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create")." ".
									 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
		}
		if(count($is_child))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_in_itself")." ".implode(',',$is_child),
									 $this->ilias->error_obj->MESSAGE);
		}
		if(count($not_allowed_subobject))
		{
			$this->ilias->raiseError($this->lng->txt("msg_may_not_contain")." ".implode(',',$not_allowed_subobject),
									 $this->ilias->error_obj->MESSAGE);
		}
		// NOW CLONE ALL OBJECTS
		// THERFORE THE CLONE METHOD OF ALL OBJECTS IS CALLED
		foreach($_SESSION["clipboard"] as $id => $object)
		{
			$this->cloneSavedNodes($id,$object["parent"],$_GET["obj_id"],$_GET["parent"],-(int) $id);
		}
//		$this->clearObject();
	}
	
	/**
	 * remove clipboard from session
	 * @access public
	*/	
	function clearObject()
	{
		foreach($_SESSION["clipboard"] as $id => $object)
		{
			$saved_tree = new Tree($id,$object["parent"],0,-(int)$id);
			$saved_tree->deleteTree($saved_tree->getNodeData($id,$object["parent"]));
		}
		session_unregister("clipboard");
	}
	function cloneSavedNodes($a_source_id,$a_source_parent,$a_dest_id,$a_dest_parent,$a_tree_id)
	{
		global $tree;
		
		$object = getObject($a_source_id);
		$new_object_id = $this->callCloneMethod($a_source_id,$a_source_parent,$a_dest_id,$a_dest_parent,$object["type"]);

		$saved_tree = new Tree($a_source_id,$a_source_parent,0,$a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);
		foreach($childs as $child)
		{
			$this->cloneSavedNodes($child["child"],$child["parent"],$new_object_id,$a_dest_id,$a_tree_id);
		}
	}


	/**
	 * recursive method to insert all saved nodes of the clipboard
	 * @access private
	 */	
	function insertSavedNodes($a_source_id,$a_source_parent,$a_dest_id,$a_dest_parent,$a_tree_id)
	{
		global $tree,$rbacadmin,$rbacreview;
		
		$tree->insertNode($a_source_id,$a_dest_id,$a_dest_parent);

		// SET PERMISSIONS
		$parentRoles = $rbacadmin->getParentRoleIds($a_dest_id,$a_dest_parent);
		$obj = getObject($a_dest_id);
		foreach ($parentRoles as $parRol)
		{
			$ops = $rbacreview->getOperations($parRol["obj_id"], $obj["type"], $parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"],$ops,$a_source_id,$a_dest_id);
		}

		$saved_tree = new Tree($a_source_id,$a_source_parent,0,$a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);
		foreach($childs as $child)
		{
			$this->insertSavedNodes($child["child"],$child["parent"],$a_source_id,$a_dest_id,$a_tree_id);
		}
	}


	/**
	 * delete objects from ILIAS
	 * However objects are only removed from rbac system and tree!! That means that the objects
	 * itself stay in the database but are not linked in any context within the system.
	 * TODO: Trash Bin Feature (Functions to manage 'deleted' objects & remove them entirely from system.)
	 * @access public
	 */
	function deleteObject()
	{
		global $tree, $rbacsystem, $rbacadmin, $objDefinition;

		// LOAD SAVED POST VALUES
		$_POST["id"] = $_SESSION["saved_post"];
		
		// AT LEAST ONE OBJECT HAS TO BE CHOSEN. 
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// FOR ALL SELECTED OBJECTS
		foreach($_POST["id"] as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $tree->getNodeData($id,$_GET["obj_id"]);
			$subtree_nodes = $tree->getSubTree($node_data);
			
			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach($subtree_nodes as $node)
			{
				if(!$rbacsystem->checkAccess('delete',$node["obj_id"],$node["parent"]))
				{
					$not_deletable[] = $node["obj_id"];
					$perform_delete = false;
				}
			}
		}
		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO DELETE
		if(count($not_deletable))
		{
			$not_deletable = implode(',',$not_deletable);
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ". 
									 $not_deletable,$this->ilias->error_obj->MESSAGE);
		}

		// DELETE THEM
		if(!$all_node_data[0]["type"])
		{
			// OBJECTS ARE NO 'TREE OBJECTS'
			
			if($rbacsystem->checkAccess('delete',$_GET["obj_id"],$_GET["parent"]))
			{
				foreach($_POST["id"] as $id)
				{
					$obj = getObject($id);
					$this->callDeleteMethod($id,$_GET["obj_id"],$obj["type"]);
				}
			}
			else
			{
				$this->ilias->raiseError($this->lng->txt("no_perm_delete"),$this->ilias->error_obj->MESSAGE);
			}
		}
		else
		{
			// FIRST DELETE AL ENTRIES IN TREE
			foreach($all_node_data as $node_data)
			{
				$tree->deleteTree($node_data);
			}
			foreach($all_subtree_nodes as $subtree_nodes)
			{
				foreach($subtree_nodes as $node)
				{
					$this->callDeleteMethod($node["obj_id"],$node["parent"],$node["type"]);
				}
			}
		}
		$this->ilias->error_obj->sendInfo($this->lng->txt("info_deleted"));
	}

	/**
	 * Call delete method of a specific object type
	 * @access private
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
	 * @access private
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