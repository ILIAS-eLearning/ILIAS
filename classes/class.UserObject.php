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
							  'm'    => "salutation_m",
							  'f'    => "salutation_f"
							  );
	}

	/**
	* create user
	* @access	public
	*/
	function createObject()
	{
		global $tree,$tpl,$rbacsystem;

		$obj = getObject($_GET["obj_id"]);

		if ($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			// gender selection
			$gender = TUtil::formSelect($Fuserdata["Gender"],"Fuserdata[Gender]",$this->gender);
			// role selection
			$rol = TUtil::getRoles();
			$role = TUtil::formSelectWoTranslation($Fuserdata["Role"],"Fuserdata[Role]",$rol);

			$data["fields"] = array();
			$data["fields"]["login"] = "";
			$data["fields"]["passwd"] = "";
			$data["fields"]["title"] = "";
			$data["fields"]["gender"] = $gender;
			$data["fields"]["firstname"] = "";
			$data["fields"]["lastname"] = "";
			$data["fields"]["email"] = "";
			$data["fields"]["default_role"] = $role;
			$data["title"] = $user->data["Title"];
			return $data;
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
		global $rbacsystem,$rbacadmin,$tree;
		
		$Fuserdata = $_POST["Fuserdata"];

		if ($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			
			// create object
			$Fobject["title"] = User::buildFullName($Fuserdata["Title"],$Fuserdata["FirstName"],$Fuserdata["SurName"]);
			$Fobject["desc"] = $Fuserdata["Email"];
			//create new Object, return ObjectID of new Object
			$Fuserdata["Id"] = createNewObject("usr",$Fobject);

			//insert user data in table user_data
			$rbacadmin->addUser($Fuserdata);
			//set role entries
			$rbacadmin->assignUser($Fuserdata["Role"],$Fuserdata["Id"],true);
			
			//create new usersetting entry 
			$Fobject2["title"] = User::buildFullName($Fuserdata["Title"],$Fuserdata["FirstName"],$Fuserdata["SurName"]);
			$Fobject2["desc"]  = "User Setting Folder";
			$Fusetdata["Id"]   = createNewObject("uset",$Fobject2);
			
			//create usertree from class.user.php	
			$tree->addTree($Fuserdata["Id"], $Fuserdata["Id"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to write to user folder",$this->ilias->error_obj->WARNING);
		}
		return true;		
	}
	
	/**
	* delete user
	* @access	public
	*/
	function deleteObject($a_obj_id, $a_parent_id, $a_tree_id = 1)
	{
		global $rbacadmin;

		$rbacadmin->deleteUserData($a_obj_id);
		return parent::deleteObject($a_obj_id, $a_parent_id, $a_tree_id = 1);
	}
	
	/**
	* edit user data
	* @access	public
	*/
	function editObject()
	{
		global $tpl, $rbacsystem, $rbacreview, $lng, $rbacadmin;

		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]) || $_GET["obj_id"] == $_SESSION["AccountId"])
		{
			// Userobjekt erzeugen
			$user = new User($_GET["obj_id"]);
			// gender selection
			$gender = TUtil::formSelect($Fuserdata["Gender"],"Fuserdata[Gender]",$this->gender);
			// role selection
			$rol = TUtil::getRoles();
			$def_role = $rbacadmin->getDefaultRole($_GET["obj_id"]);
			$role = TUtil::formSelectWoTranslation(3,"Fuserdata[Role]",$rol);

			$data = array();
			$data["fields"] = array();
			$data["fields"]["login"] = $user->data["login"];
			$data["fields"]["passwd"] = "********";
			$data["fields"]["title"] = $user->data["title"];
			$data["fields"]["gender"] = $gender;
			$data["fields"]["firstname"] = $user->data["FirstName"];
			$data["fields"]["lastname"] = $user->data["SurName"];
			$data["fields"]["email"] = $user->data["Email"];
			$data["fields"]["default_role"] = $role;
			$data["title"] = $user->data["Title"];
			
			$data["active_role"]["access"] = true;
			// BEGIN ACTIVE ROLE
			$assigned_roles = $rbacreview->assignedRoles($_GET["obj_id"]);
			foreach ($assigned_roles as $key => $role)
			{
			   // BEGIN TABLE_ROLES
			   $obj = getObject($role);
			   if($_GET["obj_id"] == $_SESSION["AccountId"])
			   {
				  $data["active_role"]["access"] = true;
				  $box = Tutil::formCheckBox(in_array($role,$_SESSION["RoleId"]),'active[]',$role);
			   }
			   else
			   {
				  $data["active_role"]["access"] = false;
				  $box = "";
			   }
			   $data["active_role"][$role]["checkbox"] = $box;
			   $data["active_role"][$role]["title"] = $obj["title"];
			}
			return $data;
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
			$Fuserdata["Id"] = $this->id;
			$rbacadmin->updateUser($Fuserdata);
			$rbacadmin->updateDefaultRole($Fuserdata["Role"],$_GET["obj_id"]);
			// TODO: Passwort muss gesondert abgefragt werden
		}
		else
		{
			$this->ilias->raiseError("No permission to delete user",$this->ilias->error_obj->WARNING);
		}
		
		return true;
	}
	
	/**
	* add active role in session
	* @access	public
	**/
	function activeRoleSaveObject()
	{
	   if (!count($_POST["active"]))
	   {
		  $this->ilias->raiseError("You must leave one active role",$this->ilias->error_obj->MESSAGE);
	   }

	   $_SESSION["RoleId"] = $_POST["active"];

	   return true;
	}
} //end class.UserObject
?>
