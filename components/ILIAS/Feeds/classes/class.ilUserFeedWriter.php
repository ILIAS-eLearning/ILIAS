<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Feed writer for personal user feeds.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserFeedWriter extends ilFeedWriter
{
    protected ilSetting $settings;
    protected ilLanguage $lng;

    public function __construct(
        int $a_user_id,
        string $a_hash,
        bool $privFeed = false
    ) {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->lng = $DIC->language();
        $ilSetting = $DIC->settings();

        parent::__construct();

        if ($a_user_id == "" || $a_hash == "") {
            return;
        }

        $news_set = new ilSetting("news");
        if (!$news_set->get("enable_rss_for_internal")) {
            return;
        }

        $hash = ilObjUser::_lookupFeedHash($a_user_id);

        $rss_period = ilNewsItem::_lookupRSSPeriod();

        if ($a_hash == $hash) {
            if ($privFeed) {
                //ilNewsItem::setPrivateFeedId($a_user_id);
                $items = ilNewsItem::_getNewsItemsOfUser($a_user_id, false, true, $rss_period);
            } else {
                $items = ilNewsItem::_getNewsItemsOfUser($a_user_id, true, true, $rss_period);
            }

            if ($ilSetting->get('short_inst_name') != "") {
                $this->setChannelTitle($ilSetting->get('short_inst_name'));
            } else {
                $this->setChannelTitle("ILIAS");
            }

            $this->setChannelAbout(ILIAS_HTTP_PATH);
            $this->setChannelLink(ILIAS_HTTP_PATH);
            //$this->setChannelDescription("ILIAS Channel Description");
            foreach ($items as $item) {
                $obj_id = ilObject::_lookupObjId($item["ref_id"]);
                $obj_type = ilObject::_lookupType($obj_id);
                $obj_title = ilObject::_lookupTitle($obj_id);

                // not nice, to do: general solution
                if ($obj_type == "mcst") {
                    if (!ilObjMediaCastAccess::_lookupOnline($obj_id)) {
                        continue;
                    }
                }

                $feed_item = new ilFeedItem();
                $title = ilNewsItem::determineNewsTitle(
                    $item["context_obj_type"],
                    $item["title"],
                    $item["content_is_lang_var"],
                    $item["agg_ref_id"],
                    $item["aggregation"]
                );

                // path
                $loc = $this->getContextPath($item["ref_id"]);

                // title
                if ($news_set->get("rss_title_format") == "news_obj") {
                    $feed_item->setTitle($this->prepareStr(str_replace("<br />", " ", $title)) .
                        " (" . $this->prepareStr($loc) . " " . $this->prepareStr($obj_title) .
                        ")");
                } else {
                    $feed_item->setTitle($this->prepareStr($loc) . " " . $this->prepareStr($obj_title) .
                        ": " . $this->prepareStr(str_replace("<br />", " ", $title)));
                }

                // description
                $content = $this->prepareStr(nl2br(
                    ilNewsItem::determineNewsContent(
                        $item["context_obj_type"],
                        $item["content"],
                        $item["content_text_is_lang_var"]
                    )
                ));
                $feed_item->setDescription($content);

                // lm page hack, not nice
                if ($item["context_obj_type"] == "lm" && $item["context_sub_obj_type"] == "pg"
                    && $item["context_sub_obj_id"] > 0) {
                    $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                        "&amp;target=pg_" . $item["context_sub_obj_id"] . "_" . $item["ref_id"]);
                } elseif ($item["context_obj_type"] == "wiki" && $item["context_sub_obj_type"] == "wpg"
                    && $item["context_sub_obj_id"] > 0) {
                    $wptitle = ilWikiPage::lookupTitle($item["context_sub_obj_id"]);
                    $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                        "&amp;target=" . $item["context_obj_type"] . "_" . $item["ref_id"] . "_" . urlencode($wptitle)); // #14629
                } elseif ($item["context_obj_type"] == "frm" && $item["context_sub_obj_type"] == "pos"
                    && $item["context_sub_obj_id"] > 0) {
                    // frm hack, not nice
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
                }
                $feed_item->setAbout($feed_item->getLink() . "&amp;il_about_feed=" . $item["id"]);
                $feed_item->setDate($item["creation_date"]);
                $this->addItem($feed_item);
            }
        }
    }
}
