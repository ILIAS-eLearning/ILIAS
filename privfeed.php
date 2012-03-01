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
ilContext::init(ilContext::CONTEXT_RSS_AUTH);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

global $lng, $ilSetting;

$feed_set = new ilSetting("news");


	if (!isset($_SERVER['PHP_AUTH_PW']) || !isset($_SERVER['PHP_AUTH_USER'])) 
	{
		Header("WWW-Authenticate: Basic realm=\"ILIAS Newsfeed\"");
		Header("HTTP/1.0 401 Unauthorized");

		exit;
	}
	else 
	{ 
		if ($_GET["user_id"] != "" && ilObjUser::_getFeedPass($_GET["user_id"]) != "" &&
		   (md5($_SERVER['PHP_AUTH_PW']) == ilObjUser::_getFeedPass($_GET["user_id"]) && 
		    $_SERVER['PHP_AUTH_USER'] == ilObjUser::_lookupLogin($_GET["user_id"]))
		    && $feed_set->get("enable_private_feed"))
		{
			include_once("./Services/Feeds/classes/class.ilUserFeedWriter.php");
			// Third parameter is true for private feed
			$writer = new ilUserFeedWriter($_GET["user_id"], $_GET["hash"], true);
			$writer->showFeed();
		}
		else if ($_GET["ref_id"] != "" && md5($_SERVER['PHP_AUTH_PW']) == ilObjUser::_getFeedPass(ilObjUser::_lookupId($_SERVER['PHP_AUTH_USER'])))
		{

			include_once("./Services/Feeds/classes/class.ilObjectFeedWriter.php");
			// Second parameter is optional to pass on to database-level to get news for logged-in users
			$writer = new ilObjectFeedWriter($_GET["ref_id"], ilObjUser::_lookupId($_SERVER['PHP_AUTH_USER']));
			$writer->showFeed();
		}
		else {
			
			// send appropriate header, if password is wrong, otherwise
			// there is no chance to re-enter it (unless, e.g. the browser is closed)
			if (md5($_SERVER['PHP_AUTH_PW']) != ilObjUser::_getFeedPass(ilObjUser::_lookupId($_SERVER['PHP_AUTH_USER'])))
			{
				Header("WWW-Authenticate: Basic realm=\"ILIAS Newsfeed\"");
				Header("HTTP/1.0 401 Unauthorized");
				exit;
			}
			
			include_once("./Services/Feeds/classes/class.ilFeedItem.php");
			include_once("./Services/Feeds/classes/class.ilFeedWriter.php");

			$blankFeedWriter = new ilFeedWriter();
			$feed_item = new ilFeedItem();
			$lng->loadLanguageModule("news");
			
			if ($ilSetting->get('short_inst_name') != "")
			{
				$blankFeedWriter->setChannelTitle($ilSetting->get('short_inst_name'));
			}
			else
			{
				$blankFeedWriter->setChannelTitle("ILIAS");
			}




			if (!$feed_set->get("enable_private_feed"))
			{
				$blankFeedWriter->setChannelAbout(ILIAS_HTTP_PATH);
				$blankFeedWriter->setChannelLink(ILIAS_HTTP_PATH);			
				// title
				$feed_item->setTitle($lng->txt("priv_feed_no_access_title"));

				// description
				$feed_item->setDescription($lng->txt("priv_feed_no_access_body"));
				$feed_item->setLink(ILIAS_HTTP_PATH);
			}
			else
			{
				$blankFeedWriter->setChannelAbout(ILIAS_HTTP_PATH);
				$blankFeedWriter->setChannelLink(ILIAS_HTTP_PATH);			
				// title
				$feed_item->setTitle($lng->txt("priv_feed_no_auth_title"));

				// description
				$feed_item->setDescription($lng->txt("priv_feed_no_auth_body"));
				$feed_item->setLink(ILIAS_HTTP_PATH);
			}
			$blankFeedWriter->addItem($feed_item);
			$blankFeedWriter->showFeed();
		}
		
	}
?>
