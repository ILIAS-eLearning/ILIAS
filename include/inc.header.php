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

// wrapper for php 4.3.2 & higher
@include_once "HTML/ITX.php";

if (!class_exists(IntegratedTemplateExtension))
{
	include_once "HTML/Template/ITX.php";
	//include_once "classes/class.ilTemplate2.php";
	include_once "classes/class.ilTemplateHTMLITX.php";
}
else
{
	//include_once "classes/class.ilTemplate.php";
	include_once "classes/class.ilTemplateITX.php";
}
require_once "classes/class.ilTemplate.php";

//include classes and function libraries
require_once "include/inc.db_session_handler.php";
require_once "classes/class.ilIniFile.php";
require_once "classes/class.ilDBx.php";
require_once "classes/class.ilias.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilFormat.php";
require_once "classes/class.ilSaxParser.php";
require_once "classes/class.ilObjectDefinition.php";
require_once "classes/class.ilStyleDefinition.php";
require_once "classes/class.perm.php";
require_once "classes/class.ilTree.php";
require_once "classes/class.ilLanguage.php";
require_once "classes/class.ilLog.php";
require_once "classes/class.ilMailbox.php";

//include role based access control system
require_once "classes/class.ilRbacAdmin.php";
require_once "classes/class.ilRbacSystem.php";
require_once "classes/class.ilRbacReview.php";

// ### AA 03.10.29 added new LocatorGUI class ###
//include LocatorGUI
require_once "classes/class.ilLocatorGUI.php";

// include error_handling
require_once "classes/class.ilErrorHandling.php";

$ilErr = new ilErrorHandling();
$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));

// load main class
$ilias = new ILIAS($_COOKIE["ilClientId"]);

if (!db_set_save_handler())
{
	$message = "Please turn off Safe mode OR set session.save_handler to \"user\" in your php.ini";
	$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
}

// LOAD OLD POST VARS IF ERROR HANDLER 'MESSAGE' WAS CALLED
if ($_SESSION["message"])
{
	$_POST = $_SESSION["post_vars"];
}

// put debugging functions here
//if (DEBUG)
//{
	include_once "include/inc.debug.php";
//}

//authenticate & start session
$ilias->auth->start();

// start logging
$log = new ilLog(ILIAS_LOG_DIR,ILIAS_LOG_FILE,$ilias->getClientId(),ILIAS_LOG_ENABLED);

// load object definitions
$objDefinition = new ilObjectDefinition();
$objDefinition->startParsing();

// current user account
$ilias->account = new ilObjUser();
// create references for subobjects in ilias object
$ilDB =& $ilias->db;
$ilUser =& $ilias->account;

//but in login.php and index.php don't check for authentication
$script = substr(strrchr($_SERVER["PHP_SELF"],"/"),1);

if ($ilias->auth->getAuth())
{
	//get user id
	if (empty($_SESSION["AccountId"]))
	{
		$_SESSION["AccountId"] = $ilias->account->checkUserId();

        // assigned roles are stored in $_SESSION["RoleId"]
		$rbacreview = new ilRbacReview();
		$_SESSION["RoleId"] = $rbacreview->assignedRoles($_SESSION["AccountId"]);
	}
	else
	{
		// init user
		$ilias->account->setId($_SESSION["AccountId"]);
	}

	// load account data of current user
	$ilias->account->read();

	// update last_login date once the user logged in
	if ($script == "login.php")
	{
		$ilias->account->refreshLogin();
	}
}
elseif ($script != "login.php" and $script != "nologin.php" and $script != "index.php" 
		and $script != "view_usr_agreement.php" and $script!= "register.php" and $script != "chat.php")
{
	header("Location: index.php?reload=true");
	exit;
}

//init language
$lang_key = ($_GET["lang"]) ? $_GET["lang"] : $ilias->account->prefs["language"];
$lng = new ilLanguage($lang_key);

// init rbac
$rbacsystem = new ilRbacSystem();
$rbacadmin = new ilRbacAdmin();
$rbacreview = new ilRbacReview();

// init ref_id on first start ref_id is set to ROOT_FOLDER_ID
$_GET["ref_id"] = $_GET["ref_id"] ? $_GET["ref_id"] : ROOT_FOLDER_ID;

// init tree
$tree = new ilTree(ROOT_FOLDER_ID);

// instantiate main template
$tpl = new ilTemplate("tpl.main.html", true, true);

// ### AA 03.10.29 added new LocatorGUI class ###
// when locator data array does not exist, initialise
if ( !isset($_SESSION["locator_level"]) )
{
	$_SESSION["locator_data"] = array();
	$_SESSION["locator_level"] = -1;
}
// initialise global ilias_locator object
$ilias_locator = new ilLocatorGUI();

// load style definitions
$styleDefinition = new ilStyleDefinition();
$styleDefinition->startParsing();

//navigation things
/*
	I really don't know in which case the following code is needed.
	If any errors occur due to disabling this, please do
	not hesitate to mail me... alex.killing@gmx.de
	
	this function was used for the no_frames template set... shofmann@databay.de

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

// load style sheet depending on user's settings
$location_stylesheet = ilUtil::getStyleSheetLocation();
$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
$tpl->setVariable("LOCATION_JAVASCRIPT",dirname($location_stylesheet));

// init infopanel
if ($mail_id = ilMailbox::hasNewMail($_SESSION["AccountId"]))
{
	$mbox = new ilMailbox($_SESSION["AccountId"]);
	$folder_id = $mbox->getInboxFolder();

	$_SESSION["infopanel"] = array ("link"	=> "mail_frameset.php?target=".
												htmlentities(urlencode("mail_read.php?mobj_id=".$folder_id."&mail_id=".$mail_id)),
									"text"	=> "new_mail"
									//"img"	=> "icon_mail.gif"
									);
}?>
