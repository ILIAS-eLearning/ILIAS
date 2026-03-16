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

use ILIAS\News\Data\NewsCriteria;
use ILIAS\News\Data\NewsItem;

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

        if ($a_hash == $hash) {
            $rss_period = ilNewsItem::_lookupRSSPeriod();
            $items = $DIC->news()->internal()->domain()->collection()->getNewsForUser(
                new ilObjUser($a_user_id),
                new NewsCriteria(period: $rss_period, only_public: !$privFeed),
                false
            );

            if ($ilSetting->get('short_inst_name') != "") {
                $this->setChannelTitle($ilSetting->get('short_inst_name'));
            } else {
                $this->setChannelTitle("ILIAS");
            }

            $this->setChannelAbout(ILIAS_HTTP_PATH);
            $this->setChannelLink(ILIAS_HTTP_PATH);
            //$this->setChannelDescription("ILIAS Channel Description");

            /** @var NewsItem $item */
            foreach ($items as $item) {
                $obj_title = ilObject::_lookupTitle($item->getContextObjId());

                // not nice, to do: general solution
                if ($item->getContextObjType() === 'mcst') {
                    if (!ilObjMediaCastAccess::_lookupOnline($item->getContextObjId())) {
                        continue;
                    }
                }

                $feed_item = new ilFeedItem();
                $title = ilNewsItem::determineNewsTitle(
                    $item->getContextObjType(),
                    $item->getTitle(),
                    $item->isContentIsLangVar(),
                    0,
                    []
                );

                // path
                $loc = $this->getContextPath($item->getContextRefId());

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
                        $item->getContextObjType(),
                        $item->getContent(),
                        $item->isContentTextIsLangVar(),
                    )
                ));
                $feed_item->setDescription($content);

                // lm page hack, not nice
                if ($item->getContextObjType() == "lm" && $item->getContextSubObjType() == "pg"
                    && $item->getContextObjId() > 0) {
                    $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                        "&amp;target=pg_" . $item->getContextSubObjId() . "_" . $item->getContextRefId());
                } elseif ($item->getContextObjType() == "wiki" && $item->getContextSubObjType() == "wpg"
                    && $item->getContextSubObjId() > 0) {
                    $wptitle = ilWikiPage::lookupTitle($item->getContextSubObjId());
                    $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                        "&amp;target=" . $item->getContextObjType() . "_" . $item->getContextRefId() . "_" . urlencode($wptitle)); // #14629
                } elseif ($item->getContextObjType() == "frm" && $item->getContextSubObjType() == "pos"
                    && $item->getContextSubObjId() > 0) {
                    // frm hack, not nice
                    $thread_id = ilObjForumAccess::_getThreadForPosting($item->getContextSubObjId());
                    if ($thread_id > 0) {
                        $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                            "&amp;target=" . $item->getContextObjType() . "_" . $item->getContextRefId() . "_" . $thread_id . "_" . $item->getContextSubObjId());
                    } else {
                        $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                            "&amp;target=" . $item->getContextObjType() . "_" . $item->getContextRefId());
                    }
                } else {
                    $feed_item->setLink(ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID .
                        "&amp;target=" . $item->getContextObjType() . "_" . $item->getContextRefId());
                }
                $feed_item->setAbout($feed_item->getLink() . "&amp;il_about_feed=" . $item->getId());
                $feed_item->setDate($item->getCreationDate()->format("Y-m-d H:i:s"));
                $this->addItem($feed_item);
            }
        }
    }
}
