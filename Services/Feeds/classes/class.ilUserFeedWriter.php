<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/classes/class.ilNewsItem.php");
include_once("./Services/Feeds/classes/class.ilFeedItem.php");
include_once("./Services/Feeds/classes/class.ilFeedWriter.php");

/** @defgroup ServicesFeeds Services/Feeds
 */

/**
* Feed writer for personal user feeds.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesFeeds
*/
class ilUserFeedWriter extends ilFeedWriter
{
	
	function ilUserFeedWriter($a_user_id, $a_hash, $privFeed = false)
	{
		global $ilSetting, $lng;

		parent::ilFeedWriter();
		
		//$lng->loadLanguageModule("news");
		
		if ($a_user_id == "" || $a_hash == "")
		{
			return;
		}
		
		$news_set = new ilSetting("news");
		if (!$news_set->get("enable_rss_for_internal"))
		{
			return;
		}

		include_once "Services/User/classes/class.ilObjUser.php";
		$hash = ilObjUser::_lookupFeedHash($a_user_id);
		
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$rss_period = ilNewsItem::_lookupRSSPeriod();

		if ($a_hash == $hash)
		{
			if ($privFeed) 
			{
				//ilNewsItem::setPrivateFeedId($a_user_id);
				$items = ilNewsItem::_getNewsItemsOfUser($a_user_id, false, true, $rss_period);
			}
			else
			{
				$items = ilNewsItem::_getNewsItemsOfUser($a_user_id, true, true, $rss_period);
			}
			
			if ($ilSetting->get('short_inst_name') != "")
			{
				$this->setChannelTitle($ilSetting->get('short_inst_name'));
			}
			else
			{
				$this->setChannelTitle("ILIAS");
			}

			$this->setChannelAbout(ILIAS_HTTP_PATH);
			$this->setChannelLink(ILIAS_HTTP_PATH);
			//$this->setChannelDescription("ILIAS Channel Description");
			$i = 0;
			foreach($items as $item)
			{
				$obj_id = ilObject::_lookupObjId($item["ref_id"]);
				$obj_type = ilObject::_lookupType($obj_id);
				$obj_title = ilObject::_lookupTitle($obj_id);

				// not nice, to do: general solution
				if ($obj_type == "mcst")
				{
					include_once("./Modules/MediaCast/classes/class.ilObjMediaCastAccess.php");
					if (!ilObjMediaCastAccess::_lookupOnline($obj_id))
					{
						continue;
					}
				}

				$i++;
				$feed_item = new ilFeedItem();
				$title = ilNewsItem::determineNewsTitle
					($item["context_obj_type"], $item["title"], $item["content_is_lang_var"],
					$item["agg_ref_id"], $item["aggregation"]);

				// path
				$loc = $this->getContextPath($item["ref_id"]);
				
				// title
				if ($news_set->get("rss_title_format") == "news_obj")
				{
					$feed_item->setTitle($this->prepareStr(str_replace("<br />", " ", $title)).
						" (".$this->prepareStr($loc)." ".$this->prepareStr($obj_title).
						")");
				}
				else
				{
					$feed_item->setTitle($this->prepareStr($loc)." ".$this->prepareStr($obj_title).
						": ".$this->prepareStr(str_replace("<br />", " ", $title)));
				}
								
				// description
				$content = $this->prepareStr(nl2br(
					ilNewsItem::determineNewsContent($item["context_obj_type"], $item["content"], $item["content_text_is_lang_var"])));
				$feed_item->setDescription($content);

				// lm page hack, not nice
				if (in_array($item["context_obj_type"], array("dbk", "lm")) && $item["context_sub_obj_type"] == "pg"
					&& $item["context_sub_obj_id"] > 0)
				{
					$feed_item->setLink(ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
						"&amp;target=pg_".$item["context_sub_obj_id"]."_".$item["ref_id"]);
				}
				else if ($item["context_obj_type"] == "wiki" && $item["context_sub_obj_type"] == "wpg"
					&& $item["context_sub_obj_id"] > 0)
				{
					include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
					$wptitle = ilWikiPage::lookupTitle($item["context_sub_obj_id"]);
					$feed_item->setLink(ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
						"&amp;target=".$item["context_obj_type"]."_".$item["ref_id"]."_".urlencode($wptitle)); // #14629
				}
				else if (in_array($item["context_obj_type"], array("frm")) && $item["context_sub_obj_type"] == "pos"
					&& $item["context_sub_obj_id"] > 0)
				{
					// frm hack, not nice
					include_once("./Modules/Forum/classes/class.ilObjForumAccess.php");
					$thread_id = ilObjForumAccess::_getThreadForPosting($item["context_sub_obj_id"]);
					if ($thread_id > 0)
					{
						$feed_item->setLink(ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
							"&amp;target=".$item["context_obj_type"]."_".$item["ref_id"]."_".$thread_id."_".$item["context_sub_obj_id"]);
					}
					else
					{
						$feed_item->setLink(ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
							"&amp;target=".$item["context_obj_type"]."_".$item["ref_id"]);
					}
				}
				else
				{
					$feed_item->setLink(ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
						"&amp;target=".$item["context_obj_type"]."_".$item["ref_id"]);
				}
				$feed_item->setAbout($feed_item->getLink()."&amp;il_about_feed=".$item["id"]);
				$feed_item->setDate($item["creation_date"]);
				$this->addItem($feed_item);
			}
		}
	}
}
?>
