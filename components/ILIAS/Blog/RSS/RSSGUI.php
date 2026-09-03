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

declare(strict_types=1);

namespace ILIAS\Blog\RSS;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;

/**
 * GUI class for handling RSS feed delivery for blogs.
 */
class RSSGUI
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
    }

    /**
     * Deliver blog as rss feed, currently using permalink from the gui layer,
     * might be moved.
     */
    public function deliver(string $a_wsp_id): void
    {
        $ilSetting = $this->domain->settings();

        if (!$ilSetting->get('enable_global_profiles')) {
            return;
        }

        // #10827
        if (!str_ends_with($a_wsp_id, "_cll")) {
            $wsp_id = new \ilWorkspaceTree(0);
            $obj_id = $wsp_id->lookupObjectId((int) $a_wsp_id);
            $pl = $this->gui->permanentLink(0, (int) $a_wsp_id);
        } else {
            $a_wsp_id = substr($a_wsp_id, 0, -4);
            $obj_id = \ilObject::_lookupObjId((int) $a_wsp_id);
            $pl = $this->gui->permanentLink((int) $a_wsp_id);
        }
        if (!$obj_id) {
            return;
        }

        $blog_settings = $this->domain->blogSettings()
            ->getByObjId($obj_id);
        if (!$blog_settings?->getRSS()) {
            return;
        }

        $blog = new \ilObjBlog($obj_id, false);
        $feed = new \ilFeedWriter();

        $url = $pl->getPermanentLink();
        $url = str_replace("&", "&amp;", $url);

        // #11870
        $feed->setChannelTitle(str_replace("&", "&amp;", $blog->getTitle()));
        $feed->setChannelDescription(str_replace("&", "&amp;", $blog->getDescription()));
        $feed->setChannelLink($url);

        foreach ($this->domain->posting()->getAllPostings($obj_id) as $item) {
            $id = $item->getId();

            // only published items
            $is_active = \ilBlogPosting::_lookupActive($id, "blp");
            if (!$is_active) {
                continue;
            }

            // #16434
            $snippet = strip_tags($this->gui->posting()->getSnippet($id), "<br><div><p>");
            $snippet = str_replace("&", "&amp;", $snippet);
            $snippet = "<![CDATA[" . $snippet . "]]>";

            $url = $pl->getPermanentLink((int) $id);
            $url = str_replace("&", "&amp;", $url);

            $feed_item = new \ilFeedItem();
            $feed_item->setTitle(str_replace("&", "&amp;", $item->getTitle())); // #16022
            $feed_item->setDate($item->getCreated()->get(IL_CAL_DATETIME));
            $feed_item->setDescription($snippet);
            $feed_item->setLink($url);
            $feed_item->setAbout($url);
            $feed->addItem($feed_item);
        }

        $feed->showFeed();
        exit();
    }
}
