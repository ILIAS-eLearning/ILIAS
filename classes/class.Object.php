<?php
/**
 * Class Object
 * Basic functions for all objects
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
 */
class Object
{
// PUBLIC METHODEN
/**
 * Constructor
 * @params object ilias
 *
 */
	function Object(&$a_ilias)
	{
		$this->ilias = $a_ilias;
		$this->SYSTEM_FOLDER_ID = "9";
		$this->ROOT_FOLDER_ID = "1";
	}
	function ownerObject()
	{
	}
	function createObject()
	{
		// Creates a child object
		global $tree;
		global $tplContent;

		$tplContent = new Template("object_form.html",true,true);
		$tplContent->setVariable($this->ilias->ini["layout"]);

		// Zur Ausgabe des 'Path' wird die Private-Methode createPath() aufgerufen 
		$tplContent->setVariable("TREEPATH",$this->getPath());
		$tplContent->setVariable("CMD","save");
		$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
		$tplContent->setVariable("TPOS",$_GET["parent"]);
		$tplContent->setVariable("TYPE",$_POST["type"]);
	}
	function saveObject()
	{
		global $tree;
		global $tplContent;

		$rbacreview = new RbacReviewH($this->ilias->db);
		$rbacadmin = new RbacAdminH($this->ilias->db); 
		$rbacsystem = new RbacSystem($this->ilias->db);
		if($rbacsystem->checkAccess('create',$_POST["type"]))
		{
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
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
	function editObject()
	{
		global  $tree;
		global  $tplContent;

		$tplContent = new Template("object_form.html",true,true);
		$tplContent->setVariable($this->ilias->ini["layout"]);

		// Show path
		$tplContent->setVariable("TREEPATH",$this->getPath());
        $tplContent->setVariable("CMD","update");
        $tplContent->setVariable("TPOS",$_GET["parent"]);

        $obj = getObject($_GET["obj_id"]);
		$tplContent->setVariable("TYPE",$obj["type"]);

        $tplContent->setVariable("OBJ_ID",$obj["obj_id"]);
        $tplContent->setVariable("OBJ_TITLE",$obj["title"]);
        $tplContent->setVariable("OBJ_DESC",$obj["desc"]);
	}
	function updateObject()
	{
        updateObject($_GET["obj_id"],$_GET["type"],$_POST["Fobject"]);
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
	function permObject()
	{
		global $tree;
		global $tplContent;

		$obj = getObject($_GET["obj_id"]);

		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacreview = new RbacReviewH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);
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

		// ADD LOCAL ROLE
		$tplContent->setVariable("MESSAGE_BOTTOM","You can also add local roles");
	}
	function permSaveObject()
	{
		global $tree;
		global $tplContent;

		$rbacreview = new RbacReviewH($this->ilias->db);
		$rbacadmin = new RbacAdminH($this->ilias->db);

		$rbacadmin->revokePermission($_GET["obj_id"]);
		foreach($_POST["perm"] as $key => $new_role_perms)
		{
			// $key enthaelt die aktuelle Role_Id
			$rbacadmin->grantPermission($key,$new_role_perms,$_GET["obj_id"],$_GET["parent"]);
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
					$role_obj["title"] = 'Role Folder';
					$role_obj["desc"] = 'Automatisch genierter Role Folder';
					$rolf_id = createNewObject("rolf",$role_obj);
					$tree->insertNode($rolf_id,$_GET["obj_id"]);
				}
				// Suche aller Parent Rollen im Baum mit der Private-Methode getParentRoleIds()
				// und Eintragen der Rechte am RoleFolder
				$parentRoles = $this->getParentRoleIds();
				foreach($parentRoles as $parRol)
				{
						$ops = $rbacreview->getOperations($parRol["obj_id"],'rolf',$parRol["parent"]);
						$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id,$_GET["obj_id"]);
				}
				// Suche die im Baum nächsten Templates der aktuellen Rolle
				$path = $tree->showPathId($_GET["parent"],1);
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
				$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_id);
			}
		}
		header ("location: object.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]&cmd=perm");
	}
	function addRoleObject()
	{
		global $ilias;
		global $tree;

		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacreview = new RbacReviewH($this->ilias->db);

		$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["obj_id"]);
		if(!($rolf_id = $rolf_data["child"]))
		{
			$role_obj["title"] = 'Role Folder';
			$role_obj["desc"] = 'Automatisch generierter Role Folder';
			$rolf_id = createNewObject("rolf",$role_obj);
			$tree->insertNode($rolf_id,$_GET["obj_id"]);
		}
		$role_data["title"] = "$Flocal_role";
		$role_data["desc"] = "";
		$new_obj_id = createNewObject('role',$role_data);
		$rbacadmin->assignRoleToFolder($new_obj_id,$rolf_id);

		// Suche aller Parent Rollen im Baum
		$parentRoles = $rthis->getParentRoleIds();
		foreach($parentRoles as $parRol)
		{
			// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
			$ops = $rbacreview->getOperations($parRol["obj_id"],'rolf',$parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id,$_GET["obj_id"]);
		}
		
		header("location:object.php?cmd=perm&obj_id=$rolf_id&parent=$_GET[obj_id]");
	}
	function ownerObject()
	{
		global $ilias;
		global $tplContent;
		global $tree;

		$tplContent = new Template("object_owner.html",true,true);
		$tplContent->setVariable($ilias->ini["layout"]);
	
		// Show path
		$tree = new Tree($_GET["obj_id"],1,1);
		$tree->getPath();
		$path = showPath($tree->Path,"content.php");
		$tplContent->setVariable("TREEPATH",$path);
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
// PRIVATE METHODEN
	function getPath($a_id = "")
	{
		// Erzeugt den Path der in jedem Template angezeigt wird
		global $tree;
		
		if(!$a_id)
		{
			$a_id = $_GET["obj_id"];
		}
		$tree = new Tree($a_id,1,1);
		$tree->getPath();
		return showPath($tree->Path,"content.php");
	}
	function getParentRoleIds()
	{
		global $tree;

		$rbacadmin = new RbacAdminH($this->ilias->db);

		$pathIds  = $tree->showPathId($_GET["obj_id"],1);
		$pathIds[0] = $this->SYSTEM_FOLDER_ID;
		return $rbacadmin->getParentRoles($pathIds);
	}
}
?>