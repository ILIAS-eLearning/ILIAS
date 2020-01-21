<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public function __construct($a_ref_id, $a_userid = false, $a_purpose = false)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->lng = $DIC->language();
        $ilSetting = $DIC->settings();
        $lng = $DIC->language();
        
        parent::__construct();
        
        if ($a_ref_id <= 0) {
            return;
        }
        
        include_once("./Services/Block/classes/class.ilBlockSetting.php");
        $news_set = new ilSetting("news");
        if (!$news_set->get("enable_rss_for_internal")) {
            return;
        }
        $obj_id = ilObject::_lookupObjId($a_ref_id);
        $obj_type = ilObject::_lookupType($obj_id);
        $obj_title = ilObject::_lookupTitle($obj_id);

        if (!ilBlockSetting::_lookup("news", "public_feed", 0, $obj_id)) {
            return;
        }

        // path
        $loc = $this->getContextPath($a_ref_id);

        if ($ilSetting->get('short_inst_name') != "") {
            $this->setChannelTitle($ilSetting->get('short_inst_name') . " - " .
                $this->prepareStr($loc . " " . $obj_title));
        } else {
            $this->setChannelTitle("ILIAS" . " - " .
                $this->prepareStr($loc . " " . $obj_title . ($a_purpose ? " - " . $a_purpose : "")));
        }
        $this->setChannelAbout(ILIAS_HTTP_PATH);
        $this->setChannelLink(ILIAS_HTTP_PATH);
        // not nice, to do: general solution
        if ($obj_type == "mcst") {
            include_once("./Modules/MediaCast/classes/class.ilObjMediaCastAccess.php");
            
            if (!ilObjMediaCastAccess::_lookupOnline($obj_id)) {
                $lng->loadLanguageModule("mcst");
                
                $feed_item = new ilFeedItem();
                $feed_item->setTitle($lng->txt("mcst_media_cast_not_online"));
                $feed_item->setDescription($lng->txt("mcst_media_cast_not_online_text"));
                $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                    "&amp;target=" . $item["context_obj_type"]);
                $this->addItem($feed_item);
                return;
            }
        }


        $rss_period = ilNewsItem::_lookupRSSPeriod();

        ilNewsItem::setPrivateFeedId($a_userid);
        $news_item = new ilNewsItem();
        $news_item->setContextObjId($obj_id);
        $news_item->setContextObjType($obj_type);
        $items = $news_item->getNewsForRefId($a_ref_id, true, false, $rss_period, true);

        if ($a_purpose) {
            include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
        }
        
        $i = 0;
        foreach ($items as $item) {
            $i++;
            
            if ($a_purpose != false && $obj_type == "mcst") {
                $mob = ilMediaItem::_getMediaItemsOfMObId($item["mob_id"], $a_purpose);

                if ($mob == false) {
                    continue;
                }
            }

            $obj_title = ilObject::_lookupTitle($item["context_obj_id"]);
            
            $feed_item = new ilFeedItem();
            
            $title = ilNewsItem::determineNewsTitle(
                $item["context_obj_type"],
                $item["title"],
                $item["content_is_lang_var"],
                $item["agg_ref_id"],
                $item["aggregation"]
            );

            $loc = "";

            if ($news_set->get("rss_title_format") == "news_obj") {
                $sep = (trim($this->prepareStr($loc)) == "")
                    ? ""
                    : " ";
                $feed_item->setTitle($this->prepareStr($title) . " (" . $this->prepareStr($loc) . $sep . $this->prepareStr($obj_title) .
                    ")");
            } else {
                $feed_item->setTitle($this->prepareStr($loc) . " " . $this->prepareStr($obj_title) .
                    ": " . $this->prepareStr($title));
            }
            $feed_item->setDescription($this->prepareStr(nl2br(
                ilNewsItem::determineNewsContent($item["context_obj_type"], $item["content"], $item["content_text_is_lang_var"])
            )));

            // lm hack, not nice
            if (in_array($item["context_obj_type"], array("lm")) && $item["context_sub_obj_type"] == "pg"
                && $item["context_sub_obj_id"] > 0) {
                $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                    "&amp;target=pg_" . $item["context_sub_obj_id"] . "_" . $item["ref_id"]);
            } elseif ($item["context_obj_type"] == "wiki" && $item["context_sub_obj_type"] == "wpg"
                && $item["context_sub_obj_id"] > 0) {
                include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
                include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");
                $wptitle = ilWikiUtil::makeUrlTitle(ilWikiPage::lookupTitle($item["context_sub_obj_id"]));
                $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                    "&amp;target=" . $item["context_obj_type"] . "_" . $item["ref_id"] . "_" . $wptitle);
            } elseif (in_array($item["context_obj_type"], array("frm")) && $item["context_sub_obj_type"] == "pos"
                && $item["context_sub_obj_id"] > 0) {
                // frm hack, not nice
                include_once("./Modules/Forum/classes/class.ilObjForumAccess.php");
                $thread_id = ilObjForumAccess::_getThreadForPosting($item["context_sub_obj_id"]);
                if ($thread_id > 0) {
                    $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                        "&amp;target=" . $item["context_obj_type"] . "_" . $item["ref_id"] . "_" . $thread_id . "_" . $item["context_sub_obj_id"]);
                } else {
                    $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                        "&amp;target=" . $item["context_obj_type"] . "_" . $item["ref_id"]);
                }
            } else {
                $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                    "&amp;target=" . $item["context_obj_type"] . "_" . $item["ref_id"]);
                //echo "<br>".ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
//					"&amp;target=".$item["context_obj_type"]."_".$item["ref_id"];
            }
    
            $feed_item->setAbout($feed_item->getLink() . "&amp;il_about_feed=" . $item["id"]);
            $feed_item->setDate($item["creation_date"]);
            
            // Enclosure
            if ($item["content_type"] == NEWS_AUDIO &&
                $item["mob_id"] > 0 && ilObject::_exists($item["mob_id"])) {
                $go_on = true;
                if ($obj_type == "mcst") {
                    include_once("./Modules/MediaCast/classes/class.ilObjMediaCastAccess.php");
                    
                    if (!ilObjMediaCastAccess::_lookupPublicFiles($obj_id)) {
                        $go_on = false;
                    }
                }
                
                if ($go_on) {
                    include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
                    $url = ilObjMediaObject::_lookupItemPath($item["mob_id"], true, true, $mob["purpose"]);
                    $file = ilObjMediaObject::_lookupItemPath($item["mob_id"], false, false, $mob["purpose"]);
                    if (is_file($file)) {
                        $size = filesize($file);
                    }
                    $feed_item->setEnclosureUrl($url);
                    $feed_item->setEnclosureType((isset($mob["format"]))?$mob["format"]:"audio/mpeg");
                    $feed_item->setEnclosureLength($size);
                }
            }
            $this->addItem($feed_item);
        }
    }
}
