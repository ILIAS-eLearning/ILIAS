<?php
/**
* mail search recipients,groups
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./include/inc.mail.php";
require_once "classes/class.User.php";
require_once "classes/class.Group.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.mail_search.html");
// LOCATOR
setLocator($_GET["mobj_id"],$_SESSION["AccountId"],$lng->txt("mail_mails_of"));

// BUTTONS
include "./include/inc.mail_buttons.php";

$tpl->setVariable("ACTION","mail_new.php?mobj_id=$_GET[mobj_id]&type=search_res");

// SET TXT VARIABLES
$tpl->setVariable("TXT_PERSONS",$lng->txt("persons"));
$tpl->setVariable("TXT_LOGIN",$lng->txt("login"));
$tpl->setVariable("TXT_FIRSTNAME",$lng->txt("firstname"));
$tpl->setVariable("TXT_LASTNAME",$lng->txt("lastname"));
$tpl->setVariable("TXT_GROUPS",$lng->txt("group"));
$tpl->setVariable("TXT_GROUP_NAME",$lng->txt("title"));
$tpl->setVariable("TXT_GROUP_DESC",$lng->txt("description"));
$tpl->setVariable("BUTTON_ADOPT",$lng->txt("adopt"));
$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));

$user = new User();
$users = $user->searchUsers(addslashes(urldecode($_GET["search"])));
if($users)
{
	$counter = 0;
	$tpl->setCurrentBlock("person_search");
	foreach($users as $user_data)
	{
		$tpl->setVariable("CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
		$tpl->setVariable("PERSON_LOGIN",$user_data["login"]);
		$tpl->setVariable("LOGIN",$user_data["login"]);
		$tpl->setVariable("FIRSTNAME",$user_data["firstname"]);
		$tpl->setVariable("LASTNAME",$user_data["lastname"]);
		$tpl->parseCurrentBlock();
	}
}
else
{
	$tpl->setCurrentBlock("no_content");
	$tpl->setVariable("TXT_PERSON_NO",$lng->txt("mail_search_person_no"));
	$tpl->parseCurrentBlock();
}
$group = new Group();
$groups = $group->searchGroups(addslashes(urldecode($_GET["search"])));
if($groups)
{
	$counter = 0;
	$tpl->setCurrentBlock("group_search");
	foreach($groups as $group_data)
	{
		$tpl->setVariable("GROUP_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
		$tpl->setVariable("GROUP_NAME","#".$group_data["title"]);
		$tpl->setVariable("GROUP_TITLE",$group_data["title"]);
		$tpl->setVariable("GROUP_DESC",$group_data["description"]);
		$tpl->parseCurrentBlock();
	}
}
else
{
	$tpl->setCurrentBlock("no_content");
	$tpl->setVariable("TXT_GROUP_NO",$lng->txt("mail_search_group_no"));
	$tpl->parseCurrentBlock();
}
 		
$tpl->show();
?>