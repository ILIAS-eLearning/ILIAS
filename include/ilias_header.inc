<?php
/**
* header include for all ilias files. This script will be always included first for every page
* in ILIAS. Inits RBAC-Classes & recent user, log-,language- & tree-object
* 
* @version $Id$
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
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
include_once "function.library.php";
include_once "classes/class.util.php";
include_once "classes/class.User.php";
include_once "classes/class.group.php";
include_once "classes/class.role.php";
include_once "classes/class.resource.php";
include_once "classes/class.groupmember.php";
include_once "classes/class.format.php";
include_once "classes/class.prevnextnavbar.php";
include_once "classes/class.perm.php";
include_once "classes/class.tree.php";
include_once "classes/class.Language.php";
include_once "classes/class.Log.php";
include_once "classes/class.UserMail.php";

//include role based access control system
include_once "classes/class.rbacAdmin.php";
include_once "classes/class.rbacSystem.php";
include_once "classes/class.rbacReview.php";
include_once "classes/class.rbacAdminH.php";
include_once "classes/class.rbacSystemH.php";
include_once "classes/class.rbacReviewH.php";

// include error_handling
include_once "classes/class.ErrorHandling.php";

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

//check for logged in user
if (substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "login.php")
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
		$rbacreview = new RbacReviewH($ilias->db);
		$_SESSION["RoleId"] = $rbacreview->assignedRoles($_SESSION["AccountId"]);
	}

	// init rbac
	$rbacsystem = new RbacSystemH($ilias->db);
	$rbacadmin = new RbacAdminH($ilias->db);
	$rbacreview = new RbacReviewH($ilias->db);

	// TODO: rbacAdmin should only started when using admin-functions.
	// At the moment the method in the 3 main classes are not separated properly
	// to do this. All rbac-classes need to be cleaned up
		
	// init user
	$ilias->account =& new User($ilias->db,$AccountId);
	
	//language handling
	// TODO: Load user settings for languages first
	if ($lang == "")
	{
		$lang = $ilias->ini->readVariable("language","default");
	}
	
	//init language
	$lng = new Language($lang);

	// init obj_id & parent; on first start obj_id is set to 1
	$obj_id = $obj_id ? $obj_id : ROOT_FOLDER_ID; // for downward compatibility
	$_GET["obj_id"] = $_GET["obj_id"] ? $_GET["obj_id"] : ROOT_FOLDER_ID;
	$parent = $parent ? $parent : 0; // for downward compatibility
	$_GET["parent"] = $_GET["parent"] ? $_GET["parent"] : 0;
	
	// init tree
	$tree = new Tree($_GET["obj_id"],$_GET["parent"],ROOT_FOLDER_ID,1);
}
?>