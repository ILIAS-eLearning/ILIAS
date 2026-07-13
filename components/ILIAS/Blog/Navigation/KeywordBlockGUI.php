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

namespace ILIAS\Blog\Navigation;

use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\Posting\Posting;
use ILIAS\Blog\Navigation\Link\LinkBuilder;

class KeywordBlockGUI
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected LinkBuilder $link_builder
    ) {
    }

    public function render(
        bool $show_inactive = false,
        int $blpg = 0
    ): string {
        $keywords = $this->getKeywords($show_inactive, $blpg);
        if ($keywords) {
            $wtpl = new \ilTemplate("tpl.blog_list_navigation_keywords.html", true, true, "components/ILIAS/Blog");

            $max = max($keywords);

            $wtpl->setCurrentBlock("keyword");
            foreach ($keywords as $keyword => $counter) {
                $url = $this->link_builder->forKeyword($keyword);

                $wtpl->setVariable("TXT_KEYWORD", (string) $keyword);
                $wtpl->setVariable("CLASS_KEYWORD", \ilTagging::getRelevanceClass((int) $counter, (int) $max));
                $wtpl->setVariable("URL_KEYWORD", $url);
                $wtpl->parseCurrentBlock();
            }

            return $wtpl->get();
        }
        return "";
    }

    protected function getKeywords(
        bool $show_inactive,
        ?int $posting_id = null
    ): array {
        $keywords = array();
        $posting_manager = $this->domain->posting();
        $obj_id = \ilObject::_lookupObjId($this->gui->standardRequest()->getRefId());

        if ($posting_id) {
            foreach ($posting_manager->getKeywords($obj_id, $posting_id) as $keyword) {
                if (isset($keywords[$keyword])) {
                    $keywords[$keyword]++;
                } else {
                    $keywords[$keyword] = 1;
                }
            }
        } else {
            $posting_list = $this->domain->postingList(
                $obj_id,
                $this->domain->blogSettings()->getByObjId($obj_id),
                $show_inactive
            );
            $all_items = $posting_list->getPostingsGroupedByMonth();
            foreach ($all_items as $month => $month_items) {
                foreach ($month_items as $item) {
                    $item_id = $item->getId();
                    foreach ($posting_manager->getKeywords($obj_id, $item_id) as $keyword) {
                        if (isset($keywords[$keyword])) {
                            $keywords[$keyword]++;
                        } else {
                            $keywords[$keyword] = 1;
                        }
                    }
                }
            }
        }

        $tmp = array();
        foreach ($keywords as $keyword => $counter) {
            $tmp[] = array("keyword" => $keyword, "counter" => $counter);
        }
        $tmp = \ilArrayUtil::sortArray($tmp, "keyword", "ASC");

        $keywords = array();
        foreach ($tmp as $item) {
            $keywords[(string) $item["keyword"]] = $item["counter"];
        }
        return $keywords;
    }
}
