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
* $Id$Id: class.ilObjUserGUI.php,v 1.22 2003/06/06 14:09:13 akill Exp $
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


function public_profile()
{
			global $ilias;
			// read object data
			$q = "SELECT * FROM usr_pref ".
			 	"WHERE usr_id='".$_GET["user"]."'".
				"AND value ='y'".
				"AND keyword='public_profile'";
			$result = $ilias->db->query($q);

			// check number of records
			if ($row = $result->fetchRow())
			{
				return $row["2"];
			}
}

function public_upload()
{
			global $ilias;
			// read object data
			$q = "SELECT * FROM usr_pref ".
			 	"WHERE usr_id='".$_GET["user"]."'".
				"AND value ='y'".
				"AND keyword='public_upload'";
			$result = $ilias->db->query($q);

			// check number of records
			if ($row = $result->fetchRow())
			{
				return $row["2"];
			}
}

function public_institution()
{
			global $ilias;
			// read object data
			$q = "SELECT * FROM usr_pref ".
			 	"WHERE usr_id='".$_GET["user"]."'".
				"AND value ='y'".
				"AND keyword='public_institution'";
			$result = $ilias->db->query($q);

			// check number of records
			if ($row = $result->fetchRow())
			{
				return $row["2"];
			}
}


function public_street()
{
			global $ilias;
			// read object data
			$q = "SELECT * FROM usr_pref ".
			 	"WHERE usr_id='".$_GET["user"]."'".
				"AND value ='y'".
				"AND keyword='public_street'";
			$result = $ilias->db->query($q);

			// check number of records
			if ($row = $result->fetchRow())
			{
				return $row["2"];
			}
}


function public_zip()
{
			global $ilias;
			// read object data
			$q = "SELECT * FROM usr_pref ".
			 	"WHERE usr_id='".$_GET["user"]."'".
				"AND value ='y'".
				"AND keyword='public_zip'";
			$result = $ilias->db->query($q);

			// check number of records
			if ($row = $result->fetchRow())
			{
				return $row["2"];
			}
}

function public_city()
{
			global $ilias;
			// read object data
			$q = "SELECT * FROM usr_pref ".
			 	"WHERE usr_id='".$_GET["user"]."'".
				"AND value ='y'".
				"AND keyword='public_city'";
			$result = $ilias->db->query($q);

			// check number of records
			if ($row = $result->fetchRow())
			{
				return $row["2"];
			}
}

function public_country()
{
			global $ilias;
			// read object data
			$q = "SELECT * FROM usr_pref ".
			 	"WHERE usr_id='".$_GET["user"]."'".
				"AND value ='y'".
				"AND keyword='public_country'";
			$result = $ilias->db->query($q);

			// check number of records
			if ($row = $result->fetchRow())
			{
				return $row["2"];
			}
}

function public_phone()
{
			global $ilias;
			// read object data
			$q = "SELECT * FROM usr_pref ".
			 	"WHERE usr_id='".$_GET["user"]."'".
				"AND value ='y'".
				"AND keyword='public_phone'";
			$result = $ilias->db->query($q);

			// check number of records
			if ($row = $result->fetchRow())
			{
				return $row["2"];
			}
}


function public_email()
{
			global $ilias;
			// read object data
			$q = "SELECT * FROM usr_pref ".
			 	"WHERE usr_id='".$_GET["user"]."'".
				"AND value ='y'".
				"AND keyword='public_email'";
			$result = $ilias->db->query($q);

			// check number of records
			if ($row = $result->fetchRow())
			{
				return $row["2"];
			}
}

	// new method
	// To display information according check box on Destop
function insertPublicProfile($a_template_var, $a_template_block_name)
{
		global $tpl;

		$tpl->addBlockFile($a_template_var, $a_template_block_name, "tpl.usr_public_profile.html");
		$tpl->setCurrentBlock($a_template_block_name);

		// Get name of picture of user

		require_once "classes/class.ilObjUser.php";
  		$userObj = new ilObjUser($_GET["user"]);
		$tpl->setVariable("ROWCOL1", "tblrow1");
		$tpl->setVariable("ROWCOL2", "tblrow2");
		//if (usr_id == $_GET["user"])

 		// Check from Database if value
		// of public_profile = "y" show user infomation
		if($this->public_profile()=="y") {
  		$tpl->setVariable("TXT_NAME","Name");
		$tpl->setVariable("FIRSTNAME",$userObj->getFirstName());
		$tpl->setVariable("LASTNAME",$userObj->getLastName());
		$tpl->setVariable("TXT_TITLE","Title");
		$tpl->setVariable("TITLE",$userObj->getTitle());
		}
		else
		{
		$tpl->setVariable("TXT_NAME","Name");

		$tpl->setVariable("FIRSTNAME","N /");
		$tpl->setVariable("LASTNAME","A");
		$tpl->setVariable("TXT_TITLE","Title");
		$tpl->setVariable("TITLE","N/A");
		}


		// Check from Database if value
		// of image"y" show user infomation
		// Todo: make path of image flexible
		if($this->public_upload()=="y") {
		$tpl->setVariable("TXT_IMAGE","Image");
$tpl->setVariable("IMAGE_PATH","./docss/usr_images/".$userObj->getPref("profile_image"));
		//echo "./docss/usr_images/".$userObj->getPref("profile_image");
		//exit;
		}
		else
		{
		$tpl->setVariable("TXT_IMAGE","Image");
		// Todo: point to anonymous picture
		}

		// Check from Database if value
		// "y" show institute information
		if($this->public_institution()=="y") {
		$tpl->setVariable("TXT_INSTITUTE","Institution");
		$tpl->setVariable("INSTITUTE",$userObj->getInstitution());
		}
		else
		{
		$tpl->setVariable("TXT_INSTITUTE","Institution");
		$tpl->setVariable("INSTITUTE","N / A");
		}

		// Check from Database if value
		// "y" show institute information
		if($this->public_street()=="y") {
		$tpl->setVariable("TXT_STREET","Street");
		$tpl->setVariable("STREET",$userObj->getStreet());
		}
		else
		{
		$tpl->setVariable("TXT_STREET","Street");
		$tpl->setVariable("STREET","N / A");
		}

		// Check from Database if value
		// "y" show zip code information
		if($this->public_zip()=="y") {
		$tpl->setVariable("TXT_ZIPCODE","Zip Code");
		$tpl->setVariable("ZIPCODE",$userObj->getZipcode());
		}
		else
		{
		$tpl->setVariable("TXT_ZIPCODE","Zip Code");
		$tpl->setVariable("ZIPCODE","N / A");
		}

		// Check from Database if value
		// "y" show city information
		if($this->public_city()=="y") {
		$tpl->setVariable("TXT_CITY","City");
		$tpl->setVariable("CITY",$userObj->getCity());
		}
		else
		{
		$tpl->setVariable("TXT_CITY","City");
		$tpl->setVariable("CITY","N / A");
		}

		// Check from Database if value
		// "y" show country information
		if($this->public_country()=="y") {
		$tpl->setVariable("TXT_COUNTRY","Country");
		$tpl->setVariable("COUNTRY",$userObj->getCountry());
		}
		else
		{
		$tpl->setVariable("TXT_COUNTRY","Country");
		$tpl->setVariable("COUNTRY","N / A");
		}

		// Check from Database if value
		// "y" show phone information
		if($this->public_phone()=="y") {
		$tpl->setVariable("TXT_PHONE","Phone");
		$tpl->setVariable("PHONE",$userObj->getPhone());
		}
		else
		{
		$tpl->setVariable("TXT_PHONE","Phone");
		$tpl->setVariable("PHONE","N / A");
		}

		// Check from Database if value
		// "y" show email information
		if($this->public_email()=="y") {
		$tpl->setVariable("TXT_EMAIL","Email");
		$tpl->setVariable("EMAIL",$userObj->getEmail());
		}
		else
		{
		$tpl->setVariable("TXT_EMAIL","Email");
		$tpl->setVariable("EMAIL","N / A");
		}
	$tpl->parseCurrentBlock($a_template_block_name);
}


	/**
	* display user create form
	*/
	function createObject()
	{
		global $tree,$tpl,$rbacsystem;

		if (!$rbacsystem->checkAccess('create', $_GET["ref_id"], $_POST["new_type"]))
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
			$this->tpl->setVariable("TXT_PASSWD2", $this->lng->txt("retype_password"));		

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

			// EMPTY SAVED VALUES
			unset($_SESSION["error_post_vars"]);
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

			// gender selection
			$gender = ilUtil::formSelect($this->object->gender,"Fobject[gender]",$this->gender);

			// role selection
			$obj_list = getObjectList("role");

			foreach ($obj_list as $obj_data)
			{
				$rol[$obj_data["obj_id"]] = $obj_data["title"];
			}

			$def_role = $rbacadmin->getDefaultRole($this->object->getId());
			$role = ilUtil::formSelectWoTranslation($def_role,"Fobject[default_role]",$rol);

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
			$data["fields"]["default_role"] = $role;

			$data["active_role"]["access"] = true;

			// BEGIN ACTIVE ROLE
			$assigned_roles = $rbacreview->assignedRoles($this->object->getId());

			foreach ($assigned_roles as $key => $role)
			{
				// BEGIN TABLE_ROLES
				require_once "classes/class.ilObjRole.php";
				$roleObj = new ilObjRole($role);

				if ($this->object->getId() == $_SESSION["AccountId"])
				{
					$data["active_role"]["access"] = true;
					$box = ilUtil::formCheckBox(in_array($role, $_SESSION["RoleId"]),'active[]',$role);
				}
				else
				{
					$data["active_role"]["access"] = false;
					$box = "";
				}

				$data["active_role"][$role]["checkbox"] = $box;
				$data["active_role"][$role]["title"] = $roleObj->getTitle();
				unset($roleObj);
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
		$this->tpl->setVariable("ACTIVE_ROLE_FORMACTION","adm_object.php?cmd=activeRoleSave&ref_id=".
								$_GET["ref_id"]."&obj_id=$_GET[obj_id]");
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
		require_once("classes/class.ilObjUser.php");
		$userObj = new ilObjUser();
		$userObj->assignData($_POST["Fobject"]);
		$userObj->setTitle($userObj->getFullname());
		$userObj->setDescription($userObj->getEmail());
		$userObj->create();

		//$user->setId($userObj->getId());

		//insert user data in table user_data
		$userObj->saveAsNew();

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
		//$tree->addTree($user->getId(), $settingObj->getRefId());

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
		require_once ("classes/class.ilMailbox.php");
		$mbox = new ilMailbox($userObj->getId());
		$mbox->createDefaultFolder();

		require_once "classes/class.ilFormatMail.php";
		$fmail = new ilFormatMail($userObj->getId());
		$fmail->createMailOptionsEntry();

		// create personal bookmark folder tree
		require_once "classes/class.ilBookmarkFolder.php";
		$bmf = new ilBookmarkFolder(0, $userObj->getId());
		$bmf->createNewBookmarkTree();


		sendInfo($this->lng->txt("user_added"),true);
		header("Location: adm_object.php?ref_id=".$this->ref_id);
		exit();
	}

	/**

	* updates user object
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
		$this->object->assignData($_POST["Fobject"]);
		if ($_POST["Fobject"]["passwd"] != "********")
		{
			$this->object->resetPassword($_POST["Fobject"]["passwd"],$_POST["Fobject"]["passwd2"]);
		}
		$this->object->updateLogin($_POST["Fobject"]["login"]);
		$this->object->setTitle($this->object->getFullname());
		$this->object->setDescription($this->object->getEmail());
		$this->update = $this->object->update();
		$rbacadmin->updateDefaultRole($_POST["Fobject"]["default_role"], $this->object->getId());

		// sent email
		if ($_POST["send_mail"] == "y")
		{
			require_once "classes/class.ilFormatMail.php";

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


	function activeRoleSaveObject()
	{
		if (!count($_POST["active"]))
		{
			$this->ilias->raiseError("You must leave one active role",$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION["RoleId"] = $_POST["active"];
		header("Location: adm_object.php?ref_id=$_GET[ref_id]&obj_id=$_GET[obj_id]&cmd=edit");
		exit;
	}

} // END class.UserObjectOut
?>
