<?php
/**
* Class UserObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.UserObjectOut.php,v 1.12 2003/03/18 08:51:23 akill Exp $
* 
* @extends Object
* @package ilias-core
*/

class UserObjectOut extends ObjectOut
{
	/**
	* array of gender abbreviations
	* @var array
	* @access public
	*/
	var $gender;


	/**
	* Constructor
	* @access	public
	*/
	function UserObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "usr";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);

		// for gender selection. don't change this
		$this->gender = array(
							  'm'    => "salutation_m",
							  'f'    => "salutation_f"
							  );
	}


	/**
	* display user create form
	*/
	function createObject()
	{
		global $tree,$tpl,$rbacsystem;

		if (!$rbacsystem->checkAccess('write', $_GET["ref_id"], $_POST["new_type"]))
		{
			$this->ilias->raiseError("No permission to write to user folder",$this->ilias->error_obj->WARNING);
		}
		else
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

			$this->getTemplateFile("edit","usr");

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"]."&new_type=".$_POST["new_type"]);
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}


	/**
	* display user edit form
	*/
	function editObject()
	{
		global $tpl, $rbacsystem, $rbacreview, $lng, $rbacadmin;

		if ($rbacsystem->checkAccess('write',$_GET["ref_id"]) || ($this->id == $_SESSION["AccountId"]))
		{
			// Userobjekt erzeugen
			$user = new User($this->obj_id);

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

			   if ($user->getId() == $_SESSION["AccountId"])
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
		}
		else
		{
			$this->ilias->raiseError("No permission to edit user",$this->ilias->error_obj->WARNING);
		}

		$this->getTemplateFile("edit","usr");

		foreach ($data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=update");
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

		// BEGIN ACTIVE ROLES
		$this->tpl->setCurrentBlock("ACTIVE_ROLE");

		// BEGIN TABLE ROLES
		$this->tpl->setCurrentBlock("TABLE_ROLES");

		$counter = 0;

		foreach($data["active_role"] as $role_id => $role)
		{
		   ++$counter;
		   $this->tpl->setVariable("ACTIVE_ROLE_CSS_ROW",TUtil::switchColor($counter,"tblrow2","tblrow1"));
		   $this->tpl->setVariable("CHECK_ROLE",$role["checkbox"]);
		   $this->tpl->setVariable("ROLENAME",$role["title"]);
		   $this->tpl->parseCurrentBlock();
		}
		// END TABLE ROLES
		$this->tpl->setVariable("ACTIVE_ROLE_FORMACTION","adm_object.php?cmd=activeRoleSave&ref_id=".$_GET["ref_id"]);
		$this->tpl->parseCurrentBlock();
		// END ACTIVE ROLES

		if ($data["active_role"]["access"] == true)
		{
		   $this->tpl->touchBlock("TABLE_SUBMIT");
	    }
	}

	/**
	* save user data
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem,$rbacadmin,$tree;

		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$user = new User();
			$user->setData($_POST["Fobject"]);
			
			//create new UserObject
			$userObj = new UserObject();
			$userObj->setTitle($user->getFullname());
			$userObj->setDescription($user->getEmail());
			$userObj->create();

			$user->setId($userObj->getId());

			//insert user data in table user_data
			$user->saveAsNew();

			//set role entries
			$rbacadmin->assignUser($_POST["Fobject"]["default_role"],$user->getId(),true);
			
			//create new usersetting entry 
			$settingObj = new Object();
			$settingObj->setType("uset");
			$settingObj->setTitle($user->getFullname());
			$settingObj->setDescription("User Setting Folder");
			$settingObj->create();
			$settingObj->createReference();
			
			//create usertree from class.user.php
			// tree_id is the obj_id of user not ref_id!
			// this could become a problem with same ids
			//$tree->addTree($user->getId(), $settingObj->getRefId());
			
			//add notefolder to user tree
			//$userTree = new tree(0,0,$user->getId());
			require_once ("classes/class.NoteFolderObject.php");
			$notfObj = new NoteFolderObject();
			$notfObj->setTitle($user->getFullname());
			$notfObj->setDescription("Note Folder Object");
			$notfObj->create();
			$notfObj->createReference();
			//$userTree->insertNode($notfObj->getRefId(), $settingObj->getRefId());
		}
		else
		{
			$this->ilias->raiseError("No permission to write to user folder",$this->ilias->error_obj->WARNING);
		}
		
		header("Location: adm_object.php?ref_id=".$this->ref_id."&cmd=view");
		exit();
	}

	
	/**
	* update object in db
	*/
	function updateObject()
	{
		global $rbacsystem, $rbacadmin;

		if ($rbacsystem->checkAccess("write", $_GET["ref_id"])
			|| $this->object->getId() == $_SESSION["AccountId"])
		{
			$user = new User($this->object->getId());
			$user->setData($_POST["Fobject"]);
			$user->update();
			$this->object->setTitle($user->getFullname());
			$this->object->setDescription($user->getEmail());
			$this->update = $this->object->update();
			$rbacadmin->updateDefaultRole($_POST["Fobject"]["default_role"], $user->getId());
		}
		else
		{
			$this->ilias->raiseError("No permission to modify user",$this->ilias->error_obj->WARNING);
		}
		//echo "adm_object.php?ref_id=".$this->ref_id."&cmd=view";
		header("Location: adm_object.php?ref_id=".$this->ref_id."&cmd=view");
		exit();
	}


	function activeRoleSaveObject()
	{
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=edit");
		exit;
	}	   

} // END class.UserObjectOut
?>
