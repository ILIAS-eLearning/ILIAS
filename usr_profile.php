<?php
/**
* change user profile
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";

$tplmain->setVariable("TXT_PAGETITLE","ILIAS - ".$lng->txt("profile"));

//display buttons
$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","usr_profile.php");
$tplbtn->setVariable("BTN_TXT",$lng->txt("personal_profile"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","usr_password.php");
$tplbtn->setVariable("BTN_TXT",$lng->txt("chg_password"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","usr_agreement.php");
$tplbtn->setVariable("BTN_TXT",$lng->txt("usr_agreement"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

//instantiate profile-template
$tpl = new Template("tpl.usr_profile.html", true, true);
$tpl->setVariable("BUTTONS",$tplbtn->get());

//if data are posted
if ($_POST["u_id"]!="")
{
	$ilias->account->setFirstName($_POST["usr_fname"]);
	$ilias->account->setLastName($_POST["usr_lname"]);
	$ilias->account->setGender($_POST["usr_gender"]);
	$ilias->account->setTitle($_POST["usr_title"]);
	$ilias->account->setEmail($_POST["usr_email"]);
	$ilias->account->setLanguage($_POST["usr_language"]);

	//set user skin
	$ilias->account->writePref("skin", $_POST["usr_skin"]);
	
	if ($ilias->account->update() == false)
	{
		$tpl->setCurrentBlock("message");
		$tpl->setVariable("MSG", $lng->txt($ilias->account->getErrorMsg()));
		$tpl->parseCurrentBlock();
	}
	else
	{
//	commented out by pgabriel 2002-08-06: 
// better use header location for eventual language change (then languages change
// have direct effect)
		header("location: ./usr_profile.php");
//		$tpl->setCurrentBlock("message");
//		$tpl->setVariable("MSG", $lng->txt("msg_changes_ok"));
//		$tpl->parseCurrentBlock();		
	}
}

//get all languages
$langs = $lng->getInstalledLanguages();

//go through languages
foreach ($langs as $row)
{
	$tpl->setCurrentBlock("sel_lang");
	$tpl->setVariable("LANG", $row["name"]);
	$tpl->setVariable("LANGSHORT", $row["id"]);
	if ($ilias->account->prefs["language"] == $row["id"])
	{
		$tpl->setVariable("SELECTED_LANG", "selected");
	}
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("TXT_PAGEHEADLINE",$lng->txt("profile"));

$tpl->setVariable("TXT_USR_DATA", $lng->txt("userdata"));
$tpl->setVariable("TXT_NICKNAME", $lng->txt("username"));
$tpl->setVariable("TXT_SALUTATION", $lng->txt("salutation"));
$tpl->setVariable("TXT_SALUTATION_M", $lng->txt("salutation_m"));
$tpl->setVariable("TXT_SALUTATION_F",$lng->txt("salutation_f"));
$tpl->setVariable("TXT_FIRSTNAME",$lng->txt("forename"));
$tpl->setVariable("TXT_LASTNAME",$lng->txt("lastname"));
$tpl->setVariable("TXT_TITLE",$lng->txt("title"));
$tpl->setVariable("TXT_INSTITUTION",$lng->txt("institution"));
$tpl->setVariable("TXT_STREET",$lng->txt("street"));
$tpl->setVariable("TXT_ZIPCODE",$lng->txt("zip_code"));
$tpl->setVariable("TXT_CITY",$lng->txt("city"));
$tpl->setVariable("TXT_COUNTRY",$lng->txt("country"));
$tpl->setVariable("TXT_PHONE",$lng->txt("phone"));
$tpl->setVariable("TXT_EMAIL",$lng->txt("email"));
$tpl->setVariable("TXT_STATUS",$lng->txt("status"));
$tpl->setVariable("TXT_GUEST",$lng->txt("guest"));
$tpl->setVariable("TXT_STUDENT",$lng->txt("student"));
$tpl->setVariable("TXT_EMPLOYEE",$lng->txt("employee"));
$tpl->setVariable("TXT_SYS_GRP",$lng->txt("system_grp"));
$tpl->setVariable("TXT_LANGUAGE",$lng->txt("language"));
$tpl->setVariable("TXT_USR_SKIN",$lng->txt("usr_skin"));



//what gui's are available for ilias?
$ilias->getSkins();

foreach ($ilias->skins as $row)
{
	$tpl->setCurrentBlock("selectskin");
	if ($ilias->account->prefs["skin"] == $row["name"])
	{
		$tpl->setVariable("SKINSELECTED", "selected");
	}
	$tpl->setVariable("SKINVALUE", $row["name"]);
	$tpl->setVariable("SKINOPTION", $row["name"]);
	$tpl->parseCurrentBlock();
}

//values
$tpl->setVariable("NICKNAME", $ilias->account->data["login"]);
$tpl->setVariable("FIRSTNAME", $ilias->account->data["FirstName"]);
$tpl->setVariable("LASTNAME", $ilias->account->data["SurName"]);
$tpl->setVariable("EMAIL", $ilias->account->data["Email"]);
$tpl->setVariable("SELECTED_".strtoupper($ilias->account->data["Gender"]), "selected");
$tpl->setVariable("TITLE", $ilias->account->data["Title"]);
$tpl->setVariable("INSTITUTION", $ilias->account->data["inst"]);
$tpl->setVariable("CITY", $ilias->account->data["city"]);
$tpl->setVariable("ZIPCODE", $ilias->account->data["zipcode"]);
$tpl->setVariable("PHONE", $ilias->account->data["phone"]);

$tpl->setVariable("SYS_GRP", $lng->txt("administrator"));
//button
$tpl->setVariable("BTN_SUBMIT",$lng->txt("submit"));


$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>