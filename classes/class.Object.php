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
	}
	
	/**
	* create object in admin interface
	* @access	public
	*/
	function createObject()
	{
		// Creates a child object
		global $tpl, $rbacsystem;

		if ($rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$_POST["type"]))
		{

			$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
			$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_form.html");

			//show tabs
			$o = array();
			$o["LINK1"] = "content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
			$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
			$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
			$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
			$tpl->setVariable("TABS", TUtil::showTabs(2,$o));			
			
			$tpl->setCurrentBlock("adm_content");
			$tpl->setVariable("TREEPATH",$this->getPath());
			$tpl->setVariable("CMD", "save");
			$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tpl->setVariable("TPOS",$_GET["parent"]);
			$tpl->setVariable("TYPE",$_POST["type"]);
			$tpl->parseCurrentBlock();
			
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
		
		if($rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$_POST["type"]))
		{
			// Erzeugen und Eintragen eines Objektes in Tree
			$new_obj_id = createNewObject($_POST["type"],$_POST["Fobject"]);
			$tree->insertNode($new_obj_id,$_GET["obj_id"]);

			$parentRoles = $rbacadmin->getParentRoleIds();
			
			foreach($parentRoles as $parRol)
			{
				// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
				$ops = $rbacreview->getOperations($parRol["obj_id"],$_POST["type"],$parRol["parent"]);
				$rbacadmin->grantPermission($parRol["obj_id"],$ops,$new_obj_id,$_GET["obj_id"]);
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to create object",$this->ilias->error_obj->WARNING);
		}
		
		header("Location: content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
		exit;
	}

	/**
	* edit object
	* @access	public
	**/
	function editObject()
	{
		global $tpl,$rbacsystem, $lng;

		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
			$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_form.html");
			$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");

			//show tabs
			$o = array();
			$o["LINK1"] = "content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
			$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
			$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
			$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
			$tpl->setVariable("TABS", TUtil::showTabs(2,$o));			

			//show locator
			$tpl->setCurrentBlock("locator");
			$tpl->setVariable("TXT_PATH", $lng->txt("path"));
			$tpl->setVariable("TREEPATH",$this->getPath());
			$tpl->parseCurrentBlock();
			
			$obj = getObject($_GET["obj_id"]);

			$tpl->setCurrentBlock("adm_content");
			$tpl->setVariable($this->ilias->ini["layout"]);
			$tpl->setVariable("TARGET","object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
			$tpl->setVariable("CMD","update");
			$tpl->setVariable("TPOS",$_GET["parent"]);
			$tpl->setVariable("TYPE",$obj["type"]);
			$tpl->setVariable("OBJ_ID",$obj["obj_id"]);
			$tpl->setVariable("OBJ_TITLE",$obj["title"]);
			$tpl->setVariable("OBJ_DESC",$obj["desc"]);
			$tpl->parseCurrentBlock();
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
	function updateObject()
	{
		global $rbacsystem;

		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			updateObject($_GET["obj_id"],$_GET["type"],$_POST["Fobject"]);
			
			header("Location: content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
			exit;
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
		global $tpl, $lng, $rbacsystem, $rbacreview, $rbacadmin;
		static $num = 0;

		$obj = getObject($_GET["obj_id"]);

		if ($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
			$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_perm.html");
			$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");	

			//show locator
			$tpl->setCurrentBlock("locator");
			$tpl->setVariable("TXT_PATH", $lng->txt("path"));
			$tpl->setVariable("TREEPATH",$this->getPath());
			$tpl->parseCurrentBlock();

			//show tabs
			$o = array();
			$o["LINK1"] = "content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
			$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
			$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
			$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
			$tpl->setVariable("TABS", TUtil::showTabs(3,$o));	
						
			$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tpl->setVariable("TPOS",$_GET["parent"]);

			// Es werden nur die Rollen übergeordneter Ordner angezeigt, lokale Rollen anderer Zweige nicht
			$parentRoles = $rbacadmin->getParentRoleIds();
		
			// BEGIN ROLENAMES
			$tpl->setCurrentBlock("ROLENAMES");

			foreach ($parentRoles as $r)
			{
				$tpl->setVariable("ROLE_NAME",$r["title"]);
				$tpl->parseCurrentBlock();
			}
			
			// BEGIN CHECK_INHERIT
			$tpl->setCurrentBLock("CHECK_INHERIT");

			foreach ($parentRoles as $r)
			{
				$box = TUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
				$tpl->setVariable("CHECK_INHERITANCE",$box);
				$tpl->parseCurrentBlock();
			}
			
			$ope_list = getOperationList($obj["type"]);

			// BEGIN TABLE_DATA_OUTER
			foreach ($ope_list as $key => $operation)
			{
				$num++;
				
				// BEGIN CHECK_PERM
				$tpl->setCurrentBlock("CHECK_PERM");
				
				foreach ($parentRoles as $role)
				{
					$checked = $rbacsystem->checkPermission($_GET["obj_id"],$role["obj_id"],$operation["operation"]);
					// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
					$box = TUtil::formCheckBox($checked,"perm[".$role["obj_id"]."][]",$operation["ops_id"]);
					$tpl->setVariable("CHECK_PERMISSION",$box);
					$tpl->parseCurrentBlock();
				}
				
				// END CHECK_PERM
				$tpl->setCurrentBlock("TABLE_DATA_OUTER");
				$css_row = TUtil::switchColor($num, "tblrow1", "tblrow2");
				$tpl->setVariable("CSS_ROW",$css_row);
				$tpl->setVariable("PERMISSION",$operation["operation"]);
				$tpl->parseCurrentBlock();
			}
			// END TABLE_DATA_OUTER
		}
		else
		{
			$this->ilias->raiseError("No permission to change permissions",$this->ilias->error_obj->WARNING);
		}
		// if exists rolefolder:
		// 		=> checkaccess('write') from rolefolder
		// else
		//      => checkaccess('create') from rolefolder
		
		$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["obj_id"]);
		$permission = $rolf_data ? 'write' : 'create';
		$rolf_id = $rolf_data["obj_id"] ? $rolf_data["obj_id"] : $_GET["obj_id"];
		$rolf_parent = $role_data["parent"] ? $rolf_data["parent"] : $_GET["parent"];
		
		if ($rbacsystem->checkAccess('edit permission',$_GET["obj_id"],$_GET["parent"]) &&
		   $rbacsystem->checkAccess($permission,$rolf_id,$rolf_parent,'rolf'))
		{
			// Check if object is able to contain role folder
			$child_objects = TUtil::getModules($ilias->typedefinition["$obj[type]"]);
			
			if ($child_objects["rolf"])
			{
				// ADD LOCAL ROLE
				$tpl->setCurrentBlock("LOCAL_ROLE");
				$tpl->setVariable("MESSAGE_BOTTOM","You can also add local roles");
				$tpl->setVariable("LR_OBJ_ID",$_GET["obj_id"]);
				$tpl->setVariable("LR_TPOS",$_GET["parent"]);
				$tpl->parseCurrentBlock();
			}
		}
	}
	
	/**
	* save permissions of object
	* @access public
	**/
	function permSaveObject()
	{
		global $tplContent,$tree,$rbacsystem,$rbacreview,$rbacadmin;

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
		
		header ("location: object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm");
		exit;
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
		global $tpl, $tree, $lng;

		$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_owner.html");
		$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");	

		//show tabs
		$o = array();
		$o["LINK1"] = "content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
		$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
		$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
		$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
		$tpl->setVariable("TABS", TUtil::showTabs(4,$o));

		//show locator
		$tpl->setCurrentBlock("locator");
		$tpl->setVariable("TXT_PATH", $lng->txt("path"));
		$tpl->setVariable("TREEPATH",$this->getPath());
		$tpl->parseCurrentBlock();
					
		$owner = TUtil::getOwner($_GET["obj_id"]);
		
		$tpl->setCurrentBlock("adm_content");
		if (is_object($owner))
			$tpl->setVariable("OWNER_NAME",$owner->buildFullName());
		else
			$tpl->setVariable("OWNER_NAME",$lng->txt("unknown"));
		$tpl->setVariable("CMD","update");
		$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
		$tpl->setVariable("TPOS",$_GET["parent"]);
		$tpl->parseCurrentBlock();
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
		
		header("Location: content.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);
		exit;
	}

	/**
	* create path
	* TODO: ist die Function nicht grosser quatsch?
	* @access	private
	* @param	integer	node_id
	* @param	integer	node_id of parent_node
	* @return	string
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
	}
} // END class.Object
?>