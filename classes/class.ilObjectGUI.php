<?php
/**
* Class ilObjectGUI
* Basic methods of all Output classes
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-core
*/
class ilObjectGUI
{
	/**
	* ilias object
	* @var		object ilias
	* @access	private
	*/
	var $ilias;

	/**
	* object Definition Object
	* @var		object ilias
	* @access	private
	*/
	var $objDefinition;

	/**
	* template object
	* @var		object ilias
	* @access	private
	*/
	var $tpl;

	/**
	* tree object
	* @var		object ilias
	* @access	private
	*/
	var $tree;

	/**
	* language object
	* @var		object ilias
	* @access	private
	*/
	var $lng;

	/**
	* output data
	* @var		data array
	* @access	private
	*/
	var $data;

	/**
	* object
	* @var          object
	* @access       private
	*/
	var $object;

	var $ref_id;
	var $obj_id;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjectGUI($a_data, $a_id, $a_call_by_reference)
	{
		global $ilias, $objDefinition, $tpl, $tree, $lng;
		
		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->tree =& $tree;

		$this->data = $a_data;
		$this->id = $a_id;
		$this->call_by_reference = $a_call_by_reference;

		$this->ref_id = $_GET["ref_id"];
		$this->obj_id = $_GET["obj_id"];

		// TODO: it seems that we always have to pass only the ref_id
		if ($this->call_by_reference)
		{
			$this->link_params = "ref_id=".$this->ref_id;
			$this->object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

		}
		else
		{
			$this->link_params = "ref_id=".$this->ref_id;
			$this->object =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
		}

		//prepare output
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$title = $this->object->getTitle();
		
		// catch feedback message
		sendInfo();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->setAdminTabs();
		$this->setLocator();
	}


	/**
	* set admin tabs
	* @access	public
	*/
	function setAdminTabs()
	{
		$tabs = array();
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
		$d = $this->objDefinition->getProperties($this->type);

		foreach ($d as $key => $row)
		{
			$tabs[] = array($row["lng"], $row["name"]);
		}
		
		if (isset($_GET["obj_id"]))
		{
			$object_link = "&obj_id=".$_GET["obj_id"];
		}

		foreach ($tabs as $row)
		{
			$i++;

			if ($row[1] == $_GET["cmd"])
			{
				$tabtype = "tabactive";
				$tab = $tabtype;
			}
			else
			{
				$tabtype = "tabinactive";
				$tab = "tab";
			}

			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", $tabtype);
			$this->tpl->setVariable("TAB_TYPE2", $tab);
			$this->tpl->setVariable("TAB_LINK", "adm_object.php?ref_id=".$_GET["ref_id"].$object_link."&cmd=".$row[1]);
			$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($row[0]));
			$this->tpl->parseCurrentBlock();
		}	
	}

	function setLocator($a_tree = "", $a_id = "")
	{
		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}
		
		if (!($a_id))
		{
			$a_id = $_GET["ref_id"]; 
		}

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $a_tree->getPathFull($a_id);

        //check if object isn't in tree, this is the case if parent_parent is set
		// TODO: parent_parent no longer exist. need another marker
		if ($a_parent_parent)
		{
			//$subObj = getObject($a_ref_id);
			$subObj =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);

			$path[] = array(
				"id"	 => $a_ref_id,
				"title"  => $this->lng->txt($subObj->getTitle())
				);
		}

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		if (isset($_GET["obj_id"]))
		{
			$modifier = 0;
		}

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $row["title"]);
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?ref_id=".$row["child"]);
			$this->tpl->parseCurrentBlock();
			
		}
		
		if (isset($_GET["obj_id"]))
		{
			//$obj_data = getObject($_GET["obj_id"]);
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $obj_data->getTitle());
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?ref_id=".$row["ref_id"]."&obj_id=".$_GET["obj_id"]);
			$this->tpl->parseCurrentBlock();		
		}

		$this->tpl->setCurrentBlock("locator");

		if (DEBUG)
		{
			$debug = "DEBUG: <font color=\"red\">".$this->type."::".$this->id."::".$_GET["cmd"]."</font><br/>";
		}
		
		$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);

		if ($_GET["cmd"] == "confirmDeleteAdm")
		{
			$prop_name = "delete_object";
		}

		$this->tpl->setVariable("TXT_PATH",$debug.$this->lng->txt($prop_name)." ".strtolower($this->lng->txt("of")));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* copy object to clipboard
	*/
	function copyObject()
	{
		global $tree, $rbacsystem;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// TODO: WE NEED ONLY THE ID IN THIS PLACE. MAYBE BY A FUNCTION getNodeIdsOfSubTree??
		// FOR ALL SELECTED OBJECTS
		foreach ($_POST["id"] as $ref_id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $tree->getNodeData($ref_id);
			$subtree_nodes = $tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK READ PERMISSION OF ALL OBJECTS
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
		foreach ($_POST["id"] as $ref_id)
		{
			//$tree->saveSubTree($ref_id);
			$clipboard[$ref_id]["parent"] = $_GET["ref_id"];
			$clipboard[$ref_id]["cmd"] = $_POST["cmd"];
		}

		$_SESSION["clipboard"] = $clipboard;
	}


	/**
	* paste object from clipboard to current place
	*/
	function pasteObject()
	{
		global $rbacsystem,$tree,$objDefinition,$lng;

		// CHECK SOME THINGS
		// TODO: clipboard array contains command multiple times. But there is only one command per clipboard action!!
		foreach($_SESSION["clipboard"] as $id => $object)
		{

			// IF CMD WAS 'copy' CALL PRIVATE CLONE METHOD
			if ($object["cmd"] == $lng->txt('copy'))
			{
				$this->cloneObject($_GET["ref_id"]);
				return true;
			}

			// TODO: both function below fetch almost the same data!!!
			//$obj_data = getObjectByReference($id);
			$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);
			$data = $tree->getNodeData($id);

			// CHECK ACCESS
			if (!$rbacsystem->checkAccess('create', $_GET["ref_id"], $obj_data->getType()))
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
			if ($tree->isGrandChild($id, $_GET["ref_id"]))
			{
				$is_child[] = $id;
			}

			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			//$object = getObjectByReference($_GET["ref_id"]);
			$object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

			$obj_type = $obj_data->getType();
			if (!in_array($obj_type, array_keys($objDefinition->getSubObjects($object->getType()))))
			{
				$not_allowed_subobject[] = $obj_data->getType();
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
	* clear clipboard
	*/
	function clearObject()
	{
//		foreach($_SESSION["clipboard"] as $id => $object)
//		{
//			$saved_tree = new ilTree($id,0,-(int)$id);
//			$saved_tree->deleteTree($saved_tree->getNodeData($id,$object["parent"]));
//		}

		session_unregister("clipboard");
	}


	/**
	* cut an object out from tree an copy information to clipboard
	* @access	public
	* // TODO: a_obj_id is saved in $clipboard. We don't need the parent. We may get it by tree->getParent
	*/
	function cutObject()
	{
		global $clipboard,$tree,$rbacsystem,$rbacadmin;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// CHECK ACCESS
		foreach ($_POST["id"] as $ref_id)
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
		foreach($_POST["id"] as $ref_id)
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
			$clipboard[$ref_id]["parent"] = $_GET["ref_id"];
			$clipboard[$ref_id]["cmd"] = $_POST["cmd"];
		}

		$_SESSION["clipboard"] = $clipboard;
	}


	/**
	* create an new reference of an object in tree
	* it's like a hard link of unix
	* @access	public
	*/
	function linkObject()
	{
		global $clipboard,$tree,$rbacsystem,$rbacadmin,$objDefinition;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// CHECK ACCESS
		foreach ($_POST["id"] as $ref_id)
		{
			if (!$rbacsystem->checkAccess('delete',$ref_id))
			{
				$no_cut[] = $ref_id;
			}

			//$object = getObjectByReference($ref_id);
			$object =& $this->ilias->obj_factory->getInstanceByRefId($ref_id);
			$actions = $objDefinition->getActions($object->getType());

			if ($actions["link"]["exec"] == 'false')
			{
				$no_link[] = $object->getType();
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
		foreach ($_POST["id"] as $ref_id)
		{
			//$tree->saveNode($ref_id);
			// TODO: Should the linked object save in db temporary?
			$clipboard[$ref_id]["parent"] = $_GET["ref_id"];
			$clipboard[$ref_id]["cmd"] = $_POST["cmd"];
		}

		$_SESSION["clipboard"] = $clipboard;
	} // END COPY


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
			
			//$obj_data = getObjectByReference($id);
			$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);
			$data = $tree->getNodeData($id);

			// CHECK ACCESS
			if (!$rbacsystem->checkAccess('create',$a_ref_id,$obj_data->getType()))
			{
				$no_paste[] = $id;
			}

			// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
			if ($tree->isGrandChild($id,$a_ref_id))
			{
				$is_child[] = $id;
			}

			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			//$object = getObjectByReference($a_ref_id);
			$object =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);

			if (!in_array($obj_data->getType(),array_keys($objDefinition->getSubObjects($object->getType()))))
			{
				$not_allowed_subobject[] = $obj_data->getType();
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
	* clone all nodes
	*/
	function cloneSavedNodes($a_source_id,$a_source_parent,$a_dest_id,$a_dest_parent,$a_tree_id)
	{
		global $tree;

		$new_object_id = $this->object->clone($a_dest_id);

		$saved_tree = new ilTree($a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);

		foreach ($childs as $child)
		{
			$this->cloneSavedNodes($child["child"],$child["parent"],$new_object_id,$a_dest_id,$a_tree_id);
		}
	}


	/**
	* get object back from trash
	*/
	function btn_undeleteObject()
	{
		global $rbacsystem;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_POST["trash_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["trash_id"] as $id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);

			if (!$rbacsystem->checkAccess('create',$_GET["ref_id"],$obj_data->getType()))
			{
				$no_create[] = $id;
			}
		}

		if (count($no_create))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_paste")." ".
									 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["trash_id"] as $id)
		{
			// INSERT AND SET PERMISSIONS
			$this->insertSavedNodes($id,$_GET["ref_id"],-(int) $id);
			// DELETE SAVED TREE
			$saved_tree = new ilTree(-(int)$id);
			$saved_tree->deleteTree($saved_tree->getNodeData($id));
		}


		header("location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();
	}


	/**
	* recursive method to insert all saved nodes of the clipboard
	* (maybe this function could be moved to a rbac class ?)
	*
	* @access	private
	*/
	function insertSavedNodes($a_source_id,$a_dest_id,$a_tree_id)
	{
		global $tree,$rbacadmin,$rbacreview;

		$tree->insertNode($a_source_id,$a_dest_id);

		// SET PERMISSIONS
		$parentRoles = $rbacadmin->getParentRoleIds($a_dest_id);
		$obj =& $this->ilias->obj_factory->getInstanceByRefId($a_source_id);

		foreach ($parentRoles as $parRol)
		{
			$ops = $rbacreview->getOperations($parRol["obj_id"], $obj->getType(), $parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"],$ops,$a_source_id);
		}

		$saved_tree = new ilTree($a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);

		foreach ($childs as $child)
		{
			$this->insertSavedNodes($child["child"],$a_source_id,$a_tree_id);
		}
	}

	/**
	* confirm deletion if objects (todo: find better name for operation)
	* However objects are only removed from tree!! That means that the objects
	* itself stay in the database but are not linked in any context within the system.
	* Trash Bin Feature: Objects can be refreshed in trash
	* @access	public
	*/
	function confirmObject()
	{
		global $tree, $rbacsystem, $rbacadmin, $objDefinition;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_SESSION["saved_post"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $tree->getNodeData($id);
			$subtree_nodes = $tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach ($subtree_nodes as $node)
			{
				if (!$rbacsystem->checkAccess('delete',$node["child"]))
				{
					$not_deletable[] = $node["child"];
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
			if ($rbacsystem->checkAccess('delete',$_GET["ref_id"]))
			{
				foreach($_SESSION["saved_post"] as $id)
				{
					//$obj = getObject($id);
					$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
					$this->callDeleteMethod($id,$_GET["ref_id"],$obj->getType());
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
			foreach ($_SESSION["saved_post"] as $id)
			{
				// DELETE OLD PERMISSION ENTRIES
				$subnodes = $tree->getSubtree($tree->getNodeData($id));

				foreach ($subnodes as $subnode)
				{
					$rbacadmin->revokePermission($subnode["child"]);
				}

				$tree->saveSubTree($id);
				$tree->deleteTree($tree->getNodeData($id));
			}
		}

		// Feedback
		sendInfo($this->lng->txt("info_deleted"),true);
	}


	/**
	* cancel deletion (todo: find better operation name)
	*/
	function cancelObject()
	{
		session_unregister("saved_post");
	}


	/**
	* remove objects from trash bin and all entries therefore every object needs a specific deleteObject() method
	* (todo: find better operation name)
	* @param	array	array of id to remove
	* @param	integer	obj_id
	* @param	integer	parent_id
	* @access	public
	*/
	function btn_remove_systemObject()
	{
		global $rbacsystem,$tree;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_POST["trash_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["trash_id"] as $id)
		{
			//$obj_data = getObject($id);
			//$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

			if (!$rbacsystem->checkAccess('delete',$_GET["ref_id"]))
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
		foreach ($_POST["trash_id"] as $id)
		{

			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$saved_tree = new ilTree(-(int)$id);
			$node_data = $saved_tree->getNodeData($id);
			$subtree_nodes = $saved_tree->getSubTree($node_data);

			// FIRST DELETE AL ENTRIES IN TREE
			$tree->deleteTree($node_data);

			foreach ($subtree_nodes as $node)
			{
				// Todo: I think it must be distinguished between obj and ref ids here somehow
				$node_obj =& $this->ilias->obj_factory->getInstanceByRefId($node["ref_id"]);
				$node_obj->delete();
				//$this->object->delete($node["obj_id"],$node["parent"]);
			}
		}

		header("location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=trash");
		exit();
	}


	/**
	* gateway for all button actions
	* @access	public
	*/
	function gatewayObject()
	{
		switch(key($_POST["cmd"]))
		{
			case "cut":
				$this->cutObject();
				break;

			case "copy":
				$this->copyObject();
				break;

			case "link":
				$this->linkObject();
				break;

			case "paste":
				$this->pasteObject();
				break;

			case "clear":
				$this->clearObject();
				break;

			case "delete":
				$this->deleteObject();
				break;

			case "btn_undelete":
				$this->btn_undeleteObject();
				break;

			case "btn_remove_system":
				$this->btn_remove_systemObject();
				break;

			case "cancel":
				$this->cancelObject();
				break;

			case "confirm":
				$this->confirmObject();
				break;

			default:
				$this->data = false;
		}

		if (key($_POST["cmd"]) != "delete")
		{
			header("location: adm_object.php?ref_id=".$_GET["ref_id"]);
			exit();
		}
	}


	/**
	* create new object form
	*/
	function createObject()
	{
		// creates a child object
		global $rbacsystem;

		// TODO: get rid of $_GET variable
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $_POST["new_type"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = "";
			$data["fields"]["desc"] = "";

			$this->getTemplateFile("edit");

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"].
				"&new_type=".$_POST["new_type"]);
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}


	/**
	* save object
	*/
	function saveObject()
	{
		global $rbacsystem, $rbacreview, $rbacadmin, $tree, $objDefinition;

		if ($rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"]))
		{
			// create and insert object in objecttree
			$class_name = "ilObj".$objDefinition->getClassName($_GET["new_type"]);
			require_once("./classes/class.".$class_name.".php");
			$newObj = new $class_name();
			$newObj->setType($_GET["new_type"]);
			$newObj->setTitle($_POST["Fobject"]["title"]);
			$newObj->setDescription($_POST["Fobject"]["desc"]);
			$newObj->create();
			$newObj->createReference();
			$newObj->putInTree($_GET["ref_id"]);

			unset($newObj);
		}
		else
		{
			$this->ilias->raiseError("No permission to create object", $this->ilias->error_obj->WARNING);
		}

		header("Location: adm_object.php?".$this->link_params);
		exit();
	}


	/**
	* edit object
	*/
	function editObject()
	{
		global $rbacsystem, $lng;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}
		else
		{
			$fields = array();
			$fields["title"] = $this->object->getTitle();
			$fields["desc"] = $this->object->getDescription();
			$this->displayEditForm($fields);
		}
	}


	/**
	* display edit form (usually called by editObject)
	*
	* @access	private
	* @param	array	$fields		key/value pairs of input fields
	*/
	function displayEditForm($fields)
	{
		$this->getTemplateFile("edit");
		foreach ($fields as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}
		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=update");
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}


	/**
	* update object in db
	*/
	function updateObject()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->object->setTitle($_POST["Fobject"]["title"]);
			$this->object->setDescription($_POST["Fobject"]["desc"]);
			$this->update = $this->object->update();
		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}

		header("Location: adm_object.php?ref_id=".$this->ref_id);
		exit();
	}


	/**
	* show permissions of current node
	*/
	function permObject()
	{
		global $lng, $log, $rbacsystem, $rbacreview, $rbacadmin;
		static $num = 0;

		if ($rbacsystem->checkAccess("edit permission", $this->object->getRefId()))
		{
			// Es werden nur die Rollen übergeordneter Ordner angezeigt, lokale Rollen anderer Zweige nicht
			$parentRoles = $rbacadmin->getParentRoleIds($this->object->getRefId());

			$data = array();

			// GET ALL LOCAL ROLE IDS
			$role_folder = $rbacadmin->getRoleFolderOfObject($this->object->getRefId());
			
			$local_roles = array();
			if ($role_folder)
			{
				$local_roles = $rbacadmin->getRolesAssignedToFolder($role_folder["ref_id"]);
			}
				
			foreach ($parentRoles as $r)
			{
				$data["rolenames"][] = $r["title"];

				if(!in_array($r["obj_id"],$local_roles))
				{
					$data["check_inherit"][] = ilUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
				}
				else
				{
					$data["check_inherit"][] = ilUtil::formCheckBox(1,"stop_inherit[]",$r["obj_id"]);
				}
			}

			$ope_list = getOperationList($this->object->getType());
			// BEGIN TABLE_DATA_OUTER
			foreach ($ope_list as $key => $operation)
			{
				$opdata = array();
				$opdata["name"] = $operation["operation"];

				foreach ($parentRoles as $role)
				{
					$checked = $rbacsystem->checkPermission($this->object->getRefId(), $role["obj_id"],$operation["operation"],$_GET["parent"]);
					// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
					$box = ilUtil::formCheckBox($checked,"perm[".$role["obj_id"]."][]",$operation["ops_id"]);
					$opdata["values"][] = $box;
				}
				$data["permission"][] = $opdata;
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to change permissions",$this->ilias->error_obj->WARNING);
		}
		
		$rolf_data = $rbacadmin->getRoleFolderOfObject($this->object->getRefId());
		$permission = $rolf_data ? 'write' : 'create';
		$rolf_id = $rolf_data["obj_id"] ? $rolf_data["obj_id"] : $this->object->getRefId();
		$rolf_parent = $role_data["parent"] ? $rolf_data["parent"] : $_GET["parent"];

		if ($rbacsystem->checkAccess("edit permission", $this->object->getRefId()) &&
		   $rbacsystem->checkAccess($permission, $rolf_id, "rolf"))
		{
			// Check if object is able to contain role folder
			$child_objects = $rbacadmin->getModules($this->object->getType(), $this->object->getRefId());

			if ($child_objects["rolf"])
			{
				$data["local_role"]["child"] = $this->object->getRefId();
				$data["local_role"]["parent"] = $_GET["parent"];
			}
		}

		// output data
		$this->getTemplateFile("perm");
		$this->tpl->setCurrentBlock("tableheader");
		$this->tpl->setVariable("TXT_PERMISSION", $this->lng->txt("permission"));
		$this->tpl->setVariable("TXT_ROLES", $this->lng->txt("roles"));
		$this->tpl->parseCurrentBlock();

		$num = 0;

		foreach($data["rolenames"] as $name)
		{
			// BLOCK ROLENAMES
			$this->tpl->setCurrentBlock("ROLENAMES");
			$this->tpl->setVariable("ROLE_NAME",$name);
			$this->tpl->parseCurrentBlock();

			// BLOCK CHECK INHERIT
			$this->tpl->setCurrentBLock("CHECK_INHERIT");
			$this->tpl->setVariable("CHECK_INHERITANCE",$data["check_inherit"][$num++]);
			$this->tpl->parseCurrentBlock();
		}
		$num = 0;

		foreach($data["permission"] as $ar_perm)
		{
			foreach ($ar_perm["values"] as $box)
			{
				// BEGIN TABLE CHECK PERM
				$this->tpl->setCurrentBlock("CHECK_PERM");
				$this->tpl->setVariable("CHECK_PERMISSION",$box);
				$this->tpl->parseCurrentBlock();
				// END CHECK PERM
			}

			// BEGIN TABLE DATA OUTER
			$this->tpl->setCurrentBlock("TABLE_DATA_OUTER");
			$css_row = ilUtil::switchColor($num++, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW",$css_row);
			$this->tpl->setVariable("PERMISSION", $ar_perm["name"]);
			$this->tpl->parseCurrentBlock();
			// END TABLE DATA OUTER
		}
		if ($data["local_role"] != "")
		{
			// ADD LOCAL ROLE
			$this->tpl->setCurrentBlock("LOCAL_ROLE");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->setVariable("MESSAGE_BOTTOM", $this->lng->txt("you_may_add_local_roles"));
			$this->tpl->setVariable("FORMACTION_LR","adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=addRole");

			$this->tpl->parseCurrentBlock();
		}
		// PARSE BLOCKFILE
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_STOP_INHERITANCE", $this->lng->txt("stop_inheritance"));
		$this->tpl->setVariable("FORMACTION","adm_object.php?".$this->link_params."&cmd=permSave");
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
	}

	
	/**
	* save permissions
	*/
	function permSaveObject()
	{
		global $tree,$rbacsystem,$rbacreview,$rbacadmin;

		// TODO: get rid of $_GET variables

		if ($rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$rbacadmin->revokePermission($_GET["ref_id"]);

			foreach ($_POST["perm"] as $key => $new_role_perms)
			{
				// $key enthaelt die aktuelle Role_Id
				$rbacadmin->grantPermission($key,$new_role_perms,$_GET["ref_id"]);
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to change permission",$this->ilias->error_obj->WARNING);
		}
		// Wenn die Vererbung der Rollen Templates unterbrochen werden soll,
		// muss folgendes geschehen:
		// - existiert kein RoleFolder, wird er angelegt und die Rechte aus den Permission Templates ausgelesen
		// - existiert die Rolle im aktuellen RoleFolder werden die Permission Templates dieser Rolle angezeigt
		// - existiert die Rolle nicht im aktuellen RoleFolder wird sie dort angelegt
		//   und das Permission Template an den Wert des nächst höher gelegenen Permission Templates angepasst

		if ($_POST["stop_inherit"])
		{
			foreach ($_POST["stop_inherit"] as $stop_inherit)
			{
				$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["ref_id"]);
				
				if (empty($rolf_data["child"]))
				{
					// CHECK ACCESS 'create' rolefolder
					if ($rbacsystem->checkAccess('create',$_GET["ref_id"],'rolf'))
					{
						require_once ("classes/class.ilObjRoleFolder.php");
						$rolfObj = new ilObjRoleFolder();
						$rolfObj->setTitle("Local roles");
						$rolfObj->setDescription("Role Folder of object no. ".$_GET["ref_id"]);
						$rolfObj->create();
						$rolfObj->createReference();
						$rolfObj->putInTree($_GET["ref_id"]);
						unset($rolfObj);
						
						$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["ref_id"]);
					}
					else
					{
						$this->ilias->raiseError("No permission to create Role Folder",$this->ilias->error_obj->WARNING);
					}
				}
				
				// CHECK ACCESS 'write' of role folder
				if ($rbacsystem->checkAccess('write',$rolf_data["child"]))
				{
					$parentRoles = $rbacadmin->getParentRoleIds($rolf_data["child"]);
					$rbacadmin->copyRolePermission($stop_inherit,$parentRoles[$stop_inherit]["parent"],
												   $rolf_data["child"],$stop_inherit);
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_data["child"],$_GET["ref_id"],'n');
				}
				else
				{
					$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
				}
			}// END FOREACH
		}// END STOP INHERIT
	
		sendinfo($this->lng->txt("saved_successfully"),true);	
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=perm");
		exit();
	}


	/**
	* add local role
	*/
	function addRoleObject()
	{
		global $tree,$rbacadmin,$rbacreview,$rbacsystem;

		$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["ref_id"]);

		if (!($rolf_id = $rolf_data["child"]))
		{
			$mods = $rbacadmin->getModules($this->object->getType(),$_GET["ref_id"]);
			//if (!in_array('rolf',$rbacadmin->getModules($this->object->getType(),$_GET["ref_id"])))
			if (!isset($mods["rolf"]))
			{
				$this->ilias->raiseError("'".$this->object->getTitle()."' are not allowed to contain Role Folder",$this->ilias->error_obj->WARNING);
			}

			// CHECK ACCESS 'create' rolefolder
			if ($rbacsystem->checkAccess('create',$_GET["ref_id"],'rolf'))
			{
				require_once ("classes/class.ilObjRoleFolder.php");
				$rolfObj = new ilObjRoleFolder();
				$rolfObj->setTitle("Role Folder");
				$rolfObj->setDescription("Automaticly generated Role Folder ".$this->object->getRefId());
				$rolfObj->create();
				$rolfObj->createReference();
				$rolfObj->putInTree($this->object->getRefId());

				$rolf_id = $rolfObj->getRefId();

				// Suche aller Parent Rollen im Baum
				$parentRoles = $rbacadmin->getParentRoleIds($this->object->getRefId());
				foreach ($parentRoles as $parRol)
				{
					// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
					$ops = $rbacreview->getOperations($parRol["obj_id"],'rolf',$parRol["parent"]);
					// TODO: make this work:
					//$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id);
				}
			}
			else
			{
				$this->ilias->raiseError("No permission to create role folder",$this->ilias->error_obj->WARNING);
			}
		}

		// CHECK ACCESS 'write' of role folder
		if ($rbacsystem->checkAccess('write',$rolf_id))
		{
			require_once ("classes/class.ilObjRole.php");
			$roleObj = new ilObjRole();
			$roleObj->setTitle($_POST["Flocal_role"]);
			$roleObj->setDescription("No description");
			$roleObj->create();
			$new_obj_id = $roleObj->getId();
			$rbacadmin->assignRoleToFolder($new_obj_id,$rolf_id,$_GET["ref_id"],'y');
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
		
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=perm");
		exit();
	}


	/*
	* display object owner
	*/
	function ownerObject()
	{
		global $lng;

		$this->getTemplateFile("owner");
		$this->tpl->setVariable("OWNER_NAME", $this->object->getOwnerName());
		$this->tpl->setVariable("TXT_OBJ_OWNER", $this->lng->txt("obj_owner"));
		$this->tpl->setVariable("CMD","update");
		$this->tpl->parseCurrentBlock();
	}


	/**
	* display object list
	*/
	function displayList()
	{
		global $tree, $rbacsystem;

	    $this->getTemplateFile("view");
		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		//table header
		$this->tpl->setCurrentBlock("table_header_cell");

		foreach ($this->data["cols"] as $key)
		{
			if ($key != "")
			{
			    $out = $this->lng->txt($key);
			}
			else
			{
				$out = "&nbsp;";
			}
			$num++;

			$this->tpl->setVariable("HEADER_TEXT", $out);
			$this->tpl->setVariable("HEADER_LINK", "adm_object.php?ref_id=".$_GET["ref_id"]."&order=type&direction=".
							  $_GET["dir"]."&cmd=".$_GET["cmd"]);

			$this->tpl->parseCurrentBlock();
		}

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				// surpress checkbox for particular object types
				if (!$this->objDefinition->hasCheckbox($ctrl["type"]))
				{
					$this->tpl->touchBlock("empty_cell");
				}
				else
				{
					$this->tpl->setCurrentBlock("checkbox");
					$this->tpl->setVariable("CHECKBOX_ID", $ctrl["ref_id"]);
					$this->tpl->setVariable("CSS_ROW", $css_row);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					//build link
					$link = "adm_object.php?";

					if ($_GET["type"] == "lo" && $key == "type")
					{
						$link = "lo_view.php?";
					}

					$n = 0;

					foreach ($ctrl as $key2 => $val2)
					{
						$link .= $key2."=".$val2;

						if ($n < count($ctrl)-1)
						{
					    	$link .= "&";
							$n++;
						}
					}

					if ($key == "title" || $key == "type")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);

						if ($_GET["type"] == "lo" && $key == "type")
						{
							$this->tpl->setVariable("NEW_TARGET", "\" target=\"lo_view\"");
						}

						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}

			// TODO: this loop is for marking objects 'cut' or 'copied'.
			// the array structure if clipboard must be change!!!
					if (isset($_SESSION["clipboard"]))
					{
						foreach ($_SESSION["clipboard"] as $clip_id => $clip)
						{
							if ($ctrl["ref_id"] == $clip_id)
							{
								if ($clip["cmd"]["cut"] and $key == "title")
								{
									$val = "<del>".$val."</del>";
								}
								
								if ($clip["cmd"]["copy"] and $key == "title")
								{
									$val = "<font color=\"green\">+</font>  ".$val;
								}
							}
						}
					}

					$this->tpl->setCurrentBlock("text");
					$this->tpl->setVariable("TEXT_CONTENT", $val);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

				} //foreach

				$this->tpl->setCurrentBlock("table_row");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
		}

		// SHOW VALID ACTIONS
		$this->showActions();

		// SHOW POSSIBLE SUB OBJECTS
		$this->showPossibleSubObjects();
	}

	/**
	* list childs of current object"
	*/
	function viewObject()
	{
		global $tree,$rbacsystem,$lng;

		//prepare objectlist
		$this->objectList = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$this->data["cols"] = array("", "type", "title", "description", "last_change");

		$childs = $tree->getChilds($_GET["ref_id"], $_GET["order"], $_GET["direction"]);

		foreach ($childs as $key => $val)
	    {
			// visible
			if (!$rbacsystem->checkAccess("visible",$val["ref_id"]))
			{
				continue;
			}
			//visible data part
			$this->data["data"][] = array(
				"type" => "<img src=\"".$this->tpl->tplPath."/images/"."icon_".$val["type"]."_b.gif\" border=\"0\">",
				"title" => $val["title"],
				"description" => $val["desc"],
				"last_change" => ilFormat::formatDate($val["last_update"])
			);
			//control information
			$this->data["ctrl"][] = array(
				"type" => $val["type"],
				"ref_id" => $val["ref_id"]
			);
	    } //foreach

		$this->displayList();
	}


	/**
	* display deletion confirmation screen
	*/
	function deleteObject()
	{
		global $lng;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["id"] as $id)
		{
			//$obj_data = getObject($id);
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate());
		}
		$this->data["buttons"] = array( "cancel"  => $lng->txt("cancel"),
								  "confirm"  => $lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		sendInfo($this->lng->txt("info_delete_sure"));
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");
		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* show trash content of object
	*/
	function trashObject()
	{
		global $lng,$tree;

		$objects = $tree->getSavedNodeData($_GET["ref_id"]);

		if(count($objects) == 0)
		{
			sendInfo($lng->txt("msg_trash_empty"));
			$this->data["empty"] = true;
		}
		else
		{
			$this->data["empty"] = false;
			$this->data["cols"] = array("","type", "title", "description", "last_change");

			foreach($objects as $obj_data)
			{
				$this->data["data"]["$obj_data[child]"] = array(
					"checkbox"    => "",
					"type"        => $obj_data["type"],
					"title"       => $obj_data["title"],
					"desc"        => $obj_data["desc"],
					"last_update" => $obj_data["last_update"]);
			}
			$this->data["buttons"] = array( "btn_undelete"  => $lng->txt("btn_undelete"),
									  "btn_remove_system"  => $lng->txt("btn_remove_system"));
		}

		$this->getTemplateFile("confirm");

		if ($this->data["empty"] == true)
		{
			return;
		}
		
		sendInfo($this->lng->txt("info_trash"));

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");

		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach($this->data["data"] as $key1 => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key2 => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");
				// CREATE CHECKBOX
				if($key2 == "checkbox")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::formCheckBox(0,"trash_id[]",$key1));
				}

				// CREATE TEXT STRING
				elseif($key2 == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	function showActions()
	{
		$notoperations = array();
		// NO PASTE AND CLEAR IF CLIPBOARD IS EMPTY
		if (empty($_SESSION["clipboard"]))
		{
			$notoperations[] = "paste";
			$notoperations[] = "clear";
		}
		// CUT COPY PASTE LINK DELETE IS NOT POSSIBLE IF CLIPBOARD IS FILLED
		if ($_SESSION["clipboard"])
		{
			$notoperations[] = "cut";
			$notoperations[] = "copy";
			$notoperations[] = "link";
		}

		$operations = array();

		$d = $this->objDefinition->getActions($_GET["type"]);

		foreach ($d as $row)
		{
			if (!in_array($row["name"], $notoperations))
			{
				$operations[] = $row;
			}
		}

		if (count($operations)>0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("operation_btn");
				$this->tpl->setVariable("BTN_NAME", $val["lng"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("operation");
			$this->tpl->parseCurrentBlock();
		}
	}

	function showPossibleSubObjects()
	{
		$d = $this->objDefinition->getSubObjects($_GET["type"]);

		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;
				if ($row["max"] > 0)
				{
					//how many elements are present?
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						if ($this->data["ctrl"][$i]["type"] == $row["name"])
						{
						    $count++;
						}
					}
				}
				if ($row["max"] == "" || $count < $row["max"])
				{
					$subobj[] = $row["name"];
				}
			}
		}

		if (is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);

			$this->tpl->setCurrentBlock("add_obj");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("FORMACTION_OBJ_ADD", "adm_object.php?cmd=create&ref_id=".$_GET["ref_id"]);
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function getTemplateFile($a_cmd,$a_type = "")
	{
		// <get rid of $_GET variable
		if (!$a_type)
		{
			$a_type = $_GET["type"];
		}

		$template = "tpl.".$a_type."_".$a_cmd.".html";

		if (!$this->tpl->fileExists($template))
		{
			$template = "tpl.obj_".$a_cmd.".html";
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", $template);
	}
}

