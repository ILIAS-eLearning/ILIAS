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
	* Constructor
	* @access	public
	*/
	function Object()
	{
		global $ilias;
		
		$this->ilias =& $ilias;

		$this->id = $_GET["obj_id"];
		$this->parent = $_GET["parent"];
		$this->parent_parent = $_GET["parent_parent"];

		$sql = "SELECT * FROM object_data
				WHERE obj_id='".$this->id."'";
		$res = $this->ilias->db->query($sql);
		$data = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$this->type = $data["type"];
		$this->title = $data["title"];
		$this->desc = $data["description"];
		$this->owner = $data["owner"];
		$this->create_date = $data["create_date"];
		$this->last_update = $data["last_update"];
	}

	/**
	* create object in admin interface
	* @access	public
	*/
	function createObject()
	{
		// creates a child object
		global $rbacsystem;

		if ($rbacsystem->checkAccess("create", $_GET["obj_id"], $_GET["parent"], $_GET["type"]))
		{
			$data = array();
									
			return $data;

		}
		else
		{
			$this->ilias->raiseError("No permission to create object",$this->ilias->error_obj->WARNING);
		}
	}
	
	/**
	* saves new object in admin interface
	* @access	public
	**/
	function saveObject()
	{
		global $rbacsystem,$rbacreview,$rbacadmin,$tree;
		
		if($rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$_GET["type"]))
		{
			// create and insert object in objecttree
			$new_obj_id = createNewObject($_GET["type"], $_POST["Fobject"]);
			$tree->insertNode($new_obj_id,$_GET["obj_id"]);

			$parentRoles = $rbacadmin->getParentRoleIds();
			
			foreach($parentRoles as $parRol)
			{
				// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
				$ops = $rbacreview->getOperations($parRol["obj_id"], $_GET["type"], $parRol["parent"]);
				$rbacadmin->grantPermission($parRol["obj_id"],$ops, $new_obj_id, $_GET["obj_id"]);
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to create object", $this->ilias->error_obj->WARNING);
		}
		return true;
	}

	/**
	* edit object
	* @access	public
	**/
	function editObject()
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
	

	function viewObject()
	{
		global $rbacsystem, $tree, $tpl;
		
		//prepare objectlist
		$this->objectList = array();
		$this->objectList["data"] = array();
		$this->objectList["ctrl"] = array();

		$this->objectList["cols"] = array("", "type", "title", "description", "last_change");
		
		if ($tree->getChilds($this->id, $_GET["order"], $_GET["direction"]))
		{
			foreach ($tree->Childs as $key => $val)
		    {
				// visible
				if (!$rbacsystem->checkAccess("visible",$val["id"],$val["parent"]))
				{
					continue;
				}
		
				//visible data part
				$this->objectList["data"][] = array(
					"type" => "<img src=\"".$tpl->tplPath."/images/"."icon_".$val["type"].".gif\" border=\"0\">",
					"title" => $val["title"],
					"description" => $val["desc"],
					"last_change" => $val["last_update"]
				);

				//control information
				$this->objectList["ctrl"][] = array(
					"type" => $val["type"],
					"obj_id" => $val["id"],
					"parent" => $val["parent"],
					"parent_parent" => $val["parent_parent"],
				);
				
		    } //foreach
		} //if 

		return $this->objectList;		
	}

	/**
	* update an object
	* @access	public
	**/
	function updateObject()
	{
		global $rbacsystem;

		if($rbacsystem->checkAccess("write", $this->id, $this->parent))
		{
			updateObject($this->id, $this->type, $_POST["Fobject"]);
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

		$obj = getObject($_GET["obj_id"]);

		if ($rbacsystem->checkAccess("write", $_GET["obj_id"], $_GET["parent"]))
		{

			// Es werden nur die Rollen übergeordneter Ordner angezeigt, lokale Rollen anderer Zweige nicht
			$parentRoles = $rbacadmin->getParentRoleIds();

			$data = array();

			foreach ($parentRoles as $r)
			{
				$data["rolenames"][] = $r["title"];
			}
			

			foreach ($parentRoles as $r)
			{
				$box = TUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
				$data["check_inherit"][] = $box;
			}
			
			$ope_list = getOperationList($obj["type"]);

			// BEGIN TABLE_DATA_OUTER
			foreach ($ope_list as $key => $operation)
			{
				$opdata = array();
				$opdata["name"] = $operation["operation"];
				
				foreach ($parentRoles as $role)
				{
					$checked = $rbacsystem->checkPermission($_GET["obj_id"],$role["obj_id"],$operation["operation"]);
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
		
		$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["obj_id"]);
		$permission = $rolf_data ? 'write' : 'create';
		$rolf_id = $rolf_data["obj_id"] ? $rolf_data["obj_id"] : $_GET["obj_id"];
		$rolf_parent = $role_data["parent"] ? $rolf_data["parent"] : $_GET["parent"];
		
		if ($rbacsystem->checkAccess("edit permission", $_GET["obj_id"], $_GET["parent"]) &&
		   $rbacsystem->checkAccess($permission, $rolf_id, $rolf_parent, "rolf"))
		{
			// Check if object is able to contain role folder
			$child_objects = TUtil::getModules($ilias->typedefinition[$obj[type]]);
			
			if ($child_objects["rolf"])
			{
				$data["local_role"]["id"] = $_GET["obj_id"];
				$data["local_role"]["parent"] = $_GET["parent"];
			}
		}
		return $data;
	}
	
	/**
	* save permissions of object
	* @access public
	**/
	function permSaveObject()
	{
		global $tree,$rbacsystem,$rbacreview,$rbacadmin;

		if ($rbacsystem->checkAccess('edit permission',$_GET["obj_id"],$_GET["parent"]))
		{
			$rbacadmin->revokePermission($_GET["obj_id"]);
			
			foreach ($_POST["perm"] as $key => $new_role_perms)
			{
				// $key enthaelt die aktuelle Role_Id
				$rbacadmin->grantPermission($key,$new_role_perms,$_GET["obj_id"],$_GET["parent"]);
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
				$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["obj_id"]);
				if (!($rolf_id = $rolf_data["child"]))
				{
					// CHECK ACCESS 'create' rolefolder
					if ($rbacsystem->checkAccess('create',$_GET["obj_id"],$_GET["parent"],'rolf'))
					{
						$role_obj["title"] = "Local roles";
						$role_obj["desc"] = "Role Folder of object no. ".$_GET["obj_id"];
						$rolf_id = createNewObject("rolf",$role_obj);
						$tree->insertNode($rolf_id,$_GET["obj_id"]);

						// Suche aller Parent Rollen im Baum mit der Private-Methode getParentRoleIds()
						$parentRoles = $rbacadmin->getParentRoleIds();
						foreach ($parentRoles as $parRol)
						{
							// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
							$ops = $rbacreview->getOperations($parRol["obj_id"],"rolf",$parRol["parent"]);
							$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id,$_GET["obj_id"]);
						}
					}
					else
					{
						$this->ilias->raiseError("No permission to create Role Folder",$this->ilias->error_obj->WARNING);
					}
				}
				// CHECK ACCESS 'write' of role folder
				if ($rbacsystem->checkAccess('write',$rolf_id,$_GET["obj_id"]))
				{
					// Suche die im Baum nächsten Templates der aktuellen Rolle
					$path = $tree->getPathId($_GET["obj_id"],$_GET["parent"]);
					$path[0] = SYSTEM_FOLDER_ID;
					// Es muss unten im Baum gestartet werden
					array_reverse($path);
					$folders = $rbacadmin->getFoldersAssignedToRole($stop_inherit);
					foreach ($path as $obj_id)
					{
						// IDs der zugehörigen RoleFolder
						$rolf_data = $rbacadmin->getRoleFolderOfObject($obj_id);
						if (in_array(array("parent" => $rolf_data["child"]),$folders) &&
							in_array(array("parent" => $rolf_data["parent"]),$folders))
						{
							fd("hallo");
							// FOUND
							$rbacadmin->copyRolePermission($stop_inherit,$rolf_data["child"],$rolf_id);
							break;
						}
					}
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_id,$_GET["obj_id"],'n');
				}
				else
				{
					$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
				}
			}
		}
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
			if (!in_array('rolf',TUtil::getModules($ilias->typedefinition["$object[type]"])))
			{
				$this->ilias->raiseError("'".$object["title"]."' are not allowed to contain Role Folder",$this->ilias->error_obj->WARNING);
			}

			// CHECK ACCESS 'create' rolefolder
			if ($rbacsystem->checkAccess('create',$_GET["obj_id"],$_GET["parent"],'rolf'))
			{
				$role_obj["title"] = 'Role Folder';
				$role_obj["desc"] = 'Automatisch generierter Role Folder';
				$rolf_id = createNewObject("rolf",$role_obj);
				$tree->insertNode($rolf_id,$_GET["obj_id"]);
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
			$role_data["title"] = $_POST["Flocal_role"];
			$role_data["desc"] = "";
			$new_obj_id = createNewObject('role',$role_data);
			$rbacadmin->assignRoleToFolder($new_obj_id,$rolf_id,$_GET["obj_id"],'y');
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
		
		header("location:object.php?cmd=perm&obj_id=".$new_obj_id."&parent=".$rolf_id);
		exit;
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
	function addPermissionObject()
	{
		global $rbacadmin,$rbacreview;

		$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);

		foreach ($_POST["id"] as $ops_id => $status)
		{
			if ($status == 'e')
			{
				if (!in_array($ops_id,$ops_valid))
				{
					$rbacreview->assignPermissionToObject($_GET["obj_id"],$ops_id);
				}
			}

			if ($status == 'd')
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
	* create path
	* TODO: ist die Function nicht grosser quatsch?
	* @access	private
	* @param	integer	node_id
	* @param	integer	node_id of parent_node
	* @return	string
	* @deprecated
	*/
	function getPath($a_id = 0, $a_id_parent = 0)
	{		
		global $tree;

		if (!$a_id)
		{
			$a_id = $_GET["obj_id"];
		}

		if (!$a_id_parent)
		{
			$a_id_parent = $_GET["parent"];
		}

		$path = $tree->getPathFull($a_id,$a_id_parent);

		$path[] = array(
			"id"	 => $_GET["obj_id"],
			"title"  => "Titel",
			"parent" => $_GET["parent"],
			"parent_parent" => $_GET["parent_parent"]
		);
		
		return $tree->showPath($path,"content.php");
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
		
		if (!empty($this->ilias->typedefinition[$this->type]))
		{
			// show only objects with permission 'create'
			$objects = TUtil::getModules($this->ilias->typedefinition[$this->type]);

			foreach ($objects as $key => $object)
			{
				if ($rbacsystem->checkAccess("create", $this->id, $obj->parent, $key))
				{
					$data[$key] = $object;
				} //if
			} //foreach
			return $data;
		} //if
		return false;	
	}
	
} // class
?>