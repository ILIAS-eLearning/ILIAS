<?php
/**
* Class UserObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class UserObject extends Object
{
	/**
	* array of gender abbreviations
	* @var array
	* @access public
	*/
	var $gender;

	/**
	* Contructor
	* @access	public
	*/
	function UserObject()
	{
		global $lng;

		$this->Object();

		$this->gender = array(
							  'm'    => $lng->txt("salutation_m"),
							  'f'    => $lng->txt("salutation_f")
							  );
	}

	/**
	* create user
	* @access	public
	*/
	function createObject()
	{
		global $tree,$tplContent,$rbacsystem;

		$obj = getObject($_GET["obj_id"]);

		if ($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
			$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_usr_form.html");
			$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");

			$tpl->setVariable($this->ilias->ini["layout"]); 

			$tpl->setVariable("STATUS","Add User");
			$tpl->setVariable("CMD","save");
			$tpl->setVariable("TYPE","user");
			$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tpl->setVariable("TPOS",$_GET["parent"]);

			//show tabs
			$o = array();
			$o["LINK1"] = "object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=edit";
			$o["LINK2"] = "./object.php?obj_id=".$_GET["parent"]."&parent=".$_GET["parent_parent"]."&cmd=edit";
			$o["LINK3"] = "./object.php?obj_id=".$_GET["parent"]."&parent=".$_GET["parent_parent"]."&cmd=perm";
			$o["LINK4"] = "./object.php?obj_id=".$_GET["parent"]."&parent=".$_GET["parent_parent"]."&cmd=owner";
			$tpl->setVariable("TABS", TUtil::showTabs(2, $o));
			
			// display path
			$tpl->setCurrentBlock("locator");
			$tpl->setVariable("TREEPATH",$this->getPath());
			$tpl->setVariable("TXT_PATH", $lng->txt("path"));
			$tpl->parseCurrentBlock();
			
			// gender selection
			$tpl->setCurrentBlock("gender");
			$opts = TUtil::formSelect($Fuserdata["Gender"],"Fuserdata[Gender]",$this->gender);
			$tpl->setVariable("GENDER",$opts);
			$tpl->parseCurrentBlock();

			// role selection
			$tpl->setCurrentBlock("role");
			$role = TUtil::getRoles();
			$opts = TUtil::formSelect($Fuserdata["Role"],"Fuserdata[Role]",$role);
			$tpl->setVariable("ROLE",$opts);
			$tpl->parseCurrentBlock();

			$tpl->setVariable("USR_ID",$_GET["obj_id"]);
			$tpl->setVariable("USR_LOGIN",$Fuserdata["Login"]);
			$tpl->setVariable("USR_PASSWD",$Fuserdata["Passwd"]);
			$tpl->setVariable("USR_TITLE",$Fuserdata["Title"]);
			$tpl->setVariable("USR_FIRSTNAME",$Fuserdata["FirstName"]);
			$tpl->setVariable("USR_SURNAME",$Fuserdata["SurName"]);
			$tpl->setVariable("USR_EMAIL",$Fuserdata["Email"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to write to user folder",$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* save user data
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem,$rbacadmin;
		
		$Fuserdata = $_POST["Fuserdata"];

		if ($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			// create object
			$Fobject["title"] = User::buildFullName($Fuserdata["Title"],$Fuserdata["FirstName"],$Fuserdata["SurName"]);
			$Fobject["desc"] = $Fuserdata["Email"];
			$Fuserdata["Id"] = createNewObject("user",$Fobject);

			// insert user data
			$rbacadmin->addUser($Fuserdata);
			$rbacadmin->assignUser($Fuserdata["Role"],$Fuserdata["Id"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to write to user folder",$this->ilias->error_obj->WARNING);
		}
		
		header("Location: content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
		exit;
	}
	
	/**
	* delete user
	* @access	public
	*/
	function deleteObject()
	{
		global $rbacadmin,$rbacsystem;
		
		// CHECK ACCESS
		if ($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			$rbacadmin->deleteUser($_POST["id"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to delete user",$this->ilias->error_obj->WARNING);
		}
		
		header("Location: content_user.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
		exit;
	}
	
	/**
	* edit user data
	* @access	public
	*/
	function editObject()
	{
		global $tpl, $rbacsystem, $rbacreview, $lng;

		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]) || $_GET["obj_id"] == $_SESSION["AccountId"])
		{
			// Userobjekt erzeugen
			$user = new User($_GET["obj_id"]);

			$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
			$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_usr_form.html");
			$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");

			//show tabs
			$o = array();
			$o["LINK1"] = "object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=edit";
			$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=edit";
			$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=perm";
			$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=owner";
			$tpl->setVariable("TABS", TUtil::showTabs(1,$o));

			// display path
			$tpl->setCurrentBlock("locator");
			$tpl->setVariable("TREEPATH", $this->getPath($_GET["parent"],$_GET["parent_parent"]));
			$tpl->setVariable("TXT_PATH", $lng->txt("path"));
			$tpl->parseCurrentBlock();			
			
			$tpl->setVariable("FORMACTION", "object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&Fuserdata[Id]=".$_GET["obj_id"]."&type=user&cmd=update");
			// gender selection
			$tpl->setCurrentBlock("gender");
			$opts = TUtil::formSelect($Fuserdata["Gender"],"Fuserdata[Gender]",$this->gender);
			$tpl->setVariable("GENDER",$opts);
			$tpl->parseCurrentBlock();	

			// role selection
			$tpl->setCurrentBlock("role");
			$role = TUtil::getRoles();
			$opts = TUtil::formSelect($Fuserdata["Role"],"Fuserdata[Role]",$role);
			$tpl->setVariable("ROLE", $opts);
			$tpl->parseCurrentBlock();
	
			$tpl->setVariable("USR_LOGIN",$user->data["login"]);
			$tpl->setVariable("USR_PASSWD","******");
			$tpl->setVariable("USR_TITLE",$user->data["Title"]);
			$tpl->setVariable("USR_FIRSTNAME",$user->data["FirstName"]);
			$tpl->setVariable("USR_SURNAME",$user->data["SurName"]);
			$tpl->setVariable("USR_EMAIL",$user->data["Email"]);

			if ($_GET["obj_id"] == $_SESSION["AccountId"])
			{
				// BEGIN AVTIVE ROLE
				$assigned_roles = $rbacreview->assignedRoles($_GET["obj_id"]);
				
				foreach ($assigned_roles as $key => $role)
				{
					// BEGIN TABLE_ROLES
					$tpl->setCurrentBlock("TABLE_ROLES");
					$obj = getObject($role);
					$tpl->setVariable("CSS_ROW_ROLE",$key % 2 ? 'tblrow1' : 'tblrow2');
					$box = Tutil::formCheckBox(in_array($role,$_SESSION["RoleId"]),'active[]',$role);
					$tpl->setVariable("CHECK_ROLE",$box);
					$tpl->setVariable("ROLENAME",$obj["title"]);
					$tpl->parseCurrentBlock();
				}
				
				$tpl->setCurrentBlock("ACTIVE_ROLE");
				$tpl->setVariable("ACTIVE_ROLE_OBJ_ID",$_GET["obj_id"]);
				$tpl->setVariable("ACTIVE_ROLE_TPOS",$_GET["parent"]);
				$tpl->setVariable("ACTIVE_ROLE_PAR",$_GET["parent_parent"]);
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to edit user",$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* update user data
	* TODO: The entry in object_data must be changed too!!
	* @access	public
	*/
	function updateObject()
	{
		global $rbacsystem,$rbacadmin;
		
		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]) || $_GET["obj_id"] == $_SESSION["AccountId"])
		{
			$Fuserdata = $_POST["Fuserdata"];
			$rbacadmin->updateUser($Fuserdata);
			$rbacadmin->assignUser($Fuserdata["Role"],$_GET["obj_id"]);
			// TODO: Passwort muss gesondert abgefragt werden
		}
		else
		{
			$this->ilias->raiseError("No permission to delete user",$this->ilias->error_obj->WARNING);
		}
		
		header("Location: content_user.php?obj_id=".$_GET["parent"]."&parent=".SYSTEM_FOLDER_ID);
		exit;
	}
	
	/**
	* add active role in session
	* @access	public
	**/
	function activeRoleSaveObject()
	{
		if ($_GET["obj_id"] == $_SESSION["AccountId"])
		{
			if (!count($_POST["active"]))
			{
				$this->ilias->raiseError("You must leave one active role",$this->ilias->error_obj->WARNING);
			}

			$_SESSION["RoleId"] = $_POST["active"];
		}
		else
		{
			$this->ilias->raiseError("You can only change your own account",$this->ilias->error_obj->WARNING);
		}
		
		header("Location: object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=edit");
		exit;
	}
} //end class.UserObject
?>