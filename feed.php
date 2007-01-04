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

function prep_string($a_str)
{
	$a_str = str_replace("&", "&amp;", $a_str);
	$a_str = str_replace("<", "&lt;", $a_str);
	$a_str = str_replace(">", "&gt;", $a_str);
	return $a_str;
}

// this should bring us all session data of the desired
// client
if (isset($_GET["client_id"]))
{
	setcookie("ilClientId",$_GET["client_id"]);
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

require_once("Services/Init/classes/class.ilInitialisation.php");
$ilInit = new ilInitialisation();
$GLOBALS['ilInit'] =& $ilInit;
$ilInit->initFeed();

$hash = ilObjUser::_lookupFeedHash($_GET["user_id"]);

if ($_GET["hash"] == $hash)
{
	include_once("./Services/News/classes/class.ilNewsItem.php");
	include_once("./Services/Feeds/classes/class.ilFeedItem.php");
	include_once("./Services/Feeds/classes/class.ilFeedWriter.php");
	
	$writer = new ilFeedWriter();
	$items = ilNewsItem::_getNewsItemsOfUser($_GET["user_id"]);
	$writer->setChannelTitle("ILIAS Channel Title");
	$writer->setChannelAbout(ILIAS_HTTP_PATH);
	$writer->setChannelLink(ILIAS_HTTP_PATH);
	$writer->setChannelDescription("ILIAS Channel Description");
	$i = 0;
	foreach($items as $item)
	{
		$i++;
		$feed_item = new ilFeedItem();
		$feed_item->setTitle(prep_string($item["title"]));
		$feed_item->setDescription(prep_string($item["content"]));
		$feed_item->setLink(ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
			"&amp;target=".$item["context_obj_type"]."_".$item["ref_id"]);
		$feed_item->setAbout(ILIAS_HTTP_PATH."/feed".$item["id"]);
		$writer->addItem($feed_item);
	}
	
	echo $writer->getFeed();
	//echo htmlentities($writer->getFeed());
}

?>
