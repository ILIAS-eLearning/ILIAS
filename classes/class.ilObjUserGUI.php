<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Class ilObjUserGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$Id: class.ilObjUserGUI.php,v 1.43 2003/08/18 18:15:29 shofmann Exp $
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
	function ilObjUserGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		$this->type = "usr";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output);

		// for gender selection. don't change this
		$this->gender = array(
							  'm'    => "salutation_m",
							  'f'    => "salutation_f"
							  );
	}

	/**
	* display public profile
	*
	* @param	string	$a_template_var			template variable where profile
	*											should be inserted
	* @param	string	$a_template_block_name	name of profile template block
	* @access	public
	*/
	function insertPublicProfile($a_template_var, $a_template_block_name)
	{
		$this->tpl->addBlockFile($a_template_var, $a_template_block_name, "tpl.usr_public_profile.html");
		$this->tpl->setCurrentBlock($a_template_block_name);

		// Get name of picture of user
		// TODO: the user is already the current user object !!
		$userObj = new ilObjUser($_GET["user"]);

		$this->tpl->setVariable("ROWCOL1", "tblrow1");
		$this->tpl->setVariable("ROWCOL2", "tblrow2");

		//if (usr_id == $_GET["user"])
		// Check from Database if value
		// of public_profile = "y" show user infomation
		if ($userObj->getPref("public_profile")=="y")
		{
			$this->tpl->setVariable("TXT_NAME",$this->lng->txt("name"));
			$this->tpl->setVariable("FIRSTNAME",$userObj->getFirstName());
			$this->tpl->setVariable("LASTNAME",$userObj->getLastName());
		}
		else
		{
			$this->tpl->setVariable("TXT_NAME",$this->lng->txt("name"));
			$this->tpl->setVariable("FIRSTNAME","N /");
			$this->tpl->setVariable("LASTNAME","A");
		}

		if ($userObj->getPref("public_upload")=="y")
		{
			//Getting the flexible path of image form ini file
			$webspace_dir = $this->ilias->ini->readVariable("server","webspace_dir");
			$this->tpl->setVariable("TXT_IMAGE",$this->lng->txt("image"));
			$this->tpl->setVariable("IMAGE_PATH","./".$webspace_dir."/usr_images/".$userObj->getPref("profile_image"));
		}
		else
		{
			$this->tpl->setVariable("TXT_IMAGE",$this->lng->txt("image"));
			// Todo: point to anonymous picture
		}

		// Check from Database if value
		// "y" show institute information
		$this->tpl->setVariable("TXT_INSTITUTE",$this->lng->txt("institution"));

		if ($userObj->getPref(public_institution)=="y")
		{
			$this->tpl->setVariable("INSTITUTE",$userObj->getInstitution());
		}
		else
		{
			$this->tpl->setVariable("INSTITUTE","N / A");
		}

		// Check from Database if value
		// "y" show institute information
		$this->tpl->setVariable("TXT_STREET",$this->lng->txt("street"));

		if ($userObj->getPref(public_street)=="y")
		{
			$this->tpl->setVariable("STREET",$userObj->getStreet());
		}
		else
		{
			$this->tpl->setVariable("STREET","N / A");
		}

		// Check from Database if value
		// "y" show zip code information
		$this->tpl->setVariable("TXT_ZIPCODE",$this->lng->txt("zipcode"));

		if ($userObj->getPref(public_zip)=="y")
		{
			$this->tpl->setVariable("ZIPCODE",$userObj->getZipcode());
		}
		else
		{
			$this->tpl->setVariable("ZIPCODE","N / A");
		}

		// Check from Database if value
		// "y" show city information
		$this->tpl->setVariable("TXT_CITY",$this->lng->txt("city"));

		if ($userObj->getPref(public_city)=="y")
		{
			$this->tpl->setVariable("CITY",$userObj->getCity());
		}
		else
		{
			$this->tpl->setVariable("CITY","N / A");
		}

		// Check from Database if value
		// "y" show country information
		$this->tpl->setVariable("TXT_COUNTRY",$this->lng->txt("country"));

		if ($userObj->getPref(public_country)=="y")
		{
			$this->tpl->setVariable("COUNTRY",$userObj->getCountry());
		}
		else
		{
			$this->tpl->setVariable("COUNTRY","N / A");
		}

		// Check from Database if value
		// "y" show phone information
		$this->tpl->setVariable("TXT_PHONE",$this->lng->txt("phone"));

		if ($userObj->getPref(public_phone)=="y")
		{
			$this->tpl->setVariable("PHONE",$userObj->getPhone());
		}
		else
		{
			$this->tpl->setVariable("PHONE","N / A");
		}

		// Check from Database if value
		// "y" show email information
		$this->tpl->setVariable("TXT_EMAIL",$this->lng->txt("email"));

		if ($userObj->getPref(public_email)=="y")
		{
			$this->tpl->setVariable("EMAIL",$userObj->getEmail());
		}
		else
		{
			$this->tpl->setVariable("EMAIL","N / A");
		}

		$this->tpl->setVariable("TXT_HOBBY",$this->lng->txt("hobby"));

		if ($userObj->getPref(public_hobby)=="y")
		{
			$this->tpl->setVariable("HOBBY",$userObj->getHobby());
		}
		else
		{
			$this->tpl->setVariable("HOBBY","N / A");
		}

		$this->tpl->parseCurrentBlock($a_template_block_name);
	}


	/**
	* display user create form
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];
		
		if (!$rbacsystem->checkAccess('create', $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
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
			$data["fields"]["hobby"] = "";
			$data["fields"]["default_role"] = $role;

			$this->getTemplateFile("edit","usr");

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);

				if ($this->prepare_output)
				{
					$this->tpl->parseCurrentBlock();
				}
			}
			
			$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"]."&new_type=".$new_type);
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FIELDS", $this->lng->txt("required_field"));
			$this->tpl->setVariable("TXT_LOGIN_DATA", $this->lng->txt("login_data"));
			$this->tpl->setVariable("TXT_PERSONAL_DATA", $this->lng->txt("personal_data"));
			$this->tpl->setVariable("TXT_CONTACT_DATA", $this->lng->txt("contact_data"));
			$this->tpl->setVariable("TXT_SETTINGS", $this->lng->txt("settings"));
			$this->tpl->setVariable("TXT_PASSWD2", $this->lng->txt("retype_password"));
			$this->tpl->setVariable("TXT_LANGUAGE",$this->lng->txt("language"));

			// FILL SAVED VALUES IN CASE OF ERROR
			$this->tpl->setVariable("LOGIN",$_SESSION["error_post_vars"]["Fobject"]["login"]);
			$this->tpl->setVariable("FIRSTNAME",$_SESSION["error_post_vars"]["Fobject"]["firstname"]);
			$this->tpl->setVariable("LASTNAME",$_SESSION["error_post_vars"]["Fobject"]["lastname"]);
			$this->tpl->setVariable("TITLE",$_SESSION["error_post_vars"]["Fobject"]["title"]);
			$this->tpl->setVariable("INSTITUTION",$_SESSION["error_post_vars"]["Fobject"]["institution"]);
			$this->tpl->setVariable("STREET",$_SESSION["error_post_vars"]["Fobject"]["street"]);
			$this->tpl->setVariable("CITY",$_SESSION["error_post_vars"]["Fobject"]["city"]);
			$this->tpl->setVariable("ZIPCODE",$_SESSION["error_post_vars"]["Fobject"]["zipcode"]);
			$this->tpl->setVariable("COUNTRY",$_SESSION["error_post_vars"]["Fobject"]["country"]);
			$this->tpl->setVariable("PHONE",$_SESSION["error_post_vars"]["Fobject"]["phone"]);
			$this->tpl->setVariable("EMAIL",$_SESSION["error_post_vars"]["Fobject"]["email"]);
			$this->tpl->setVariable("HOBBY",$_SESSION["error_post_vars"]["Fobject"]["hobby"]);

			// language selection
			$languages = $this->lng->getInstalledLanguages();
	
			foreach ($languages as $lang_key)
			{
				$this->tpl->setCurrentBlock("language_selection");
				$this->tpl->setVariable("LANG", $this->lng->txt("lang_".$lang_key));
				$this->tpl->setVariable("LANGSHORT", $lang_key);
	
				if ($this->ilias->getSetting("language") == $lang_key)
				{
					$this->tpl->setVariable("SELECTED_LANG", "selected=\"selected\"");
				}
	
				$this->tpl->parseCurrentBlock();
			} // END language selection

			// EMPTY SAVED VALUES not needed; done by system
			//unset($_SESSION["error_post_vars"]);
		}
	}


	/**
	* display user edit form
	* 
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem, $rbacreview, $rbacadmin;

		// deactivated:
		// or ($this->id != $_SESSION["AccountId"])
		if (!$rbacsystem->checkAccess('write',$_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"),$this->ilias->error_obj->WARNING);
		}
		else
		{
			// gender selection
			$gender = ilUtil::formSelect($this->object->gender,"Fobject[gender]",$this->gender);

			$data = array();
			$data["fields"] = array();
			$data["fields"]["login"] = $this->object->getLogin();
			$data["fields"]["passwd"] = "********";	// will not be saved
			$data["fields"]["passwd2"] = "********";	// will not be saved
			$data["fields"]["title"] = $this->object->getUTitle();
			$data["fields"]["gender"] = $gender;
			$data["fields"]["firstname"] = $this->object->getFirstname();
			$data["fields"]["lastname"] = $this->object->getLastname();
			$data["fields"]["institution"] = $this->object->getInstitution();
			$data["fields"]["street"] = $this->object->getStreet();
			$data["fields"]["city"] = $this->object->getCity();
			$data["fields"]["zipcode"] = $this->object->getZipcode();
			$data["fields"]["country"] = $this->object->getCountry();
			$data["fields"]["phone"] = $this->object->getPhone();
			$data["fields"]["email"] = $this->object->getEmail();
			$data["fields"]["hobby"] = $this->object->getHobby();

			if (!count($user_online = ilUtil::getUsersOnline($this->object->getId())) == 1)
			{
				$user_is_online = false;
			}
			else
			{
				$user_is_online = true;

				// extract serialized role Ids from session data
				preg_match("/RoleId.*?;\}/",$user_online[$this->object->getId()]["data"],$matches);

				$active_roles = unserialize(substr($matches[0],7));
				
				// gather data for active roles
				$assigned_roles = $rbacreview->assignedRoles($this->object->getId());

				foreach ($assigned_roles as $key => $role)
				{
					$roleObj = $this->ilias->obj_factory->getInstanceByObjId($role);

					// fetch context path of role
					$rolf = $rbacreview->getFoldersAssignedToRole($role,true);
			
					$path = "";		
					
					$tmpPath = $this->tree->getPathFull($rolf[0]);		

					// count -1, to exclude the role folder itself
					for ($i = 0; $i < (count($tmpPath)-1); $i++)
					{
						if ($path != "")
						{
							$path .= " > ";
						}

						$path .= $tmpPath[$i]["title"];						
					}					

					if (in_array($role,$active_roles))
					{
						$data["active_role"][$role]["active"] = true;
					}

					$data["active_role"][$role]["title"] = $roleObj->getTitle();
					$data["active_role"][$role]["context"] = $path;
								
					unset($roleObj);
				}
			}
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
		$this->tpl->setVariable("TXT_LANGUAGE",$this->lng->txt("language"));

		// language selection
		$languages = $this->lng->getInstalledLanguages();

		foreach ($languages as $lang_key)
		{
			$this->tpl->setCurrentBlock("language_selection");
			$this->tpl->setVariable("LANG", $this->lng->txt("lang_".$lang_key));
			$this->tpl->setVariable("LANGSHORT", $lang_key);

			if ($this->object->getLanguage() == $lang_key)
			{
				$this->tpl->setVariable("SELECTED_LANG", "selected=\"selected\"");
			}

			$this->tpl->parseCurrentBlock();
		} // END language selection

		// inform user about changes option
		$this->tpl->setCurrentBlock("inform_user");

		if (true)
		{
			$this->tpl->setVariable("SEND_MAIL", " checked=\"checked\"");
		}

		$this->tpl->setVariable("TXT_INFORM_USER_MAIL", $this->lng->txt("inform_user_mail"));
		$this->tpl->parseCurrentBlock();
		
		if ($user_is_online)
		{
			// BEGIN TABLE ROLES
			$this->tpl->setCurrentBlock("TABLE_ROLES");
		
			$counter = 0;

			foreach ($data["active_role"] as $role_id => $role)
			{
				++$counter;
				$css_row = ilUtil::switchColor($counter,"tblrow2","tblrow1");
				($role["active"]) ? $checked = "checked=\"checked\"" : $checked = "";

				$this->tpl->setVariable("ACTIVE_ROLE_CSS_ROW",$css_row);
				$this->tpl->setVariable("ROLECONTEXT",$role["context"]);
				$this->tpl->setVariable("ROLENAME",$role["title"]);
				$this->tpl->setVariable("CHECKBOX_ID", $role_id);
				$this->tpl->setVariable("CHECKED", $checked);
				$this->tpl->parseCurrentBlock();
			}
			// END TABLE ROLES

			// BEGIN ACTIVE ROLES
			$this->tpl->setCurrentBlock("ACTIVE_ROLE");
			$this->tpl->setVariable("ACTIVE_ROLE_FORMACTION","adm_object.php?cmd=activeRoleSave&ref_id=".
									$_GET["ref_id"]."&obj_id=$_GET[obj_id]");
			$this->tpl->setVariable("TXT_ACTIVE_ROLES",$this->lng->txt("active_roles"));
			$this->tpl->setVariable("TXT_ASSIGN",$this->lng->txt("change_active_assignment"));
			$this->tpl->parseCurrentBlock();
			// END ACTIVE ROLES
		}
	}

	/**
	* save user data
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem,$rbacadmin;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];
		
		if (!$rbacsystem->checkAccess('create', $_GET["ref_id"],$new_type))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_user"),$this->ilias->error_obj->WARNING);
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
		$userObj = new ilObjUser();
		$userObj->assignData($_POST["Fobject"]);
		$userObj->setTitle($userObj->getFullname());
		$userObj->setDescription($userObj->getEmail());
		$userObj->create();

		//$user->setId($userObj->getId());

		//insert user data in table user_data
		$userObj->saveAsNew();
		
		// setup user preferences
		$userObj->setLanguage($_POST["usr_language"]);
		$userObj->writePrefs();

		//set role entries
		$rbacadmin->assignUser($_POST["Fobject"]["default_role"],$userObj->getId(),true);

		//create new usersetting entry
		/*
		$settingObj = new ilObject();
		$settingObj->setType("uset");
		$settingObj->setTitle($user->getFullname());
		$settingObj->setDescription("User Setting Folder");
		$settingObj->create();
		$settingObj->createReference();
		*/
		//create usertree from class.user.php
		// tree_id is the obj_id of user not ref_id!
		// this could become a problem with same ids
		//$this->tree->addTree($user->getId(), $settingObj->getRefId());

		//add notefolder to user tree
		//$userTree = new ilTree(0,0,$user->getId());
		/*
		require_once ("classes/class.ilObjNoteFolder.php");
		$notfObj = new ilObjNoteFolder();
		$notfObj->setType("notf");
		$notfObj->setTitle($user->getFullname());
		$notfObj->setDescription("Note Folder Object");
		$notfObj->create();
		$notfObj->createReference();
		//$userTree->insertNode($notfObj->getRefId(), $settingObj->getRefId());
		* */

		// CREATE ENTRIES FOR MAIL BOX
		include_once ("classes/class.ilMailbox.php");
		$mbox = new ilMailbox($userObj->getId());
		$mbox->createDefaultFolder();

		include_once "classes/class.ilFormatMail.php";
		$fmail = new ilFormatMail($userObj->getId());
		$fmail->createMailOptionsEntry();

		// create personal bookmark folder tree
		include_once "classes/class.ilBookmarkFolder.php";
		$bmf = new ilBookmarkFolder(0, $userObj->getId());
		$bmf->createNewBookmarkTree();

		sendInfo($this->lng->txt("user_added"),true);

		header("Location:".$this->getReturnLocation("save","adm_object.php?ref_id=".$this->ref_id));
		exit();
	}

	/**
	* Does input checks and updates a user account if everything is fine.
	* @access	public
	*/
	function updateObject()
	{
		global $rbacsystem, $rbacadmin;

		// check write access
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"),$this->ilias->error_obj->WARNING);
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
		$this->object->assignData($_POST["Fobject"]);

		if ($_POST["Fobject"]["passwd"] != "********")
		{
			$this->object->resetPassword($_POST["Fobject"]["passwd"],$_POST["Fobject"]["passwd2"]);
		}
		$this->object->updateLogin($_POST["Fobject"]["login"]);
		$this->object->setTitle($this->object->getFullname());
		$this->object->setDescription($this->object->getEmail());
		$this->object->setLanguage($_POST["usr_language"]);
		$this->update = $this->object->update();
		//$rbacadmin->updateDefaultRole($_POST["Fobject"]["default_role"], $this->object->getId());

		// send email
		if ($_POST["send_mail"] == "y")
		{
			include_once "classes/class.ilFormatMail.php";

			$umail = new ilFormatMail($_SESSION["AccountId"]);

			// mail body
			$body = $this->lng->txt("login").": ".$this->object->getLogin()."\n\r".
					$this->lng->txt("passwd").": ".$_POST["Fobject"]["passwd"]."\n\r".
					$this->lng->txt("title").": ".$this->object->getTitle()."\n\r".
					$this->lng->txt("gender").": ".$this->object->getGender()."\n\r".
					$this->lng->txt("firstname").": ".$this->object->getFirstname()."\n\r".
					$this->lng->txt("lastname").": ".$this->object->getLastname()."\n\r".
					$this->lng->txt("institution").": ".$this->object->getInstitution()."\n\r".
					$this->lng->txt("street").": ".$this->object->getStreet()."\n\r".
					$this->lng->txt("city").": ".$this->object->getCity()."\n\r".
					$this->lng->txt("zipcode").": ".$this->object->getZipcode()."\n\r".
					$this->lng->txt("country").": ".$this->object->getCountry()."\n\r".
					$this->lng->txt("phone").": ".$this->object->getPhone()."\n\r".
					$this->lng->txt("email").": ".$this->object->getEmail()."\n\r".
					$this->lng->txt("hobby").": ".$this->object->getHobby()."\n\r".
					$this->lng->txt("default_role").": ".$_POST["Fobject"]["default_role"]."\n\r";

			if ($error_message = $umail->sendMail($this->object->getLogin(),"","",
												  $this->lng->txt("profile_changed"),$body,array(),array("normal")))
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


	/**
	* updates actives roles of user in session
	* DEPRECATED
	* 
	* @access	public
	*/
	function activeRoleSaveObject()
	{
		if (!count($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_min_one_active_role"),$this->ilias->error_obj->MESSAGE);
		}
		
		if ($this->object->getId() == $_SESSION["AccountId"])
		{
			$_SESSION["RoleId"] = $_POST["id"];
		}
		else
		{
			if (count($user_online = ilUtil::getUsersOnline($this->object->getId())) == 1)
			{
				//var_dump("<pre>",$user_online,$_POST["id"],"</pre>");exit;
				
				$roles = "RoleId|".serialize($_POST["id"]);
				$modified_data = preg_replace("/RoleId.*?;\}/",$roles,$user_online[$this->object->getId()]["data"]);
			
				$q = "UPDATE usr_session SET data='".$modified_data."' WHERE user_id = '".$this->object->getId()."'";
				$this->ilias->db->query($q);			
			}
			else
			{
				// user went offline - do nothing
			}
		}

		header("Location: adm_object.php?ref_id=$_GET[ref_id]&obj_id=$_GET[obj_id]&cmd=edit");
		exit;
	}

	/**
	* assign users to role
	*
	* @access	public
	*/
	function assignSaveObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
		}
		else
		{
			$_POST["id"] = $_POST["id"] ? $_POST["id"] : array();
			
			$assigned_roles_all = $rbacreview->assignedRoles($this->object->getId());
			$assigned_roles = array_intersect($assigned_roles_all,$_SESSION["role_list"]);
			
			//var_dump("<pre>",$_POST["id"],$assigned_roles_all,$_SESSION["role_list"],$assigned_roles,"</pre>");exit;
			
			if (empty($_POST["id"]) and (count($assigned_roles_all) == count($assigned_roles)))
			{
				$this->ilias->raiseError($this->lng->txt("msg_min_one_role")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
			}
			
			foreach (array_diff($assigned_roles,$_POST["id"]) as $role)
			{
				$rbacadmin->deassignUser($role,$this->object->getId());
			}

			foreach (array_diff($_POST["id"],$assigned_roles) as $role)
			{
				$rbacadmin->assignUser($role,$this->object->getId(),false);
			}
				
			$online_users = ilUtil::getUsersOnline();
			
			if (in_array($this->object->getId(),array_keys($online_users)))
			{
				$role_arr = $rbacreview->assignedRoles($this->object->getId());	
				
				if ($_SESSION["AccountId"] == $this->object->getId())
				{
					$_SESSION["RoleId"] = $role_arr;
				}
				else
				{
					$roles = "RoleId|".serialize($role_arr);
					$modified_data = preg_replace("/RoleId.*?;\}/",$roles,$online_users[$this->object->getId()]["data"]);
			
					$q = "UPDATE usr_session SET data='".$modified_data."' WHERE user_id = '".$this->object->getId()."'";
					$this->ilias->db->query($q);
				}
			}
		}

		// update object data entry (to update last modification date)
		$this->object->update();		

		sendInfo($this->lng->txt("msg_roleassignment_changed"),true);
		
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=roleassignment&sort_by=".$_GET["sort_by"]."&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);
		exit();
	}

	/**
	* display roleassignment panel
	* 
	* @access	public
	*/
	function roleassignmentObject ()
	{
		global $rbacreview;
		
		$obj_str = "&obj_id=".$this->obj_id;
				
		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "type", "role", "desc", "context");

		// get all assignable roles
		$all_roles = getObjectList("role");
		
		foreach ($all_roles as $key => $val)
		{
			// fetch context path of role
			$rolf = $rbacreview->getFoldersAssignedToRole($val["obj_id"],true);
			
			$path = "";		
					
			$tmpPath = $this->tree->getPathFull($rolf[0]);		

			// count -1, to exclude the role folder itself
			for ($i = 1; $i < (count($tmpPath)-1); $i++)
			{
				if ($path != "")
				{
					$path .= " > ";
				}

				$path .= $tmpPath[$i]["title"];						
			}	

			//visible data part
			$this->data["data"][] = array(
						"type"			=> $val["type"],
						"role"			=> $val["title"],
						"desc"			=> $val["desc"],
						//"last_change"	=> $val["last_update"],
						"context"		=> $path,
						"obj_id"		=> $val["obj_id"]
					);
		} //foreach role

		$this->maxcount = count($this->data["data"]);

		// TODO: correct this in objectGUI
		if ($_GET["sort_by"] == "title")
		{
			$_GET["sort_by"] = "role";
		}		
		
		// sorting array
		include_once "./include/inc.sort.php";
		$this->data["data"] = sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		$assigned_roles = $rbacreview->assignedRoles($this->object->getId());

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$checked = in_array($this->data["data"][$key]["obj_id"],$assigned_roles);

			$this->data["ctrl"][$key] = array(
											"ref_id"	=> $this->id,
											"obj_id"	=> $val["obj_id"],
											"type"		=> $val["type"],
											"assigned"	=> $checked
											);
			$tmp[] = $val["obj_id"];

			unset($this->data["data"][$key]["obj_id"]);

			//$this->data["data"][$key]["last_change"] = ilFormat::formatDate($this->data["data"][$key]["last_change"]);
		}

		// remember filtered users
		$_SESSION["role_list"] = $tmp;		
	
		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id.$obj_str."&cmd=assignSave&sort_by=".$_GET["sort_by"]."&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);

		include_once "./classes/class.ilTableGUI.php";

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("role_assignment"),"icon_".$this->object->getType()."_b.gif",$this->lng->txt("obj_".$this->object->getType()));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}
		
		$tbl->setHeaderNames($header_names);
		
		$header_params = array(
								"ref_id"	=> $this->ref_id,
								"obj_id"	=> $this->obj_id,
								"cmd"		=> "roleassignment"
							  );

		$tbl->setHeaderVars($this->data["cols"],$header_params);
		//$tbl->setColumnWidth(array("4","","15%","30%","24%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));	

		// display action button
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "assignSave");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("change_assignment"));
		$this->tpl->parseCurrentBlock();

		// display arrow
		$this->tpl->touchBlock("tbl_action_row");
	
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				//var_dump("<pre>",$ctrl,"</pre>");
				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				($ctrl["assigned"]) ? $checked = "checked=\"checked\"" : $checked = "";
				
				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID", $ctrl["obj_id"]);
				$this->tpl->setVariable("CHECKED", $checked);
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
	

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					$this->tpl->setCurrentBlock("text");

					if ($key == "type")
					{
						$val = ilUtil::getImageTagByType($val,$this->tpl->tplPath);						
					}

					$this->tpl->setVariable("TEXT_CONTENT", $val);					
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();
				} //foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for

		} //if is_array
	}
} // END class.ilObjUserGUI
?>
