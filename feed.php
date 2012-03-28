<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* News feed script.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/


// this should bring us all session data of the desired
// client
if (isset($_GET["client_id"]))
{
	$cookie_domain = $_SERVER['SERVER_NAME'];
	$cookie_path = dirname( $_SERVER['PHP_SELF'] );

	/* if ilias is called directly within the docroot $cookie_path
	is set to '/' expecting on servers running under windows..
	here it is set to '\'.
	in both cases a further '/' won't be appended due to the following regex
	*/
	$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";
		
	if($cookie_path == "\\") $cookie_path = '/';
	
	$cookie_domain = ''; // Temporary Fix
	
	setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, $cookie_domain);
	
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_RSS);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

if ($_GET["user_id"] != "")
{
	include_once("./Services/Feeds/classes/class.ilUserFeedWriter.php");
	$writer = new ilUserFeedWriter($_GET["user_id"], $_GET["hash"]);
	$writer->showFeed();
}
else if ($_GET["ref_id"] != "")
{
	include_once("./Services/Feeds/classes/class.ilObjectFeedWriter.php");
	$writer = new ilObjectFeedWriter($_GET["ref_id"], false, $_GET["purpose"]);
	$writer->showFeed();
}
else if ($_GET["blog_id"] != "")
{
	include_once("Modules/Blog/classes/class.ilObjBlog.php");
	ilObjBlog::deliverRSS($_GET["blog_id"]);
}
?>
