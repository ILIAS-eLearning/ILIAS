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
* forums_user_view
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilForum.php";

$lng->loadLanguageModule("forum");

$ref_obj =& ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
if($ref_obj->getType() == "frm")
{
	$forumObj = new ilObjForum($_GET["ref_id"]);
	$frm =& $forumObj->Forum;
	$frm->setForumId($forumObj->getId());
	$frm->setForumRefId($forumObj->getRefId());
}
else
{
	$frm =& new ilForum();
}

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_user_view.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

// locator
require_once("classes/class.ilForumLocatorGUI.php");
$frm_loc =& new ilForumLocatorGUI();
$frm_loc->setRefId($_GET["ref_id"]);
if ($ref_obj->getType() == "frm")
{
	$frm_loc->setForum($frm);
}
if (!empty($_GET["thr_pk"]))
{
	$frm->setWhereCondition("thr_pk = ".$_GET["thr_pk"]);
	$threadData = $frm->getOneThread();
	$frm_loc->setThread($_GET["thr_pk"], $threadData["thr_subject"]);
}
$frm_loc->showUser(true);
$frm_loc->display();

require_once ("classes/class.ilObjUserGUI.php");

$_GET["obj_id"]=$_GET["user"];
$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
// count articles of user
$numPosts = $frm->countUserArticles($_GET["user"]);
$add = array($lng->txt("forums_posts") => $numPosts);
$user_gui->insertPublicProfile("USR_PROFILE","usr_profile", $add);

// display infopanel if something happened
infoPanel();

//$tpl->setCurrentBlock("usertable");
if($_GET['backurl'])
{
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK",urldecode($_GET["backurl"]));
	$tpl->setVariable("BTN_TXT", $lng->txt("back"));
	$tpl->parseCurrentBlock();
}

if (!$rbacsystem->checkAccess("read", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("userdata"));

// get user data
$author = $frm->getUser($_GET["user"]);

$tpl->setVariable("TPLPATH", $tpl->vars["TPLPATH"]);

$tpl->show();
?>
