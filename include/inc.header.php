<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2004 ILIAS open source, University of Cologne            |
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

// get pear
include("include/inc.get_pear.php");

//include class.util first to start StopWatch
require_once "classes/class.ilUtil.php";
require_once "classes/class.ilBenchmark.php";
$ilBench =& new ilBenchmark();
$GLOBALS['ilBench'] =& $ilBench;
$ilBench->start("Core", "HeaderInclude");

// start the StopWatch
$t_pagestart = ilUtil::StopWatch();

$ilBench->start("Core", "HeaderInclude_IncludeFiles");

//include files from PEAR
require_once "PEAR.php";
require_once "DB.php";
require_once "Auth/Auth.php";

// memory usage at this point (2005-02-09): ~781KB)
//echo "<br>memory1:".memory_get_usage()."<br>";

// wrapper for php 4.3.2 & higher
@include_once "HTML/ITX.php";

if (!class_exists("IntegratedTemplateExtension"))
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
require_once "classes/class.ilShibboleth.php";
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
require_once "classes/class.ilCtrl.php";
require_once "classes/class.ilConditionHandler.php";
require_once "classes/class.ilBrowser.php";
require_once "classes/class.ilFrameTargetInfo.php";
require_once "include/inc.ilias_version.php";

//include role based access control system
require_once "Services/AccessControl/classes/class.ilAccessHandler.php";
require_once "classes/class.ilRbacAdmin.php";
require_once "classes/class.ilRbacSystem.php";
require_once "classes/class.ilRbacReview.php";

// memory usage at this point (2005-02-09): ~3MB)
//echo "<br>memory2:".memory_get_usage()."<br>";

// ### AA 03.10.29 added new LocatorGUI class ###
//include LocatorGUI
require_once "classes/class.ilLocatorGUI.php";

// include error_handling
require_once "classes/class.ilErrorHandling.php";

$ilBench->stop("Core", "HeaderInclude_IncludeFiles");

$ilBench->start("Core", "HeaderInclude_GetErrorHandler");
$ilErr = new ilErrorHandling();
$GLOBALS['ilErr'] =& $ilErr;
$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
$ilBench->stop("Core", "HeaderInclude_GetErrorHandler");

// load main class
$ilBench->start("Core", "HeaderInclude_GetILIASObject");
$ilias =& new ILIAS($_COOKIE["ilClientId"]);
$GLOBALS['ilias'] =& $ilias;
$ilBench->stop("Core", "HeaderInclude_GetILIASObject");

require_once './classes/class.ilHTTPS.php';

$https =& new ilHTTPS();
$GLOBALS['https'] =& $https;
$https->checkPort();


$ilDB = $ilias->db;
$GLOBALS['ilDB'] =& $ilDB;
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
$ilBench->start("Core", "HeaderInclude_Authentication");
$ilias->auth->start();
$ilBench->stop("Core", "HeaderInclude_Authentication");

// force login ; workaround for hsu
if ($_GET["cmd"] == "force_login")
{
	$ilias->auth->logout();
	$_SESSION["AccountId"] = "";
	$ilias->auth->start();
}

// start logging
$log = new ilLog(ILIAS_LOG_DIR,ILIAS_LOG_FILE,$ilias->getClientId(),ILIAS_LOG_ENABLED,ILIAS_LOG_LEVEL);
$GLOBALS['log'] =& $log;

// load object definitions
$ilBench->start("Core", "HeaderInclude_getObjectDefinitions");
$objDefinition = new ilObjectDefinition();
$GLOBALS['objDefinition'] =& $objDefinition;
$objDefinition->startParsing();
$ilBench->stop("Core", "HeaderInclude_getObjectDefinitions");


// current user account
$ilBench->start("Core", "HeaderInclude_getCurrentUser");
$ilias->account = new ilObjUser();
$ilBench->stop("Core", "HeaderInclude_getCurrentUser");

// create references for subobjects in ilias object
$ilUser =& $ilias->account;
$GLOBALS['ilUser'] =& $ilias->account;
$ilCtrl = new ilCtrl();
$GLOBALS['ilCtrl'] =& $ilCtrl;
$ilLog =& $log;
$GLOBALS['ilLog'] =& $ilLog;

//but in login.php and index.php don't check for authentication
$script = substr(strrchr($_SERVER["PHP_SELF"],"/"),1);

// check ilias 2 password, if authentication failed
// only if AUTH_LOCAL
if (AUTH_CURRENT == AUTH_LOCAL && !$ilias->auth->getAuth() && $script == "login.php" && $_POST["username"] != "")
//if (!$ilias->auth->getAuth() && $script == "login.php" && $_POST["username"] != "")
{
	if (ilObjUser::_lookupHasIlias2Password($_POST["username"]))
	{
		if (ilObjUser::_switchToIlias3Password($_POST["username"], $_POST["password"]))
		{
			$ilias->auth->start();
			ilUtil::redirect("start.php");
		}
	}
}

if ($ilias->auth->getAuth() && $ilias->account->isCurrentUserActive())
{
	$ilBench->start("Core", "HeaderInclude_getCurrentUserAccountData");

	//get user id
	if (empty($_SESSION["AccountId"]))
	{
		$_SESSION["AccountId"] = $ilias->account->checkUserId();

        // assigned roles are stored in $_SESSION["RoleId"]
		$rbacreview = new ilRbacReview();
		$GLOBALS['rbacreview'] =& $rbacreview;
		$_SESSION["RoleId"] = $rbacreview->assignedRoles($_SESSION["AccountId"]);
	} // TODO: do we need 'else' here?
	else
	{
		// init user
		$ilias->account->setId($_SESSION["AccountId"]);
	}

	// load account data of current user
	$ilias->account->read();
	
	// check if client ip is set, if so compare and shows error if client ip differs from profile ip
	if ($ilias->account->getClientIP()!="" && strcmp($ilias->account->getClientIP(),$_SERVER["REMOTE_ADDR"])!=0) {
		$message = "Remote IP does not match IP stored in profile!";
		$log ->logError(1, $ilias->account->getLogin().":".$_SERVER["REMOTE_ADDR"].":".$message);
		$ilias->raiseError($message,$ilias->error_obj->WARNING);		
	}
	
	// check wether user has accepted the user agreement
//echo "-".$script;
	if (!$ilias->account->hasAcceptedUserAgreement() &&
		$script != "view_usr_agreement.php" &&
		$script != "login.php" &&
		$ilias->account->getId() != ANONYMOUS_USER_ID)
	{
//echo "redirect from $script for ".$ilias->account->getFirstName();
		ilUtil::redirect("view_usr_agreement.php?cmd=getAcceptance");
	}

	// update last_login date once the user logged in
	if ($script == "login.php")
	{
		$ilias->account->refreshLogin();
	}

	// set hits per page for all lists using table module
	// Since hits_per_page is always read in ilObjUser we don't need to read it from the session
	#_SESSION["tbl_limit"] = ($_SESSION["tbl_limit"]) ? intval($_SESSION["tbl_limit"]) : intval($ilias->account->prefs["hits_per_page"]);
	#_GET["limit"] = ($_SESSION["tbl_limit"]) ? ($_SESSION["tbl_limit"]) : intval($ilias->account->prefs["hits_per_page"]);
	#_GET["offset"] = intval($_GET["offset"]);
	$_GET['limit'] = $_SESSION['tbl_limit'] = (int) $ilUser->getPref('hits_per_page');
	$_GET['offset'] = (int) $_GET['offset'];
	

	$ilBench->stop("Core", "HeaderInclude_getCurrentUserAccountData");
}
elseif (
			$script != "login.php" 
			and $script != "shib_login.php" 
			and $script != "nologin.php" 
			and $script != "error.php" 
			and $script != "index.php"
			and $script != "view_usr_agreement.php" 
			and $script!= "register.php" 
			and $script != "chat.php"
			and $script != "pwassist.php"
		)
{
	// phpinfo();exit;


	$dirname = dirname($_SERVER["PHP_SELF"]);
	$ilurl = parse_url(ILIAS_HTTP_PATH);
	$subdir = substr(strstr($dirname,$ilurl["path"]),strlen($ilurl["path"]));
	$updir = "";

	if ($subdir)
	{
		$num_subdirs = substr_count($subdir,"/");

		for ($i=1;$i<=$num_subdirs;$i++)
		{
			$updir .= "../";
		}
	}

    if ($ilias->auth->getAuth() && !$ilias->account->isCurrentUserActive())
    {
        $inactive = true;
    }

	session_unset();
	session_destroy();

	$return_to = urlencode(substr($_SERVER["REQUEST_URI"],strlen($ilurl["path"])+1));

	if (($_GET["inactive"]) || $inactive)
	{
		ilUtil::redirect($updir."index.php?reload=true&inactive=true&return_to=".$return_to);
	}
	else
	{
		ilUtil::redirect($updir."index.php?client_id=".$_GET["client_id"]."&reload=true&return_to=".$return_to);
	}
}

//init language
$ilBench->start("Core", "HeaderInclude_initLanguage");
$_SESSION['lang'] = $_GET['lang'] ? $_GET['lang'] : $_SESSION['lang'];
$lang_key = ($_GET["lang"]) ? $_GET["lang"] : $ilias->account->prefs["language"];
$lng = new ilLanguage($lang_key);
$GLOBALS['lng'] =& $lng;
$ilBench->stop("Core", "HeaderInclude_initLanguage");

// init rbac
$ilBench->start("Core", "HeaderInclude_initRBAC");
$rbacsystem = new ilRbacSystem();
$GLOBALS['rbacsystem'] =& $rbacsystem;
$rbacadmin = new ilRbacAdmin();
$GLOBALS['rbacadmin'] =& $rbacadmin;
$rbacreview = new ilRbacReview();
$GLOBALS['rbacreview'] =& $rbacreview;
$ilAccess =& new ilAccessHandler();
$GLOBALS["ilAccess"] =& $ilAccess;

$ilBench->stop("Core", "HeaderInclude_initRBAC");


// init ref_id on first start ref_id is set to ROOT_FOLDER_ID
//$_GET["ref_id"] = $_GET["ref_id"] ? $_GET["ref_id"] : ROOT_FOLDER_ID;

// init tree
$tree = new ilTree(ROOT_FOLDER_ID);
$GLOBALS['tree'] =& $tree;

// instantiate main template
$tpl = new ilTemplate("tpl.main.html", true, true);
$GLOBALS['tpl'] =& $tpl;


// ### AA 03.10.29 added new LocatorGUI class ###
// when locator data array does not exist, initialise
if ( !isset($_SESSION["locator_level"]) )
{
	$_SESSION["locator_data"] = array();
	$_SESSION["locator_level"] = -1;
}
// initialise global ilias_locator object
$ilias_locator = new ilLocatorGUI();
$GLOBALS['ilias_locator'] =& $ilias_locator;

// load style definitions
$ilBench->start("Core", "HeaderInclude_getStyleDefinitions");
$styleDefinition = new ilStyleDefinition();
$GLOBALS['styleDefinition'] =& $styleDefinition;
$styleDefinition->startParsing();
$ilBench->stop("Core", "HeaderInclude_getStyleDefinitions");

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
	$mail =& new ilMail($_SESSION['AccountId']);
	if($rbacsystem->checkAccess('mail_visible',$mail->getMailObjectReferenceId()))
	{
		$folder_id = $mbox->getInboxFolder();
		
		$_SESSION["infopanel"] = array ("link"	=> "mail_frameset.php?target=".
										htmlentities(urlencode("mail_read.php?mobj_id=".$folder_id."&mail_id=".$mail_id)),
										"text"	=> "new_mail"
										//"img"	=> "icon_mail.gif"
			);
	}
}

// php5 downward complaince to php 4 dom xml
if (version_compare(PHP_VERSION,'5','>='))
{
	require_once("include/inc.xml5compliance.php");
}

// php5 downward complaince to php 4 sablotorn xslt
if (version_compare(PHP_VERSION,'5','>='))
{
	require_once("include/inc.xsl5compliance.php");
}

// provide global browser information
$ilBrowser = new ilBrowser();
$GLOBALS['ilBrowser'] =& $ilBrowser;

$ilBench->stop("Core", "HeaderInclude");
$ilBench->save();

?>
