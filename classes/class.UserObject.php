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
	function UserObject($a_id,$a_call_by_reference = "")
	{
		global $lng;

		$this->Object($a_id,$a_call_by_reference);

		// for gender selection. don't change this
		$this->gender = array(
							  'm'    => "salutation_m",
							  'f'    => "salutation_f"
							  );
	}

	/**
	* create user
	* @access	public
	* @param	integer	reference_id
	* @param	string	object type
	*/
	/**
	 * UserObject::createObject()
	 * 
	 * @param $a_ref_id
	 * @param $a_new_type
	 * @return 
	 **/
	function createObject($a_ref_id, $a_new_type)
	{
		global $tree,$tpl,$rbacsystem;

		$obj = getObjectByReference($a_ref_id);
		
		// TODO: get rid of $_GET variables

		if ($rbacsystem->checkAccess('write',$a_ref_id))
		{
			// gender selection
			$gender = TUtil::formSelect($Fobject["gender"],"Fobject[gender]",$this->gender);

			// role selection
			$obj_list = getObjectList("role");
			
			foreach ($obj_list as $obj_data)
			{
				$rol[$obj_data["obj_id"]] = $obj_data["title"];
			}
			
			$role = TUtil::formSelectWoTranslation($Fobject["default_role"],"Fobject[default_role]",$rol);

			$data = array();
			$data["fields"] = array();
			$data["fields"]["login"] = "";
			$data["fields"]["passwd"] = "";
			$data["fields"]["title"] = "";
			$data["fields"]["gender"] = $gender;
			$data["fields"]["firstname"] = "";
			$data["fields"]["lastname"] = "";
			$data["fields"]["institution"] = "";
			$data["fields"]["street"] = "";
			$data["fields"]["city"] = "";
			$data["fields"]["zipcode"] = "";
			$data["fields"]["country"] = "";
			$data["fields"]["phone"] = "";		
			$data["fields"]["email"] = "";
			$data["fields"]["default_role"] = $role;
			$data["title"] = $obj["title"];
			
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
	function saveObject($a_parent_ref_id, $a_type, $a_new_type, $a_data)
	{
		global $rbacsystem,$rbacadmin,$tree;
		
		if ($rbacsystem->checkAccess('write', $a_parent_ref_id))
		{
			$user = new User();
			$user->setData($a_data);
			
			//create new Object, return ObjectID of new Object
			$user_id = createNewObject("usr",$user->getFullname(),$user->getEmail());
			$user->setId($user_id);

			//insert user data in table user_data
			$user->saveAsNew();

			//set role entries
			$rbacadmin->assignUser($a_data["default_role"],$user->getId(),true);
			
			//create new usersetting entry 
			$uset_id = createNewObject("uset",$user->getFullname(),"User Setting Folder");
			$uset_ref = createNewReference($uset_id);
			
			//create usertree from class.user.php
			// tree_id is the obj_id of user not ref_id!
			// this could become a problem with same ids
			$tree->addTree($user->getId(), $uset_ref);
			
			//add notefolder to user tree
			$userTree = new tree(0,0,$user->getId());
			$notf_id = createNewObject("notf",$user->getFullname(),"Note Folder Object");
			$notf_ref = createNewReference($notf_id);
			$userTree->insertNode($notf_ref, $uset_ref);			
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
		
		// delete user data
		$user = new User();
		$user->delete($a_obj_id);

		// delete rbac data of user
		$rbacadmin->removeUser($a_obj_id);
		
		// delete object_data entry
		return parent::deleteObject($a_obj_id, $a_parent_id, $a_tree_id = 1);
	}
	
	/**
	* edit user data
	* @access	public
	*/
	function editObject($a_order, $a_direction)
	{
		global $tpl, $rbacsystem, $rbacreview, $lng, $rbacadmin;
		
		// TODO: get rid of $_GET vars
		if ($rbacsystem->checkAccess('write',$_GET["parent"]) || $this->id == $_SESSION["AccountId"])
		{
			// Userobjekt erzeugen
			$user = new User($this->id);
			
			// gender selection
			$gender = TUtil::formSelect($user->gender,"Fobject[gender]",$this->gender);

			// role selection
			$obj_list = getObjectList("role");
			
			foreach ($obj_list as $obj_data)
			{
				$rol[$obj_data["obj_id"]] = $obj_data["title"];
			}
			
			$def_role = $rbacadmin->getDefaultRole($user->getId());
			$role = TUtil::formSelectWoTranslation($def_role,"Fobject[default_role]",$rol);

			$data = array();
			$data["fields"] = array();
			$data["fields"]["login"] = $user->getLogin();
			$data["fields"]["passwd"] = "********";	// will not be saved
			$data["fields"]["title"] = $user->getTitle();
			$data["fields"]["gender"] = $gender;
			$data["fields"]["firstname"] = $user->getFirstname();
			$data["fields"]["lastname"] = $user->getLastname();
			$data["fields"]["institution"] = $user->getInstitution();
			$data["fields"]["street"] = $user->getStreet();
			$data["fields"]["city"] = $user->getCity();
			$data["fields"]["zipcode"] = $user->getZipcode();
			$data["fields"]["country"] = $user->getCountry();
			$data["fields"]["phone"] = $user->getPhone();					
			$data["fields"]["email"] = $user->getEmail();
			$data["fields"]["default_role"] = $role;
			
			$data["active_role"]["access"] = true;

			// BEGIN ACTIVE ROLE
			$assigned_roles = $rbacreview->assignedRoles($user->getId());

			foreach ($assigned_roles as $key => $role)
			{
			   // BEGIN TABLE_ROLES
			   $obj = getObject($role);

			   if($user->getId() == $_SESSION["AccountId"])
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
	function updateObject($a_data)
	{
		global $rbacsystem,$rbacadmin;
		
		// TODO: get rid of $_GET vars and evil $_POST var Fuserdata
		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]) || $this->id == $_SESSION["AccountId"])
		{
			$user = new User($this->id);
			$user->setData($a_data);
			$user->update();
			
			// update object_data entry
			updateObject($user->getId(),$user->getFullname(),$user->getEmail());

			// update default role if changed
			$rbacadmin->updateDefaultRole($a_data["default_role"], $user->getId());
			
			// TODO: Passwort muss gesondert abgefragt werden
		}
		else
		{
			$this->ilias->raiseError("No permission to modify user",$this->ilias->error_obj->WARNING);
		}
		
		return true;
	}
	
	/**
	* add active role in session
	* @access	public
	**/
	function activeRoleSaveObject()
	{
		// TODO: get rif of $_POST var
	   if (!count($_POST["active"]))
	   {
		  $this->ilias->raiseError("You must leave one active role",$this->ilias->error_obj->MESSAGE);
	   }

	   $_SESSION["RoleId"] = $_POST["active"];

	   return true;
	}
} //end class.UserObject
?>
