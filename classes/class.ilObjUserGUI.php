<?php
/**
* Class ilObjUserGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjUserGUI.php,v 1.5 2003/03/31 09:38:20 akill Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjUserGUI extends ilObjectGUI
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
	function ilObjUserGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "usr";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
		
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
			$gender = ilUtil::formSelect($Fobject["gender"],"Fobject[gender]",$this->gender);

			// role selection
			$obj_list = getObjectList("role");

			foreach ($obj_list as $obj_data)
			{
				$rol[$obj_data["obj_id"]] = $obj_data["title"];
			}

			$role = ilUtil::formSelectWoTranslation($Fobject["default_role"],"Fobject[default_role]",$rol);

			$data = array();
			$data["fields"] = array();
			$data["fields"]["login"] = "";
			$data["fields"]["passwd"] = "";
			$data["fields"]["passwd2"] = "";
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
			$this->tpl->setVariable("TXT_REQUIRED_FIELDS", $this->lng->txt("required_field"));
			$this->tpl->setVariable("TXT_LOGIN_DATA", $this->lng->txt("login_data"));
			$this->tpl->setVariable("TXT_PERSONAL_DATA", $this->lng->txt("personal_data"));
			$this->tpl->setVariable("TXT_CONTACT_DATA", $this->lng->txt("contact_data"));
			$this->tpl->setVariable("TXT_SETTINGS", $this->lng->txt("settings"));
			$this->tpl->setVariable("TXT_PASSWD2", $this->lng->txt("retype_password"));		}
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
			$gender = ilUtil::formSelect($user->gender,"Fobject[gender]",$this->gender);

			// role selection
			$obj_list = getObjectList("role");

			foreach ($obj_list as $obj_data)
			{
				$rol[$obj_data["obj_id"]] = $obj_data["title"];
			}

			$def_role = $rbacadmin->getDefaultRole($user->getId());
			$role = ilUtil::formSelectWoTranslation($def_role,"Fobject[default_role]",$rol);

			$data = array();
			$data["fields"] = array();
			$data["fields"]["login"] = $user->getLogin();
			$data["fields"]["passwd"] = "********";	// will not be saved
			$data["fields"]["passwd2"] = "********";	// will not be saved
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
				  $box = ilUtil::formCheckBox(in_array($role,$_SESSION["RoleId"]),'active[]',$role);
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
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id.$obj_str."&cmd=update");
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FIELDS", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_LOGIN_DATA", $this->lng->txt("login_data"));
		$this->tpl->setVariable("TXT_PERSONAL_DATA", $this->lng->txt("personal_data"));
		$this->tpl->setVariable("TXT_CONTACT_DATA", $this->lng->txt("contact_data"));
		$this->tpl->setVariable("TXT_SETTINGS", $this->lng->txt("settings"));
		$this->tpl->setVariable("TXT_PASSWD2", $this->lng->txt("retype_password"));

		$this->tpl->setCurrentBlock("inform_user");
		
		if (true)
		{
			$tpl->setVariable("SEND_MAIL", " checked=\"checked\"");
		}
		
		$this->tpl->setVariable("TXT_INFORM_USER_MAIL", $this->lng->txt("inform_user_mail"));
		$this->tpl->parseCurrentBlock();

		// BEGIN ACTIVE ROLES
		$this->tpl->setCurrentBlock("ACTIVE_ROLE");

		// BEGIN TABLE ROLES
		$this->tpl->setCurrentBlock("TABLE_ROLES");

		$counter = 0;

		foreach($data["active_role"] as $role_id => $role)
		{
		   ++$counter;
		   $this->tpl->setVariable("ACTIVE_ROLE_CSS_ROW",ilUtil::switchColor($counter,"tblrow2","tblrow1"));
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

		if (!$rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$this->ilias->raiseError("No permission to create user",$this->ilias->error_obj->WARNING);
		}
		// check required fields
		if (empty($_POST["Fobject"]["firstname"]) or empty($_POST["Fobject"]["lastname"])
			or empty($_POST["Fobject"]["login"]) or empty($_POST["Fobject"]["email"])
			or empty($_POST["Fobject"]["passwd"]) or empty($_POST["Fobject"]["passwd2"]))
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}

		// check loginname
		if (loginExists($_POST["Fobject"]["login"]))
		{
			$this->ilias->raiseError($this->lng->txt("login_exists"),$this->ilias->error_obj->MESSAGE);
		}

		// check passwords
		if ($_POST["Fobject"]["passwd"] != $_POST["Fobject"]["passwd2"])
		{
			$this->ilias->raiseError($this->lng->txt("passwd_not_match"),$this->ilias->error_obj->MESSAGE);
		}
		
		// validate password
		if (!ilUtil::is_password($_POST["Fobject"]["passwd"]))
		{
			$this->ilias->raiseError($this->lng->txt("passwd_invalid"),$this->ilias->error_obj->MESSAGE);
		}

		// validate email
		if (!ilUtil::is_email($_POST["Fobject"]["email"]))
		{
			$this->ilias->raiseError($this->lng->txt("email_not_valid"),$this->ilias->error_obj->MESSAGE);
		}
		
		// TODO: check if login or passwd already exists
		// TODO: check length of login and passwd
		
		// checks passed. save user		
		$user = new User();
		$user->assignData($_POST["Fobject"]);
		
		//create new UserObject
		$userObj = new ilObjUser();
		$userObj->setTitle($user->getFullname());
		$userObj->setDescription($user->getEmail());
		$userObj->create();

		$user->setId($userObj->getId());

		//insert user data in table user_data
		$user->saveAsNew();

		//set role entries
		$rbacadmin->assignUser($_POST["Fobject"]["default_role"],$user->getId(),true);
		
		//create new usersetting entry 
		$settingObj = new ilObject();
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
		//$userTree = new ilTree(0,0,$user->getId());
		require_once ("classes/class.ilObjNoteFolder.php");
		$notfObj = new ilObjNoteFolder();
		$notfObj->setType("notf");
		$notfObj->setTitle($user->getFullname());
		$notfObj->setDescription("Note Folder Object");
		$notfObj->create();
		$notfObj->createReference();
		//$userTree->insertNode($notfObj->getRefId(), $settingObj->getRefId());

		// CREATE ENTRIES FOR MAIL BOX
		require_once ("classes/class.ilMailbox.php");
		$mbox = new ilMailbox($userObj->getId());
		$mbox->createDefaultFolder();
			
		require_once "classes/class.ilFormatMail.php";
		$fmail = new ilFormatMail($userObj->getId());
		$fmail->createMailOptionsEntry();
		header("Location: adm_object.php?ref_id=".$this->ref_id);
		exit();
	}

	
	/**
	* update object in db
	*/
	function updateObject()
	{
		global $rbacsystem, $rbacadmin;

		// check write access
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError("No permission to modify user",$this->ilias->error_obj->WARNING);
		}

		// check required fields
		if (empty($_POST["Fobject"]["firstname"]) or empty($_POST["Fobject"]["lastname"])
			or empty($_POST["Fobject"]["login"]) or empty($_POST["Fobject"]["email"])
			or empty($_POST["Fobject"]["passwd"]) or empty($_POST["Fobject"]["passwd2"]))
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}

		// check loginname
		if (loginExists($_POST["Fobject"]["login"],$this->id))
		{
			$this->ilias->raiseError($this->lng->txt("login_exists"),$this->ilias->error_obj->MESSAGE);
		}

		// check passwords
		if ($_POST["Fobject"]["passwd"] != $_POST["Fobject"]["passwd2"])
		{
			$this->ilias->raiseError($this->lng->txt("passwd_not_match"),$this->ilias->error_obj->MESSAGE);
		}
		
		// validate password
		if (!ilUtil::is_password($_POST["Fobject"]["passwd"]))
		{
			$this->ilias->raiseError($this->lng->txt("passwd_invalid"),$this->ilias->error_obj->MESSAGE);
		}

		// validate email
		if (!ilUtil::is_email($_POST["Fobject"]["email"]))
		{
			$this->ilias->raiseError($this->lng->txt("email_not_valid"),$this->ilias->error_obj->MESSAGE);
		}
		
		// TODO: check length of login and passwd

		// checks passed. save user
		$user = new User($this->object->getId());
		$user->assignData($_POST["Fobject"]);
		$user->update();

		// reset user's passwd if is it NOT ******** (8 asterisks)
		if ($_POST["Fobject"]["passwd"] != "********")
		{
			$user->resetPassword($_POST["Fobject"]["passwd"],$_POST["Fobject"]["passwd2"]);
		}
		
		// update login
		$user->updateLogin($_POST["Fobject"]["login"]);

		$this->object->setTitle($user->getFullname());
		$this->object->setDescription($user->getEmail());
		$this->update = $this->object->update();
		$rbacadmin->updateDefaultRole($_POST["Fobject"]["default_role"], $user->getId());

		// sent email
		if ($_POST["send_mail"] == "y")
		{
			require_once "classes/class.ilFormatMail.php";

			$umail = new ilFormatMail($_SESSION["AccountId"]);
			
			$attachments = array();
			
			// mail body
			$body = $this->lng->txt("login").": ".$user->getLogin()."\n\r".
					$this->lng->txt("passwd").": ".$_POST["Fobject"]["passwd"]."\n\r".
					$this->lng->txt("title").": ".$user->getTitle()."\n\r".
					$this->lng->txt("gender").": ".$user->getGender()."\n\r".
					$this->lng->txt("firstname").": ".$user->getFirstname()."\n\r".
					$this->lng->txt("lastname").": ".$user->getLastname()."\n\r".
					$this->lng->txt("institution").": ".$user->getInstitution()."\n\r".
					$this->lng->txt("street").": ".$user->getStreet()."\n\r".
					$this->lng->txt("city").": ".$user->getCity()."\n\r".
					$this->lng->txt("zipcode").": ".$user->getZipcode()."\n\r".
					$this->lng->txt("country").": ".$user->getCountry()."\n\r".
					$this->lng->txt("phone").": ".$user->getPhone()."\n\r".
					$this->lng->txt("email").": ".$user->getEmail()."\n\r".
					$this->lng->txt("default_role").": ".$_POST["Fobject"]["default_role"]."\n\r";
				
			if ($error_message = $umail->sendMail($user->getLogin(),"","",$this->lng->txt("profile_changed"),$body,$attachments,"normal",0))
			{
				$msg = $this->lng->txt("saved_successfully")."<br/>".$error_message;
			}
			else
			{
				$msg = $this->lng->txt("saved_successfully")."<br/>".$this->lng->txt("mail_sent");
			}
		}
		else
		{
			$msg = $this->lng->txt("saved_successfully");
		}
		
		// feedback
		sendInfo($msg,true);

		header("Location: adm_object.php?ref_id=".$this->ref_id);
		exit();
	}


	function activeRoleSaveObject()
	{
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=edit");
		exit;
	}	   

} // END class.UserObjectOut
?>
