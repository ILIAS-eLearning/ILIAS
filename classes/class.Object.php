<?php
/**
 * Class Object
 * Basic functions for all objects
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package ilias-core
 * 
 */
class Object
{
// PUBLIC METHODEN
/**
 * ilias object
 * @var object ilias
 * @access private
 **/
	var $ilias;
/**
 * object id of system folder
 * @var int
 * @access private
 **/
	var $SYSTEM_FOLDER_ID;
/**
 * object id of root folder
 * @var int
 * @access private
 **/
	var $ROOT_FOLDER_ID;

/**
 * Constructor
 * @param object ilias
 * @access public
 **/
	function Object(&$a_ilias)
	{

		$this->ilias = $a_ilias;
		$this->SYSTEM_FOLDER_ID = "9";
		$this->ROOT_FOLDER_ID = "1";
	}
/**
 * create object in admin interface
 * @access public
 *
 **/
	function createObject()
	{
		// Creates a child object
		global $tplContent;

		$rbacsystem = new RbacSystemH($this->ilias->db);
		if($rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$_POST["type"]))
		{
			$tplContent = new Template("object_form.html",true,true);
			$tplContent->setVariable($this->ilias->ini["layout"]);

			// Zur Ausgabe des 'Path' wird die Private-Methode createPath() aufgerufen 
			$tplContent->setVariable("TREEPATH",$this->getPath());
			$tplContent->setVariable("CMD","save");
			$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tplContent->setVariable("TPOS",$_GET["parent"]);
			$tplContent->setVariable("TYPE",$_POST["type"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to create object",$this->ilias->error_class->WARNING);
			exit();
		}
	}
/**
 * saves new object in admin interface
 * @access public
 *
 **/
	function saveObject()
	{
		$rbacsystem = new RbacSystemH($this->ilias->db);
		$tree = new Tree($_GET["obj_id"],$_GET["parent"]);
		if($rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$_POST["type"]))
		{
			$rbacreview = new RbacReviewH($this->ilias->db);
			$rbacadmin = new RbacAdminH($this->ilias->db); 
			$rbacsystem = new RbacSystem($this->ilias->db);
			// Erzeugen und Eintragen eines Objektes in Tree
			$new_obj_id = createNewObject($_POST["type"],$_POST["Fobject"]);
			$tree->insertNode($new_obj_id,$_GET["obj_id"]);

			// Suche aller Parent Rollen im Baum mit der Private-Methode getParentRoleIds()
			$parentRoles = $this->getParentRoleIds();
			foreach($parentRoles as $parRol)
			{
				// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
				$ops = $rbacreview->getOperations($parRol["obj_id"],$_POST["type"],$parRol["parent"]);
				$rbacadmin->grantPermission($parRol["obj_id"],$ops,$new_obj_id,$_GET["obj_id"]);
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to create object",$this->ilias->error_class->WARNING);
			exit();
		}
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
/**
 * edit object
 * @access public
 * 
 **/
	function editObject()
	{
		global $tplContent;

		$rbacsystem = new RbacSystemH($this->ilias->db);
		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			$tplContent = new Template("object_form.html",true,true);
			$tplContent->setVariable($this->ilias->ini["layout"]);
			$tplContent->setVariable("TREEPATH",$this->getPath());
			$tplContent->setVariable("CMD","update");
			$tplContent->setVariable("TPOS",$_GET["parent"]);

			$obj = getObject($_GET["obj_id"]);
			$tplContent->setVariable("TYPE",$obj["type"]);

			$tplContent->setVariable("OBJ_ID",$obj["obj_id"]);
			$tplContent->setVariable("OBJ_TITLE",$obj["title"]);
			$tplContent->setVariable("OBJ_DESC",$obj["desc"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_class->WARNING);
			exit();
		}
			
	}
/**
 * update an object
 * @access public
 **/
	function updateObject()
	{
		$rbacsystem = new RbacSystemH($this->ilias->db);

		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			updateObject($_GET["obj_id"],$_GET["type"],$_POST["Fobject"]);
			header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_class->WARNING);
			exit();
		}
	}
/**
 * show permissions of object
 * @access public
 **/
	function permObject()
	{
		global $tplContent;

		$obj = getObject($_GET["obj_id"]);

		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacreview = new RbacReviewH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);

		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			$tplContent = new Template("object_permission.html",true,true);
			$tplContent->setVariable($this->ilias->ini["layout"]);

			$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tplContent->setVariable("TPOS",$_GET["parent"]);
			$tplContent->setVariable("TREEPATH",$this->getPath());
			$tplContent->setVariable("MESSAGE_TOP","Permissions of: ".$obj["title"]);

			// Es werden nur die Rollen übergeordneter Ordner angezeigt, lokale Rollen anderer Zweige nicht
			$parentRoles = $this->getParentRoleIds();
		
			// BEGIN ROLENAMES
			$tplContent->setCurrentBlock("ROLENAMES");
			foreach($parentRoles as $r)
			{
				$tplContent->setVariable("ROLE_NAME",$r["title"]);
				$tplContent->parseCurrentBlock();
			}
			// BEGIN CHECK_INHERIT
			$tplContent->setCurrentBLock("CHECK_INHERIT");
			foreach($parentRoles as $r)
			{
				$box = TUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
				$tplContent->setVariable("CHECK_INHERITANCE",$box);
				$tplContent->parseCurrentBlock();
			}
			$ope_list = getOperationList($obj["type"]);

			// BEGIN TABLE_DATA_OUTER
			foreach($ope_list as $key => $operation)
			{
				// BEGIN CHECK_PERM
				$tplContent->setCurrentBlock("CHECK_PERM");
				foreach($parentRoles as $role)
				{
					$checked = $rbacsystem->checkPermission($_GET["obj_id"],$role["obj_id"],$operation["operation"]);
					// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
					$box = TUtil::formCheckBox($checked,"perm[".$role["obj_id"]."][]",$operation["ops_id"]);
					$tplContent->setVariable("CHECK_PERMISSION",$box);
					$tplContent->parseCurrentBlock();
				}
				// END CHECK_PERM
				$tplContent->setCurrentBlock("TABLE_DATA_OUTER");
				$css_row = $key % 2 ? "row_low" : "row_high";
				$tplContent->setVariable("CSS_ROW",$css_row);
				$tplContent->setVariable("PERMISSION",$operation["operation"]);
				$tplContent->parseCurrentBlock();
			}
			// END TABLE_DATA_OUTER
		}
		else
		{
			$this->ilias->raiseError("No permission to change permissions",$this->ilias->error_class->WARNING);
			exit();
		}
		// if exists rolefolder:
		//      => checkaccess('write') from rolefolder
		// else
		//      => checkaccess('create') from rolefolder
		$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["obj_id"]);
		$permission = $rolf_data ? 'write' : 'create';
		$rolf_id = $rolf_data["obj_id"] ? $rolf_data["obj_id"] : $_GET["obj_id"];
		$rolf_parent = $role_data["parent"] ? $rolf_data["parent"] : $_GET["parent"];
		if($rbacsystem->checkAccess('edit permission',$_GET["obj_id"],$_GET["parent"]) &&
		   $rbacsystem->checkAccess($permission,$rolf_id,$rolf_parent,'rolf'))
		{
			// ADD LOCAL ROLE
			$tplContent->setCurrentBlock("LOCAL_ROLE");
			$tplContent->setVariable("MESSAGE_BOTTOM","You can also add local roles");
			$tplContent->setVariable("LR_OBJ_ID",$_GET["obj_id"]);
			$tplContent->setVariable("LR_TPOS",$_GET["parent"]);
			$tplContent->parseCurrentBlock();
		}
	}
/**
 * save permissions of object
 * @access public
 **/
	function permSaveObject()
	{
		global $tplContent;

		$tree = new Tree($_GET["obj_id"],$_GET["parent"]);
		$rbacreview = new RbacReviewH($this->ilias->db);
		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);
		if($rbacsystem->checkAccess('edit permission',$_GET["obj_id"],$_GET["parent"]))
		{
			$rbacadmin->revokePermission($_GET["obj_id"]);
			foreach($_POST["perm"] as $key => $new_role_perms)
			{
				// $key enthaelt die aktuelle Role_Id
				$rbacadmin->grantPermission($key,$new_role_perms,$_GET["obj_id"],$_GET["parent"]);
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to change permission",$this->ilias->error_class->WARNING);
			exit();
		}
		// Wenn die Vererbung der Rollen Templates unterbrochen werden soll,
		// muss folgendes geschehen:
		// - existiert kein RoleFolder, wird er angelegt und die Rechte aus den Permission Templates ausgelesen
		// - existiert die Rolle im aktuellen RoleFolder werden die Permission Templates dieser Rolle angezeigt
		// - existiert die Rolle nicht im aktuellen RoleFolder wird sie dort angelegt
		//   und das Permission Template an den Wert des nächst höher gelegenen Permission Templates angepasst

		if($_POST["stop_inherit"])
		{
			foreach($_POST["stop_inherit"] as $stop_inherit)
			{
				$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["obj_id"]);
				if(!($rolf_id = $rolf_data["child"]))
				{
					// CHECK ACCESS 'create' rolefolder
					if($rbacsystem->checkAccess('create',$_GET["obj_id"],$_GET["parent"],'rolf'))
					{
						$role_obj["title"] = 'Role Folder';
						$role_obj["desc"] = 'Automatisch genierter Role Folder';
						$rolf_id = createNewObject("rolf",$role_obj);
						$tree->insertNode($rolf_id,$_GET["obj_id"]);

						// Suche aller Parent Rollen im Baum mit der Private-Methode getParentRoleIds()
						$parentRoles = $this->getParentRoleIds();
						foreach($parentRoles as $parRol)
						{
							// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
							$ops = $rbacreview->getOperations($parRol["obj_id"],"rolf",$parRol["parent"]);
							$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id,$_GET["obj_id"]);
						}
					}
					else
					{
						$this->ilias->raiseError("No permission to create Role Folder",$this->ilias->error_class->WARNING);
						exit();
					}
				}
				// CHECK ACCESS 'write' of role folder
				if($rbacsystem->checkAccess('write',$rolf_id,$_GET["obj_id"]))
				{
					// Suche die im Baum nächsten Templates der aktuellen Rolle
					$path = $tree->getPathId($_GET["parent"],1);
					$path[0] = $this->SYSTEM_FOLDER_ID;
					// Es muss unten im Baum gestartet werden
					array_reverse($path);
					$folders = $rbacadmin->getFoldersAssignedToRole($stop_inherit);
					foreach($path as $obj_id)
					{
						// IDs der zugehörigen RoleFolder
						$rolf_data = $rbacadmin->getRoleFolderOfObject($obj_id);
						if(in_array($rolf_data["child"],$folders))
						{
							// FOUND
							$rbacadmin->copyRolePermission($stop_inherit,$rolf_data["child"],$rolf_id);
							break;
						}
					}
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_id,'n');
				}
				else
				{
					$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_class->WARNING);
					exit();
				}
			}
		}
		header ("location: object.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]&cmd=perm");
	}
/**
 * add a new local role
 * @access public
 *
 **/
	function addRoleObject()
	{
		global $ilias;
		global $tree;

		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacreview = new RbacReviewH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);

		$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["obj_id"]);
		if(!($rolf_id = $rolf_data["child"]))
		{
			// CHECK ACCESS 'create' rolefolder
			if($rbacsystem->checkAccess('create',$_GET["obj_id"],$_GET["parent"],'rolf'))
			{
				$role_obj["title"] = 'Role Folder';
				$role_obj["desc"] = 'Automatisch generierter Role Folder';
				$rolf_id = createNewObject("rolf",$role_obj);
				$tree->insertNode($rolf_id,$_GET["obj_id"]);
				// Suche aller Parent Rollen im Baum
				$parentRoles = $this->getParentRoleIds();
				foreach($parentRoles as $parRol)
				{
					// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
					$ops = $rbacreview->getOperations($parRol["obj_id"],'rolf',$parRol["parent"]);
					$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id,$_GET["obj_id"]);
				}
			}
			else
			{
				$this->ilias->raiseError("No permission to create role folder",$this->ilias->error_class->WARNING);
				exit();
			}
		}
		// CHECK ACCESS 'write' of role folder
		if($rbacsystem->checkAccess('write',$rolf_id,$_GET["obj_id"]))
		{
			$role_data["title"] = $_POST["Flocal_role"];
			$role_data["desc"] = "";
			$new_obj_id = createNewObject('role',$role_data);
			$rbacadmin->assignRoleToFolder($new_obj_id,$rolf_id,'y');
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_class->WARNING);
			exit();
		}
		header("location:object.php?cmd=perm&obj_id=$new_obj_id&parent=$rolf_id");
	}
/**
 * show owner of object
 * @access public
 **/
	function ownerObject()
	{
		global $ilias;
		global $tplContent;
		global $tree;

		$tplContent = new Template("object_owner.html",true,true);
		$tplContent->setVariable($ilias->ini["layout"]);
	
		$tplContent->setVariable("TREEPATH",$this->getPath());
        $tplContent->setVariable("CMD","update");
		$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
        $tplContent->setVariable("TPOS",$_GET["parent"]);

		$owner = TUtil::getOwner($_GET["obj_id"]);
		
		if (is_object($owner))
		{
			$tplContent->setVariable("OWNER_NAME",$owner->buildFullName());
		}
		else
		{
			$tplContent->setVariable("OWNER_NAME","UNKNOWN");
		}
	}
/**
 * add a new permission to an object
 * @public
 * 
 **/
	function addPermissionObject()
	{
		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacreview = new RbacReviewH($this->ilias->db);

		$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);
		foreach($_POST["id"] as $ops_id => $status)
		{
			if($status == 'e')
			{
				if(!in_array($ops_id,$ops_valid))
				{
					$rbacreview->assignPermissionToObject($_GET["obj_id"],$ops_id);
				}
			}
			if($status == 'd')
			{
				if(in_array($ops_id,$ops_valid))
				{
					$this->ilias->raiseError("It's not possible to deassign operations",$this->ilias->error_class->WARNING);
				}
			}
		}
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
// PRIVATE METHODEN
/**
 * create path
 * @access private
 * @param int object id of an object
 * @return string
 **/
	function getPath($a_id = "")
	{		
		if(!$a_id)
		{
			$a_id = $_GET["obj_id"];
		}
		$tree = new Tree($a_id,1,1);
		return $tree->showPath($tree->getPathFull($a_id,$this->ROOT_FOLDER_ID),"content.php");
	}
/**
 * get role ids of all parent roles
 * @access private
 * @param int object id of start node
 * @return string 
 **/
	function getParentRoleIds($a_start_node = '')
	{
		$a_start_node = $a_start_node ? $a_start_node : $_GET["obj_id"];
		$tree = new Tree($_GET["parent"],$this->ROOT_FOLDER_ID);
		$rbacadmin = new RbacAdminH($this->ilias->db);

		$pathIds  = $tree->getPathId($a_start_node,1);
		$pathIds[0] = $this->SYSTEM_FOLDER_ID;
		return $rbacadmin->getParentRoles($pathIds);
	}
/**
 * returns the parent object id of $_GET["parent"]
 * @access private
 * @return int
 */
	function getParentObjectId()
	{
		$tree = new Tree($_GET["parent"],$this->ROOT_FOLDER_ID);
		$path_ids = $tree->getPathId($_GET["parent"],$this->ROOT_FOLDER_ID);
		array_pop($path_ids);
		return array_pop($path_ids);
	}
}
?>