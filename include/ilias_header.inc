<?php

/**
 * header include for all ilias files
 * 
 * @author Stefan Meyer <smeyer@databay.de>
 * @author Sascha Hofmann <shofmann@databay.de>
 *
 * @version $Id$
 * @package ilias-core
 */

//include files from PEAR
require_once "PEAR.php";
require_once "DB.php";
require_once "Auth/Auth.php";

//include classes and function libraries
require_once "classes/class.template.php";
require_once "classes/class.ilias.php";
include_once "function.library.php";
include_once "classes/class.util.php";
include_once "classes/class.User.php";
include_once "classes/class.group.php";
include_once "classes/class.role.php";
include_once "classes/class.resource.php";
include_once "classes/class.groupmember.php";
include_once "classes/class.auth.php";
include_once "classes/class.format.php";
include_once "classes/class.prevnextnavbar.php";
include_once "classes/class.perm.php";
include_once "classes/class.tree.php";
include_once "classes/class.Language.php";
include_once("./classes/class.Log.php");
include("./classes/class.UserMail.php");

//include role based access
include_once "classes/class.rbacAdmin.php";
include_once "classes/class.rbacSystem.php";
include_once "classes/class.rbacReview.php";
include_once "classes/class.rbacAdminH.php";
include_once "classes/class.rbacSystemH.php";
include_once "classes/class.rbacReviewH.php";

// include error_handling
include_once "classes/class.ErrorHandling.php";

session_start();

// set uri for error handling
if (substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "error.php")
{
	$_SESSION["referer"] = $_SERVER["HTTP_REFERER"];
	if($_POST["cmd"])
	{
		$_SESSION["referer"] .= "&cmd=".$_POST["cmd"];
	}
}

// load main class
$ilias =& new ILIAS;
//authenticate
$ilias->auth->start();

$log = new Log("ilias.log");

//check for logged in user
if (substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "login.php")
{
	//if not authenticated display login screen
	if (!$ilias->auth->getAuth())
	{
		header("location: sessionexpired.php?from=".urlencode($_SERVER['REQUEST_URI']));
		exit();
	}

//get user id
	if (empty($AccountId))
	{
		$res = $ilias->db->query("SELECT usr_id FROM user_data WHERE login='".$ilias->auth->getUsername()."'");
	
		//check for error
		if (DB::isError($res))
		{
			die("<b>".$res->getMessage()."</b><br>Script: ".__FILE__."<br>Line: ".__LINE__);
		}
	
		//query has got a result
		if ($res->numRows() > 0)
		{
			$data = $res->fetchRow();
			$AccountId = $data[0];
			$_SESSION["AccountId"] = $AccountId;
		}
	}
// assigned roles are stored in $_SESSION["RoleId"]
	$rbacreview = new RbacReviewH($ilias->db);
	$_SESSION["RoleId"] = $rbacreview->assignedRoles($_SESSION["AccountId"]);

	$rbacsystem = new RbacSystemH($ilias->db);

// instantiate and init user
	$ilias->account =& new User($ilias->db,$AccountId);
	$lng = new Language($ilias->account->data["language"]);

}
?>