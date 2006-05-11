<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @package ilias-core
*/

// get pear
require_once("include/inc.get_pear.php");
require_once("include/inc.check_pear.php");

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
//require_once "classes/class.ilIniFile.php";
require_once "Services/Init/classes/class.ilInitUtil.php";
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
require_once "Services/Help/classes/class.ilHelp.php";
require_once "include/inc.ilias_version.php";

//include role based access control system
require_once "Services/AccessControl/classes/class.ilAccessHandler.php";
require_once "classes/class.ilRbacAdmin.php";
require_once "classes/class.ilRbacSystem.php";
require_once "classes/class.ilRbacReview.php";

// include object_data cache
require_once "classes/class.ilObjectDataCache.php";

require_once 'Services/Tracking/classes/class.ilOnlineTracking.php';

// memory usage at this point (2005-02-09): ~3MB)
//echo "<br>memory2:".memory_get_usage()."<br>";

// ### AA 03.10.29 added new LocatorGUI class ###
//include LocatorGUI
require_once "classes/class.ilLocatorGUI.php";

// include error_handling
require_once "classes/class.ilErrorHandling.php";

$ilBench->stop("Core", "HeaderInclude_IncludeFiles");

// set error handler (to do: check preconditions for error handler to work)
$ilBench->start("Core", "HeaderInclude_GetErrorHandler");
$ilErr = new ilErrorHandling();
$GLOBALS['ilErr'] =& $ilErr;
$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
$ilBench->stop("Core", "HeaderInclude_GetErrorHandler");

// prepare file access to work with safe mode (has been done in class ilias before)
umask(0117);


// $ilInitUtil initialisation
$ilInitUtil =& new ilInitUtil();
$GLOBALS['ilInitUtil'] =& $ilInitUtil;


// $ilIliasIniFile initialisation
$ilInitUtil->initIliasIniFile();


// CLIENT_ID determination
$ilInitUtil->determineClient();


// $ilClientIniFile initialisation
if (!$ilInitUtil->initClientIniFile())
{
	ilUtil::redirect("./setup/setup.php");	// to do: this could fail in subdirectories
											// this is also source of a bug (see mantis)
}


// maintenance mode
if (!$ilClientIniFile->readVariable("client","access"))
{
	if (is_file("./maintenance.html"))
	{
		ilUtil::redirect("./maintenance.html");
	}
	else
	{
		echo '<br /><p style="text-align:center;">The server is not '.
			'available due to maintenance. We apologise for any inconvenience.</p>';
		exit;
	}
}


// $ilDB initialisation
$ilInitUtil->initDatabase();


// set session handler
if(ini_get('session.save_handler') != 'user')
{
	ini_set("session.save_handler", "user");
}
if (!db_set_save_handler())
{
	$message = "Please turn off Safe mode OR set session.save_handler to \"user\" in your php.ini";
	$ilErr->raiseError($message, $ilErr->FATAL);
}


// $ilSetting initialisation
$ilInitUtil->initSettings();


// $ilAuth initialisation
require_once("classes/class.ilAuthUtils.php");
ilAuthUtils::_initAuth();


// $ilias initialisation
$ilBench->start("Core", "HeaderInclude_GetILIASObject");
$ilias =& new ILIAS();
$GLOBALS['ilias'] =& $ilias;
$ilBench->stop("Core", "HeaderInclude_GetILIASObject");


// trace function calls in debug mode
if (DEVMODE)
{
	if (function_exists("xdebug_start_trace"))
	{
		//xdebug_start_trace("/tmp/test.txt");
	}
}


require_once './classes/class.ilHTTPS.php';

$https =& new ilHTTPS();
$GLOBALS['https'] =& $https;
$https->checkPort();


// $ilObjDataCache initialisation
$ilObjDataCache = new ilObjectDataCache();
$GLOBALS['ilObjDataCache'] =& $ilObjDataCache;


// LOAD OLD POST VARS IF ERROR HANDLER 'MESSAGE' WAS CALLED
if ($_SESSION["message"])
{
	$_POST = $_SESSION["post_vars"];
}


// put debugging functions here
require_once "include/inc.debug.php";


// $ilLog initialisation
$log = new ilLog(ILIAS_LOG_DIR,ILIAS_LOG_FILE,$ilias->getClientId(),ILIAS_LOG_ENABLED,ILIAS_LOG_LEVEL);
$GLOBALS['log'] =& $log;
$ilLog =& $log;
$GLOBALS['ilLog'] =& $ilLog;


// authenticate & start session
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr, "errorHandler"));
$ilBench->start("Core", "HeaderInclude_Authentication");
$ilAuth->start();
$ilias->setAuthError($ilErr->getLastError());
$ilBench->stop("Core", "HeaderInclude_Authentication");


// force login ; workaround for hsu
if ($_GET["cmd"] == "force_login")
{
	$ilAuth->logout();
	$_SESSION["AccountId"] = "";
	$ilAuth->start();
	$ilias->setAuthError($ilErr->getLastError());
}


// $objDefinition initialisation
$ilBench->start("Core", "HeaderInclude_getObjectDefinitions");
$objDefinition = new ilObjectDefinition();
$GLOBALS['objDefinition'] =& $objDefinition;
$objDefinition->startParsing();
$ilBench->stop("Core", "HeaderInclude_getObjectDefinitions");


// $ilUser initialisation
$ilBench->start("Core", "HeaderInclude_getCurrentUser");
$ilUser = new ilObjUser();
$ilias->account =& $ilUser;
$GLOBALS['ilUser'] =& $ilUser;
$ilBench->stop("Core", "HeaderInclude_getCurrentUser");


// $ilCtrl initialisation
$ilCtrl = new ilCtrl();
$GLOBALS['ilCtrl'] =& $ilCtrl;


//but in login.php and index.php don't check for authentication
$script = substr(strrchr($_SERVER["PHP_SELF"],"/"),1);

// set theme for login
if (in_array($script, array("login.php", "register.php", "view_usr_agreement.php")))
{
	// load style definitions
	$ilBench->start("Core", "HeaderInclude_getStyleDefinitions");
	$styleDefinition = new ilStyleDefinition();
	$GLOBALS['styleDefinition'] =& $styleDefinition;
	$styleDefinition->startParsing();
	$ilBench->stop("Core", "HeaderInclude_getStyleDefinitions");
	
	if ($_GET['skin']  && $_GET['style'])
	{
		include_once("classes/class.ilObjStyleSettings.php");
		if ($styleDefinition->styleExists($_GET['skin'], $_GET['style']) &&
			ilObjStyleSettings::_lookupActivatedStyle($_GET['skin'], $_GET['style']))
		{
			$_SESSION['skin'] = $_GET['skin'];
			$_SESSION['style'] = $_GET['style'];
		}
	}
	if ($_SESSION['skin'] && $_SESSION['style'])
	{
		include_once("classes/class.ilObjStyleSettings.php");
		if ($styleDefinition->styleExists($_SESSION['skin'], $_SESSION['style']) &&
			ilObjStyleSettings::_lookupActivatedStyle($_SESSION['skin'], $_SESSION['style']))
		{
			$ilias->account->skin = $_SESSION['skin'];
			$ilias->account->prefs['style'] = $_SESSION['style'];
		}
	}
}


// check ilias 2 password, if authentication failed
// only if AUTH_LOCAL
if (AUTH_CURRENT == AUTH_LOCAL && !$ilAuth->getAuth() && $script == "login.php" && $_POST["username"] != "")
//if (!$ilias->auth->getAuth() && $script == "login.php" && $_POST["username"] != "")
{
	if (ilObjUser::_lookupHasIlias2Password($_POST["username"]))
	{
		if (ilObjUser::_switchToIlias3Password($_POST["username"], $_POST["password"]))
		{
			$ilAuth->start();
			$ilias->setAuthError($ilErr->getLastError());
			ilUtil::redirect("start.php");
		}
	}
}

if (strpos($_SERVER["SCRIPT_FILENAME"], "save_java_question_result") !== FALSE)
{
	// dirty hack for the saving of java applet questions in tests. Unfortunately
	// some changes in this file for ILIAS 3.6 caused this script to stop at the
	// following check (worked in ILIAS <= 3.5).
	// So we return here, because it's only necessary to get the $ilias class for
	// the database connection
	// TODO: Find out what happens here. Talk to Alex Killing
	$lng = new ilLanguage($_SESSION['lang']);
	$GLOBALS['lng'] =& $lng;
	return;
}

if ($ilAuth->getAuth() && $ilias->account->isCurrentUserActive())
{
	//
	// SUCCESSFUL AUTHENTICATION
	//
	
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
	
	// check client ip
	$clientip = $ilias->account->getClientIP();
	if (trim($clientip) !="" and $clientip != $_SERVER["REMOTE_ADDR"])
	{
		$log ->logError(1,
			$ilias->account->getLogin().":".$_SERVER["REMOTE_ADDR"].":".$message);
		$ilAuth->logout();
		@session_destroy();
		ilUtil::redirect("login.php?wrong_ip=true");
	}
	
	// check wether user has accepted the user agreement
	//	echo "-".$script;
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
	
	// the next line makes it impossible to save the offset somehow in a session for
	// a specific table (I tried it for the user administration).
	// its not posssible to distinguish whether it has been set to page 1 (=offset = 0)
	// or not set at all (then we want the last offset, e.g. being used from a session var).
	// So I added the wrapping if statement. Seems to work (hopefully).
	// Alex April 14th 2006
	if ($_GET['offset'] != "")							// added April 14th 2006
	{
		$_GET['offset'] = (int) $_GET['offset'];		// old code
	}

	$ilBench->stop("Core", "HeaderInclude_getCurrentUserAccountData");
}
elseif (
			$script != "login.php" 
			and $script != "shib_login.php" 
			and $script != "nologin.php" 
			and $script != "error.php" 
			and $script != "index.php"
			and $script != "view_usr_agreement.php" 
			and $script != "register.php" 
			and $script != "chat.php"
			and $script != "pwassist.php"
		)
{
	//
	// AUTHENTICATION FAILED
	//

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

	// authentication failed due to inactive user?
    if ($ilAuth->getAuth() && !$ilias->account->isCurrentUserActive())
    {
        $inactive = true;
    }
	
	//$return_to = urlencode(substr($_SERVER["REQUEST_URI"],strlen($ilurl["path"])+1));

	//
	// NO AUTHENTICATION
	//
	if ($ilSetting->get("pub_section"))
	{
		// auth as anonymous
		$_POST["username"] = "anonymous";
		$_POST["password"] = "anonymous";
		$ilAuth->start();
		
		if (ANONYMOUS_USER_ID == "")
		{
			die ("Public Section enabled, but no Anonymous user found.");
		}
		if (!$ilAuth->getAuth())
		{
			die("ANONYMOUS user with the object_id ".ANONYMOUS_USER_ID." not found!");
		}
		if (empty($_GET["ref_id"]))
		{
			$_GET["ref_id"] = ROOT_FOLDER_ID;
		}
		
		$_GET["cmd"] = "frameset";
		$jump_script = "repository.php";
		$script = $updir.$jump_script."?cmd=".$_GET["cmd"]."&ref_id=".$_GET["ref_id"];
		
		// todo do it better, if JS disabled
		echo "<script language=\"Javascript\">\ntop.location.href = \"".$script."\";\n</script>\n";
		exit;
	}

	session_unset();
	session_destroy();

	// no public section
	if (($_GET["inactive"]) || $inactive)
	{
		ilUtil::redirect($updir."index.php?reload=true&inactive=true&return_to=".$return_to);
	}
	else
	{
		ilUtil::redirect($updir."index.php?client_id=".$_COOKIE["ilClientId"]."&reload=true&return_to=".$return_to);
	}
}


//init language
$ilBench->start("Core", "HeaderInclude_initLanguage");

if (is_null($_SESSION['lang']))
{
	$_GET["lang"] = ($_GET["lang"]) ? $_GET["lang"] : $ilias->account->getPref("language");
}

if ($_POST['change_lang_to'] != "")
{
	$_GET['lang'] = $_POST['change_lang_to'];
}

$_SESSION['lang'] = ($_GET['lang']) ? $_GET['lang'] : $_SESSION['lang'];

// prefer personal setting when coming from login screen 
if ($script == "login.php")
{
	$_SESSION['lang'] = $ilias->account->getPref("language");
}

$lng = new ilLanguage($_SESSION['lang']);
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
$ilias_locator = new ilLocatorGUI();			// deprecated
$ilLocator = new ilLocatorGUI();
$GLOBALS['ilias_locator'] =& $ilias_locator;	// deprecated
$GLOBALS['ilLocator'] =& $ilLocator;

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

// php5 downward complaince to php 4 dom xml and clone method
if (version_compare(PHP_VERSION,'5','>='))
{
	require_once("include/inc.xml5compliance.php");
	require_once("include/inc.xsl5compliance.php");
	require_once("include/inc.php4compliance.php");
}
else
{
	require_once("include/inc.php5compliance.php");
}

// provide global browser information
$ilBrowser = new ilBrowser();
$GLOBALS['ilBrowser'] =& $ilBrowser;

// provide global help object
$ilHelp = new ilHelp();
$GLOBALS['ilHelp'] =& $ilHelp;

// main tabs gui
include_once 'classes/class.ilTabsGUI.php';
$ilTabs = new ilTabsGUI();
$GLOBALS['ilTabs'] =& $ilTabs;

// main menu
include_once 'classes/class.ilMainMenuGUI.php';
$ilMainMenu = new ilMainMenuGUI("_top");
$GLOBALS['ilMainMenu'] =& $ilMainMenu;

// Store online time of user
ilOnlineTracking::_updateAccess($ilUser->getId());


// utf-8 fix?
$q = "SET NAMES utf8";
//$ilDB->query($q);

$ilBench->stop("Core", "HeaderInclude");
$ilBench->save();
?>
