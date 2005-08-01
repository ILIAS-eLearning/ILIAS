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
* personal desktop
* welcome screen of ilias with new mails, last lo's etc.
* adapted from ilias 2
*
* @author Peter Gabriel <pgabriel@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilMail.php";
require_once "classes/class.ilPersonalDesktopGUI.php";


// catch hack attempts
if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
{
    $ilias->raiseError($lng->txt("msg_not_available_for_anon"),$ilias->error_obj->MESSAGE);
}

switch($_GET["cmd"])
{
    case "dropItem":
        $ilias->account->dropDesktopItem($_GET["id"], $_GET["type"]);
        break;

    case "removeMember":
        //$groupObj = $ilias->obj_factory->getInstanceByRefId($_GET["id"]);
        //$groupObj = new ilGroupGUI($a_data, $_GET["id"], false);
        //$err_msg = $groupObj->removeMember($ilias->account->getId());
        if (strlen($err_msg) > 0)
            $ilias->raiseError($lng->txt($err_msg),$ilias->error_obj->MESSAGE);
        break;
		
	case "showSelectedItemsDetails":
		$ilUser->writePref("pd_selected_items_details", "y");
		break;

	case "hideSelectedItemsDetails":
		$ilUser->writePref("pd_selected_items_details", "n");
		break;

}
/*if ($_GET["action"] == "removeMember")
{
    $groupObj = new ilGroupGUI($a_data, $_GET["id"], false);
    //$err_msg = $groupObj->removeMember("usr_personaldesktop.php" , "loaction: usr_personaldesktop.php");//$ilias->account->getId());
    if(strlen($err_msg) > 0)
        $ilias->raiseError($lng->txt($err_msg),$ilias->error_obj->MESSAGE);
    exit();
    break;
}*/

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
//$tpl->addBlockFile("CONTENT", "content", "tpl.usr_personaldesktop.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_personaldesktop.html");
//$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

// set locator
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("personal_desktop"));
$tpl->setVariable("LINK_ITEM", "usr_personaldesktop.php");
$tpl->parseCurrentBlock();

// catch feedback message
sendInfo();
// display infopanel if something happened
infoPanel();

// display tabs
//include "./include/inc.personaldesktop_buttons.php";

$tpl->setCurrentBlock("adm_content");
$tpl->setVariable("HEADER", $lng->txt("personal_desktop"));
include "./include/inc.personaldesktop_buttons.php";
//$tpl->setVariable("TABS", "KK");
$tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));

// courses
/*
$courses = $ilias->account->getCourses();

// forums
$frm_obj = ilUtil::getObjectsByOperations('frm','read');
$frmNum = count($frm_obj);
$lastLogin = $ilias->account->getLastLogin();
*/

//********************************************
//* OUTPUT
//********************************************

//begin mailblock if there are new mails

$deskgui =& new ilPersonalDesktopGUI();

$deskgui->displaySelectedItems();
$deskgui->displaySystemMessages();
$deskgui->displayMails();
$deskgui->displayUsersOnline();
$deskgui->displayBookmarks();
//$deskgui->displayTests();             // see display selected items

// output
$tpl->show();
?>