<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once("./Services/News/classes/class.ilNewsItem.php");
include_once("./Services/Feeds/classes/class.ilFeedItem.php");
include_once("./Services/Feeds/classes/class.ilFeedWriter.php");

/** @defgroup ServicesFeeds Services/Feeds
 */

/**
* Feed writer for objects.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesFeeds
*/
class ilObjectFeedWriter extends ilFeedWriter
{
	function ilObjectFeedWriter($a_ref_id)
	{
		global $ilAccess, $ilSetting, $lng;
		
		parent::ilFeedWriter();
		
		if ($a_ref_id <= 0)
		{
			return;
		}
		
		include_once("./Services/Block/classes/class.ilBlockSetting.php");
		$news_set = new ilSetting("news");
		if (!$news_set->get("enable_rss_for_internal"))
		{
			return;
		}
		$obj_id = ilObject::_lookupObjId($a_ref_id);
		$obj_type = ilObject::_lookupType($obj_id);
		$obj_title = ilObject::_lookupTitle($obj_id);

		if (!ilBlockSetting::_lookup("news", "public_feed", 0, $obj_id))
		{
			return;
		}

		// not nice, to do: general solution
		if ($obj_type == "mcst")
		{
			include_once("./Modules/MediaCast/classes/class.ilObjMediaCastAccess.php");
			
			if (!ilObjMediaCastAccess::_lookupOnline($obj_id))
			{
				return;
			}
		}

		$news_item = new ilNewsItem();
		$news_item->setContextObjId($obj_id);
		$news_item->setContextObjType($obj_type);
		$items = $news_item->getNewsForRefId($a_ref_id, true);
		if ($ilSetting->get('short_inst_name') != "")
		{
			$this->setChannelTitle($ilSetting->get('short_inst_name')." - ".$obj_title);
		}
		else
		{
			$this->setChannelTitle("ILIAS"." - ".$obj_title);
		}
		$this->setChannelAbout(ILIAS_HTTP_PATH);
		$this->setChannelLink(ILIAS_HTTP_PATH);
		//$this->setChannelDescription("ILIAS Channel Description");
		$i = 0;
		foreach($items as $item)
		{
			$i++;
			
			$obj_title = ilObject::_lookupTitle($item["context_obj_id"]);
			
			$feed_item = new ilFeedItem();
			if ($item["content_is_lang_var"])
			{
				$feed_item->setTitle($obj_title.": ".$this->prepareStr($lng->txt($item["title"])));
			}
			else
			{
				$feed_item->setTitle($obj_title.": ".$this->prepareStr($item["title"]));
			}
			$feed_item->setDescription($this->prepareStr($item["content"]));
			$feed_item->setLink(ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
				"&amp;target=".$item["context_obj_type"]."_".$item["ref_id"]);
			$feed_item->setAbout($feed_item->getLink()."&amp;il_about_feed=".$item["id"]);
			$feed_item->setDate($item["creation_date"]);
			
			// Enclosure
			if ($item["content_type"] == NEWS_AUDIO &&
				$item["mob_id"] > 0 && ilObject::_exists($item["mob_id"]))
			{
				$go_on = true;
				if ($obj_type == "mcst")
				{
					include_once("./Modules/MediaCast/classes/class.ilObjMediaCastAccess.php");
					
					if (!ilObjMediaCastAccess::_lookupPublicFiles($obj_id))
					{
						$go_on = false;
					}
				}
				
				if ($go_on)
				{
					include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
					$url = ilObjMediaObject::_lookupStandardItemPath($item["mob_id"], true);
					$file = ilObjMediaObject::_lookupStandardItemPath($item["mob_id"], false, false);
					if (is_file($file))
					{
						$size = filesize($file);
					}
					$feed_item->setEnclosureUrl($url);
					$feed_item->setEnclosureType("audio/mpeg");
					$feed_item->setEnclosureLength($size);
				}
			}
			
			$this->addItem($feed_item);
		}
	}
}
?>
