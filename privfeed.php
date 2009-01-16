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
	$cookie_path = dirname( $_SERVER['PHP_SELF'] ).'/';
	
	$cookie_domain = ''; // Temporary Fix
	
	setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, $cookie_domain);
	
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

require_once("Services/Init/classes/class.ilInitialisation.php");
$ilInit = new ilInitialisation();
$GLOBALS['ilInit'] =& $ilInit;
$ilInit->initFeed();
$ilInit->initAccessHandling();

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
