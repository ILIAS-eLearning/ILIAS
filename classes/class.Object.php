<?php
/**
* Class Object
* Basic functions for all objects
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
*
* @package ilias-core
*/
class Object
{
	/**
	* ilias object
	* @var		object ilias
	* @access	private
	*/
	var $ilias;

	/**
	* lng object
	* @var		object language
	* @access	private
	*/
	var $lng;

	/**
	* object id
	* @var		integer object id of object itself
	* @access	private
	*/
	var $id;
	var $type;
	var $title;
	var $desc;
	var $owner;
	var $create_date;
	var $last_update;
	
	/**
	* object list
	* @var		array	contains all child objects of current object
	* @access	private
	*/
	var $objectList;
	
	/**
	* Constructor
	* @access	public
	*/
	function Object($a_id)
	{
		global $ilias, $lng;
		
		$this->ilias =& $ilias;
		$this->lng = &$lng; 

		$this->id = $a_id;
		$this->parent = $_GET["parent"]; // possible deprecated
		$this->parent_parent = $_GET["parent_parent"]; // // possible deprecated

		// get object data...
		$data = getObject($this->id);
		// ...and write data to class member variables 
		$this->type = $data["type"];
		$this->title = $data["title"];
		$this->desc = $data["description"];
		$this->owner = $data["owner"];
		$this->create_date = $data["create_date"];
		$this->last_update = $data["last_update"];
	}

	/**
	* copy all entries of an object !!! IT MUST RETURN THE NEW OBJECT ID !!
	* @access	public
	* @return new object id
	*/
	function cloneObject($a_obj_id,$a_parent,$a_dest_id,$a_dest_parent)
	{
		global $tree,$rbacadmin,$rbacreview;

		$object = getObject($a_obj_id);
		$new_id = copyObject($a_obj_id);
		$tree->insertNode($new_id,$a_dest_id,$a_dest_parent);

		$parentRoles = $rbacadmin->getParentRoleIds($a_dest_id,$a_dest_parent);
			
		foreach ($parentRoles as $parRol)
		{
			// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
			$ops = $rbacreview->getOperations($parRol["obj_id"], $object["type"], $parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"],$ops, $new_id, $a_dest_id);
		}
		return $new_id;
	}

	/**
	* gateway for all button actions
	* @access	public
	*/
	function gatewayObject()
	{
		global $lng;

		include_once ("classes/class.Admin.php");
		
		$admin = new Admin();
		
		switch(key($_POST["cmd"]))
		{
			case "cut":
				return $admin->cutObject($_POST["id"],$_POST["cmd"],$_GET["obj_id"]);
				break;
			case "copy":
				return $admin->copyObject($_POST["id"],$_POST["cmd"],$_GET["obj_id"]);
				break;
			case "link":
				return $admin->linkObject($_POST["id"],$_POST["cmd"],$_GET["obj_id"]);
				break;
			case "paste":
				return $admin->pasteObject($_GET["obj_id"],$_GET["parent"]);
				break;
			case "clear":
				return $admin->clearObject();
				break;
			case "delete":
				return $this->confirmDeleteAdmObject();
				break;
			case "btn_undelete":
				return $admin->undeleteObject($_POST["trash_id"],$_GET["obj_id"],$_GET["parent"]); 
				break;
			case "btn_remove_system":
				return $admin->removeObject($_POST["trash_id"],$_GET["obj_id"],$_GET["parent"]); 
				break;
			case "cancel":
				session_unregister("saved_post");
				break;
			case "confirm":
				return $admin->deleteObject($_SESSION["saved_post"],$_GET["obj_id"],$_GET["parent"]);
				break;
			default: 
				return false;
		}


	}
	/**
	* create object in admin interface
	* @access	public
	*/
	function createObject($a_id, $a_new_type)
	{
		// creates a child object
		global $rbacsystem;

		// TODO: get rid of $_GET variable
		if ($rbacsystem->checkAccess("create", $a_id, $_GET["parent"], $a_new_type))
		{
			$data = array();
			$data["fields"] = array();						
			$data["fields"]["title"] = "";
			$data["fields"]["desc"] = "";
			return $data;
		}
		else
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
	}
	
	/**
	* saves new object in admin interface
	* 
	* @param	integer		obj_id
	* @param	integer		parent_id
	* @param	string		obj_type
	* @param	string		new_obj_type
	* @param	array		title & description
	* @return	integer		new obj_id
	* @access	public
	**/
	function saveObject($a_obj_id, $a_parent,$a_type, $a_new_type, $a_data)
	{
		global $rbacsystem,$rbacreview,$rbacadmin,$tree;

		if ($rbacsystem->checkAccess("create",$a_obj_id,$a_parent,$a_new_type))
		{
			// create and insert object in objecttree
			$this->id = createNewObject($a_new_type,$a_data["title"],$a_data["desc"]);
			$tree->insertNode($this->id,$a_obj_id,$a_parent);

			$parentRoles = $rbacadmin->getParentRoleIds();
			
			foreach ($parentRoles as $parRol)
			{
				// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
				$ops = $rbacreview->getOperations($parRol["obj_id"], $a_new_type, $parRol["parent"]);
				$rbacadmin->grantPermission($parRol["obj_id"],$ops, $this->id, $a_obj_id);
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to create object", $this->ilias->error_obj->WARNING);
		}
		
		return $this->id;
	}

	/**
	* edit object
	* @access	public
	**/
	function editObject($a_order, $a_direction)
	{
		global $rbacsystem, $lng;

		if ($rbacsystem->checkAccess("write", $this->id, $this->parent))
		{
			$obj = getObject($this->id);
			
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = $obj["title"];
			$data["fields"]["desc"] = $obj["desc"];
			$data["cmd"] = "update";
			return $data;
 		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}
	}
	

	/**
	* update an object
	* @access	public
	**/
	function updateObject($a_data)
	{
		global $rbacsystem;

		if($rbacsystem->checkAccess("write", $this->id, $this->parent))
		{
			updateObject($this->id,$a_data["title"],$a_data["desc"]);
			$this->update = true;
			return true;
		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}
	}
	
	/**
	* show permissions of object
	* @access	public
	**/
	function permObject()
	{
		global $lng, $rbacsystem, $rbacreview, $rbacadmin;
		static $num = 0;
		
		// TODO: get rif of $_GET["parent"] in this function

		$obj = getObject($this->id);

		if ($rbacsystem->checkAccess("edit permission", $this->id, $_GET["parent"]))
		{

			// Es werden nur die Rollen übergeordneter Ordner angezeigt, lokale Rollen anderer Zweige nicht
			$parentRoles = $rbacadmin->getParentRoleIds();

			$data = array();

			foreach ($parentRoles as $r)
			{
				// GET ALL LOCAL ROLE IDS
				$role_folders = $rbacadmin->getRoleFolderOfObject($this->id);
				$local_roles = $rbacadmin->getRolesAssignedToFolder($role_folders["child"]);
				$data["rolenames"][] = $r["title"];
				if(!in_array($r["obj_id"],$local_roles))
				{
					$data["check_inherit"][] = TUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
				}
				else
				{
					$data["check_inherit"][] = TUtil::formCheckBox(1,"stop_inherit[]",$r["obj_id"]);
				}
			}
			
			$ope_list = getOperationList($obj["type"]);
			// BEGIN TABLE_DATA_OUTER
			foreach ($ope_list as $key => $operation)
			{
				$opdata = array();
				$opdata["name"] = $operation["operation"];
				
				foreach ($parentRoles as $role)
				{
					$checked = $rbacsystem->checkPermission($this->id,$role["obj_id"],$operation["operation"],$_GET["parent"]);
					// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
					$box = TUtil::formCheckBox($checked,"perm[".$role["obj_id"]."][]",$operation["ops_id"]);
					$opdata["values"][] = $box;
				}
				$data["permission"][] = $opdata;
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to change permissions",$this->ilias->error_obj->WARNING);
		}
		
		$rolf_data = $rbacadmin->getRoleFolderOfObject($this->id);
		$permission = $rolf_data ? 'write' : 'create';
		$rolf_id = $rolf_data["obj_id"] ? $rolf_data["obj_id"] : $this->id;
		$rolf_parent = $role_data["parent"] ? $rolf_data["parent"] : $_GET["parent"];
		
		if ($rbacsystem->checkAccess("edit permission", $this->id, $_GET["parent"]) &&
		   $rbacsystem->checkAccess($permission, $rolf_id, $rolf_parent, "rolf"))
		{
			// Check if object is able to contain role folder
			$child_objects = TUtil::getModules($obj[type]);
			
			if ($child_objects["rolf"])
			{
				$data["local_role"]["id"] = $this->id;
				$data["local_role"]["parent"] = $_GET["parent"];
			}
		}
		return $data;
	}
	
	/**
	* save permissions of object
	* @access public
	**/
	function permSaveObject($a_perm, $a_stop_inherit, $a_type, $a_template_perm, $a_recursive)
	{
		global $tree,$rbacsystem,$rbacreview,$rbacadmin;
		
		// TODO: get rid of $_GET variables

		if ($rbacsystem->checkAccess('edit permission',$this->id, $_GET["parent"]))
		{
			$rbacadmin->revokePermission($this->id, $_GET["parent"]);
			
			foreach ($a_perm as $key => $new_role_perms)
			{
				// $key enthaelt die aktuelle Role_Id
				$rbacadmin->grantPermission($key,$new_role_perms, $this->id, $_GET["parent"]);
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

		if ($a_stop_inherit)
		{
			foreach ($a_stop_inherit as $stop_inherit)
			{
				$rolf_data = $rbacadmin->getRoleFolderOfObject($this->id);
				if (!($rolf_id = $rolf_data["child"]))
				{
					// CHECK ACCESS 'create' rolefolder
					if ($rbacsystem->checkAccess('create', $this->id, $_GET["parent"],'rolf'))
					{
						$role_obj["title"] = "Local roles";
						$role_obj["desc"] = "Role Folder of object no. ".$this->id;
						$this->saveObject($this->id,$_GET["parent"],$a_type,'rolf',$role_obj);
					}
					else
					{
						$this->ilias->raiseError("No permission to create Role Folder",$this->ilias->error_obj->WARNING);
					}
				}
				// CHECK ACCESS 'write' of role folder
				$rolf_data = $rbacadmin->getRoleFolderOfObject($this->id);
				if ($rbacsystem->checkAccess('write',$rolf_data["child"],$this->id))
				{
					$parentRoles = $rbacadmin->getParentRoleIds();
					$rbacadmin->copyRolePermission($stop_inherit,$parentRoles[$stop_inherit]["parent"],
												   $rolf_data["child"],$stop_inherit);
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_data["child"],$this->id,'n');
				}
				else
				{
					$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
				}
			}// END FOREACH
		}// END STOP INHERIT
		return true;
	}
	
	/**
	* add a new local role
	* @access public
	**/
	function addRoleObject()
	{
		global $tree,$rbacadmin,$rbacreview,$rbacsystem;

		$object = getObject($_GET["obj_id"]);
		$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["obj_id"]);

		if (!($rolf_id = $rolf_data["child"]))
		{
			if (!in_array('rolf',TUtil::getModules($object[type])))
			{
				$this->ilias->raiseError("'".$object["title"]."' are not allowed to contain Role Folder",$this->ilias->error_obj->WARNING);
			}

			// CHECK ACCESS 'create' rolefolder
			if ($rbacsystem->checkAccess('create',$_GET["obj_id"],$_GET["parent"],'rolf'))
			{
				$rolf_id = createNewObject("rolf","Role Folder","Automatisch generierter Role Folder");
				$tree->insertNode($rolf_id,$_GET["obj_id"],$_GET["parent"]);
				// Suche aller Parent Rollen im Baum
				$parentRoles = $rbacadmin->getParentRoleIds();
				
				foreach ($parentRoles as $parRol)
				{
					// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
					$ops = $rbacreview->getOperations($parRol["obj_id"],'rolf',$parRol["parent"]);
					$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id,$_GET["obj_id"]);
				}
			}
			else
			{
				$this->ilias->raiseError("No permission to create role folder",$this->ilias->error_obj->WARNING);
			}
		}

		// CHECK ACCESS 'write' of role folder
		if ($rbacsystem->checkAccess('write',$rolf_id,$_GET["obj_id"]))
		{
			$new_obj_id = createNewObject("role",$_POST["Flocal_role"],"No description");
			$rbacadmin->assignRoleToFolder($new_obj_id,$rolf_id,$_GET["obj_id"],'y');
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
		
		return true;
	}
	
	/**
	* show owner of object
	* @access public
	**/
	function ownerObject()
	{
		global $tree, $lng;

		$owner = TUtil::getOwner($_GET["obj_id"]);

		if (is_object($owner))
			$data = $owner->buildFullName();
		else
			$data = $lng->txt("unknown");

		return $data;

	}

	/**
	* add a new permission to an object
	* @access	public
	* 
	**/
	function alterOperationsOnObject()
	{
		global $rbacadmin,$rbacreview;

		$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);

		foreach ($_POST["id"] as $ops_id => $status)
		{
			if ($status == 'enabled')
			{
				if (!in_array($ops_id,$ops_valid))
				{
					$rbacreview->assignPermissionToObject($_GET["obj_id"],$ops_id);
				}
			}

			if ($status == 'disabled')
			{
				if (in_array($ops_id,$ops_valid))
				{
					$rbacreview->deassignPermissionFromObject($_GET["obj_id"],$ops_id);
//					$this->ilias->raiseError("It's not possible to deassign operations",$this->ilias->error_obj->WARNING);
				}
			}
		}
		return true;
	}

	/**
	* This method is called automatically from class.Admin.php
	* It removes all object entries for a specific object
	* This method should be overwritten by all object types
	* @access public
	**/
	function deleteObject($a_obj_id, $a_parent_id, $a_tree_id = 1)
	{
		global $rbacadmin, $tree;
		
		// ALL OBJECT ENTRIES IN TREE HAVE BEEN DELETED FROM CLASS ADMIN.PHP

		// IF THERE IS NO REFERENCE, DELETE ENTRY IN OBJECT_DATA
		if(!$tree->countTreeEntriesOfObject($a_tree_id,$a_obj_id))
		{
			deleteObject($a_obj_id);
		}
		// DELETE PERMISSION ENTRIES IN RBAC_PA
		$rbacadmin->revokePermission($a_obj_id,$a_parent_id);

		return true;
	}


	function confirmDeleteAdmObject()
	{
		global $lng;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		$data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["id"] as $id)
		{
			$obj_data = getObject($id);
			$data["data"]["$id"] = array(
				"type"        => $obj_data["type"],
				"title"       => $obj_data["title"],
				"desc"        => $obj_data["desc"],
				"last_update" => $obj_data["last_update"]);
		}
		$data["buttons"] = array( "cancel"  => $lng->txt("cancel"),
								  "confirm"  => $lng->txt("confirm"));

		return $data;
	}
	function trashObject()
	{
		global $lng,$tree;


		$objects = $tree->getSavedNodeData($_GET["obj_id"]);
		if(count($objects))
		{
			$data["empty"] = false;
			$data["cols"] = array("","type", "title", "description", "last_change");
			
			foreach($objects as $obj_data)
			{
				$data["data"]["$obj_data[child]"] = array(
					"checkbox"    => "",
					"type"        => $obj_data["type"],
					"title"       => $obj_data["title"],
					"desc"        => $obj_data["desc"],
					"last_update" => $obj_data["last_update"]);
			}
			$data["buttons"] = array( "btn_undelete"  => $lng->txt("btn_undelete"),
									  "btn_remove_system"  => $lng->txt("btn_remove_system"));
			return $data;
		}
		else
		{
			$this->ilias->error_obj->sendInfo($lng->txt("msg_trash_empty"));
			$data["empty"] = true;
			return $data;
		}
	}		

	/**
	* returns the parent object id of $_GET["parent"]
	* @access	private
	* @param	integer		node_id where to start
	* @return	integer
	*/
	function getParentObjectId($a_start = 0)
	{
		global $tree;
		
		$a_start = $a_start ? $a_start : $_GET["parent"];
		
		$path_ids = $tree->getPathId($a_start,ROOT_FOLDER_ID);
		array_pop($path_ids);
		
		return array_pop($path_ids);
	} //function
	
	function getSubObjects()
	{
		global $rbacsystem;
		
		$data = array();
		
		// show only objects with permission 'create'
		$objects = TUtil::getModules($this->type);

		foreach ($objects as $key => $object)
		{
			if ($rbacsystem->checkAccess("create", $this->id, $obj->parent, $key))
			{
				$data[$key] = $object;
			} //if
		} //foreach
		return $data;
	}
} // class
?>
