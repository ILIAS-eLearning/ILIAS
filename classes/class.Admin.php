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
	 * Constructor
	 * @access public
	 */
	function Admin()
	{
		global $ilias;
		
		$this->ilias = &$ilias;
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
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
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
			$this->ilias->raiseError("You have no permission to cut object(s) No. ".
									 implode(',',$no_cut)."<br />Action aborted",$this->ilias->error_obj->MESSAGE);
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
	 * @access public
	 */	
	function linkObject()
	{
		global $clipboard,$tree,$rbacsystem,$rbacadmin,$objDefinition;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
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
			$this->ilias->raiseError("You have no permission to create a link on  object(s) No. ".
									 implode(',',$no_cut)."<br />Action aborted",$this->ilias->error_obj->MESSAGE);
		}
		if(count($no_link))
		{
			$this->ilias->raiseError("It's not possible to create a symbolic link on object type(s) ".
									 implode(',',$no_link)."<br />Action aborted",$this->ilias->error_obj->MESSAGE);
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

	function pasteObject()
	{
		global $rbacsystem,$tree;
		
		// CHECK SOME THINGS
		foreach($_SESSION["clipboard"] as $id => $object)
		{
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
		}
		if(count($no_paste))
		{
			$this->ilias->raiseError("You have no permission to paste object(s) No. ".
									 implode(',',$no_paste)."<br />Action aborted",$this->ilias->error_obj->MESSAGE);
		}
		if(count($exists))
		{
			$this->ilias->raiseError("The object(s) No. ".implode(',',$exists)." already exists in this folder",
									 $this->ilias->error_obj->MESSAGE);
		}
		if(count($is_child))
		{
			$this->ilias->raiseError("It's not possible to paste the object(s) No. ".implode(',',$is_child)." in itself",
									 $this->ilias->error_obj->MESSAGE);
		}
		foreach($_SESSION["clipboard"] as $id => $object)
		{
			$this->insertSavedNodes($id,$object["parent"],$_GET["obj_id"],$_GET["parent"],-(int) $id);
		}
		$this->clearObject();
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
		
		// AT LEAST ONE OBJECT HAS TO BE CHOSEN. 
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
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
			$this->ilias->raiseError("You have no permission to delete object(s) No. ".
									 $not_deletable."<br />Action aborted",$this->ilias->error_obj->MESSAGE);
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
				$this->ilias->raiseError("You have no permission to delete these objects",$this->ilias->error_obj->MESSAGE);
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
		$this->ilias->error_obj->sendInfo("Object(s) deleted!");
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
		$obj->deleteObject($a_obj_id,$a_parent);
	}
} // END class.Admin