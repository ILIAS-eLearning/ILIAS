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
 * contructor
 * @param object ilias
 * @access public
 */
	function UserObject(&$a_ilias)
	{
		$this->Object($a_ilias);
		$this->gender = array(
			'm'    => 'Herr',
			'f'    => 'Frau');
	}
/**
 * create user
 * @access public
 */
	function createObject()
	{
		global $tree;
		global $tplContent;

		$obj = getObject($_GET["obj_id"]);
		$rbacsystem = new RbacSystemH($this->ilias->db);

		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
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
			$this->ilias->raiseError("No permission to write to user folder",$this->ilias->error_class->WARNING);
			exit();
		}
	}
/**
 * save user data
 * @access public
 */
	function saveObject()
	{
		$Fuserdata = $_POST["Fuserdata"];
		$rbacsystem = new RbacSystemH($this->ilias->db);
		$rbacadmin = new RbacAdminH($this->ilias->db);

		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
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
			$this->ilias->raiseError("No permission to write to user folder",$this->ilias->error_class->WARNING);
			exit();
		}
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
/**
 * delete user
 * @access public
 */
	function deleteObject()
	{
		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);
		
		// CHECK ACCESS
		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			$rbacadmin->deleteUser($_POST["id"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to delete user",$this->ilias->error_class->WARNING);
			exit();
		}
		header("Location: content_user.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
/**
 * edit user data
 * @access public
 */
	function editObject()
	{
		global $tplContent;

		$rbacsystem = new RbacSystemH($this->ilias->db);
		$rbacreview = new RbacReviewH($this->ilias->db);

		$parent_obj_id = $this->getParentObjectId();

		if($rbacsystem->checkAccess('write',$_GET["parent"],$parent_obj_id))
		{
			// Userobjekt erzeugen
			$user = new User($this->ilias->db,$_GET["obj_id"]);
			
			$tplContent = new Template("user_form.html",true,true);
			$tplContent->setVariable($this->ilias->ini["layout"]);
			$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tplContent->setVariable("TPOS",$_GET["parent"]);
			$tplContent->setVariable("CMD","update");
			$tplContent->setVariable("TYPE","user");

			$tplContent->setVariable("TREEPATH",$this->getPath($_GET["parent"]));

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

			if($_GET["obj_id"] == $_SESSION["AccountId"])
			{
				// BEGIN AVTIVE ROLE

				// BEGIN TABLE_ROLES
				$tplContent->setCurrentBlock("TABLE_ROLES");
				$assigned_roles = $rbacreview->assignedRoles($_GET["obj_id"]);
				foreach($assigned_roles as $key => $role)
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
				$tplContent->parseCurrentBlock();
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to edit user",$this->ilias->error_class->WARNING);
			exit();
		}
	}
/**
 * update user data
 * @access public
 */
	function updateObject()
	{
		$Fuserdata = $_POST["Fuserdata"];

		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);

		$parent_obj_id = $this->getParentObjectId();
		if($rbacsystem->checkAccess('write',$_GET["parent"],$parent_obj_id))
		{
			$rbacadmin->updateUser($Fuserdata);
			$rbacadmin->assignUser($Fuserdata["Role"],$_GET["obj_id"]);
			// TODO: Passwort muss gesondert abgefragt werden
		}
		else
		{
			$this->ilias->raiseError("No permission to delete user",$this->ilias->error_class->WARNING);
			exit();
		}
		header("Location: content_user.php?obj_id=$_GET[parent]&parent=$this->SYSTEM_FOLDER_ID");
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
				$this->ilias->raiseError("You must leave one active role",$this->ilias->error_class->WARNING);
			}
			$_SESSION["RoleId"] = $_POST["active"];
		}
		else
		{
			$this->ilias->raiseError("You can only change your own account",$this->ilias->error_class->WARNING);
		}
		header("Location: object.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]&cmd=edit");
	}		

} //end class
?>