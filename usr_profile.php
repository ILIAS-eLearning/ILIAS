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
* change user profile
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

// added by ratana ty to read image directory
// for upload file
require_once "./classes/class.ilSetup.php";
// TODO: use $ilias->ini->getVariable instead using setup class to fetch webspace_path
// Read from ini file webspace dir ./docss
require_once "classes/class.ilSetup.php";
$mySetup = new ilSetup();
$mySetup->readIniFile();
$webspace_dir = $mySetup->getWebspacePath();


//$image_dir = $webspace_dir."/usr_images";

// purpose is to upload file of user
// function added by ratana ty
function upload_file()
{
// TODO
// Check the type of file and then check the size
// of the file whether we allow people to upload or not
	global $userfile, $userfile_name, $userfile_size,
	$userfile_type, $archive_dir, $WINDIR, $webspace_dir,$ilias;
	global $target_file, $return_path;

	$image_dir = $webspace_dir."/usr_images";
	if(!@is_dir($image_dir))		// this is done in setup.php, line 518
	{
		mkdir($image_dir);
		chmod($image_dir, 0770);
	}

	$path_info = pathinfo($_FILES["userfile"]["name"]);
	$target_file = $image_dir."/usr_".$ilias->account->getId()."."."jpg";
	$store_file = "usr_".$ilias->account->getID()."."."jpg";
	$ilias->account->setPref("profile_image", $store_file);
	$ilias->account->update();
	move_uploaded_file($_FILES["userfile"]["tmp_name"],$target_file);
	//echo "from:".$_FILES["userfile"]["tmp_name"]."to:".$target_file."<br>";
	// by default after copy it will loss
	// some permission so set it to readable
	chmod($target_file, 0770);

	// got file name (ex-usr_6.jpg) change then convert it to
	// the appropriate size and only jpg format
	$rename_file = "usr_".$ilias->account->getId().
		".".$path_info["extension"];
	$part = explode(".",$rename_file);
	$show_file = $image_dir."/".$part[0].".jpg";

	//convert -size 1000x1000 usr_6.jpg usr_66.jpg
	system("convert -size 100x100 $target_file $show_file");

	return $target_file;
}
// End of function upload file

$tpl->addBlockFile("CONTENT", "content", "tpl.usr_profile.html");

// display infopanel if something happened
infoPanel();

$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

// set locator 
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->touchBlock("locator_separator");
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("personal_desktop"));
$tpl->setVariable("LINK_ITEM", "usr_personaldesktop.php");
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("personal_profile"));
$tpl->setVariable("LINK_ITEM", "usr_profile.php");
$tpl->parseCurrentBlock();

//add template for buttons
$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

// display buttons
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_profile.php");
$tpl->setVariable("BTN_TXT",$lng->txt("personal_profile"));
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_password.php");
$tpl->setVariable("BTN_TXT",$lng->txt("chg_password"));
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_agreement.php");
$tpl->setVariable("BTN_TXT",$lng->txt("usr_agreement"));
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_bookmarks.php?cmd=frameset");
$tpl->setVariable("BTN_TXT",$lng->txt("bookmarks"));
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_personaldesktop.php?cmd=whois");
$tpl->setVariable("BTN_TXT",$lng->txt("who_is_online"));
$tpl->parseCurrentBlock();

$tpl->touchBlock("btn_row");


// To display picture after Uploaded
//$tpl->setVariable("IMAGE_PATH","./".$webspace_dir."/usr_images/".$ilias->account->prefs["profile_image"]);

// if data are posted check on upload button


//if data are posted
if ($_GET["cmd"] == "save")
{
	if(!empty($_POST["usr_upload"]))
	{
	upload_file();
	//echo "./".$target_file;
	//exit;
	//2$tpl->setVariable("IMAGE_PATH", "./".$target_file);

	//3header ("Location: usr_profile.php");
	//4exit;
	}
	//init checking var
	$form_valid = true;


	// testing by ratana ty:
	// if people check on check box it will
	// write some datata to table usr_pref
	// if check on Public Profile
	if (($_POST["chk_pub"])=="on")
	{
		$ilias->account->setPref("public_profile","y");
	}
	else
	{
		$ilias->account->setPref("public_profile","n");
	}

	// if check on Institute
	if (($_POST["chk_institute"])=="on")
	{
		$ilias->account->setPref("public_institution","y");
	}
	else
	{
		$ilias->account->setPref("public_institution","n");
	}
	//if check on picture upload
	if (($_POST["chk_upload"])=="on")
	{
		$ilias->account->setPref("public_upload","y");
	}
	else
	{
		$ilias->account->setPref("public_upload","n");
	}

	// if check on Street
	if (($_POST["chk_street"])=="on")
	{
		$ilias->account->setPref("public_street","y");
	}
	else
	{
		$ilias->account->setPref("public_street","n");
	}

	// if check on Zip Code
	if (($_POST["chk_zip"])=="on")
	{
		$ilias->account->setPref("public_zip","y");
	}
	else
	{
		$ilias->account->setPref("public_zip","n");
	}

	// if check on City
	if (($_POST["chk_city"])=="on")
	{
		$ilias->account->setPref("public_city","y");
	}
	else
	{
		$ilias->account->setPref("public_city","n");
	}

	// if check on Country
	if (($_POST["chk_country"])=="on")
	{
		$ilias->account->setPref("public_country","y");
	}
	else
	{
		$ilias->account->setPref("public_country","n");
	}

	// if check on Phone
	if (($_POST["chk_phone"])=="on")
	{
		$ilias->account->setPref("public_phone","y");
	}
	else
	{
		$ilias->account->setPref("public_phone","n");
	}

	// if check on Email address
	if (($_POST["chk_email"])=="on")
	{
		$ilias->account->setPref("public_email","y");
	}
	else
	{
		$ilias->account->setPref("public_email","n");
	}

		// if check on Email address
	if (($_POST["chk_hobby"])=="on")						// here
	{
		$ilias->account->setPref("public_hobby","y");
	}
	else
	{
		$ilias->account->setPref("public_hobby","n");
	}
	// end of testing by ratana ty

	// check required fields
	if (empty($_POST["usr_fname"]) or empty($_POST["usr_lname"])
		 or empty($_POST["usr_email"]))
	{
		sendInfo($lng->txt("fill_out_all_required_fields"));
		$form_valid = false;
	}

	// check email adress
	if (!ilUtil::is_email($_POST["usr_email"]) and !empty($_POST["usr_email"]) and $form_valid)
	{
		sendInfo($lng->txt("email_not_valid"));
		$form_valid = false;
	}

	//update user data (not saving!)
	$ilias->account->setFirstName($_POST["usr_fname"]);
	$ilias->account->setLastName($_POST["usr_lname"]);
	$ilias->account->setGender($_POST["usr_gender"]);
	$ilias->account->setUTitle($_POST["usr_title"]);
	$ilias->account->setFullname();
	// added for upload by ratana ty
	//$ilias->account->setFile($_POST["usr_file"]);
	$ilias->account->setInstitution($_POST["usr_institution"]);
	$ilias->account->setStreet($_POST["usr_street"]);
	$ilias->account->setZipcode($_POST["usr_zipcode"]);
	$ilias->account->setCity($_POST["usr_city"]);
	$ilias->account->setCountry($_POST["usr_country"]);
	$ilias->account->setPhone($_POST["usr_phone"]);
	$ilias->account->setEmail($_POST["usr_email"]);
	$ilias->account->setHobby($_POST["usr_hobby"]);

	$ilias->account->setLanguage($_POST["usr_language"]);

	// everthing's ok. save form data
	if ($form_valid)
	{
		// init reload var. page should only be reloaded if skin or style were changed
		$reload = false;

		//set user skin
		if ($_POST["usr_skin"] != "" and $_POST["usr_skin"] != $ilias->account->getPref("skin"))
		{
			$ilias->account->setPref("skin", $_POST["usr_skin"]);
			$reload = true;
		}
		else	// set user style only if skin wasn't changed
		{
			if ($_POST["usr_style"] != "" and $_POST["usr_style"] != $ilias->account->getPref("style"))
			{
				$ilias->account->setPref("style", $_POST["usr_style"]);
				$reload = true;
			}
		}

		// save user data & object_data
		$ilias->account->setTitle($ilias->account->getFullname());
		$ilias->account->setDescription($ilias->account->getEmail());
		$ilias->account->update();
		//upload_file();

		// this is not needed because the object_data entry is updated by ilObject
		// update object_data
		//include_once "classes/class.ilObjUser.php";
		//$userObj = new ilObjUser($ilias->account->getId());
		//$userObj->setTitle($ilias->account->getFullname());
		//$userObj->setDescription($ilias->account->getEmail());
		//$userObj->update();
		
		//$userObj->setTitle($ilias->account->getFullname());
		//$userObj->setDescription($ilias->account->getEmail());
		//$userObj->update();

		// reload page only if skin or style were changed
		if ($reload)
		{
			// feedback
			sendInfo($lng->txt("saved_successfully"));
			$tpl->setVariable("RELOAD","<script language=\"Javascript\">\ntop.location.href = \"./start.php\";\n</script>\n");
		}
		else
		{
			// feedback
			sendInfo($lng->txt("saved_successfully"),true);

			header ("Location: usr_personaldesktop.php");
			exit();
		}

	}
}


//get all languages
$languages = $lng->getInstalledLanguages();

//go through languages
foreach ($languages as $lang_key)
{
	$tpl->setCurrentBlock("sel_lang");
	$tpl->setVariable("LANG", $lng->txt("lang_".$lang_key));
	$tpl->setVariable("LANGSHORT", $lang_key);

	if ($ilias->account->prefs["language"] == $lang_key)
	{
		$tpl->setVariable("SELECTED_LANG", "selected=\"selected\"");
	}

	$tpl->parseCurrentBlock();
}

//what gui's are available for ilias?
$ilias->getSkins();

foreach ($ilias->skins as $row)
{
	$tpl->setCurrentBlock("selectskin");

	if ($ilias->account->skin == $row["name"])
	{
		$tpl->setVariable("SKINSELECTED", "selected=\"selected\"");
	}

	$tpl->setVariable("SKINVALUE", $row["name"]);
	$tpl->setVariable("SKINOPTION", $row["name"]);
	$tpl->parseCurrentBlock();
}

//what styles are available for current skin
$ilias->getStyles($ilias->account->skin);

foreach ($ilias->styles as $row)
{
	$tpl->setCurrentBlock("selectstyle");

	if ($ilias->account->prefs["style"] == $row["name"])
	{
		$tpl->setVariable("STYLESELECTED", "selected=\"selected\"");
	}

	$tpl->setVariable("STYLEVALUE", $row["name"]);
	$tpl->setVariable("STYLEOPTION", $row["name"]);
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("content");
$tpl->setVariable("FORMACTION", "usr_profile.php?cmd=save");

$tpl->setVariable("TXT_PAGEHEADLINE",$lng->txt("personal_profile"));
$tpl->setVariable("TXT_OF",strtolower($lng->txt("of")));
$tpl->setVariable("USR_FULLNAME",$ilias->account->getFullname());

$tpl->setVariable("TXT_USR_DATA", $lng->txt("userdata"));
$tpl->setVariable("TXT_NICKNAME", $lng->txt("username"));
$tpl->setVariable("TXT_PUBLIC_PROFILE", $lng->txt("public_profile"));
$tpl->setVariable("TXT_SALUTATION", $lng->txt("salutation"));
$tpl->setVariable("TXT_SALUTATION_M", $lng->txt("salutation_m"));
$tpl->setVariable("TXT_SALUTATION_F",$lng->txt("salutation_f"));
$tpl->setVariable("TXT_FIRSTNAME",$lng->txt("firstname"));
// todo
// capture image name including path ($archive_dir/$filename)
//$tpl->setVariable("IMAGE_PATH",$return_path);
//$tpl->setVariable("IMAGE_PATH",'$archive_dir."/".$filename');

$tpl->setVariable("TXT_LASTNAME",$lng->txt("lastname"));
$tpl->setVariable("TXT_TITLE",$lng->txt("title"));
$tpl->setVariable("TXT_UPLOAD",$lng->txt("personal_picture"));
$tpl->setVariable("UPLOAD",$lng->txt("upload"));
$tpl->setVariable("TXT_FILE", $lng->txt("userfile"));
$tpl->setVariable("USER_FILE", $lng->txt("user_file"));

$tpl->setVariable("TXT_INSTITUTION",$lng->txt("institution"));
$tpl->setVariable("TXT_STREET",$lng->txt("street"));
$tpl->setVariable("TXT_ZIPCODE",$lng->txt("zipcode"));
$tpl->setVariable("TXT_CITY",$lng->txt("city"));
$tpl->setVariable("TXT_COUNTRY",$lng->txt("country"));
$tpl->setVariable("TXT_PHONE",$lng->txt("phone"));
$tpl->setVariable("TXT_EMAIL",$lng->txt("email"));
$tpl->setVariable("TXT_HOBBY",$lng->txt("hobby"));					// here
$tpl->setVariable("TXT_DEFAULT_ROLE",$lng->txt("default_role"));
$tpl->setVariable("TXT_LANGUAGE",$lng->txt("language"));
$tpl->setVariable("TXT_USR_SKIN",$lng->txt("usr_skin"));
$tpl->setVariable("TXT_USR_STYLE",$lng->txt("usr_style"));
$tpl->setVariable("TXT_PERSONAL_DATA", $lng->txt("personal_data"));
$tpl->setVariable("TXT_CONTACT_DATA", $lng->txt("contact_data"));
$tpl->setVariable("TXT_SETTINGS", $lng->txt("settings"));

//values
$tpl->setVariable("NICKNAME", $ilias->account->getLogin());
$tpl->setVariable("SELECTED_".strtoupper($ilias->account->getGender()), "selected");
$tpl->setVariable("FIRSTNAME", $ilias->account->getFirstname());
$tpl->setVariable("LASTNAME", $ilias->account->getLastname());

$tpl->setVariable("TITLE", $ilias->account->getUTitle());
$tpl->setVariable("INSTITUTION", $ilias->account->getInstitution());
$tpl->setVariable("STREET", $ilias->account->getStreet());
$tpl->setVariable("ZIPCODE", $ilias->account->getZipcode());
$tpl->setVariable("CITY", $ilias->account->getCity());
$tpl->setVariable("COUNTRY", $ilias->account->getCountry());
$tpl->setVariable("PHONE", $ilias->account->getPhone());
$tpl->setVariable("EMAIL", $ilias->account->getEmail());
$tpl->setVariable("HOBBY", $ilias->account->getHobby());		// here

require_once "./classes/class.ilObjRole.php";
$roleObj = new ilObjRole($rbacadmin->getDefaultRole($_SESSION["AccountId"]));
$tpl->setVariable("DEFAULT_ROLE",$roleObj->getTitle());

$tpl->setVariable("TXT_REQUIRED_FIELDS",$lng->txt("required_field"));
//button
$tpl->setVariable("TXT_SAVE",$lng->txt("save"));
// addeding by ratana ty
$tpl->setVariable("UPLOAD", $lng->txt("upload"));
// end adding
// Testing by ratana ty
// Show check if value in table usr_pref is y
//
if($ilias->account->prefs["public_profile"]=="y")
{
	$tpl->setVariable("CHK_PUB","checked");
}
if($ilias->account->prefs["public_upload"]=="y")
{
	$tpl->setVariable("CHK_UPLOAD","checked");
}
if($ilias->account->prefs["public_institution"]=="y")
{
	$tpl->setVariable("CHK_INSTITUTE","checked");
}
if($ilias->account->prefs["public_street"]=="y")
{
	$tpl->setVariable("CHK_STREET","checked");
}
if($ilias->account->prefs["public_zip"]=="y")
{
	$tpl->setVariable("CHK_ZIP","checked");
}
if($ilias->account->prefs["public_city"]=="y")
{
	$tpl->setVariable("CHK_CITY","checked");
}
if($ilias->account->prefs["public_country"]=="y")
{
	$tpl->setVariable("CHK_COUNTRY","checked");
}
if($ilias->account->prefs["public_phone"]=="y")
{
	$tpl->setVariable("CHK_PHONE","checked");
}
if($ilias->account->prefs["public_email"]=="y")
{
	$tpl->setVariable("CHK_EMAIL","checked");
}
if($ilias->account->prefs["public_hobby"]=="y")			// here
{
	$tpl->setVariable("CHK_HOBBY","checked");
}
// End of shwing
// Testing by ratana ty

$tpl->parseCurrentBlock();
$tpl->show();
?>
