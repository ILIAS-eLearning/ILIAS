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
* header include for all ilias files. This script will be always included first for every page
* in ILIAS. Inits RBAC-Classes & recent user, log-,language- & tree-object
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/

//include class.util first to start StopWatch
require_once "classes/class.ilUtil.php";

// start the StopWatch
$t_pagestart = ilUtil::StopWatch();

//include files from PEAR
require_once "PEAR.php";
require_once "DB.php";
require_once "Auth/Auth.php";

//include classes and function libraries
require_once "classes/class.ilIniFile.php";
require_once "classes/class.ilDBx.php";
require_once "classes/class.ilTemplate.php";
require_once "classes/class.ilias.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilFormat.php";
require_once "classes/class.ilObjectDefinition.php";
require_once "classes/class.perm.php";
require_once "classes/class.ilTree.php";
require_once "classes/class.ilLanguage.php";
require_once "classes/class.ilLog.php";
require_once "classes/class.ilMailbox.php";

//include role based access control system
require_once "classes/class.ilRbacAdmin.php";
require_once "classes/class.ilRbacSystem.php";
require_once "classes/class.ilRbacReview.php";
require_once "classes/class.ilRbacAdminH.php";
require_once "classes/class.ilRbacSystemH.php";
require_once "classes/class.ilRbacReviewH.php";

// include error_handling
require_once "classes/class.ilErrorHandling.php";

ini_set("session.save_handler", "files");
session_save_path("/tmp");
session_start();

// LOAD OLD POST VARS IF ERROR HANDLER 'MESSAGE' WAS CALLED
if($_SESSION["message"])
{
	$_POST = $_SESSION["post_vars"];
}

// load main class
$ilias = new ILIAS();

if (DEBUG)
{
	require_once "include/inc.debug.php";
}

//authenticate
$ilias->auth->start();

// start logging
$log = new ilLog("ilias.log");

//load object definitions
$objDefinition = new ilObjectDefinition();
$objDefinition->startParsing();
//var_dump("<pre>",$objDefinition->obj_data,"</pre");
//instantiate user object

$ilias->account = new ilObjUser();

//but in login.php and index.php don't check for authentication
$script = substr(strrchr($_SERVER["PHP_SELF"],"/"),1);

if ($script != "login.php" && $script != "index.php")
{
	//if not authenticated display login screen
	//if (!$ilias->auth->getAuth())
	//{
	//	header("location: sessionexpired.php?from=".urlencode($_SERVER['REQUEST_URI']));
	//	exit;
	//}
	//get user id
	if (empty($_SESSION["AccountId"]))
	{
		$_SESSION["AccountId"] = $ilias->account->checkUserId($_SESSION["AccountId"]);
        // assigned roles are stored in $_SESSION["RoleId"]
		$rbacreview = new ilRbacReviewH();
		$_SESSION["RoleId"] = $rbacreview->assignedRoles($_SESSION["AccountId"]);
	}
	else
	{
		// init user
		$ilias->account->setId($_SESSION["AccountId"]);
	}

	$ilias->account->read();

	if ($script == "logout.php")
	{
		$ilias->account->refreshLogin();
	}

	//init language
	$lng = new ilLanguage($ilias->account->prefs["language"]);

	// init rbac
	$rbacsystem = new ilRbacSystemH();
	$rbacadmin = new ilRbacAdminH();
	$rbacreview = new ilRbacReviewH();

	// TODO: rbacAdmin should only start when using admin-functions.
	// At the moment the method in the 3 main classes are not separated properly
	// to do this. All rbac-classes need to be cleaned up

	// init ref_id & parent; on first start ref_id is set to 1
	//$ref_id = $ref_id ? $ref_id : ROOT_FOLDER_ID; // for downward compatibility
	$_GET["ref_id"] = $_GET["ref_id"] ? $_GET["ref_id"] : ROOT_FOLDER_ID;
	//$parent = $parent ? $parent : 0; // for downward compatibility
	//$_GET["parent"] = $_GET["parent"] ? $_GET["parent"] : 0;

	// init tree
	$tree = new ilTree(ROOT_FOLDER_ID);
}

// instantiate main template
$tpl = new ilTemplate("tpl.main.html", true, true);

//navigation things
/*
	I really don't know in which case the following code is needed.
	If any errors occur due to disabling this, please do
	not hesitate to mail me... alex.killing@gmx.de

if ($script != "login.php" && $script != "index.php")
{
	if ($tpl->includeNavigation() == true)
	{
		$menu = new ilMainMenu();
		$menu->setTemplate($tpl);
		$menu->addMenuBlock("NAVIGATION", "navigation");
		$menu->setTemplateVars();
		//include("include/inc.mainmenu.php");
	}
}*/

$location_stylesheet = ilUtil::getStyleSheetLocation();

$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
$tpl->setVariable("TPLPATH",dirname($location_stylesheet));

if ($mail_id = ilMailbox::hasNewMail($_SESSION["AccountId"]))
{
	$mbox = new ilMailbox($_SESSION["AccountId"]);
	$folder_id = $mbox->getInboxFolder();

	$_SESSION["infopanel"] = array ("link"	=> "mail_frameset.php?target=".
									htmlentities(urlencode("mail_read.php?mobj_id=".$folder_id."&mail_id=".$mail_id)),
									"text"	=> "new_mail",
									"img"	=> "icon_mail.gif"
									);
}
?>
