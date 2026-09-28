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

namespace ILIAS\Blog\Keywords;

use ILIAS\Blog\InternalDomainService;

class KeywordManager
{
    protected \ILIAS\Blog\Posting\PostingManager $posting_manager;

    public function __construct(
        protected InternalDomainService $domain
    ) {
        $this->posting_manager = $this->domain->posting();
    }

    public function getKeywords(
        int $blog_id,
        bool $a_show_inactive,
        ?int $a_posting_id = null
    ): array {

        $posting_list = $this->domain->postingList($blog_id);
        $month_items = $posting_list->getPostingsGroupedByMonth();

        $keywords = array();
        if ($a_posting_id) {
            foreach ($this->posting_manager->getKeywords($blog_id, $a_posting_id) as $keyword) {
                if (isset($keywords[$keyword])) {
                    $keywords[$keyword]++;
                } else {
                    $keywords[$keyword] = 1;
                }
            }
        } else {
            foreach ($month_items as $month => $items) {
                foreach ($items as $item) {
                    /** @var \ILIAS\Blog\Posting\Posting $item */
                    $item_id = $item->getId();
                    if ($a_show_inactive || \ilBlogPosting::_lookupActive($item_id, "blp")) {
                        foreach ($this->posting_manager->getKeywords($blog_id, $item_id) as $keyword) {
                            if (isset($keywords[$keyword])) {
                                $keywords[$keyword]++;
                            } else {
                                $keywords[$keyword] = 1;
                            }
                        }
                    }
                }
            }
        }

        // #15881
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
