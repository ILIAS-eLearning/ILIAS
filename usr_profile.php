<?php
/**
* change user profile
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.usr_profile.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

//display buttons
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
$tpl->setCurrentBlock("btn_row");
$tpl->parseCurrentBlock();

//if data are posted
if ($_GET["cmd"] == "save")
{
	//init checking var
	$form_valid = true;

	// check required fields
	if (empty($_POST["usr_fname"]) or empty($_POST["usr_lname"])
		 or empty($_POST["usr_email"]))
	{
		sendInfo($lng->txt("fill_out_all_required_fields"));
		$form_valid = false;
	}

	// check email adress
	if (!TUtil::is_email($_POST["usr_email"]) and !empty($_POST["usr_email"]) and $form_valid)
	{
		sendInfo($lng->txt("email_not_valid"));
		$form_valid = false;
	}

	//update user data (not saving!)
	$ilias->account->setFirstName($_POST["usr_fname"]);
	$ilias->account->setLastName($_POST["usr_lname"]);
	$ilias->account->setGender($_POST["usr_gender"]);
	$ilias->account->setTitle($_POST["usr_title"]);
	$ilias->account->setInstitution($_POST["usr_institution"]);
	$ilias->account->setStreet($_POST["usr_street"]);
	$ilias->account->setZipcode($_POST["usr_zipcode"]);
	$ilias->account->setCity($_POST["usr_city"]);
	$ilias->account->setCountry($_POST["usr_country"]);
	$ilias->account->setPhone($_POST["usr_phone"]);
	$ilias->account->setEmail($_POST["usr_email"]);
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
		
		// save user data
		$ilias->account->update();
		
		// update object_data
		require_once "classes/class.UserObject.php";
		$userObj = new UserObject($ilias->account->getId());
		$userObj->setTitle($ilias->account->getFullname());
		$userObj->setDescription($ilias->account->getEmail());
		$userObj->update();
		
		// feedback
		sendInfo($lng->txt("saved_successfully"),true);

		// reload page only if skin or style were changed
		if ($reload)
		{
			$tpl->setVariable("RELOAD","<script language=\"Javascript\">\ntop.location.href = \"./start.php\";\n</script>\n");
		}
		else
		{
			header ("Location: usr_personaldesktop.php");			
			exit;
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
$tpl->setVariable("TXT_SALUTATION", $lng->txt("salutation"));
$tpl->setVariable("TXT_SALUTATION_M", $lng->txt("salutation_m"));
$tpl->setVariable("TXT_SALUTATION_F",$lng->txt("salutation_f"));
$tpl->setVariable("TXT_FIRSTNAME",$lng->txt("firstname"));
$tpl->setVariable("TXT_LASTNAME",$lng->txt("lastname"));
$tpl->setVariable("TXT_TITLE",$lng->txt("title"));
$tpl->setVariable("TXT_INSTITUTION",$lng->txt("institution"));
$tpl->setVariable("TXT_STREET",$lng->txt("street"));
$tpl->setVariable("TXT_ZIPCODE",$lng->txt("zipcode"));
$tpl->setVariable("TXT_CITY",$lng->txt("city"));
$tpl->setVariable("TXT_COUNTRY",$lng->txt("country"));
$tpl->setVariable("TXT_PHONE",$lng->txt("phone"));
$tpl->setVariable("TXT_EMAIL",$lng->txt("email"));
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
$tpl->setVariable("TITLE", $ilias->account->getTitle());
$tpl->setVariable("INSTITUTION", $ilias->account->getInstitution());
$tpl->setVariable("STREET", $ilias->account->getStreet());
$tpl->setVariable("ZIPCODE", $ilias->account->getZipcode());
$tpl->setVariable("CITY", $ilias->account->getCity());
$tpl->setVariable("COUNTRY", $ilias->account->getCountry());
$tpl->setVariable("PHONE", $ilias->account->getPhone());
$tpl->setVariable("EMAIL", $ilias->account->getEmail());


$obj = getObject($rbacadmin->getDefaultRole($_SESSION["AccountId"]));
$tpl->setVariable("DEFAULT_ROLE",$obj["title"]);

$tpl->setVariable("TXT_REQUIRED_FIELDS",$lng->txt("required_field"));
//button
$tpl->setVariable("TXT_SAVE",$lng->txt("save"));

$tpl->parseCurrentBlock();

$tpl->show();
?>