<?php
/**
* forums_user_view
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.Forum.php";

$frm = new Forum();

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_user_view.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
// display infopanel if something happened
infoPanel();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK",$_GET["backurl"].".php?ref_id=".$_GET["ref_id"]."&thr_pk=".$_GET["thr_pk"]."&pos_pk=".$_GET["pos_pk"]."&offset=".$_GET["offset"]."&orderby=".$_GET["orderby"]);
$tpl->setVariable("BTN_TXT", $lng->txt("back"));
$tpl->parseCurrentBlock();

if (!$rbacsystem->checkAccess("read", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("userdata"));

// get user data
$author = $frm->getUser($_GET["user"]);	

$tpl->setCurrentBlock("usertable");

$tpl->setVariable("ROWCOL1", "tblrow1");
$tpl->setVariable("ROWCOL2", "tblrow2");

$tpl->setVariable("TXT_LOGIN", $lng->txt("login"));
$tpl->setVariable("LOGIN",$author->getLogin());	

$tpl->setVariable("TXT_NAME", $lng->txt("name"));
$tpl->setVariable("FIRSTNAME",$author->getFirstName());	
$tpl->setVariable("LASTNAME",$author->getLastName());	

$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TITLE",$author->getTitle());

$tpl->setVariable("TXT_GENDER", $lng->txt("gender"));
$tpl->setVariable("GENDER",$author->getGender());

$tpl->setVariable("TXT_EMAIL", $lng->txt("email"));

if ($rbacsystem->checkAccess("write", $_GET["ref_id"]))
{
	$tpl->setVariable("EMAIL","<a href=\"mailto:".$author->getEmail()."\">".$author->getEmail()."</a>");
}
else
{
	$tpl->setVariable("EMAIL",$author->Email());
}

$tpl->setVariable("TXT_REGISTERED", $lng->txt("registered_since"));
$tpl->setVariable("REGISTERED",$frm->convertDate($author->getCreateDate()));

// count articles of user
$numPosts = $frm->countUserArticles($_GET["user"]);
$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_posts"));
$tpl->setVariable("NUM_POSTS",$numPosts);

$tpl->parseCurrentBlock("usertable");

$tpl->show();
?>