<?php
include_once("classes/class.Object.php");

/**
* Class UserObject
* @extends class.Object.php
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$ 
* @package ilias-core
* 
*/
class UserObject extends Object
{
	/**
	* array of gender abbreviations
	* @var array
	*/
	var $gender;

	/**
	* Contructor
	* @access public
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
	* @access public
	*/
	function createObject()
	{
		global $tree,$tplContent,$rbacsystem;

		$obj = getObject($_GET["obj_id"]);

		if ($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			$tplContent = new Template("user_form.html",true,true);
			$tplContent->setVariable($this->ilias->ini["layout"]); 

			$tplContent->setVariable("STATUS","Add User");
			$tplContent->setVariable("CMD","save");
			$tplContent->setVariable("TYPE","user");
			$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tplContent->setVariable("TPOS",$_GET["parent"]);

			// set Path
			$tplContent->setVariable("TREEPATH",$this->getPath());

			// gender selection
			$tplContent->setCurrentBlock("gender");
			$opts = TUtil::formSelect($Fuserdata["Gender"],"Fuserdata[Gender]",$this->gender);
			$tplContent->setVariable("GENDER",$opts);
			$tplContent->parseCurrentBlock();

			// role selection
			$tplContent->setCurrentBlock("role");
			$role = TUtil::getRoles();
			$opts = TUtil::formSelect($Fuserdata["Role"],"Fuserdata[Role]",$role);
			$tplContent->setVariable("ROLE",$opts);
			$tplContent->parseCurrentBlock();

			$tplContent->setVariable("USR_ID",$_GET["obj_id"]);
			$tplContent->setVariable("USR_LOGIN",$Fuserdata["Login"]);
			$tplContent->setVariable("USR_PASSWD",$Fuserdata["Passwd"]);
			$tplContent->setVariable("USR_TITLE",$Fuserdata["Title"]);
			$tplContent->setVariable("USR_FIRSTNAME",$Fuserdata["FirstName"]);
			$tplContent->setVariable("USR_SURNAME",$Fuserdata["SurName"]);
			$tplContent->setVariable("USR_EMAIL",$Fuserdata["Email"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to write to user folder",$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* save user data
	* @access public
	*/
	function saveObject()
	{
		global $rbacsystem,$rbacadmin;
		
		$Fuserdata = $_POST["Fuserdata"];

		if ($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			// create object
			$Fobject["title"] = User::buildFullName($Fuserdata["Title"],$Fuserdata["FirstName"],$Fuserdata["SurName"]);
			$Fobject["desc"] = "nix";
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
	* @access public
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
	* @access public
	*/
	function editObject()
	{
		global $tplContent,$rbacsystem,$rbacreview;

		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]) || $_GET["obj_id"] == $_SESSION["AccountId"])
		{
			// Userobjekt erzeugen
			$user = new User($this->ilias->db,$_GET["obj_id"]);
			
			$tplContent = new Template("user_form.html",true,true);
			$tplContent->setVariable($this->ilias->ini["layout"]);
			$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tplContent->setVariable("TPOS",$_GET["parent"]);
			$tplContent->setVariable("PAR",$_GET["parent_parent"]);
			$tplContent->setVariable("CMD","update");
			$tplContent->setVariable("TYPE","user");

			$tplContent->setVariable("TREEPATH",$this->getPath($_GET["parent"],$_GET["parent_parent"]));

			// gender selection
			$tplContent->setCurrentBlock("gender");
			$opts = TUtil::formSelect($Fuserdata["Gender"],"Fuserdata[Gender]",$this->gender);
			$tplContent->setVariable("GENDER",$opts);
			$tplContent->parseCurrentBlock();	

			// role selection
			$tplContent->setCurrentBlock("role");
			$role = TUtil::getRoles();
			$opts = TUtil::formSelect($Fuserdata["Role"],"Fuserdata[Role]",$role);
			$tplContent->setVariable("ROLE",$opts);
			$tplContent->parseCurrentBlock();
	
			$tplContent->setVariable("USR_ID",$_GET["obj_id"]);
			$tplContent->setVariable("USR_LOGIN",$user->data["login"]);
			$tplContent->setVariable("USR_PASSWD","******");
			$tplContent->setVariable("USR_TITLE",$user->data["Title"]);
			$tplContent->setVariable("USR_FIRSTNAME",$user->data["FirstName"]);
			$tplContent->setVariable("USR_SURNAME",$user->data["SurName"]);
			$tplContent->setVariable("USR_EMAIL",$user->data["Email"]);

			if ($_GET["obj_id"] == $_SESSION["AccountId"])
			{
				// BEGIN AVTIVE ROLE

				// BEGIN TABLE_ROLES
				$tplContent->setCurrentBlock("TABLE_ROLES");
				$assigned_roles = $rbacreview->assignedRoles($_GET["obj_id"]);
				
				foreach ($assigned_roles as $key => $role)
				{
					$obj = getObject($role);
					$tplContent->setVariable("CSS_ROW_ROLE",$key % 2 ? 'row_low' : 'row_high');
					$box = Tutil::formCheckBox(in_array($role,$_SESSION["RoleId"]),'active[]',$role);
					$tplContent->setVariable("CHECK_ROLE",$box);
					$tplContent->setVariable("ROLENAME",$obj["title"]);
					$tplContent->parseCurrentBlock();
				}
				
				$tplContent->setCurrentBlock("ACTIVE_ROLE");
				$tplContent->setVariable("ACTIVE_ROLE_OBJ_ID",$_GET["obj_id"]);
				$tplContent->setVariable("ACTIVE_ROLE_TPOS",$_GET["parent"]);
				$tplContent->setVariable("ACTIVE_ROLE_PAR",$_GET["parent_parent"]);
				$tplContent->parseCurrentBlock();
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to edit user",$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* update user data
	* TODO: The entrry in object_data must be changed too!!
	* @access public
	*/
	function updateObject()
	{
		global $rbacsystem,$rbacadmin;
		
		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]) || $_GET["obj_id"] == $_SESSION["AccountId"])
		{
			$Fuserdata = $_POST["Fuserdata"];
			
			$parent_obj_id = $this->getParentObjectId();

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
	* @access public
	*
	**/
	function activeRoleSaveObject()
	{
		if($_GET["obj_id"] == $_SESSION["AccountId"])
		{
			if(!count($_POST["active"]))
			{
				$this->ilias->raiseError("You must leave one active role",$this->ilias->error_obj->WARNING);
			}
			$_SESSION["RoleId"] = $_POST["active"];
		}
		else
		{
			$this->ilias->raiseError("You can only change your own account",$this->ilias->error_obj->WARNING);
		}
		
		header("Location: object.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]&parent_parent=$_GET[parent_parent]&cmd=edit");
		exit;
	}
} //end class.UserObject
?>