<?php
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

//include files from PEAR
require_once "PEAR.php";
require_once "DB.php";
require_once "Auth/Auth.php";

//include classes and function libraries
require_once "classes/class.IniFile.php";
require_once "classes/class.DBx.php";
require_once "classes/class.template.php";
require_once "classes/class.ilias.php";
require_once "classes/class.util.php";
require_once "classes/class.User.php";
require_once "classes/class.format.php";
require_once "classes/class.prevnextnavbar.php";
require_once "classes/class.perm.php";
require_once "classes/class.tree.php";
require_once "classes/class.Language.php";
require_once "classes/class.Log.php";
require_once "classes/class.UserMail.php";

//include role based access control system
require_once "classes/class.rbacAdmin.php";
require_once "classes/class.rbacSystem.php";
require_once "classes/class.rbacReview.php";
require_once "classes/class.rbacAdminH.php";
require_once "classes/class.rbacSystemH.php";
require_once "classes/class.rbacReviewH.php";

// include error_handling
require_once "classes/class.ErrorHandling.php";

// i think this file is just for debugging. SH
include_once "./include/inc.main.php";

session_start();

// load main class
$ilias = new ILIAS;

// set constants
define ("ROOT_FOLDER_ID",$ilias->ini->readVariable('system','ROOT_FOLDER_ID')); 
define ("SYSTEM_FOLDER_ID",$ilias->ini->readVariable('system','SYSTEM_FOLDER_ID'));

//authenticate
$ilias->auth->start();

// start logging
$log = new Log("ilias.log");

//check for logged in user except in index.php and login.php
$script = substr(strrchr($_SERVER["PHP_SELF"],"/"),1);
if ($script != "login.php" && $script != "index.php")
{
	//if not authenticated display login screen
	if (!$ilias->auth->getAuth())
	{
		header("location: sessionexpired.php?from=".urlencode($_SERVER['REQUEST_URI']));
		exit;
	}

	//get user id
	if (empty($AccountId))
	{
		$res = $ilias->db->query("SELECT usr_id FROM user_data WHERE login='".$ilias->auth->getUsername()."'");
	
		//query has got a result
		if ($res->numRows() > 0)
		{
			$data = $res->fetchRow();
			$AccountId = $data[0];
			$_SESSION["AccountId"] = $AccountId;
		}

        // assigned roles are stored in $_SESSION["RoleId"]
		$rbacreview = new RbacReviewH();
		$_SESSION["RoleId"] = $rbacreview->assignedRoles($_SESSION["AccountId"]);
	}

	// init rbac
	$rbacsystem = new RbacSystemH();
	$rbacadmin = new RbacAdminH();
	$rbacreview = new RbacReviewH();

	// TODO: rbacAdmin should only started when using admin-functions.
	// At the moment the method in the 3 main classes are not separated properly
	// to do this. All rbac-classes need to be cleaned up
		
	// init user
	$ilias->account =& new User($AccountId);
	
	//init language
	$lng = new Language($ilias->account->prefs["language"]);

	// init obj_id & parent; on first start obj_id is set to 1
	$obj_id = $obj_id ? $obj_id : ROOT_FOLDER_ID; // for downward compatibility
	$_GET["obj_id"] = $_GET["obj_id"] ? $_GET["obj_id"] : ROOT_FOLDER_ID;
	$parent = $parent ? $parent : 0; // for downward compatibility
	$_GET["parent"] = $_GET["parent"] ? $_GET["parent"] : 0;
	
	// init tree
	$tree = new Tree($_GET["obj_id"],$_GET["parent"],ROOT_FOLDER_ID,1);

}	

// instantiate main template
$tplmain = new Template("tpl.main.html", true, false);

//navigation things
if ($script != "login.php" && $script != "index.php")
{
	if ($tplmain->includeNavigation() == true)
	{
		include("./include/inc.mainmenu.php");		
		$tplmain->setVariable("NAVIGATION", $tplnav->get());
	}
}

//pda support
if (strpos($_SERVER["HTTP_USER_AGENT"],"Windows CE")>0)
{
	$ilias->account->setSkin = "pda";
}

//user stylehandling
if (is_object($ilias->account) && $ilias->account->prefs["style_".$ilias->account->prefs["skin"]] != "")
{
	$style = $ilias->account->prefs["style_".$ilias->account->prefs["skin"]].".css";
}
else
{
	$style = "style.css";
}
$tplmain->setVariable("LOCATION_STYLESHEET", $tplmain->tplPath."/".$style);

?>