<?php
// Basis Klasse aller Objekte
// Enthält die grundlegenden Methoden um Objekte zu erzeugen, editieren usw.

class Object
{
	var $ilias;

// PUBLIC METHODEN
	function Object(&$a_ilias)
	{
		$this->ilias = $a_ilias;
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
		global $ilias;
		global $tree;
		global $tplContent;


		$rbacadmin = new RbacAdminH($ilias->db);
		$rbacreview = new RbacReviewH($ilias->db);
		$tplContent = new Template("object_permissions.html",true,true);
		
		$obj = getObject($_GET["obj_id"]);

		// Liefert alle möglichen Operationen eines Objekttyps
		$ope_list = getOperationList($obj["type"]);
		// Es werden nur noch die Rollen übergeordneter Ordner angezeigt, lokale Rollen anderer Zweige
		// nicht mehr
		$pathIds  = $tree->showPathId($_GET["obj_id"],1);
		$roles = $rbacadmin->getParentRoles($pathIds);

		// BEGIN TABLE_HEADER
		// Anzeige der Operationen
		$tplContent->setCurrentBlock("TABLE_HEADER");

		$tplContent->setVariable("HEADER","");
		$tplContent->parseCurrentBlock();
		foreach($ope_list as $o)
		{
			$tplContent->setVariable("HEADER",$o["operation"]);
			$tplContent->parseCurrentBlock();
		}
		// END TABLE_HEADER
		// BEGIN TABLE_OUTER
		foreach($roles as $r)
		{
			// BEGIN TABLE_DATA
			$tplContent->setCurrentBlock("TABLE_DATA");
			foreach($ope_list as $o)
			{
				// Prüft, ob Checkbox gesetzt
				$ops_id = $rbacreview->roleOperationsOnObject($r["obj_id"],$_GET["obj_id"]);
				$checked = 0;
				foreach($ops_id as $ops)
				{
					if($ops == $o["ops_id"])
						$checked = 1;
				}
				if($_GET["show"] == $r["obj_id"])
				{
					$box = TUtil::formCheckBox($checked,$o["operation"],1);
					$tplContent->setVariable("CHECKBOX",$box);
				}
				else
				{
					$tplContent->setVariable("CHECKBOX","");
				}
				$tplContent->parseCurrentBlock();
			} // END TABLE_DATA
			// BEGIN TABLE_INNER
			$tplContent->setCurrentBlock("TABLE_INNER");
			$box = TUtil::formCheckBox(0,"stop_vererbung[]",$r["obj_id"]);
			$tplContent->setVariable("ERBT",$box);
			$tplContent->setVariable("OBJECT",$_GET["obj_id"]);
			$tplContent->setVariable("SHOW",$r["obj_id"]);
			$tplContent->setVariable("ROLE",$r["title"]);
			$tplContent->setVariable("PARENT",$_GET["parent"]);
			$tplContent->parseCurrentBlock();


			// END TABLE_INNER
			$tplContent->setCurrentBlock("TABLE_OUTER");
			$tplContent->parseCurrentBlock();
		}
		// END TABLE_OUTER

		// Show path
		$tree = new Tree($_GET["obj_id"],1,1);
		$tree->getPath();
		$path = showPath($tree->Path,"content.php");
		$tplContent->setVariable("TREEPATH",$path);
		$tplContent->setVariable($ilias->ini["layout"]);
		$tplContent->setVariable("TPOS",$_GET["parent"]);	
		$tplContent->setVariable("ROL_ID",$_GET["show"]);
		$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
		$tplContent->setVariable("COL_ANZ",2*count($roles));

		$tplContent->setCurrentBlock("LOCAL_ROLE");
		$tplContent->setVariable("OBJECT_ID",$_GET["obj_id"]);
		$tplContent->setVariable("PARENT_ID",$_GET["parent"]);

		$tplContent->parseCurrentBlock();
	}
	function perms_saveObject()
	{
		global $ilias;
		global $tree;
		global $tplContent; 

		$obj = getObject($_GET["obj_id"]);
		$rbacadmin = new RbacAdminH($ilias->db);
		$rbacreview = new RbacReviewH($ilias->db);
		$rbacadmin->revokePermission($_GET["obj_id"],$_GET["show"],$_GET["parent"]);
		$ope_list = getOperationList($obj["type"]);
		foreach($ope_list as $o)
		{
			if($_POST[$o["operation"]] == 1)
			{
				$new_ops[] = $o["ops_id"];
			}
		}
		$rbacadmin->grantPermission($_GET["show"],$new_ops,$_GET["obj_id"],$_GET["parent"]);
		// Wenn die Vererbung der Rollen Templates unterbrochen werden soll,
		// muss folgendes geschehen:
		// - existiert kein RoleFolder, wird er angelegt und die Rechte aus den Permission Templates ausgelesen
		// - existiert die Rolle im aktuellen RoleFolder werden die Permission Templates dieser Rolle angezeigt
		// - existiert die Rolle nicht im aktuellen RoleFolder wird sie dort angelegt
		//   und das Permission Template an den Wert des nächst höher gelegenen Permission Templates angepasst
		//   
		if($_POST["stop_vererbung"])
		{
			foreach($_POST["stop_vererbung"] as $stop)
			{
				$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["obj_id"]);
				if(!($rolf_id = $rolf_data["child"]))
				{
					$role_obj["title"] = 'Role Folder';
					$role_obj["desc"] = 'Automatisch genierter Role Folder';
					$rolf_id = createNewObject("rolf",$role_obj);
					$tree->insertNode($rolf_id,$_GET["obj_id"]);

					// Suche aller Parent Rollen im Baum
					$pathIds  = $tree->showPathId($_GET["obj_id"],1);
					$parentRoles = $rbacadmin->getParentRoles($pathIds);
					foreach($parentRoles as $parRol)
					{
						// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
						$ops = $rbacreview->getOperations($parRol["obj_id"],'rolf',$parRol["parent"]);
						$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id,$_GET["obj_id"]);
					}
				}
				// FINDE DAS IM BAUM NÄCHSTE TEMPLATE DER AKTUELLEN ROLLE
				// Anzeigen des Path
				$path = array_reverse($tree->showPathId($_GET["parent"],1));
				// Alle RoleFolder der aktuellen RoleId
				$folders = $rbacadmin->getFoldersAssignedToRole($stop);
				foreach($path as $p)
				{
					// IDs der zugehörigen RoleFolder
					$rolf_data = $rbacadmin->getRoleFolderOfObject($p);
					if(in_array($rolf_data["child"],$folders))
					{
						// FOUND
						$rbacadmin->copyRolePermission($stop,$rolf_data["child"],$rolf_id);
						break;
					}
				}
				$rbacadmin->assignRoleToFolder($stop,$rolf_id);
			}
			header("location:object.php?cmd=perm&obj_id=$stop&parent=$rolf_id");
		}
		else
		{
			header("location:object.php?cmd=perm&obj_id=$_GET[obj_id]&show=$_GET[show]&parent=$_GET[parent]");
		}
	}
	function add_roleObject()
	{
		global $ilias;
		global $tree;

		$rbacadmin = new RbacAdminH($ilias->db);
		$rbacreview = new RbacReviewH($ilias->db);

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
		$pathIds  = $tree->showPathId($_GET["obj_id"],1);
		$parentRoles = $rbacadmin->getParentRoles($pathIds);
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
	function getPath()
	{
		// Erzeugt den Path der in jedem Template angezeigt wird
		global $tree;

		$tree = new Tree($_GET["obj_id"],1,1);
		$tree->getPath();
		return showPath($tree->Path,"content.php");
	}
	function getParentRoleIds()
	{
		global $tree;

		$rbacadmin = new RbacAdminH($this->ilias->db);

		$pathIds  = $tree->showPathId($_GET["obj_id"],1);
		return $rbacadmin->getParentRoles($pathIds);
	}
}
?>